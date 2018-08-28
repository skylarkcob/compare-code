<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$user_id = get_current_user_id();
$user    = wp_get_current_user();

$page = Pixelify()->get_option( 'wish_lists_page' );

$page = get_post( $page );

if ( ! ( $page instanceof WP_Post ) ) {
	if ( current_user_can( 'manage_options' ) ) {
		echo wpautop( sprintf( __( 'Please config <a href="%s">Wish Lists Page</a>!', 'pixelify' ), Pixelify()->get_options_page_url() ) );
	}

	return;
}

$baseurl = get_permalink( $page );
$baseurl = trailingslashit( $baseurl );

$args = array(
	'post_type'      => 'collection',
	'posts_per_page' => - 1,
	'author'    => $user_id,
	'post_status'    => 'private',
	'orderby'        => 'name',
	'order'          => 'asc'
);

$private = new WP_Query( $args );

$args['post_status'] = 'publish';

$public = new WP_Query( $args );
?>
<div id="fes-vendor-dashboard" class="fes-vendor-dashboard">
	<p class="">
		<a href="<?php echo $baseurl; ?>create/" class="edd-wl-button edd-wl-action"
		   title="Create new wish list"><?php _e( 'Create new wish list', 'pixelify' ); ?></a>
	</p>
	<?php
	if ( $private->have_posts() ) {
		?>
		<h3 class="edd-wl-heading"><?php _e( 'Private', 'pixeilfy' ); ?></h3>
		<ul class="edd-wish-list">
			<?php
			while ( $private->have_posts() ) {
				$private->the_post();
				global $post;
				$childs = get_post_meta( get_the_ID(), 'childs', true );

				$childs = (array) $childs;
				$childs = array_filter( $childs );
				$childs = array_unique( $childs );

				$count = count( $childs );
				?>
				<li>
					<a href="<?php the_permalink(); ?>"
					   title="<?php echo $post->post_title; ?>"
					   class="edd-wl-item-title"><?php echo $post->post_title; ?> <span
							class="edd-wl-item-count">(<?php echo number_format( $count ); ?>)</span></a>
					<a class="edd-wl-edit" href="<?php echo get_edit_post_link(); ?>"
					   title="edit"><?php _e( 'Edit', 'pixelify' ); ?></a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}

	if ( $public->have_posts() ) {
		?>
		<h3 class="edd-wl-heading"><?php _e( 'Public', 'pixeilfy' ); ?></h3>
		<ul class="edd-wish-list">
			<?php
			while ( $public->have_posts() ) {
				$public->the_post();
				global $post;
				$childs = get_post_meta( get_the_ID(), 'childs', true );

				$childs = (array) $childs;
				$childs = array_filter( $childs );
				$childs = array_unique( $childs );

				$count = count( $childs );
				?>
				<li>
					<a href="<?php the_permalink(); ?>"
					   title="<?php echo $post->post_title; ?>"
					   class="edd-wl-item-title"><?php echo $post->post_title; ?> <span
							class="edd-wl-item-count">(<?php echo number_format( $count ); ?>)</span></a>
					<a class="edd-wl-edit" href="<?php echo get_edit_post_link(); ?>"
					   title="edit"><?php _e( 'Edit', 'pixelify' ); ?></a>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	?>
</div>