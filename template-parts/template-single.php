<div class="<?php hocwp_wrap_class(); ?>">
	<?php
	$post_id = get_the_ID();
	hocwp_theme_get_module( 'search-cat' );
	hocwp_theme_site_main_before();
	hocwp_theme_get_module( 'custom-preview' );
	hocwp_article_before( 'fontArchiveContents current-post', 'div' );
	the_title( '<h1>', '</h1>' );
	hocwp_theme_get_loop( 'post' );
	$pathinfo = hocwp_theme_custom_get_demo_dir( $post_id, '', true );

	$demo_path = $pathinfo['dir'];

	$fonts = array();
	$files = array();

	if ( is_dir( $demo_path ) ) {
		$files = scandir( $demo_path );
	}

	foreach ( $files as $file ) {
		$info = pathinfo( $file );
		if ( isset( $info['extension'] ) && 'ttf' == $info['extension'] ) {
			$font = trailingslashit( $demo_path ) . $file;
			array_unshift( $fonts, $font );
		}
	}

	array_shift( $fonts );

	if ( hocwp_array_has_value( $fonts ) ) {
		$demo_page = hocwp_theme_custom_get_page_option( 'font', 'page-templates/font.php' );
		$post_name = get_post_meta( $post_id, 'name', true );

		if ( empty( $post_name ) ) {
			$post_name = get_the_title();
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

		$demo_url  = '';
		$is_custom = false;

		if ( isset( $_POST['submitPreviewSettings'] ) ) {
			$is_custom = ( ! empty( $_POST['customPreviewText'] ) || ! ( '#000' == $color || '#000000' == $color ) || ( 80 != $size ) );
		}

		if ( $demo_page instanceof WP_Post ) {
			$demo_url = get_permalink( $demo_page );
		}

		$demo_style = 'min-height:' . $size . 'px;';

		if ( ! empty( $demo_url ) ) {
			$demo_style .= 'background-image: url(' . esc_attr( get_template_directory_uri() . '/images/transparent.gif' ) . ')';
		}

		$demo_text  = false;
		$demo_class = 'demo clearfix preview';

		if ( empty( $demo_url ) ) {
			$demo_text = true;
			$demo_class .= ' demo-text';
		}
		?>
		<div class="post-group more-font-demo">
			<h2><?php _e( 'More Demo', 'hocwp-theme' ); ?></h2>

			<div class="box">
				<?php
				$demo_params = array(
					'post_id' => $post_id,
					'size'    => $size,
					'text'    => urlencode( $post_name ),
					'color'   => str_replace( '#', '', $color )
				);
				$post_data   = base64_encode( json_encode( $_POST ) );
				?>
				<script type="text/javascript">
					var datas = [];
					<?php
					foreach ( $fonts as $font ) {
						$name     = basename( $font );
						$original = '';
						if ( ! empty( $demo_url ) ) {
							$demo_params['font'] = base64_encode( $font );
							$original            = add_query_arg( $demo_params, $demo_url );
						}
						?>
					var data = {
						post_id: <?php the_ID(); ?>,
						name: '<?php echo $name; ?>',
						demo_class: '<?php echo $demo_class; ?>',
						demo_style: '<?php echo $demo_style; ?>',
						original: '<?php echo $original; ?>',
						demo_text: '<?php echo $demo_text; ?>',
						post_name: '<?php echo $post_name; ?>',
						post_data: '<?php echo $post_data; ?>'
					};
					datas.push(JSON.stringify(data));
					<?php
				}
				?>
					jQuery(document).ready(function ($) {
						function hocwp_theme_custom_more_demo(data) {
							var loading = $('.more-font-demo .ajax-loading');
							loading.show();
							$.ajax({
								type: 'POST',
								dataType: 'json',
								url: hocwp.ajax_url,
								cache: true,
								data: {
									action: 'hocwp_generate_font_more_demo',
									post_data: data
								},
								success: function (response) {
									loading.hide();
									if (response.success) {
										$('.more-font-demo .box').append(response.html);
										$('.more-font-demo .preview').lazyload();
										if (datas.length > 0) {
											var data = datas.shift();
											hocwp_theme_custom_more_demo(data);
										}
									}
								}
							});
						}

						if (datas.length > 0) {
							var data = datas.shift();
							hocwp_theme_custom_more_demo(data);
						}
					});
				</script>
			</div>
			<img class="ajax-loading" src="<?php echo hocwp_theme_get_image_url( 'ajax-loading-small-blue.gif' ); ?>"
			     alt="" style="display: none">
		</div>
		<?php
	}

	$content = get_the_content();

	if ( ! empty( $content ) ) {
		?>
		<div class="post-group designer-note">
			<h2><?php _e( 'Designers Note', 'hocwp-theme' ); ?></h2>
			<?php hocwp_entry_content(); ?>
		</div>
		<?php
	}

	$character_map = get_post_meta( $post_id, 'character_map', true );

	if ( empty( $character_map ) && false ) {
		$demo_url = hocwp_theme_custom_get_page_option( 'character_map', 'page-templates/character-map.php' );
		$demo_url = get_permalink( $demo_url );

		$demo_params = array(
			'post_id' => $post_id
		);

		$demo_url      = add_query_arg( $demo_params, $demo_url );
		$character_map = '<img src="' . esc_attr( $demo_url ) . '" alt="">';
	}

	if ( ! empty( $character_map ) ) {
		?>
		<div class="post-group character-map">
			<h2><?php _e( 'Character Map', 'hocwp-theme' ); ?></h2>
			<?php echo wpautop( $character_map ); ?>
		</div>
		<?php
	}

	$file_contents = get_post_meta( $post_id, 'file_contents', true );
	$file_contents = hocwp_sanitize_media_value( $file_contents );

	if ( ! empty( $file_contents['url'] ) ) {
		$file_id = $file_contents['id'];
		$path    = hocwp_get_media_file_path( $file_contents['id'] );

		$zip = false;

		if ( is_dir( $path ) ) {
			$zip = zip_open( $path );
		}

		if ( $zip ) {
			?>
			<div class="post-group file-contents">
				<h2><?php _e( 'File Contents', 'hocwp-theme' ); ?></h2>
				<table width="100%" cellspacing="0" cellpadding="0">
					<thead>
					<tr>
						<th class="col-name">filename</th>
						<th class="col-size" style="width:110px; text-align:center;">filesize</th>
						<th class="col-type" style="width:200px; text-align:center;">type</th>
						<th class="col-options" style="width:100px; text-align:center;">options</th>
					</tr>
					</thead>
					<tbody>
					<?php
					while ( $zip_entry = zip_read( $zip ) ) {
						$file_name = zip_entry_name( $zip_entry );
						$types     = wp_check_filetype( $file_name );
						?>
						<tr>
							<td class="col-name"><?php echo $file_name; ?></td>
							<td class="col-size"
							    style="text-align:center;"><?php echo size_format( zip_entry_filesize( $zip_entry ) ); ?></td>
							<td class="col-type" style="text-align:center;"><?php echo $types['type']; ?></td>
							<td class="col-options" style="text-align:center;">
								<?php
								if ( hocwp_theme_custom_is_font_file( $file_name ) ) {
									?>
									<a class="download-ajax" data-id="<?php the_ID(); ?>"
									   href="<?php echo $file_contents['url']; ?>">download</a>
									<?php
								}
								?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
			<?php
			zip_close( $zip );
		}
	}

	if ( function_exists( 'Pixelify' ) && ! Pixelify()->is_font_demo_post() ) {
		Pixelify()->font_details_table();
	} elseif ( ! function_exists( 'Pixelify' ) || ! Pixelify()->is_font_demo_post() ) {
		$designers  = wp_get_post_terms( $post_id, 'designer' );
		$author_id  = get_the_author_meta( 'ID' );
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

		$license = get_post_meta( $post_id, 'license', true );

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
		?>
		<div class="post-group fontDesigner font" style="display: none">
			<h2><?php _e( 'Font', 'hocwp-theme' ); ?></h2>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tbody>
				<?php
				if ( ! empty( $author_url ) ) {
					?>
					<tr>
						<td style="width: 200px;">Designer Name:</td>
						<td><?php echo $author_url; ?></td>
					</tr>
					<?php
				}

				if ( ! empty( $license ) ) {
					?>
					<tr>
						<td style="width: 120px;">License:</td>
						<td><?php echo $license; ?></td>
					</tr>
					<?php
				}

				if ( ! empty( $website ) ) {
					?>
					<tr>
						<td>Website:</td>
						<td><a href="<?php echo $website; ?>" target="_blank"><?php echo $website; ?></a></td>
					</tr>
					<?php
				}

				if ( ! empty( $com_lic ) ) {
					?>
					<tr>
						<td>Commercial License:</td>
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

	hocwp_theme_get_module( 'random-post' );
	hocwp_article_after( 'div' );
	?>
	<?php hocwp_theme_site_main_after(); ?>
	<?php get_sidebar(); ?>
</div>