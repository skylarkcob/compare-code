<?php
function hocwp_compress_style_and_script_ajax_callback() {
	$result         = array();
	$type           = hocwp_get_method_value( 'type' );
	$type           = hocwp_json_string_to_array( $type );
	$force_compress = hocwp_get_method_value( 'force_compress' );
	$force_compress = hocwp_string_to_bool( $force_compress );
	$compress_core  = hocwp_get_method_value( 'compress_core' );
	$compress_core  = hocwp_string_to_bool( $compress_core );
	$args           = array(
		'type'           => $type,
		'force_compress' => $force_compress,
		'compress_core'  => $compress_core
	);
	hocwp_compress_style_and_script( $args );
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_compress_style_and_script', 'hocwp_compress_style_and_script_ajax_callback' );

function hocwp_debug_log_ajax_callback() {
	$object = hocwp_get_method_value( 'object' );
	$object = hocwp_json_string_to_array( $object );
	hocwp_debug_log( $object );
	exit;
}

add_action( 'wp_ajax_hocwp_debug_log', 'hocwp_debug_log_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_debug_log', 'hocwp_debug_log_ajax_callback' );

function hocwp_debug_log( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( print_r( $message, true ) );
		} else {
			error_log( $message );
		}
	}
}