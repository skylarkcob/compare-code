<?php
require( HOCWP_THEME_CORE_INC_PATH . '/meta.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/theme-option.php' );
require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-admin.php' );
require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-meta.php' );
require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-ajax.php' );

global $pagenow;

function hocwp_dashboard_widget_ajax_callback() {
	$result = array(
		'html_data' => ''
	);
	$widget = hocwp_get_method_value( 'widget' );
	if ( ! empty( $widget ) ) {
		$widgets = explode( '_', $widget );
		array_shift( $widgets );
		$widget   = implode( '_', $widgets );
		$callback = 'hocwp_theme_dashboard_widget_' . $widget;
		if ( hocwp_callback_exists( $callback ) ) {
			ob_start();
			call_user_func( $callback );
			$result['html_data'] = ob_get_clean();
		}
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_dashboard_widget', 'hocwp_dashboard_widget_ajax_callback' );

function hocwp_theme_dashboard_init() {
	$lang        = hocwp_get_language();
	$dash_widget = hocwp_dashboard_services_news_widget();
	if ( 'vi' == $lang && $dash_widget ) {
		add_action( 'wp_dashboard_setup', 'hocwp_setup_theme_wp_dashboard_setup' );
		add_filter( 'dashboard_primary_link', 'hocwp_setup_theme_dashboard_primary_link' );
		add_filter( 'dashboard_primary_feed', 'hocwp_setup_theme_dashboard_primary_feed' );
		add_filter( 'dashboard_secondary_link', 'hocwp_setup_theme_dashboard_secondary_link' );
		add_filter( 'dashboard_secondary_feed', 'hocwp_setup_theme_dashboard_secondary_feed' );
		add_filter( 'dashboard_primary_title', 'hocwp_setup_theme_dashboard_primary_title', 1 );
		add_filter( 'dashboard_secondary_title', 'hocwp_setup_theme_dashboard_primary_title' );
		add_filter( 'dashboard_secondary_items', 'hocwp_setup_theme_dashboard_secondary_items' );
	}
}

add_action( 'after_setup_theme', 'hocwp_theme_dashboard_init', 30 );

function hocwp_theme_admin_on_permalink_setting_page() {
	$admin_page = hocwp_get_current_admin_page();
	if ( is_admin() && 'hocwp_permalink' == $admin_page ) {
		flush_rewrite_rules();
	}
}

add_action( 'after_setup_theme', 'hocwp_theme_admin_on_permalink_setting_page' );

if ( 'profile.php' == $pagenow ) {
	add_filter( 'hocwp_use_admin_style_and_script', '__return_true' );
}