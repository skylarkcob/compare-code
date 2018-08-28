<?php
$maintenance_mode = hocwp_in_maintenance_mode();

if ( $maintenance_mode && ! hocwp_maintenance_mode_exclude_condition() ) {
	add_action( 'admin_notices', 'hocwp_setup_theme_in_maintenance_mode_notice' );
	add_action( 'init', 'hocwp_theme_maintenance_mode' );
	add_action( 'hocwp_maintenance_head', 'hocwp_setup_theme_maintenance_head' );
	add_action( 'hocwp_maintenance', 'hocwp_setup_theme_maintenance' );
	add_action( 'wp_enqueue_scripts', 'hocwp_setup_theme_maintenance_scripts' );
	add_filter( 'body_class', 'hocwp_setup_theme_maintenance_body_class' );
}

function hocwp_setup_theme_in_maintenance_mode_notice() {
	hocwp_in_maintenance_mode_notice();
}

function hocwp_setup_theme_maintenance_head() {
	$args       = hocwp_maintenance_mode_settings();
	$background = hocwp_get_value_by_key( $args, 'background' );
	$background = hocwp_sanitize_media_value( $background );
	$background = $background['url'];
	$css        = '';
	if ( ! empty( $background ) ) {
		$css .= hocwp_build_css_rule( array( '.hocwp-maintenance' ), array( 'background-image' => 'url("' . $background . '")' ) );
	}
	if ( ! empty( $css ) ) {
		$css = hocwp_minify_css( $css );
		echo '<style type="text/css">' . $css . '</style>';
	}
}

function hocwp_setup_theme_maintenance() {
	$options = hocwp_maintenance_mode_settings();
	$heading = hocwp_get_value_by_key( $options, 'heading' );
	$text    = hocwp_get_value_by_key( $options, 'text' );
	echo '<h2 class="heading">' . $heading . '</h2>';
	echo wpautop( $text );
}

function hocwp_setup_theme_maintenance_scripts() {
	wp_enqueue_style( 'hocwp-maintenance-style', HOCWP_URL . '/css/hocwp-maintenance' . HOCWP_CSS_SUFFIX, array() );
}

function hocwp_setup_theme_maintenance_body_class( $classes ) {
	$classes[] = 'hocwp-maintenance';

	return $classes;
}