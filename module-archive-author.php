<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$author_id    = get_queried_object_id();
$city_country = get_user_meta( $author_id, 'city_country', true );

$class = 'follow-button follow';

if ( is_user_logged_in() ) {
	$user_id = get_current_user_id();

	$followed_authors = get_user_meta( $user_id, 'followed_authors', true );
	$followed_authors = (array) $followed_authors;
	$followed_authors = array_filter( $followed_authors );
	$followed_authors = array_unique( $followed_authors );

	$followed = ( in_array( $author_id, $followed_authors ) ) ? 1 : 0;
} else {
	$followed = 0;
}

$following_text = __( 'Following', 'pixelify' );
$follow_text    = __( 'Follow', 'pixelify' );

$button_text = $follow_text;

if ( 1 == $followed ) {
	$class .= ' following';
	$button_text = $following_text;
}

$followers = get_user_meta( $author_id, 'followers', true );
$followers = absint( $followers );

$downloads = get_user_meta( $author_id, 'downloads', true );
$downloads = absint( $downloads );

$rating = get_user_meta( $author_id, 'rating', true );
$rates  = get_user_meta( $author_id, 'rates', true );

$rating = floatval( $rating );
$rating = round( $rating, 1 );

global $wp_query;
?>
<div class="container">
	<div class="row">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
				<div class="author-page-info artist-info text-center">
					<?php echo get_avatar( $author_id, 115 ); ?>
					<div class="author-info-text">
						<h1><?php the_author(); ?></h1>
						<?php
						if ( ! empty( $city_country ) ) {
							?>
							<span class="author-residence"><?php echo $city_country; ?></span>
							<?php
						}

						echo wpautop( get_user_meta( $author_id, 'description', true ) );
						?>
						<div class="<?php echo $class; ?>" id="follow-button-<?php echo $author_id; ?>"
						     data-author-id="<?php echo $author_id; ?>"
						     data-following="<?php echo $following_text; ?>"
						     data-follow="<?php echo $follow_text; ?>"><?php echo $button_text; ?></div>
						<div class="author-page-info-meta">
							<span>
								<strong>
									<span class="followers-number">
										<span class="follower-count"><?php echo $followers; ?></span>
									</span>
								</strong>&nbsp;<?php _e( 'Followers', 'hocwp-theme' ); ?>
							</span>
							<span><strong><?php echo number_format( $wp_query->found_posts ); ?></strong>&nbsp;<?php _e( 'Items', 'hocwp-theme' ); ?></span>
							<span><strong><?php echo number_format( $downloads ); ?></strong>&nbsp;<?php _e( 'Downloads', 'hocwp-theme' ); ?></span>

							<div class="author-rating">
								<?php
								$args = array(
									'rating' => $rating,
									'type'   => 'rating',
									'number' => 12345
								);

								if ( ! function_exists( 'wp_star_rating' ) ) {
									require ABSPATH . 'wp-admin/includes/template.php';
								}

								wp_star_rating( $args );
								?>
								<span><?php printf( __( '<strong>%s</strong> (<strong>%s</strong> Reviews)', 'hocwp-theme' ), number_format( $rating ), number_format( $rates ) ); ?></span>
							</div>
						</div>
						<!--/author-info-text-->
					</div>
					<!--/author-page-info-meta-->

				</div>
				<div class="archive-post loop-posts">
					<?php
					if ( have_posts() ) {
						?>
						<div class="row">
							<?php
							// Loop with ads
							hocwp_theme_custom_loop_post_with_ads( null, 'archive-post-item post-item', 'post-pixel' );
							?>
							<div class="archive-pagination ajax-pagination col-sm-12">
								<?php HT_Frontend()->pagination(); ?>
							</div>
						</div>
						<?php
					} else {
						hocwp_theme_load_content_none();
					}
					?>
				</div>
			</main>
			<!-- #main -->
		</div>
		<!-- #primary -->
	</div>
</div>