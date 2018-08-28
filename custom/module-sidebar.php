<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( is_page() || ( ! is_single() && ! is_singular() ) ) {
	return;
}

$download = get_post_meta( get_the_ID(), 'download', true );

$rating = get_post_meta( get_the_ID(), 'rating', true );
$rates  = get_post_meta( get_the_ID(), 'rates', true );
$rates  = absint( $rates );

$user_id = get_the_author_meta( 'ID' );
$auth    = new WP_User( $user_id );

$rating = floatval( $rating );
$rating = round( $rating, 1 );
ob_start();
?>
<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"
   class="readmore"><?php the_author(); ?></a>
<?php
$author = ob_get_clean();

ob_start();
the_category( ', ' );
$links = ob_get_clean();

if ( ! function_exists( 'wp_star_rating' ) ) {
	require_once ABSPATH . 'wp-admin/includes/template.php';
}
?>
<div class="primary-sidebar hocwp">
	<div class="star-reviews text-right">
		<?php
		$args = array(
			'rating' => $rating,
			'type'   => 'rating',
			'number' => 12345
		);
		wp_star_rating( $args );
		?>
		<div class="rating-number">
			<?php
			if ( 0 == $rates ) {
				echo '<span>' . __( 'no reviews yet', 'pixelify' ) . '</span>';
			} else {
				if ( ! empty( $rating ) ) {
					echo '<strong>' . $rating . '</strong>';
				}
				?>
				<span>(<?php printf( __( '%d Reviews', 'pixelify' ), $rates ); ?>)</span>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	if ( ! empty( $download ) ) {
		include Pixelify()->get_basedir() . '/custom/module-download-button.php';
	}

	$more_link = get_post_meta( get_the_ID(), 'more_link', true );

	if ( ! empty( $more_link ) ) {
		?>
		<div class="media-content">
			<div class="media-info">
				<div class="details visible">
					<div class="media-download">
						<?php
						$a = new HOCWP_HTML( 'a' );
						$a->set_href( $more_link );
						$a->set_text( __( 'Check out more', 'pixelify' ) );
						$a->add_class( 'more-link' );
						$a->output();
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	
	$donate_link = get_post_meta( get_the_ID(), 'donate_link', true );

	if ( ! empty( $donate_link ) ) {
		?>
		<div class="media-content">
			<div class="media-info">
				<div class="details visible">
					<div class="media-download">
						<?php
						$a = new HOCWP_HTML( 'a' );
						$a->set_href( $donate_link );
						$a->set_text( __( 'Check out more', 'pixelify' ) );
						$a->add_class( 'more-link' );
						$a->output();
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	
	?>
	<div class="sidebar-social-share text-center">
		<p class="text-center"><?php _e( 'Credit the author by sharing. Thanks!', 'pixelify' ); ?></p>

		<div id="share-buttons" class="share-buttons">
			<?php dynamic_sidebar( 'addthis' ); ?>
		</div>
		<div class="like-wishlist">
			<div id="likes" class="likes">
				<div>
					<div id="wp-ulike-post-971120" class="wpulike wpulike-robeen">
						<div class="wp_ulike_general_class wp_ulike_is_liked">
							<label>
								<?php
								$likes = get_post_meta( get_the_ID(), 'likes', true );
								$likes = absint( $likes );

								if ( is_user_logged_in() ) {
									$user_id = get_current_user_id();
									$ulikes  = get_user_meta( $user_id, 'likes', true );
									$ulikes  = (array) $ulikes;
									$ulikes  = array_filter( $ulikes );
									$ulikes  = array_unique( $ulikes );

									$liked = ( in_array( get_the_ID(), $ulikes ) ) ? 1 : 0;
								} else {
									$liked = 0;
								}
								?>
								<input type="checkbox" data-id="<?php the_ID(); ?>"
								       class="wp_ulike_btn wp_ulike_put_image image-unlike"<?php checked( 1, $liked ); ?>>
								<span class="count-box"><strong><?php echo number_format( $likes ); ?></strong></span>
								<strong><?php _ex( 'Likes', 'like count', 'pixelify' ); ?></strong>
							</label>
						</div>
					</div>
				</div>
			</div>
			<!--/likes-->
			<div class="collection">
				<a href="#" class="plain edd-wl-action edd-wl-open-modal glyph-left edd-has-js" data-toggle="modal"
				   data-target="#edd-wl-modal" data-post-id="<?php the_ID(); ?>">
					<i class="glyphicon glyphicon-add"></i>
					<span class="label"><?php _e( 'Collection', 'pixelify' ); ?></span>
				</a>


			</div>
			<!--/collection-->
		</div>
		<!--/like-wishlist-->
	</div>
	<div class="sidebar-info">
		<div class="info-row">
			<span><?php _e( 'Date', 'pixelify' ); ?></span>
			<span><?php Pixelify()->the_date( '', null, false ); ?></span>
		</div>
		<!--/info-row-->
		<?php
		if ( ! empty( $download ) ) {
			$size = filesize( get_attached_file( $download ) );

			$downloads = get_post_meta( get_the_ID(), 'downloads', true );
			$downloads = absint( $downloads );
			?>
			<div class="info-row">
				<span><?php _e( 'Size', 'pixelify' ); ?></span>
				<span><?php echo size_format( $size, 2 ); ?></span>
			</div>
			<!--/info-row-->
			<div class="info-row">
				<span><?php _e( 'Downloads', 'pixelify' ); ?></span>
				<span><?php echo number_format( $downloads ); ?></span>
			</div>
			<!--/info-row-->
			<?php
		}
		?>
	</div>
	<?php
	$license1 = get_post_meta( get_the_ID(), 'license1', true );
	$license2 = get_post_meta( get_the_ID(), 'license2', true );

	if ( 1 == $license1 || 1 == $license2 ) {
		$page = Pixelify()->get_option_post( 'licenses_page' );
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
			$html .= ' | ';
			$html .= '<a href="' . get_permalink( $page ) . '">' . __( 'License info', 'pixelify' ) . '</a>';
		}
		?>
		<div class="sidebar-license-info text-center">
			<?php echo $html; ?>
		</div>
		<?php
	}
	?>
	<div class="sidebar-author-info">
		<?php
		$class     = 'follow-button follow';
		$author_id = get_the_author_meta( 'ID' );

		if ( is_user_logged_in() ) {
			$user_id          = get_current_user_id();
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
		?>
		<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
			<?php echo get_avatar( $author_id ); ?>
		</a>
		<h4>
			<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
				<?php the_author(); ?>
			</a>
		</h4>

		<div class="<?php echo $class; ?>" id="follow-button-<?php echo $author_id; ?>"
		     data-author-id="<?php echo $author_id; ?>"
		     data-following="<?php echo $following_text; ?>"
		     data-follow="<?php echo $follow_text; ?>"><?php echo $button_text; ?></div>
		<?php
		$desc = get_the_author_meta( 'description' );
		echo wpautop( $desc );
		$rating = get_user_meta( $author_id, 'rating', true );
		$rates  = get_user_meta( $author_id, 'rates', true );

		$rating = floatval( $rating );
		$rating = round( $rating, 1 );

		$args = array(
			'rating' => $rating,
			'type'   => 'rating',
			'number' => 12345
		);

		wp_star_rating( $args );
		?>
		<div class="rating-number">
			<?php
			if ( ! empty( $rating ) ) {
				echo '<strong>' . $rating . '</strong>';
			}
			?>
			<span>(<?php printf( __( '<strong>%d</strong> Reviews', 'pixelify' ), absint( $rates ) ); ?>
				)</span>
		</div>
	</div>
	<?php
	$args = array(
		'author'         => $author_id,
		'posts_per_page' => 6,
		'post__not_in'   => array( get_the_ID() )
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		?>
		<div class="sidebar-more-from-author">
			<h3><?php _e( 'More from this author', 'pixelify' ); ?></h3>
			<a class="see-all-author"
			   href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php printf( __( 'See all (%d)', 'pixelify' ), $query->found_posts ); ?></a>
			<ul class="list-inline list-unstyled">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					?>
					<li>
						<div class="thumbnail-container">
							<a href="<?php the_permalink(); ?>">
								<?php Pixelify()->custom_post_thumbnail( get_the_ID(), array( 300, 200 ) ); ?>
							</a>
						</div>
					</li>
					<?php
				}

				wp_reset_postdata();
				?>
			</ul>

		</div>
		<?php
	}

	if ( has_tag() ) {
		?>
		<div class="sidebar-tags">
			<h3><?php _e( 'Tags', 'pixelify' ); ?></h3>
			<?php the_tags( '', ' ' ); ?>
		</div>
		<?php
	}
	?>
</div>