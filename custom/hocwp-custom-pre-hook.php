<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

if ( ! defined( 'HOCWP_THEME_VERSION' ) ) {
	define( 'HOCWP_THEME_VERSION', '1.0.7' );
}

function hocwp_theme_custom_license_data() {
	$data = array(
		'hashed'  => '',
		'key_map' => ''
	);

	return $data;
}

add_filter( 'hocwp_theme_license_defined_data', 'hocwp_theme_custom_license_data' );

function hocwp_theme_custom_load_addthis( $use ) {
	$use = true;

	return $use;
}

add_filter( 'hocwp_use_addthis', 'hocwp_theme_custom_load_addthis' );

function hocwp_theme_custom_scripts() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
		'jquery-ui-draggable',
		'jquery-ui-slider',
		'jquery-touch-punch'
	), false, 1 );
	wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), false, 1 );
	$colorpicker_l10n = array(
		'clear'         => __( 'Clear', 'hocwp-theme' ),
		'defaultString' => __( 'Default', 'hocwp-theme' ),
		'pick'          => __( 'Select Color', 'hocwp-theme' )
	);
	wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
	wp_enqueue_script( 'lazyload', get_template_directory_uri() . '/lib/jquery.lazyload.min.js', array( 'jquery' ), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_custom_scripts' );

global $hocwp_ads_positions;
$hocwp_ads_positions['below_notices'] = array(
	'id'          => 'below_notices',
	'name'        => 'Below Notices',
	'description' => ''
);
$hocwp_ads_positions['site_footer']   = array(
	'id'          => 'site_footer',
	'name'        => 'Site Footer',
	'description' => ''
);

function hocwp_theme_custom_upload_mimes_filter( $mimes ) {
	$mimes['zip'] = 'application/zip';
	$mimes['gz']  = 'application/x-gzip';

	return $mimes;
}

add_filter( 'upload_mimes', 'hocwp_theme_custom_upload_mimes_filter', 10 );