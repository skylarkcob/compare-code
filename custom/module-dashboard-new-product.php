<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$edit = false;

$task = isset( $_GET['task'] ) ? $_GET['task'] : '';

if ( 'edit-product' == $task ) {
	$edit = true;
}

$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '';

if ( ! is_numeric( $post_id ) ) {
	$edit = false;
}

$max_upload   = (int) ( ini_get( 'upload_max_filesize' ) );
$max_post     = (int) ( ini_get( 'post_max_size' ) );
$memory_limit = (int) ( ini_get( 'memory_limit' ) );
$upload_mb    = min( $max_upload, $max_post, $memory_limit );

$image_size = Pixelify()->get_image_size( 'large' );

$or = __( 'or', 'pixelify' );

$font_exts = Pixelify()->font_extension;

$font_desc = '';

foreach ( $font_exts as $ext ) {
	$font_desc .= $ext . ' PXF_OR';
}

$font_desc = trim( $font_desc, ' PXF_OR' );

$font_desc = str_replace( 'PXF_OR', $or, $font_desc );

$file_exts = Pixelify()->download_extension;

$file_desc = '';

foreach ( $file_exts as $ext ) {
	$file_desc .= $ext . ' PXF_OR';
}

$file_desc = trim( $file_desc, ' PXF_OR' );

$file_desc = str_replace( 'PXF_OR', $or, $file_desc );

$download_file   = isset( $_POST['download_file'] ) ? $_POST['download_file'] : ( isset( $_FILES['download_file'] ) ) ? $_FILES['download_file'] : '';
$font_file       = isset( $_POST['font_file'] ) ? $_POST['font_file'] : ( isset( $_FILES['font_file'] ) ) ? $_FILES['font_file'] : '';
$product_images  = isset( $_POST['product_images'] ) ? $_POST['product_images'] : ( isset( $_FILES['product_images'] ) ) ? $_FILES['product_images'] : '';
$category        = isset( $_POST['category'] ) ? $_POST['category'] : '';
$post_title      = isset( $_POST['post_title'] ) ? $_POST['post_title'] : '';
$post_content    = isset( $_POST['post_content'] ) ? $_POST['post_content'] : '';
$download_tag    = isset( $_POST['download_tag'] ) ? $_POST['download_tag'] : '';
$checkout_url    = isset( $_POST['checkout_url'] ) ? $_POST['checkout_url'] : '';
$license_options = isset( $_POST['license_options'] ) ? $_POST['license_options'] : '';

$ffcu = __( 'Free for Commercial Use', 'pixelify' );

$license1 = 0;

$redirect = false;

if ( md5( $license_options ) == md5( $ffcu ) ) {
	$license2 = 1;
} else {
	if ( isset( $_POST['create_post'] ) ) {
		$license2 = 0;
		$license1 = 1;
	} else {
		$license2 = 1;
	}
}

if ( $edit && ! isset( $_POST['create_post'] ) ) {
	$obj = get_post( $post_id );

	$post_title   = $obj->post_title;
	$post_content = $obj->post_content;
	$category     = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
	$tags         = wp_get_post_terms( $post_id, 'post_tag', array( 'fields' => 'names' ) );
	$download_tag = implode( ', ', $tags );

	$l1 = get_post_meta( $post_id, 'license1', true );

	if ( 1 == $l1 ) {
		$license2 = 0;
	} else {
		$license2 = 1;
	}

	$checkout_url = get_post_meta( $post_id, 'more_link', true );
}
?>
<div id="fes-vendor-dashboard" class="fes-vendor-dashboard">
	<?php
	if ( isset( $_POST['create_post'] ) ) {
		$false = false;

		if ( is_array( $product_images ) ) {
			$product_images = array_filter( $product_images );
		}

		$errors = array();

		$download = isset( $_POST['download'] ) ? $_POST['download'] : '';

		if ( ! HP()->is_positive_number( $download ) ) {
			if ( empty( $download_file ) || ( isset( $download_file['name'] ) && empty( $download_file['name'] ) ) ) {
				$errors[] = __( 'Please select a download file!', 'pixelify' );
			}
		}

		$font_demos = isset( $_POST['font_demos'] ) ? $_POST['font_demos'] : '';

		if ( ! HP()->array_has_value( $font_demos ) ) {
			if ( empty( $font_file ) || ( isset( $font_file['name'] ) && empty( $font_file['name'] ) ) ) {
				$errors[] = __( 'Please select a font demo!', 'pixelify' );
			}
		}

		$post_images = isset( $_POST['post_images'] ) ? $_POST['post_images'] : '';
		$post_images = (array) $post_images;

		$post_images = array_filter( $post_images );
		$post_images = array_unique( $post_images );

		if ( empty( $product_images ) || ( isset( $product_images['name'][0] ) && empty( $product_images['name'][0] ) ) && ! HP()->array_has_value( $post_images ) ) {
			$errors[] = __( 'Please select at least one product image!', 'pixelify' );
		}

		if ( empty( $category ) ) {
			$errors[] = __( 'Please select at least one category!', 'pixelify' );
		}

		if ( empty( $post_title ) ) {
			$errors[] = __( 'Please enter post title!', 'pixelify' );
		}

		if ( empty( $post_content ) ) {
			$errors[] = __( 'Please enter post content!', 'pixelify' );
		}

		if ( ! HP()->array_has_value( $errors ) ) {
			if ( HP()->is_positive_number( $download ) ) {
				$upload = array( 'id' => $download );
			} else {
				$upload = Pixelify()->upload_file( basename( $download_file['name'] ), $download_file['tmp_name'] );
			}

			if ( isset( $upload['error'] ) && ! isset( $upload['file'] ) ) {
				$errors[] = sprintf( __( '<strong>Download File:</strong> %s', 'pixelify' ), $upload['error'] );
			} else {
				$download_file = $upload;

				if ( ! HP()->array_has_value( $font_demos ) ) {
					$tmp_file = isset( $font_file['name'] ) ? $font_file['name'] : '';

					if ( is_array( $tmp_file ) ) {
						$tmp_file = current( $tmp_file );
					}

					$tmp_name = isset( $font_file['tmp_name'] ) ? $font_file['tmp_name'] : '';

					if ( is_array( $tmp_name ) ) {
						$tmp_name = current( $tmp_name );
					}

					$upload = Pixelify()->upload_file( basename( $tmp_file ), $tmp_name );
				}

				if ( isset( $upload['error'] ) && ! isset( $upload['file'] ) ) {
					$errors[] = sprintf( __( '<strong>Font File:</strong> %s', 'pixelify' ), $upload['error'] );
				} else {
					$font_file = $upload;

					if ( ! HP()->array_has_value( $post_images ) ) {
						$names = isset( $product_images['name'] ) ? $product_images['name'] : '';
						$names = (array) $names;

						$names = array_filter( $names );

						if ( HP()->array_has_value( $names ) ) {
							$tmp = array();

							$count = 0;

							foreach ( $names as $key => $name ) {
								$tmp_name     = $product_images['tmp_name'][ $key ];
								$image_info   = getimagesize( $tmp_name );
								$image_width  = $image_info[0];
								$image_height = $image_info[1];

								if ( $image_width < $image_size['width'] || $image_height < $image_size['height'] ) {
									continue;
								} else {
									$upload = Pixelify()->upload_file( basename( $name ), $product_images['tmp_name'][ $key ] );

									if ( isset( $upload['error'] ) && ! isset( $upload['file'] ) ) {
										$false = true;
									} else {
										$tmp[] = $upload;
										$count ++;

										if ( $count >= Pixelify()->max_image ) {
											break;
										}
									}
								}
							}

							if ( empty( $tmp ) ) {
								$errors[] = __( 'Please select at least one valid product image!', 'pixelify' );
							} else {
								$product_images = $tmp;
							}
						} else {
							$errors[] = __( 'Please select at least one product image!', 'pixelify' );
						}
					}
				}
			}
		}

		if ( HP()->array_has_value( $errors ) ) {
			foreach ( $errors as $msg ) {
				?>
				<p class="alert alert-danger">
					<?php echo $msg; ?>
				</p>
				<?php
			}
		} else {
			$post_status = 'pending';

			if ( current_user_can( 'publish_posts' ) ) {
				$post_status = 'publish';
			}

			if ( isset( $_POST['save-draft'] ) ) {
				$post_status = 'draft';
			}

			$data = array(
				'post_title'   => wp_strip_all_tags( $post_title ),
				'post_content' => $post_content,
				'post_author'  => get_current_user_id(),
				'post_type'    => 'post',
				'post_status'  => $post_status
			);

			if ( $edit ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
				$id = $post_id;
			} else {
				$id = wp_insert_post( $data );
			}

			if ( $id ) {
				wp_set_post_terms( $id, $category, 'category' );
				wp_set_post_terms( $id, $download_tag );
				update_post_meta( $id, 'license1', $license1 );
				update_post_meta( $id, 'license2', $license2 );
				update_post_meta( $id, 'more_link', $checkout_url );

				if ( HP()->is_positive_number( $download ) ) {
					update_post_meta( $id, 'download', $download );
				} else {
					if ( isset( $download_file['id'] ) ) {
						update_post_meta( $id, 'download', $download_file['id'] );
					}
				}

				if ( HP()->array_has_value( $font_demos ) ) {
					$demos = array();

					foreach ( (array) $font_demos as $key => $att_id ) {
						$file = get_attached_file( $att_id );

						if ( file_exists( $file ) ) {
							$demos[ $key ] = array(
								'name' => basename( $file ),
								'url'  => wp_get_attachment_url( $att_id ),
								'id'   => $att_id
							);
						}
					}

					update_post_meta( $id, 'font_demos', $demos );
				} else {
					if ( isset( $font_file['id'] ) ) {
						$font_file['name'] = basename( $font_file['url'] );

						update_post_meta( $id, 'font_demos', array( '1' => $font_file ) );
					}
				}

				$slider_ids = array();

				if ( HP()->array_has_value( $post_images ) ) {
					$imgs = '';

					foreach ( $post_images as $att_id ) {
						$img = wp_get_attachment_image( $att_id, 'full' );

						if ( ! empty( $img ) ) {
							$imgs .= $img;
							$slider_ids[] = $att_id;

							if ( ! has_post_thumbnail( $id ) ) {
								set_post_thumbnail( $id, $att_id );
							}
						}
					}

					if ( ! empty( $imgs ) ) {
						update_post_meta( $id, 'post_slider', $imgs );
					}
				} else {
					if ( HP()->array_has_value( $product_images ) ) {
						$imgs = '';

						foreach ( $product_images as $data ) {
							if ( isset( $data['id'] ) && HP()->is_positive_number( $data['id'] ) ) {
								$img = wp_get_attachment_image( $data['id'], 'full' );

								if ( ! empty( $img ) ) {
									$imgs .= $img;
									$slider_ids[] = $data['id'];

									if ( ! has_post_thumbnail( $id ) ) {
										set_post_thumbnail( $id, $data['id'] );
									}
								}
							}
						}

						if ( ! empty( $imgs ) ) {
							update_post_meta( $id, 'post_slider', $imgs );
						}
					}
				}

				update_post_meta( $id, 'slider_ids', $slider_ids );

				$redirect = true;

				if ( $false ) {
					if ( $edit ) {
						?>
						<p class="alert alert-info">
							<?php _e( 'Post has been updated successfully but some data not valid.', 'pixelify' ); ?>
						</p>
						<?php
					} else {
						?>
						<p class="alert alert-info">
							<?php _e( 'Post has been added successfully but some data not valid.', 'pixelify' ); ?>
						</p>
						<?php
					}
				} else {
					if ( $edit ) {
						?>
						<p class="alert alert-success">
							<?php _e( 'Post has been updated successfully.', 'pixelify' ); ?>
						</p>
						<?php
					} else {
						?>
						<p class="alert alert-success">
							<?php _e( 'Post has been added successfully.', 'pixelify' ); ?>
						</p>
						<?php
					}
				}

				$post_id = $id;

				if ( ! has_post_thumbnail( $post_id ) ) {
					$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

					if ( HP()->array_has_value( $slider_ids ) ) {
						$id = array_shift( $slider_ids );
						set_post_thumbnail( $post_id, $id );
					}
				}

				do_action( 'hocwp_theme_save_post_data', $post_id );
			}
		}
	}
	?>
	<form class="fes-ajax-form fes-submission-form" action="" name="fes-submission-form" method="post"
	      enctype="multipart/form-data">
		<div class="fes-form fes-submission-form-div">
			<fieldset class="fes-form-fieldset fes-form-fieldset-submission">
				<legend class="fes-form-legend"
				        id="fes-submission-form-title"><?php _e( 'Create New Product', 'pixelify' ); ?></legend>
				<div class="fes-el zf_fes_file zf_fes_file">
					<div class="fes-label">
						<label
							for="zf_fes_file"><?php printf( __( 'Product File (%s only)', 'pixelify' ), $file_desc ); ?>
							<span
								class="fes-required-indicator">*</span>
						</label>
					</div>
					<?php
					$class = 'dropzone dz-clickable';

					$download = '';

					if ( $edit ) {
						$download = get_post_meta( $post_id, 'download', true );

						if ( HP()->is_positive_number( $download ) ) {
							$class .= ' has-media';
						}
					}
					?>
					<div id="fes-zf_fes_file" class="<?php echo $class; ?>" data-post-id="<?php echo $post_id; ?>">
						<input id="download-file" type="file" name="download_file"
						       accept="<?php echo join( ', ', $file_exts ); ?>" style="display: none"
						       data-max-size="<?php echo $upload_mb; ?>">

						<div class="dz-default dz-message">
							<span><?php printf( __( 'Upload File (%s)', 'pixelify' ), $file_desc ); ?><br><span
									class="size">(<?php printf( __( 'max size %dMB', 'pixelify' ), $upload_mb ); ?>
									)</span></span>
						</div>
						<div class="list-files">
							<?php
							if ( HP()->is_positive_number( $download ) ) {
								echo Pixelify()->file_preview_html( $download, $post_id, 'download' );
							}
							?>
						</div>
					</div>
				</div>
				<div class="fes-el zf_fes_file zf_fes_file">
					<div class="fes-label">
						<label
							for="zf_fes_file"><?php printf( __( 'Font demo (%s only)', 'pixelify' ), $font_desc ); ?>
							<span
								class="fes-required-indicator">*</span>
						</label>
					</div>
					<?php
					$class = 'dropzone dz-clickable multiple';

					$font_demos = array();

					if ( $edit ) {
						$font_demos = get_post_meta( $post_id, 'font_demos', true );

						if ( HP()->array_has_value( $font_demos ) ) {
							$class .= ' has-media';
						}
					}
					?>
					<div id="fes-font_file" class="<?php echo $class; ?>" data-post-id="<?php echo $post_id; ?>">
						<input id="font-file" type="file" name="font_file[]" multiple="multiple"
						       accept="<?php echo join( ', ', $font_exts ); ?>" style="display: none"
						       data-max-size="<?php echo $upload_mb; ?>">

						<div class="list-files">
							<?php
							if ( HP()->array_has_value( $font_demos ) ) {
								foreach ( $font_demos as $data ) {
									$att_id = isset( $data['id'] ) ? $data['id'] : '';

									if ( HP()->is_positive_number( $att_id ) ) {
										echo Pixelify()->file_preview_html( $att_id, $post_id );
									}
								}
							}
							?>
						</div>
						<div class="dz-default dz-message">
							<span><?php printf( __( 'Upload File (%s)', 'pixelify' ), $font_desc ); ?><br><span
									class="size">(<?php printf( __( 'max size %dMB', 'pixelify' ), $upload_mb ); ?>
									)</span></span>
						</div>
					</div>
				</div>
				<div id="zf_fes_featured_images" class="fes-el zf_fes_featured_images ui-sortable">
					<div class="fes-label">
						<label
							for="zf_fes_featured_images"><?php printf( __( 'Product Images (min. %s). Max %d images', 'pixelify' ), sprintf( '%dx%d pixel', $image_size['width'], $image_size['height'] ), Pixelify()->max_image ); ?>
							<span class="fes-required-indicator">*</span>
						</label>
					</div>
					<?php
					$class = 'dropzone dz-clickable';

					$slider_ids = array();

					$dzls = '';

					if ( $edit ) {
						$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

						if ( HP()->array_has_value( $slider_ids ) ) {
							$class .= ' has-image';
						}
					}

					if ( ! HP()->array_has_value( $slider_ids ) ) {
						$post_slider = get_post_meta( $post_id, 'post_slider', true );

						if ( ! empty( $post_slider ) ) {
							$slider_ids = array();

							$doc = new DOMDocument();
							@$doc->loadHTML( $post_slider );

							$tags = $doc->getElementsByTagName( 'img' );

							foreach ( $tags as $tag ) {
								$src = $tag->getAttribute( 'src' );

								$att_id = Pixelify()->get_attachment_id( $src );

								if ( HP()->is_positive_number( $att_id ) ) {
									$slider_ids[] = $att_id;
								}
							}

							if ( HP()->array_has_value( $slider_ids ) ) {
								$class .= ' has-image';
							}
						}
					}

					if ( HP()->array_has_value( $slider_ids ) && count( $slider_ids ) >= Pixelify()->max_image ) {
						$dzls = 'display:none;';
					}
					?>
					<div id="fes-zf_fes_featured_images" class="<?php echo $class; ?>"
					     data-post-id="<?php echo $post_id; ?>">
						<input id="product-images" type="file" name="product_images[]"
						       accept=".jpg, .png, .gif" style="display: none" multiple="multiple"
						       data-max="<?php echo Pixelify()->max_image; ?>"
						       data-max-width="<?php echo $image_size['width']; ?>"
						       data-max-height="<?php echo $image_size['height']; ?>"
						       data-max-size="<?php echo $upload_mb; ?>">

						<div class="dz-default dz-message" style="<?php echo $dzls; ?>">
							<span><?php _e( 'Add photos', 'pixelify' ); ?></span>
						</div>
						<?php
						if ( HP()->array_has_value( $slider_ids ) ) {
							foreach ( $slider_ids as $att_id ) {
								echo Pixelify()->image_preview_html( $att_id, $post_id );
							}
						}
						?>
					</div>
				</div>
				<div class="fes-el download_category download_category">
					<div class="fes-label">
						<label for="download_category"><?php _e( 'Category', 'pixelify' ); ?><span
								class="fes-required-indicator">*</span></label>
					</div>
					<div class="fes-fields">
						<?php
						$settings = array(
							'hide_empty'       => false,
							'show_option_none' => __( '-- Select categories --', 'pixelify' ),
							'hierarchical'     => true,
							'name'             => 'category[]',
							'id'               => 'category'
						);
						wp_dropdown_categories( $settings );
						?>
					</div>
				</div>
				<div class="fes-el post_title post_title">
					<div class="fes-label">
						<label for="post_title"><?php _e( 'Product Name', 'pixelify' ); ?><span
								class="fes-required-indicator">*</span></label>
					</div>
					<div class="fes-fields">
						<input class="textfield fes-required-field" id="post_title" type="text" data-required="1"
						       data-type="text" name="post_title" placeholder="" value="<?php echo $post_title; ?>"
						       size="40">
					</div>
				</div>
				<div class="fes-el post_content post_content">
					<div class="fes-label">
						<label for="post_content"><?php _e( 'Product Description', 'pixelify' ); ?><span
								class="fes-required-indicator">*</span></label>
					</div>
					<div class="fes-fields">
						<span class="fes-rich-validation" data-required="yes" data-type="rich"
						      data-id="post_content"></span>
						<?php
						$settings = array(
							'teeny'         => false,
							'media_buttons' => false,
							'quicktags'     => false,
							'textarea_rows' => 5
						);
						wp_editor( $post_content, 'post_content', $settings );
						?>
					</div>
				</div>
				<div class="fes-el download_tag download_tag">
					<div class="fes-label">
						<label
							for="download_tag"><?php printf( __( 'Tags (max %d, separate with comma)', 'pixelify' ), Pixelify()->max_tag ); ?></label>
					</div>
					<div class="fes-fields">
						<input class="textfield" id="download_tag" type="text" data-required="" data-type="text"
						       name="download_tag" value="<?php echo $download_tag; ?>" size="40" autocomplete="off">
						<script type="text/javascript">
							jQuery(function () {
								jQuery('#download_tagd').suggest(pixelify.ajaxUrl + '?action=fes_ajax_taxonomy_search&tax=download_tag', {
									delay: 500,
									minchars: 2,
									multiple: true,
									multipleSep: ', '
								});
							});
						</script>
					</div>
				</div>
				<div class="fes-el checkout-url download_tag">
					<div class="fes-label">
						<label
							for="checkout_url"><?php _e( 'Check Out More', 'pixelify' ); ?></label>
					</div>
					<div class="fes-fields">
						<input class="textfield" id="checkout_url" type="text" data-type="text"
						       name="checkout_url" value="<?php echo $checkout_url; ?>" autocomplete="off">
					</div>
				</div>
				<div class="fes-el radio license_options">
					<div class="fes-label">
						<label for="license_options"><?php _e( 'License option', 'pixelify' ); ?><span
								class="fes-required-indicator">*</span></label>
					</div>

					<?php
					if ( 1 != $license2 ) {
						$license1 = 1;
					}
					?>

					<div class="fes-fields">
						<ul class="fes-checkbox-checklist">
							<li>
								<label>
									<input name="license_options" type="radio"
									       value="<?php _e( 'Free for Personal Use', 'pixelify' ); ?>"<?php checked( 1, $license1 ); ?>>
									<?php _e( 'Free for Personal Use', 'pixelify' ); ?>
								</label>
							</li>
							<li>
								<label>
									<input name="license_options" type="radio"
									       value="<?php echo $ffcu; ?>"<?php checked( 1, $license2 ); ?>>
									<?php echo $ffcu; ?>
								</label>
							</li>
						</ul>
					</div>
				</div>
				<div class="fes-submit">
					<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>">

					<?php
					if ( $edit ) {
						?>
						<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
						<input type="submit" id="fes-submit" class="edd-submit blue button" name="submit"
						       value="<?php _e( 'Update', 'pixelify' ); ?>">
						<?php
					} else {
						?>
						<input type="submit" id="fes-save-as-draft" class="edd-submit blue button" name="save-draft"
						       value="<?php _e( 'Save Draft', 'pixelify' ); ?>">
						<input type="submit" id="fes-submit" class="edd-submit blue button" name="submit"
						       value="<?php _e( 'Submit', 'pixelify' ); ?>">
						<?php
					}
					?>
					<input type="hidden" name="create_post">
				</div>
			</fieldset>
			<script>
				function formatBytes(bytes, decimals, wrap) {
					wrap = wrap || null;
					if (0 >= bytes) {
						bytes = 0;
					} else {
						var k = 1024,
							dm = decimals || 2,
							sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
							i = Math.floor(Math.log(bytes) / Math.log(k));
						bytes = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));
					}

					if (wrap) {
						bytes = wrap.replace("NUMBER", bytes);
					}

					return bytes + ' ' + sizes[i];
				}

				jQuery(document).ready(function ($) {
					var body = $("body");

					function hocwp_theme_remove_file_preview(filePreview, inputFile, fesFile, key) {
						filePreview.on("click", ".dz-remove", function (e) {
							e.preventDefault();
							e.stopPropagation();
							inputFile.val(null);
							inputFile.trigger("change");

							if (confirm(pixelify.text.are_you_sure)) {
								var postID = parseInt(filePreview.attr("data-post-id"));

								if (!$.isNumeric(postID)) {
									postID = parseInt(fesFile.attr("data-post-id"));
								}

								key = key || "font_demos";

								$.ajax({
									type: "POST",
									dataType: "JSON",
									url: pixelify.ajaxUrl,
									cache: true,
									data: {
										action: "hocwp_pxf_remove_file",
										id: filePreview.attr("data-id"),
										post_id: postID,
										key: key
									},
									success: function () {
										filePreview.removeClass("dz-error");
										filePreview.removeClass("dz-complete");
										filePreview.removeClass("dz-processing");
										filePreview.removeClass("has-media");
										filePreview.attr("data-id", "");
										filePreview.find("input.media-id").val();

										var thumbs = fesFile.find(".dz-preview.has-media");

										if (!thumbs.length) {
											fesFile.removeClass("has-media");
										} else {
											fesFile.addClass("has-media");
										}

										fesFile.find(".dz-message").show();

										filePreview.remove();
									}
								});
							}
						});
					}

					// Download upload
					(function () {
						var fesFile = $("#fes-zf_fes_file"),
							inputFile = fesFile.find("#download-file"),
							dzLabel = fesFile.find(".dz-default.dz-message");

						dzLabel.on("click", function (e) {
							e.preventDefault();
							inputFile.val(null);
							inputFile.trigger("click");
						});

						fesFile.find(".dz-preview.has-media").each(function () {
							hocwp_theme_remove_file_preview($(this), inputFile, fesFile, "download");
						});

						inputFile.on("change", function () {
							if (this.files && this.files.length) {
								var files = this.files,
									i = 0,
									count = files.length,
									maxSize = parseFloat(inputFile.attr("data-max-size"));

								for (i; i < count; i++) {
									var file = files[i];

									if (file) {
										var fileSize = file.size;

										if (fileSize > (maxSize * 1024 * 1024)) {
											inputFile.val(null);
											inputFile.trigger("change");

											return;
										}

										(function (file) {
											dzLabel.hide();

											fesFile.addClass("has-media");

											var filePreview = fesFile.find("div.dz-preview:not(.has-media):not(.dz-processing)").first();

											if (!filePreview.length) {
												fesFile.find(".list-files").append(pixelify.downloadPreview);
												filePreview = fesFile.find("div.dz-preview:not(.has-media):not(.dz-processing)").first();
											}

											filePreview.show();
											filePreview.addClass("dz-processing");

											var Upload = function (file) {
												this.file = file;
											};

											Upload.prototype.getType = function () {
												return this.file.type;
											};

											Upload.prototype.getSize = function () {
												return this.file.size;
											};

											Upload.prototype.getName = function () {
												return this.file.name;
											};

											Upload.prototype.doUpload = function () {
												var that = this;
												var formData = new FormData();

												formData.append("file", this.file);
												formData.append("upload_file", true);
												formData.append("accept", inputFile.attr("accept"));

												var postID = parseInt(fesFile.attr("data-post-id"));

												$.ajax({
													type: "POST",
													url: pixelify.ajaxUrl + "?action=hocwp_pxf_upload_file&key=download&post_id=" + postID,
													xhr: function () {
														var myXhr = $.ajaxSettings.xhr();

														if (myXhr.upload) {
															myXhr.upload.addEventListener("progress", that.progressHandling, false);
														}

														return myXhr;
													},
													success: function (response) {
														filePreview.removeClass("dz-processing");
														filePreview.addClass("dz-complete");

														if (response.success) {
															if (response.data.id) {
																filePreview.attr("data-id", response.data.id);
																filePreview.find("input.media-id").val(response.data.id);
																filePreview.addClass("has-media");
																fesFile.addClass("has-media");
																filePreview.removeClass("dz-processing");

																var sizePreview = filePreview.find(".dz-size span"),
																	namePreview = filePreview.find(".dz-filename span");

																sizePreview.attr("data-dz-size", that.getSize());
																sizePreview.html(formatBytes(that.getSize(), 2, "<strong>NUMBER</strong>"));

																namePreview.attr("data-dz-name", that.getName());
																namePreview.html(that.getName());
																filePreview.show();
															}

															inputFile.val(null);
														} else {
															filePreview.remove();

															var previews = fesFile.find(".dz-preview");

															if (!previews.length) {
																fesFile.removeClass("has-media");
																dzLabel.show();
															}

															if (response.data.message) {
																alert(response.data.message);
															}
														}

														hocwp_theme_remove_file_preview(filePreview, inputFile, fesFile, "download");
													},
													error: function (error) {
													},
													async: true,
													data: formData,
													cache: false,
													contentType: false,
													processData: false,
													timeout: 60000
												});
											};

											Upload.prototype.progressHandling = function (event) {
												var percent = 0;
												var position = event.loaded || event.position;
												var total = event.total;

												if (event.lengthComputable) {
													percent = Math.ceil(position / total * 100);
												}

												var dzUpload = filePreview.find(".dz-upload");

												dzUpload.css({width: percent.toString() + "%"});

												if (percent <= 100) {
													filePreview.addClass("dz-processing");
												} else {
													filePreview.removeClass("dz-processing");
												}
											};

											var upload = new Upload(file);

											upload.doUpload();
										})(file);
									}
								}
							}
						});
					})();

					// Fonts upload
					(function () {
						var fesFile = $("#fes-font_file"),
							inputFile = fesFile.find("#font-file"),
							dzLabel = fesFile.find(".dz-default.dz-message");

						dzLabel.on("click", function (e) {
							e.preventDefault();
							inputFile.val(null);
							inputFile.trigger("click");
						});

						fesFile.find(".dz-preview.has-media").each(function () {
							hocwp_theme_remove_file_preview($(this), inputFile, fesFile);
						});

						inputFile.on("change", function () {
							if (this.files && this.files.length) {
								var files = this.files,
									i = 0,
									count = files.length,
									maxSize = parseFloat(inputFile.attr("data-max-size"));

								for (i; i < count; i++) {
									var file = files[i];

									if (file) {
										var fileSize = file.size;

										if (fileSize > (maxSize * 1024 * 1024)) {
											inputFile.val(null);
											inputFile.trigger("change");

											return;
										}

										(function (file) {
											fesFile.addClass("has-media");

											var filePreview = fesFile.find("div.dz-preview:not(.has-media):not(.dz-processing)").first();

											if (!filePreview.length) {
												fesFile.find(".list-files").append(pixelify.filePreview);
												filePreview = fesFile.find("div.dz-preview:not(.has-media):not(.dz-processing)").first();
											}

											filePreview.show();
											filePreview.addClass("dz-processing");

											var Upload = function (file) {
												this.file = file;
											};

											Upload.prototype.getType = function () {
												return this.file.type;
											};

											Upload.prototype.getSize = function () {
												return this.file.size;
											};

											Upload.prototype.getName = function () {
												return this.file.name;
											};

											Upload.prototype.doUpload = function () {
												var that = this;
												var formData = new FormData();

												// add assoc key values, this will be posts values
												formData.append("file", this.file);
												formData.append("upload_file", true);
												formData.append("accept", inputFile.attr("accept"));

												var postID = parseInt(fesFile.attr("data-post-id"));

												$.ajax({
													type: "POST",
													url: pixelify.ajaxUrl + "?action=hocwp_pxf_upload_file&key=font_demos&post_id=" + postID,
													xhr: function () {
														var myXhr = $.ajaxSettings.xhr();

														if (myXhr.upload) {
															myXhr.upload.addEventListener("progress", that.progressHandling, false);
														}

														return myXhr;
													},
													success: function (response) {
														// your callback here
														filePreview.removeClass("dz-processing");
														filePreview.addClass("dz-complete");

														if (response.success) {
															if (response.data.id) {
																filePreview.attr("data-id", response.data.id);
																filePreview.find("input.media-id").val(response.data.id);
																filePreview.addClass("has-media");
																fesFile.addClass("has-media");
																filePreview.removeClass("dz-processing");

																var sizePreview = filePreview.find(".dz-size span"),
																	namePreview = filePreview.find(".dz-filename span");

																sizePreview.attr("data-dz-size", that.getSize());
																sizePreview.html(formatBytes(that.getSize(), 2, "<strong>NUMBER</strong>"));

																namePreview.attr("data-dz-name", that.getName());
																namePreview.html(that.getName());
																filePreview.show();
															}

															inputFile.val(null);
														} else {
															filePreview.remove();

															var previews = fesFile.find(".dz-preview");

															if (!previews.length) {
																fesFile.removeClass("has-media");
																dzLabel.show();
															}

															if (response.data.message) {
																alert(response.data.message);
															}
														}

														hocwp_theme_remove_file_preview(filePreview, inputFile, fesFile);
													},
													error: function (error) {
														// handle error
													},
													async: true,
													data: formData,
													cache: false,
													contentType: false,
													processData: false,
													timeout: 60000
												});
											};

											Upload.prototype.progressHandling = function (event) {
												var percent = 0;
												var position = event.loaded || event.position;
												var total = event.total;

												if (event.lengthComputable) {
													percent = Math.ceil(position / total * 100);
												}

												var dzUpload = filePreview.find(".dz-upload");

												dzUpload.css({width: percent.toString() + "%"});

												if (percent <= 100) {
													filePreview.addClass("dz-processing");
												} else {
													filePreview.removeClass("dz-processing");
												}
											};

											var upload = new Upload(file);

											// maby check size or type here with upload.getSize() and upload.getType()

											// execute upload
											upload.doUpload();
										})(file);
									}
								}
							}
						});
					})();

					// Sliders upload
					(function () {
						var fesFile = $("#fes-zf_fes_featured_images"),
							inputFile = fesFile.find("#product-images"),
							dzLabel = fesFile.find(".dz-default.dz-message");

						dzLabel.on("click", function (e) {
							e.preventDefault();
							e.stopPropagation();
							inputFile.trigger("click");
						});

						fesFile.find(".dz-preview.has-image").each(function () {
							hocwp_theme_remove_image_preview($(this), inputFile, fesFile, null, dzLabel);
						});

						function hocwp_theme_remove_image_preview(imagePreview, inputFile, fesFile, maxImage, dzLabel) {
							imagePreview.on("click", ".dz-remove", function (e) {
								e.preventDefault();
								e.stopPropagation();
								inputFile.val(null);
								inputFile.trigger("change");

								if (confirm(pixelify.text.are_you_sure)) {
									maxImage = maxImage || parseInt(inputFile.attr("data-max"));

									var postID = parseInt(imagePreview.attr("data-post-id"));

									if (!$.isNumeric(postID)) {
										postID = parseInt(fesFile.attr("data-post-id"));
									}

									$.ajax({
										type: "POST",
										dataType: "JSON",
										url: pixelify.ajaxUrl,
										cache: true,
										data: {
											action: "hocwp_pxf_remove_file",
											id: imagePreview.attr("data-id"),
											post_id: postID
										},
										success: function () {
											imagePreview.removeClass("dz-error");
											imagePreview.removeClass("dz-complete");
											imagePreview.removeClass("dz-processing");
											imagePreview.removeClass("has-image");
											imagePreview.attr("data-id", "");
											imagePreview.find("input.media-id").val();

											var thumbs = fesFile.find(".dz-preview.has-image");

											if (!thumbs.length) {
												fesFile.removeClass("has-image");
											} else {
												fesFile.addClass("has-image");
											}

											imagePreview.remove();

											var previews = fesFile.find(".dz-preview");

											if (!previews.length) {
												fesFile.append(pixelify.imagePreview);
											}

											if (thumbs.length < maxImage) {
												dzLabel.show();
											} else {
												dzLabel.hide();
											}
										}
									});
								}
							});
						}

						inputFile.on("change", function () {
							dzLabel.hide();

							if (this.files && this.files.length) {
								var maxImage = parseInt(inputFile.attr("data-max")),
									thumbs = fesFile.find(".dz-preview.has-image");

								if (this.files.length <= maxImage && (!thumbs.length || (thumbs.length + this.files.length) <= maxImage)) {
									var files = this.files,
										i = 0,
										count = files.length,
										maxSize = parseFloat(inputFile.attr("data-max-size"));

									//dzLabel.hide();

									for (i; i < count; i++) {
										var file = files[i];

										if (file) {
											if (file.size > (maxSize * 1024 * 1024)) {
												inputFile.val(null);
												inputFile.trigger("change");

												return;
											}

											(function (file) {
												var imagePreview = fesFile.find("div.dz-preview:not(.has-image):not(.dz-processing)").first();

												if (!imagePreview.length) {
													fesFile.append(pixelify.imagePreview);
													imagePreview = fesFile.find("div.dz-preview:not(.has-image):not(.dz-processing)").first();
												}

												imagePreview.addClass("dz-processing");

												var Upload = function (file) {
													this.file = file;
												};

												Upload.prototype.getType = function () {
													return this.file.type;
												};

												Upload.prototype.getSize = function () {
													return this.file.size;
												};

												Upload.prototype.getName = function () {
													return this.file.name;
												};

												Upload.prototype.doUpload = function () {
													var that = this;
													var formData = new FormData();

													// add assoc key values, this will be posts values
													formData.append("file", this.file);
													formData.append("upload_file", true);
													formData.append("accept", inputFile.attr("accept"));

													var postID = parseInt(fesFile.attr("data-post-id"));

													$.ajax({
														type: "POST",
														url: pixelify.ajaxUrl + "?action=hocwp_pxf_upload_file&post_id=" + postID,
														xhr: function () {
															var myXhr = $.ajaxSettings.xhr();

															if (myXhr.upload) {
																myXhr.upload.addEventListener("progress", that.progressHandling, false);
															}

															return myXhr;
														},
														success: function (response) {
															// your callback here
															imagePreview.removeClass("dz-processing");
															imagePreview.addClass("dz-complete");

															if (response.success) {
																if (response.data.id) {
																	imagePreview.attr("data-id", response.data.id);
																	imagePreview.find("input.media-id").val(response.data.id);
																	imagePreview.addClass("has-image");
																	fesFile.addClass("has-image");
																	imagePreview.removeClass("dz-processing");

																	var thumbs = fesFile.find(".dz-preview.has-image");

																	if (count < maxImage && (!thumbs.length || (thumbs.length + 1) <= maxImage)) {
																		dzLabel.show();
																	} else {
																		dzLabel.hide();
																	}
																}

																inputFile.val(null);
															} else {
																imagePreview.remove();

																var previews = fesFile.find(".dz-preview");

																if (!previews.length) {
																	fesFile.removeClass("has-image");
																	dzLabel.show();
																}

																if (response.data.message) {
																	alert(response.data.message);
																}
															}
														},
														error: function (error) {
															// handle error
														},
														async: true,
														data: formData,
														cache: false,
														contentType: false,
														processData: false,
														timeout: 60000
													});
												};

												Upload.prototype.progressHandling = function (event) {
													var percent = 0;
													var position = event.loaded || event.position;
													var total = event.total;

													if (event.lengthComputable) {
														percent = Math.ceil(position / total * 100);
													}

													var dzUpload = imagePreview.find(".dz-upload");

													dzUpload.css({width: percent.toString() + "%"});

													if (percent <= 100) {
														imagePreview.addClass("dz-processing");
													} else {
														imagePreview.removeClass("dz-processing");
													}
												};

												var upload = new Upload(file);

												// maby check size or type here with upload.getSize() and upload.getType()

												// execute upload
												upload.doUpload();

												var maxWidth = parseInt(inputFile.attr("data-max-width")),
													maxHeight = parseInt(inputFile.attr("data-max-height"));

												(function (maxWidth, maxHeight, imagePreview, file) {
													var img = new Image();

													img.src = window.URL.createObjectURL(file);

													img.onload = function () {
														var width = img.width,
															height = img.height;

														window.URL.revokeObjectURL(img.src);

														if (width < maxHeight || height < maxHeight) {
															imagePreview.addClass("dz-error");
														}
													};

													var reader = new FileReader();

													reader.onload = function (e) {
														imagePreview.find(".dz-image img").attr("src", e.target.result).show();
														imagePreview.show();
													};

													reader.readAsDataURL(file);

													hocwp_theme_remove_image_preview(imagePreview, inputFile, fesFile, maxImage, dzLabel);
												})(maxWidth, maxHeight, imagePreview, file);
											})(file);
										}
									}
								} else {
									alert("<?php printf(__('You cannot upload more than %d images.', 'pixelify'), Pixelify()->max_image); ?>");
									inputFile.val(null);
									inputFile.trigger("change");

									if (this.files.length == 1) {
										dzLabel.hide();
									} else {
										dzLabel.show();
									}
								}
							}
						});
					})();
				});
			</script>
		</div>
	</form>
	<?php
	if ( $redirect ) {
		?>
		<script>
			setTimeout(function () {
				window.location.href = "<?php the_permalink(); ?>";
			}, 2000);
		</script>
		<?php
	}
	?>
</div>