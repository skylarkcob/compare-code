<?php
function hocwp_font_downloads_ajax_callback() {
	$post_id = hocwp_get_method_value( 'post_id' );
	if ( hocwp_id_number_valid( $post_id ) ) {
		$downloads = get_post_meta( $post_id, 'downloads', true );
		$downloads = absint( $downloads );
		$downloads ++;
		update_post_meta( $post_id, 'downloads', $downloads );
	}
	die();
}

add_action( 'wp_ajax_hocwp_font_downloads', 'hocwp_font_downloads_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_font_downloads', 'hocwp_font_downloads_ajax_callback' );

function hocwp_font_update_demo_ajax_callback() {
	$post_id = hocwp_get_method_value( 'post_id' );
	$result  = array(
		'success' => false
	);
	if ( hocwp_id_number_valid( $post_id ) ) {
		if ( ! hocwp_can_save_post( $post_id ) ) {
			return;
		}
		$obj     = get_post( $post_id );
		$alphabe = substr( $obj->post_title, 0, 1 );
		if ( is_numeric( $alphabe ) || preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $alphabe ) ) {
			$alphabe = '#';
		}
		update_post_meta( $post_id, 'alphabe', $alphabe );
		$demo          = get_post_meta( $post_id, 'demo', true );
		$demo          = hocwp_sanitize_media_value( $demo );
		$file_contents = get_post_meta( $post_id, 'file_contents', true );
		$file_contents = hocwp_sanitize_media_value( $file_contents );
		if ( empty( $demo['url'] ) && ! empty( $file_contents['url'] ) ) {
			$demo = hocwp_theme_custom_add_demo_from_file_contents( $post_id, $file_contents );
			update_post_meta( $post_id, 'demo', $demo );
			unset( $_POST['demo'] );
		}
		if ( ! empty( $demo['url'] ) ) {
			$result['success'] = true;
		}
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_font_update_demo', 'hocwp_font_update_demo_ajax_callback' );

function hocwp_generate_font_thumbnail_ajax_callback() {
	$post_id = hocwp_get_method_value( 'post_id' );

	HT_Custom()->generate_font_preview_thumbnail( $post_id );

	die();
}

add_action( 'wp_ajax_hocwp_generate_font_thumbnail', 'hocwp_generate_font_thumbnail_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_generate_font_thumbnail', 'hocwp_generate_font_thumbnail_ajax_callback' );

function hocwp_generate_font_more_demo_ajax_callback() {
	$result         = array(
		'success' => false
	);
	$post_data      = isset( $_POST['post_data'] ) ? $_POST['post_data'] : '';
	$post_data      = hocwp_json_string_to_array( $post_data );
	$transient_name = 'hocwp_more_demo_' . md5( json_encode( $post_data ) );
	if ( false === ( $html = get_transient( $transient_name ) ) ) {
		$name       = isset( $post_data['name'] ) ? $post_data['name'] : '';
		$demo_class = isset( $post_data['demo_class'] ) ? $post_data['demo_class'] : '';
		$demo_style = isset( $post_data['demo_style'] ) ? $post_data['demo_style'] : '';
		$original   = isset( $post_data['original'] ) ? $post_data['original'] : '';
		$post_name  = isset( $post_data['post_name'] ) ? $post_data['post_name'] : '';
		if ( ! empty( $original ) ) {
			$parts = parse_url( $original );
			parse_str( $parts['query'], $query );
			$post_id = isset( $query['post_id'] ) ? $query['post_id'] : '';
			$size    = isset( $query['size'] ) ? $query['size'] : 80;
			$text    = isset( $query['text'] ) ? $query['text'] : '';
			if ( ! empty( $post_name ) ) {
				$text = $post_name;
			}
			$color   = isset( $query['color'] ) ? $query['color'] : '000';
			$font    = isset( $query['font'] ) ? $query['font'] : '';
			$data    = isset( $post_data['post_data'] ) ? $post_data['post_data'] : '';
			$preview = hocwp_theme_custom_get_preview_url();
			$query   = array_map( 'urlencode', $query );
			$preview = add_query_arg( $query, $preview );
			//$original = hocwp_generate_font_preview( $post_id, $text, $size, $color, false, $font, $data );
			$original = $preview;
		}
		$demo_text = isset( $post_data['demo_text'] ) ? $post_data['demo_text'] : '';
		$size      = isset( $post_data['size'] ) ? $post_data['size'] : 80;
		$color     = isset( $post_data['color'] ) ? $post_data['color'] : '000';
		ob_start();
		?>
		<div class="font-row">
			<div>
				<span class="file-name"><?php echo $name; ?></span>
			</div>
			<div class="<?php echo $demo_class; ?>" style="<?php echo $demo_style; ?>"
			     data-original="<?php echo $original; ?>">
				<?php
				$font_family = '';
				$style       = 'font-size:' . $size . 'px;color:' . $color;
				if ( $demo_text ) {
					$font_name   = $name;
					$font_info   = pathinfo( $font_name );
					$font_family = $font_info['filename'];
					$font_family = sanitize_html_class( $font_family );
					$style .= ';font-family:' . $font_family;
					$font_url = trailingslashit( $pathinfo['url'] ) . $font_name;
					?>
					<style type="text/css">
						@font-face {
							font-family: '<?php echo $font_family; ?>';
							src: url('<?php echo $font_url; ?>') format('truetype');
						}
					</style>
					<?php
				}
				?>
				<h3 class="font-name" style="<?php echo $style; ?>"><?php echo $post_name; ?></h3>
			</div>
		</div>
		<?php
		$html = ob_get_clean();
		set_transient( $transient_name, $html, MONTH_IN_SECONDS );
	}
	$result['html']    = $html;
	$result['success'] = true;
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_generate_font_more_demo', 'hocwp_generate_font_more_demo_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_generate_font_more_demo', 'hocwp_generate_font_more_demo_ajax_callback' );