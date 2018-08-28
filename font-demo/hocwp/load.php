<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

if ( defined( 'HOCWP_VERSION' ) ) {
	return;
}

function hocwp_autoload( $class_name ) {
	$base_path   = dirname( __FILE__ ) . '/inc';
	$pieces      = explode( '_', $class_name );
	$pieces      = array_filter( $pieces );
	$first_piece = current( $pieces );
	if ( 'HOCWP' !== $class_name && 'HOCWP' !== $first_piece ) {
		return;
	}
	if ( false !== strrpos( $class_name, 'HOCWP_Widget' ) ) {
		$base_path .= '/widgets';
	}
	$file = $base_path . '/class-' . hocwp_sanitize_file_name( $class_name );
	$file .= '.php';
	if ( file_exists( $file ) ) {
		require( $file );
	}
}

spl_autoload_register( 'hocwp_autoload' );

require( dirname( __FILE__ ) . '/class-hocwp.php' );
$core = new HOCWP();
$core->load();