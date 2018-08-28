<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_pxf_post_slider_meta_boxes() {
	add_meta_box( 'pxf_post_slider', __( 'Post Slider', 'pixelify' ), 'hocwp_pxf_post_slider_meta_box_callback', 'post' );
	add_meta_box( 'pxf_extra_information', __( 'Extra Information', 'pixelify' ), 'hocwp_pxf_extra_information_meta_box_callback', 'post' );
}

add_action( 'add_meta_boxes', 'hocwp_pxf_post_slider_meta_boxes' );

function hocwp_pxf_post_slider_meta_box_callback( $post ) {
	wp_nonce_field( 'pxf-post-slider', 'pxf_post_slider_nonce' );
	$value = get_post_meta( $post->ID, 'post_slider', true );

	$args = array(
		'textarea_name' => 'post_slider',
		'textarea_rows' => 6
	);
	wp_editor( $value, 'post_slider', $args );
}

function hocwp_pxf_extra_information_meta_box_callback( $post ) {
	$license1 = get_post_meta( $post->ID, 'license1', true );
	$license2 = get_post_meta( $post->ID, 'license2', true );

	if ( '' == $license1 && '' == $license2 ) {
		$license2 = 1;
	}
	?>
	<table class="form-table">
		<tbody>
		<tr>
			<th>
				<label for="license"><?php _e( 'License option', 'pixelify' ); ?></label>
			</th>
			<td>
				<div class="meta-row">
					<fieldset>
						<label for="license_license1">
							<input type="radio" id="license_license1" name="license"
							       value="license1"<?php checked( 1, $license1 ); ?>><?php _e( 'Free for Personal Use', 'pixelify' ); ?>
						</label>
						<span style="display: inline-block; margin: 0 20px;"></span>
						<label for="license_license2">
							<input type="radio" id="license_license2" name="license"
							       value="license2"<?php checked( 1, $license2 ); ?>><?php _e( 'Free for Commercial Use', 'pixelify' ); ?>
						</label>
					</fieldset>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				<label for="download"><?php _e( 'Download', 'pixelify' ); ?></label>
			</th>
			<td>
				<div class="meta-row media-file">
					<fieldset>
						<?php
						$download = get_post_meta( $post->ID, 'download', true );

						if ( is_array( $download ) ) {
							$download = isset( $download['id'] ) ? $download['id'] : '';
						}

						if ( empty( $download ) ) {
							$download = '';
						}
						?>
						<input id="download" type="text" readonly name="download"
						       value="<?php echo esc_attr( $download ); ?>">
						<button class="set-download button"
						        type="button"><?php _e( 'Add file', 'pixelify' ); ?></button>
						<p class="description">
							<?php
							if ( HP()->is_positive_number( $download ) ) {
								echo '(' . wp_get_attachment_url( $download ) . ')';
							}
							?>
						</p>
					</fieldset>
				</div>
				<script>
					jQuery(document).ready(function ($) {
						(function () {
							var button = $(".media-file .button");

							if (button.length) {
								if (typeof wp !== "undefined" && wp.media && wp.media.editor) {
									button.on("click", function (e) {
										e.preventDefault();
										var element = $(this);
										var id = element.prev();
										wp.media.editor.send.attachment = function (props, attachment) {
											element.next().html("(" + attachment.url + ")");
											id.val(attachment.id);
										};
										wp.media.editor.open(button);
										return false;
									});
								}
							}
						})();
					});
				</script>
			</td>
		</tr>
		</tbody>
	</table>
	<?php
}

function hocwp_pxf_meta_save( $post_id ) {
	if ( isset( $_POST['post_slider'] ) ) {
		update_post_meta( $post_id, 'post_slider', $_POST['post_slider'] );
	}

	if ( isset( $_POST['license'] ) ) {
		$license = $_POST['license'];

		if ( 'license1' == $license ) {
			update_post_meta( $post_id, 'license1', 1 );
			update_post_meta( $post_id, 'license2', 0 );
		} else {
			update_post_meta( $post_id, 'license1', 0 );
			update_post_meta( $post_id, 'license2', 1 );
		}
	}

	if ( isset( $_POST['download'] ) ) {
		update_post_meta( $post_id, 'download', $_POST['download'] );
	}
}

add_action( 'save_post', 'hocwp_pxf_meta_save' );