<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$user_id = get_current_user_id();

$ppp = Pixelify()->get_posts_per_page();
$ppp *= 3;

$downloads = get_user_meta( $user_id, 'download', true );
$downloads = (array) $downloads;

$downloads = array_filter( $downloads );
$downloads = array_unique( $downloads );

$query = new WP_Query();

if ( HP()->array_has_value( $downloads ) ) {
	$args = array(
		'posts_per_page' => $ppp,
		'paged'          => Pixelify()->get_paged(),
		'post__in'       => $downloads
	);

	$query = new WP_Query( $args );
}

$baseurl = get_the_permalink();

if ( $query->have_posts() ) {
	?>
	<div id="fes-vendor-dashboard" class="fes-vendor-dashboard">
		<table class="your-products tablesaw tablesaw-stack" data-tablesaw-mode="stack">
			<thead>
			<tr>
				<th class="thumbnail-col"></th>
				<th class="title-col"></th>
				<th class="date-col"><?php _ex( 'Added', 'dashboard products column', 'pixelify' ); ?></th>
				<th class="downloads-col"><?php _ex( 'Downloads', 'dashboard products column', 'pixelify' ); ?></th>
				<th class="views-col"><?php _ex( 'Views', 'dashboard products column', 'pixelify' ); ?></th>
				<th class="likes-col"><?php _ex( 'Likes', 'dashboard products column', 'pixelify' ); ?></th>
				<th class="reviews-col"><?php _ex( 'Reviews', 'dashboard products column', 'pixelify' ); ?></th>
				<th class="questions-col"><?php _ex( 'Questions', 'dashboard products column', 'pixelify' ); ?></th>
				<th class="tool-col"></th>
			</tr>
			</thead>
			<tbody>
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$downloads = get_post_meta( get_the_ID(), 'downloads', true );
				$downloads = absint( $downloads );
				$views     = get_post_meta( get_the_ID(), 'views', true );
				$views     = absint( $views );
				$likes     = get_post_meta( get_the_ID(), 'likes', true );
				$likes     = absint( $likes );
				$rating    = get_post_meta( get_the_ID(), 'rating', true );
				$rates     = get_post_meta( get_the_ID(), 'rates', true );

				$rating = floatval( $rating );
				$rating = round( $rating, 1 );
				$rates  = absint( $rates );

				$delete = add_query_arg(
					array(
						'action'  => 'delete-post',
						'post_id' => get_the_ID(),
						'nonce'   => wp_create_nonce()
					),
					$baseurl
				);

				$cqa = array(
					'meta_query' => array(
						array(
							'key'   => 'question',
							'value' => 1,
							'type'  => 'numeric'
						)
					),
					'post_ID'    => get_the_ID()
				);

				$cq = new WP_Comment_Query( $cqa );

				$questions = count( $cq->comments );
				?>
				<tr>
					<td class="fes-product-list-td thumbnail-col">
						<a href="<?php the_permalink(); ?>">
							<div class="thumbnail-container">
								<?php Pixelify()->custom_post_thumbnail( get_the_ID() ); ?>
							</div>
							<!--/thumbnail-container-->
						</a>
					</td>
					<td class="title-td fes-product-list-td title-col">
						<a href="<?php the_permalink(); ?>">
							<h3><?php the_title(); ?></h3>
						</a>

						<div class="archive-single-meta">
							<?php
							ob_start();
							?>
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"
							   class="readmore"><?php the_author(); ?></a>
							<?php
							$author = ob_get_clean();

							ob_start();
							the_category( ', ' );
							$links = ob_get_clean();
							printf( _x( '%s in %s', 'author category', 'pixelify' ), $author, $links );
							?>
						</div>
						<!--/archive-single-meta-->
						<?php
						$license1 = get_post_meta( get_the_ID(), 'license1', true );
						$license2 = get_post_meta( get_the_ID(), 'license2', true );

						if ( 1 == $license1 || 1 == $license2 ) {
							$page = Pixelify()->get_option_post( 'license_page' );
							$html = '';

							if ( 1 == $license1 ) {
								$html = __( '<span>Free for <strong>Personal Use</strong>', 'pixelify' );
							}

							if ( 1 == $license2 ) {
								if ( ! empty( $html ) ) {
									$html .= ' | ';
								}

								$html = __( '<span>Free for <strong>Commercial Use</strong>', 'pixelify' );
							}

							if ( $page instanceof WP_Post ) {
								//$html .= ' | ';
								//$html .= '<a href="' . get_permalink( $page ) . '">' . __( 'License info', 'pixelify' ) . '</a>';
							}
							?>
							<div class="sidebar-license-info">
								<?php echo $html; ?>
							</div>
							<?php
						}
						?>
					</td>
					<td class="fes-product-list-td date-col">
						<span class="tablesaw-cell-label">
							<strong><?php _ex( 'Added', 'dashboard products column', 'pixelify' ); ?></strong>
						</span>
						<span class="tablesaw-cell-content"><?php Pixelify()->the_date( '', null, false ); ?></span>
					</td>
					<td class="fes-product-list-td downloads-col">
						<span class="tablesaw-cell-label">
							<strong><?php _ex( 'Downloads', 'dashboard products column', 'pixelify' ); ?></strong>
						</span>
						<span class="tablesaw-cell-content"><?php echo number_format( $downloads ); ?></span>
					</td>
					<td class="fes-product-list-td views-col">
						<span class="tablesaw-cell-label">
							<strong><?php _ex( 'Views', 'dashboard products column', 'pixelify' ); ?></strong>
						</span>
						<span class="tablesaw-cell-content"><?php echo number_format( $views ); ?></span>
					</td>
					<td class="fes-product-list-td likes-col">
						<span class="tablesaw-cell-label">
							<strong><?php _ex( 'Likes', 'dashboard products column', 'pixelify' ); ?></strong>
						</span>
						<span class="tablesaw-cell-content"><?php echo number_format( $likes ); ?></span>
					</td>
					<td class="fes-product-list-td reviews-col">
						<span class="tablesaw-cell-label">
							<strong><?php _ex( 'Reviews', 'dashboard products column', 'pixelify' ); ?></strong>
						</span>
						<span class="tablesaw-cell-content">
							<strong><?php echo $rating; ?></strong> (<?php echo number_format( $rates ); ?>)
						</span>
					</td>
					<td class="fes-product-list-td questions-col">
						<span class="tablesaw-cell-label">
							<strong><?php _ex( 'Questions', 'dashboard products column', 'pixelify' ); ?></strong>
						</span>
						<span class="tablesaw-cell-content"><?php echo number_format( $questions ); ?></span>
					</td>
					<td class="fes-product-list-td tool-col">
						<?php Pixelify()->post_buttons_tool( null, true, $baseurl ); ?>
					</td>
				</tr>
				<?php
			}

			wp_reset_postdata();
			?>
			</tbody>
		</table>
		<?php Pixelify()->pagination( $query ); ?>
	</div>
	<?php
} else {
	?>
	<p class="alert alert-info">
		<?php _e( 'Nothing here yet!', 'pixelify' ); ?>
	</p>
	<?php
}