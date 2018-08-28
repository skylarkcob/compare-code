<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

function hocwp_font_demo_download_count_ajax_callback() {
	$post_id = hocwp_get_method_value( 'post_id' );
	if ( hocwp_id_number_valid( $post_id ) ) {
		$count = hocwp_get_post_meta( 'download_count', $post_id );
		$count = absint( $count );
		$count ++;
		update_post_meta( $post_id, 'download_count', $count );
	}
	die();
}

add_action( 'wp_ajax_hocwp_font_demo_download_count', 'hocwp_font_demo_download_count_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_font_demo_download_count', 'hocwp_font_demo_download_count_ajax_callback' );