<div class="<?php hocwp_wrap_class(); ?>">
	<?php hocwp_theme_get_module( 'search-cat' ); ?>
	<?php hocwp_theme_site_main_before(); ?>
	<div class="fonts-box">
		<?php
		if ( is_author() || is_tax( 'designer' ) ) {
			if ( is_author() ) {
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
			}

			$term_id = get_queried_object_id();
			$term    = get_queried_object();

			global $wp_query;
			?>
			<div class="author-page-info artist-info text-center">
				<?php
				if ( is_author() ) {
					echo get_avatar( $author_id, 115 );
				} else {
					$email = get_term_meta( $term_id, 'email', true );

					if ( is_email( $email ) ) {
						echo get_avatar( $email, 115 );
					}
				}
				?>
				<div class="author-info-text">
					<h1>
						<?php
						if ( is_author() ) {
							the_author();
						} else {
							echo $term->name;
						}
						?>
					</h1>
					<?php
					if ( is_author() && ! empty( $city_country ) ) {
						?>
						<span class="author-residence"><?php echo $city_country; ?></span>
						<?php
					}

					if ( is_author() ) {
						echo wpautop( get_user_meta( $author_id, 'description', true ) );
					} else {
						echo wpautop( $term->description );
					}

					if ( is_author() ) {
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
								if ( ! is_numeric( $rating ) ) {
									$rating = 0;
								}

								if ( ! is_numeric( $rates ) ) {
									$rates = 0;
								}

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
						<?php
					}
					?>
					<!--/author-info-text-->
				</div>
				<!--/author-page-info-meta-->

			</div>
			<?php
		} else {
			the_archive_title( '<h1>', '</h1>' );
		}

		if ( have_posts() ) {
			hocwp_pagination();
			hocwp_theme_get_module( 'custom-preview' );
			?>
			<div class="loops">
				<?php
				while ( have_posts() ) {
					the_post();
					hocwp_theme_get_loop( 'post' );
				}
				?>
			</div>
			<?php
			hocwp_pagination();
		} else {
			hocwp_theme_get_content_none();
		}
		?>
	</div>
	<?php hocwp_theme_site_main_after(); ?>
	<?php get_sidebar(); ?>
</div>