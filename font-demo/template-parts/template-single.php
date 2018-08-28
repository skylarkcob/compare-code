<div class="media-content">
	<?php
	global $hocwp;
	$post_id      = get_the_ID();
	$current      = get_post( $post_id );
	$media_images = hocwp_get_post_meta( 'media_images', $post_id );

	if ( ! empty( $media_images ) ) {
		$media_images = hocwp_wrap_tag( $media_images, 'div', 'media-images' );
		echo $media_images;
	}

	$download = hocwp_get_post_meta( 'download', $post_id );

	if ( empty( $download ) ) {
		$download = get_post_meta( $post_id, 'download_url', true );
	}

	$download = hocwp_sanitize_media_value( $download );

	$license = hocwp_get_post_meta( 'license', $post_id );
	$format  = hocwp_get_post_meta( 'file_format', $post_id );
	$types   = wp_check_filetype( $download['path'] );

	if ( empty( $format ) && isset( $types['ext'] ) ) {
		$format = $types['ext'];
	}

	$small_image    = hocwp_get_post_meta( 'small_image', $post_id );
	$small_image    = hocwp_sanitize_media_value( $small_image );
	$small_image    = $small_image['url'];
	$media_download = '';

	$download_url = Pixelify()->get_download_url();

	if ( ! empty( $download_url ) ) {
		$download['url'] = $download_url;
	}

	if ( ! empty( $download['url'] ) ) {
		$count = hocwp_get_post_meta( 'download_count', $post_id );
		$count = absint( $count );
		$media_download .= '<div class="media-download">';
		$more_link = hocwp_get_post_meta( 'more_link', $post_id );
		$donate_link = hocwp_get_post_meta( 'donate_link', $post_id );

		$a = new HOCWP_HTML( 'a' );
		$a->set_attribute( 'data-id', $post_id );
		$a->add_class( 'download-link down-link' );
		$a->set_href( Pixelify()->get_virtual_download_url() );
		$a->set_text( '<i class="fa fa-arrow-down" aria-hidden="true"></i> ' . __( 'Download', 'pixelify' ) . ' (' . $count . ')' );
		$media_download .= $a->build();

		if ( ! empty( $more_link ) ) {
			$a->set_href( $more_link );
			$a->set_text( '<i class="fa fa-tag" aria-hidden="true"></i> ' . __( 'Check out more', 'pixelify' ) );
			$a->add_class( 'more-link' );
			$media_download .= $a->build();
		}
		
		if ( ! empty( $donate_link ) ) {
			$a->set_href( $donate_link );
			$a->set_text( '<i class="fa fa-external-link" aria-hidden="true"></i> ' . __( 'Donate', 'pixelify' ) );
			$a->add_class( 'more-link' );
			$media_download .= $a->build();
		}
		

		ob_start();
		?>
		<a href="#" class="plain edd-wl-action edd-wl-open-modal glyph-left edd-has-js download-link"
		   data-toggle="modal"
		   data-target="#edd-wl-modal" data-post-id="644">
			<i class="glyphicon glyphicon-add"></i>
			<span class="label"><i class="fa fa-heart"
			                       aria-hidden="true"></i> <?php _e( 'Add to Collection', 'hocwp-theme' ); ?></span>
		</a>
		<?php
		$media_download .= ob_get_clean();
		

		
		
		
		
		

		

		$media_download .= '</div>';
	}

	$small_ads = $hocwp->plugin->font_demo->get_option_value_by_key( 'small_ads' );
	?>
	<div class="media-info">
		<div class="details">
			<div class="col-inner">
				<?php
				if ( ! empty( $format ) ) {
					?>
					<div>
						<label class="list-label"><?php _e( 'Format', 'pixelify' ); ?></label>
						<span>: <?php echo strtoupper( $format ); ?></span>
					</div>
					<?php
				}

				if ( ! empty( $download['size_format'] ) ) {
					?>
					<div>
						<label class="list-label"><?php _e( 'Size', 'pixelify' ); ?></label>
						<span>: <?php echo $download['size_format']; ?></span>
					</div>
					<?php
				}

				if ( ! empty( $license ) ) {
					?>
					<div>
						<label class="list-label">License</label>
						<span>: <?php echo $license; ?></span>
					</div>
					<?php
				}

				if ( ! empty( $small_ads ) ) {
					$small_ads = hocwp_wrap_tag( $small_ads, 'div', 'small-ads' );
					echo $small_ads;
				}

				if ( ! empty( $small_image ) ) {
					$small_image_link = hocwp_get_post_meta( 'small_image_link', $post_id );

					if ( empty( $small_image_link ) ) {
						$small_image_link = 'javascript:';
					}

					$a = new HOCWP_HTML( 'a' );
					$a->set_href( $small_image_link );
					$img = new HOCWP_HTML( 'img' );
					$img->set_image_src( $small_image );
					$a->set_text( $img );
					$media_ads = hocwp_wrap_tag( $a->build(), 'div', 'media-ads' );
					echo $media_ads;
				}

				$post_id = get_the_ID();
				?>

				<?php echo $media_download; ?>
			</div>
		</div>
		<div class="description">
			<div class="col-inner">
				<?php
				$description = get_post_meta( $post_id, 'media_description', true );

				if ( empty( $description ) ) {
					//$description = get_the_excerpt( $post_id );
				}

				$description = wpautop( $description );
				$description = do_shortcode( $description );
				echo $description;

				$license = __( ' Free for Personal Use', 'hocwp-theme' );

				$l1 = get_post_meta( $post_id, 'license1', true );

				if ( 1 != $l1 ) {
					$license = __( 'Free for Commercial Use', 'hocwp-theme' );
				}

				$com_lic = get_post_meta( $post_id, 'commercial_license', true );
				$com_lic = esc_url( $com_lic );

				$author_id = $current->post_author;
				$author    = get_user_by( 'id', $author_id );

				if ( has_term( '', 'designer' ) || ! empty( $license ) || ! empty( $com_lic ) || $author instanceof WP_User ) {
					?>
					<div class="post-group fontDesigner font">
						<h2><?php _e( 'Font info', 'hocwp-theme' ); ?></h2>
						<table width="100%" cellspacing="0" cellpadding="0">
							<tbody>
							<?php
							if ( has_term( '', 'designer' ) ) {
								?>
								<tr>
									<td style="width: 200px;"><?php _e( 'Designer Name:', 'hocwp-theme' ); ?></td>
									<td><?php the_terms( get_the_ID(), 'designer' ); ?></td>
								</tr>
								<?php
							}

							?>
							<tr>
								<td style="width: 200px;"><?php _e( 'Author:', 'hocwp-theme' ); ?></td>
								<td>
									<a href="<?php echo get_author_posts_url( $author_id ); ?>"><?php echo $author->display_name; ?></a>
								</td>
							</tr>
							<?php

							if ( ! empty( $license ) ) {
								?>
								<tr>
									<td style="width: 120px;"><?php _e( 'License:', 'hocwp-theme' ); ?></td>
									<td><?php echo $license; ?></td>
								</tr>
								<?php
							}

							if ( ! empty( $com_lic ) ) {
								?>
								<tr>
									<td><?php _e( 'Commercial License:', 'hocwp-theme' ); ?></td>
									<td><a href="<?php echo $com_lic; ?>" target="_blank"><?php echo $com_lic; ?></a>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</div>
					<?php
				}

				if ( ! empty( $small_image ) || true ) {
					echo $media_download;
				}
				?>
			</div>
		</div>
	</div>
	<?php
	$font_demos = hocwp_get_post_meta( 'font_demos', $post_id );

	if ( ( is_single() || is_page() ) && hocwp_array_has_value( $font_demos ) ) {
		$demo_text = hocwp_font_demo_get_demo_text( $post_id );
		?>
		<div class="font-demos">
			<div class="font-tester">
				<input class="set-text-preview" name="text_preview"
				       placeholder="<?php echo $demo_text; ?>"
				       value="<?php echo $demo_text; ?>" type="text" autocomplete="off">
				<span class="toolbar-divider"></span>

				<div class="toolbar-btn-group set-text-transform">
					<a class="set-text-transform-uppercase set-text-transform toolbar-btn set-uppercase">
						<span>AA</span>
					</a>
					<a class="set-text-transform-first-letter set-text-transform toolbar-btn set-capitalize">
						<span>Aa</span>
					</a>
					<a class="set-text-transform-lowercase set-text-transform toolbar-btn set-lowercase">
						<span>aa</span>
					</a>
				</div>
				<span class="toolbar-divider"></span>

				<div class="font-size toolbar-btn-group slider">
					<div id="fontSize"></div>
				</div>
			</div>
			<div class="list-fonts font-entry">
				<?php
				foreach ( $font_demos as $key => $data ) {
					$id = $data['id'];

					if ( ! hocwp_id_number_valid( $id ) ) {
						continue;
					}

					$info = hocwp_get_media_info( $id );
					?>
					<style type="text/css">
						@font-face {
							font-family: '<?php echo $data['name']; ?>';
							src: url('<?php echo $info['url'] ?>') format('woff'),
							url('<?php echo $info['url'] ?>') format('truetype');
						}
					</style>
					<div class="font-entry-head">
						<span class="font-name"><?php echo $data['name']; ?></span>
						<span class="font-display" style="font-family: '<?php echo $data['name']; ?>';"
						      contenteditable="true"><?php echo $demo_text; ?></span>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>