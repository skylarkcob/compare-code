<?php
hocwp_article_before( 'font-box' );
$post_id   = get_the_ID();
$post_name = get_post_meta( $post_id, 'name', true );

if ( empty( $post_name ) ) {
	$post_name = get_the_title();
}

$post_link  = '<a href="' . get_the_permalink() . '">' . $post_name . '</a>';
$designers  = wp_get_post_terms( $post_id, 'designer' );
$author_id  = get_the_author_meta( 'ID' );
$donate     = get_user_meta( $author_id, 'donate', true );
$donate     = esc_url( $donate );
$author_url = '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . get_the_author() . '</a></span>';

if ( hocwp_array_has_value( $designers ) ) {
	$author_url = '';

	foreach ( $designers as $designer ) {
		$author_url .= '<a href="' . get_term_link( $designer ) . '">' . $designer->name . '</a>, ';
		$dol = get_term_meta( $designer->term_id, 'donate', true );

		if ( ! empty( $dol ) ) {
			$donate = $dol;
		}
	}

	$author_url = trim( $author_url, ', ' );
}

$donate_p = get_post_meta( $post_id, 'donate', true );

if ( ! empty( $donate_p ) ) {
	$donate = $donate_p;
}

if ( empty( $donate ) ) {
	$user   = wp_get_current_user();
	$donate = $user->user_email;
}

$byline   = sprintf( __( 'by %s', 'hocwp-theme' ), $author_url );
$download = get_post_meta( $post_id, 'downloads', true );
$download = absint( $download );
$download = number_format( $download );
$demo     = get_post_meta( $post_id, 'demo', true );
$demo     = hocwp_sanitize_media_value( $demo );
$license  = get_post_meta( $post_id, 'license', true );

if ( has_term( '', 'license' ) ) {
	$licenses = wp_get_post_terms( $post_id, 'license' );

	if ( hocwp_array_has_value( $licenses ) ) {
		$license = array_shift( $licenses );
		$license = $license->name;
	}
}

$website = get_post_meta( $post_id, 'website', true );
$com_lic = get_post_meta( $post_id, 'commercial_license', true );
$com_lic = esc_url( $com_lic );

$file_contents = get_post_meta( $post_id, 'file_contents', true );
$file_contents = hocwp_sanitize_media_value( $file_contents );

if ( empty( $demo['url'] ) ) {
	$demo = hocwp_theme_custom_add_demo_from_file_contents( $post_id, $file_contents );
}

$size  = 80;
$color = '#000';

if ( isset( $_POST['submitPreviewSettings'] ) ) {
	if ( 'update' == $_POST['submitPreviewSettings'] ) {
		if ( ! empty( $_POST['customPreviewText'] ) ) {
			$post_name = $_POST['customPreviewText'];
		}

		$size = $_POST['customPreviewSize'];

		if ( ! empty( $_POST['customPreviewTextColour'] ) ) {
			$color = $_POST['customPreviewTextColour'];
		}
	}
}

$demo_page = hocwp_theme_custom_get_page_option( 'font', 'page-templates/font.php' );
$demo_url  = '';
$is_custom = false;

if ( isset( $_POST['submitPreviewSettings'] ) ) {
	$is_custom = ( ! empty( $_POST['customPreviewText'] ) || ! ( '#000' == $color || '#000000' == $color ) || ( 80 != $size ) );
}

if ( has_post_thumbnail() && ! $is_custom ) {
	$demo_url = get_the_post_thumbnail_url( $post_id, 'full' );
} else {
	$demo_url = get_permalink( $demo_page );

	$demo_params = array(
		'post_id' => $post_id,
		'size'    => $size,
		'text'    => urlencode( $post_name ),
		'color'   => str_replace( '#', '', $color ),
		'font'    => base64_encode( hocwp_get_media_file_path( $demo['id'] ) )
	);

	$demo_url = add_query_arg( $demo_params, hocwp_theme_custom_get_preview_url() );

	if ( ! $is_custom ) {
		?>
		<script>
			jQuery(document).ready(function ($) {
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: hocwp.ajax_url,
					cache: true,
					data: {
						action: 'hocwp_generate_font_thumbnail',
						post_id: <?php the_ID(); ?>
					}
				});
			});
		</script>
		<?php
	}
}

$demo_style = '';

if ( ! empty( $demo_url ) ) {
	$demo_style = 'background-image: url(' . esc_attr( get_template_directory_uri() . '/images/transparent.gif' ) . ')';
}

$demo_text  = false;
$demo_class = 'demo clearfix';

if ( ! empty( $demo ) && empty( $demo_url ) ) {
	$demo_text = true;
	$demo_class .= ' demo-text';
}
?>
	<div class="top-info clearfix">
		<div class="name-author pull-left">
			<?php printf( '%1$s %2$s <span style="color:#979797;">%3$s downloads</span>', $post_link, $byline, $download ); ?>
		</div>
		<div class="cats pull-right">
			<span><?php _e( 'Found in: ', 'hocwp-theme' ); ?></span>
			<?php the_category( ', ' ); ?>
		</div>
	</div>
	<div class="<?php echo $demo_class; ?>" style="<?php echo $demo_style; ?>" data-original="<?php echo $demo_url; ?>">
		<div class="pull-lefts custom-text">
			<?php
			$font_family = '';
			$style       = 'font-size:' . $size . 'px;color:' . $color;

			if ( $demo_text ) {
				$font_name   = basename( $demo['url'] );
				$font_info   = pathinfo( $font_name );
				$font_family = $font_info['filename'];
				$font_family = sanitize_html_class( $font_family );
				$style .= ';font-family:' . $font_family;
				?>
				<style type="text/css">
					@font-face {
						font-family: '<?php echo $font_family; ?>';
						src: url('<?php echo $demo['url']; ?>') format('truetype');
					}
				</style>
				<?php
			}

			if ( mb_strlen( $post_name ) > 30 ) {
				$post_name = mb_substr( $post_name, 0, 25 ) . '...';
			}
			?>
			<h1 class="font-name" style="<?php echo $style; ?>">
				<a href="<?php the_permalink(); ?>"><?php echo $post_name; ?></a>
			</h1>
			<?php
			$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

			if ( ! empty( $slider_ids ) && is_array( $slider_ids ) ) {
				?>
				<div class="font-thumbs">
					<?php
					foreach ( $slider_ids as $mi ) {
						?>
						<a href="<?php the_permalink(); ?>">
							<?php echo wp_get_attachment_image( $mi, array( 290, 160 ) ); ?>
						</a>
						<?php
					}
					?>
				</div>
				<?php
			} else {
				$post_slider = get_post_meta( $post_id, 'post_slider', true );

				if ( ! empty( $post_slider ) && function_exists( 'Pixelify' ) && function_exists( 'HP' ) ) {
					?>
					<div class="font-thumbs">
						<?php
						$slider_ids = Pixelify()->get_media_ids_from_string( $post_slider );

						if ( HP()->array_has_value( $slider_ids ) ) {
							foreach ( $slider_ids as $mi ) {
								?>
								<a href="<?php the_permalink(); ?>">
									<?php echo wp_get_attachment_image( $mi, array( 290, 160 ) ); ?>
								</a>
								<?php
							}
						} else {
							$post_slider = str_replace( '\\', '', $post_slider );
							$images      = hocwp_get_all_image_from_string( $post_slider );

							if ( HP()->array_has_value( $images ) ) {
								foreach ( $images as $image ) {
									?>
									<a href="<?php the_permalink(); ?>">
										<?php echo $image; ?>
									</a>
									<?php
								}
							}
						}
						?>
					</div>
					<?php
				}
			}
			?>
		</div>
		<div class="pull-rights links">
			<div class="link-inner">
				<?php
				if ( ! empty( $license ) ) {
					?>
					<div class="license">
						<?php echo $license; ?>
					</div>
					<?php
				}
				?>
				<?php
				if ( ! empty( $file_contents['url'] ) ) {
					?>
					<a class="btn-download button btn-dark"
					   href="<?php echo $file_contents['url']; ?>"
					   data-id="<?php the_ID(); ?>" target="_blank"><?php _e( 'Download', 'hocwp-theme' ); ?></a>
					<?php
				}
				?>
				<?php
				if ( ! empty( $donate ) ) {
					if ( is_email( $donate ) ) {
						$return_url = home_url( '/' );

						if ( is_single() || is_singular() || is_page() ) {
							$return_url = get_the_permalink();
						}

						$item_name = 'Donation via ' . hocwp_uppercase_first_char( hocwp_get_domain_name( home_url() ) );
						?>
						<form name="donate_form" action="https://www.paypal.com/cgi-bin/webscr" method="post"
						      target="_blank">
							<input type="hidden" name="cmd" value="_donations">
							<input type="hidden" name="cancel_return" value="<?php echo $return_url; ?>">
							<input type="hidden" name="return" value="<?php echo $return_url; ?>">
							<input type="hidden" name="business" value="<?php echo $donate; ?>">
							<input type="hidden" name="lcu" value="C2">
							<input type="hidden" name="item_name" value="<?php echo $item_name; ?>">
							<input type="hidden" name="currency_code" value="USD">
							<input type="hidden" name="button_subtype" value="services">
							<input type="hidden" name="no_note" value="0">
							<input type="submit" name="submit"
							       value="<?php _e( 'Donate To Designer', 'hocwp-theme' ); ?>"
							       class="btn-light button">
						</form>
						<?php
					}
					?>
					<div class="donate">
						<?php
						if ( is_email( $donate ) ) {

						} else {
							?>
							<a class="btn-light button"
							   href="<?php echo $donate; ?>"
							   target="_blank"><?php _e( 'Donate To Designer', 'hocwp-theme' ); ?></a>
							<?php
						}
						?>
					</div>
					<?php
				}
				if ( ! empty( $com_lic ) ) {
					?>
					<div class="commerial-license">
						<a class="btn-light button"
						   href="<?php echo $com_lic; ?>"
						   target="_blank"><?php _e( 'Buy Commercial License', 'hocwp-theme' ); ?></a>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
<?php
hocwp_article_after();