<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$user_id = get_current_user_id();
$user    = wp_get_current_user();

$artists = get_user_meta( $user_id, 'followed_authors', true );
$artists = (array) $artists;

$artists = array_filter( $artists );
$artists = array_unique( $artists );

$baseurl = get_permalink();
$baseurl = trailingslashit( $baseurl );

if ( HP()->array_has_value( $artists ) ) {
	?>
	<div id="fes-vendor-dashboard" class="fes-vendor-dashboard">
		<?php
		if ( HP()->array_has_value( $artists ) ) {
			?>
			<ul class="edd-wish-list followed-authors">
				<?php

				foreach ( $artists as $author_id ) {
					$author = new WP_User( $author_id );

					if ( $author instanceof WP_User ) {
						?>
						<li>
							<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
								<?php echo get_avatar( $author_id ); ?>
							</a>
							<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
								<?php echo $author->display_name; ?>
							</a>

							<div class="follow-button follow following remove" data-author-id="<?php echo $author_id; ?>">
								<?php _e( 'Unfollow', 'pixelify' ); ?>
							</div>
						</li>
						<?php
					}
				}
				?>
			</ul>
			<?php
		}
		?>
	</div>
	<?php
} else {
	?>
	<p class="alert alert-info">
		<?php _e( 'Nothing here yet, how about following some author?', 'pixelify' ); ?>
	</p>
	<?php
}
