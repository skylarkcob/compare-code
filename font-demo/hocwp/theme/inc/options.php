<?php
function hocwp_option_reading_defaults() {
	$alls     = hocwp_option_defaults();
	$defaults = hocwp_get_value_by_key( $alls, 'reading' );
	if ( ! hocwp_array_has_value( $defaults ) ) {
		$defaults = array(
			'statistics'                    => 0,
			'trending'                      => 0,
			'search_tracking'               => 0,
			'enlarge_thumbnail'             => 0,
			'excerpt_length'                => 75,
			'post_statistics'               => 0,
			'sticky_widget'                 => 0,
			'redirect_404'                  => 0,
			'breadcrumb_label'              => '',
			'disable_post_title_breadcrumb' => 0,
			'link_last_item_breadcrumb'     => 0,
			'go_to_top'                     => 0,
			'go_to_top_on_left'             => 0,
			'scroll_top_icon'               => '',
			'content_none_title'            => '',
			'thumbnail_image_sizes'         => array(),
			'products_per_page'             => hocwp_get_product_posts_per_page()
		);
	}

	return apply_filters( 'hocwp_option_reading_defaults', $defaults );
}

function hocwp_option_reading() {
	$defaults = hocwp_option_reading_defaults();
	$options  = get_option( 'hocwp_reading' );
	$options  = wp_parse_args( $options, $defaults );

	return apply_filters( 'hocwp_option_reading', $options );
}

function hocwp_option_utilities_defaults() {
	$alls     = hocwp_option_defaults();
	$defaults = hocwp_get_value_by_key( $alls, 'utilities' );
	if ( ! hocwp_array_has_value( $defaults ) ) {
		$defaults = array(
			'link_manager'        => 0,
			'dashboard_widget'    => 1,
			'force_admin_english' => 1
		);
	}

	return apply_filters( 'hocwp_option_utilities_defaults', $defaults );
}

function hocwp_option_utilities() {
	$defaults = hocwp_option_utilities_defaults();
	$options  = get_option( 'hocwp_utilities' );
	$options  = wp_parse_args( $options, $defaults );

	return apply_filters( 'hocwp_option_utilities', $options );
}

function hocwp_dashboard_services_news_widget() {
	$args             = hocwp_option_utilities();
	$dashboard_widget = hocwp_get_value_by_key( $args, 'dashboard_widget' );

	return (bool) apply_filters( 'hocwp_dashboard_services_news_widget', $dashboard_widget );
}

function hocwp_force_admin_english() {
	$args                = hocwp_option_utilities();
	$force_admin_english = (bool) hocwp_get_value_by_key( $args, 'force_admin_english' );
	$force_admin_english = apply_filters( 'hocwp_force_admin_english', $force_admin_english );

	return $force_admin_english;
}

function hocwp_users_can_register() {
	$result = (bool) get_option( 'users_can_register' );

	return $result;
}

function hocwp_allow_user_login_with_email() {
	$options = get_option( 'hocwp_user_login' );

	return apply_filters( 'hocwp_allow_user_login_with_email', (bool) hocwp_get_value_by_key( $options, 'login_with_email' ) );
}

function hocwp_maintenance_mode_default_settings() {
	$defaults = array(
		'title'   => __( 'Maintenance mode', 'hocwp-theme' ),
		'heading' => __( 'Maintenance mode', 'hocwp-theme' ),
		'text'    => __( '<p>Sorry for the inconvenience.<br />Our website is currently undergoing scheduled maintenance.<br />Thank you for your understanding.</p>', 'hocwp-theme' )
	);

	return apply_filters( 'hocwp_maintenance_mode_default_settings', $defaults );
}

function hocwp_option_optimize_defaults() {
	$alls     = hocwp_option_defaults();
	$defaults = hocwp_get_value_by_key( $alls, 'optimize' );
	if ( ! hocwp_array_has_value( $defaults ) ) {
		$defaults = array(
			'use_jquery_cdn'      => 1,
			'use_bootstrap_cdn'   => 1,
			'use_fontawesome_cdn' => 1,
			'use_superfish_cdn'   => 1
		);
	}

	return apply_filters( 'hocwp_option_optimize_defaults', $defaults );
}

function hocwp_option_optimize() {
	$defaults = hocwp_option_optimize_defaults();
	$options  = get_option( 'hocwp_optimize' );
	$options  = wp_parse_args( $options, $defaults );

	return apply_filters( 'hocwp_option_optimize', $options );
}

function hocwp_reading_content_none_title() {
	global $hocwp_reading_options;
	$options    = $hocwp_reading_options;
	$page_title = hocwp_get_value_by_key( $options, 'content_none_title' );
	if ( empty( $page_title ) ) {
		$page_title = __( 'Nothing Found', 'hocwp-theme' );
		$page_title = apply_filters( 'hocwp_content_none_title', $page_title );
	}

	return $page_title;
}

function hocwp_option_smtp_email_testing_message() {
	$transient_name = hocwp_build_transient_name( 'hocwp_cache_test_smtp_result_%s', '' );
	if ( false !== ( $message = get_transient( $transient_name ) ) ) {
		hocwp_admin_notice( array( 'text' => $message ) );
		delete_transient( $transient_name );
	}
	unset( $message, $transient_name );
}

function hocwp_option_theme_custom_defaults() {
	$alls     = hocwp_option_defaults();
	$defaults = hocwp_get_value_by_key( $alls, 'theme_custom' );
	if ( ! hocwp_array_has_value( $defaults ) ) {
		$defaults = array(
			'background_lazyload' => 0
		);
	}

	return apply_filters( 'hocwp_option_theme_custom_defaults', $defaults );
}

function hocwp_option_theme_custom() {
	$defaults = hocwp_option_theme_custom_defaults();
	$options  = get_option( 'hocwp_theme_custom' );
	$options  = wp_parse_args( $options, $defaults );

	return apply_filters( 'hocwp_option_theme_custom', $options );
}

function hocwp_option_home_setting_defaults() {
	$alls     = hocwp_option_defaults();
	$defaults = hocwp_get_value_by_key( $alls, 'home_setting' );
	if ( ! hocwp_array_has_value( $defaults ) ) {
		$defaults = array(
			'recent_posts'   => 1,
			'posts_per_page' => hocwp_get_posts_per_page(),
			'pagination'     => 1
		);
	}

	return apply_filters( 'hocwp_option_home_setting_defaults', $defaults );
}

function hocwp_option_home_setting() {
	$defaults = hocwp_option_home_setting_defaults();
	$options  = get_option( 'hocwp_home_setting' );
	$options  = wp_parse_args( $options, $defaults );

	return apply_filters( 'hocwp_option_home_setting', $options );
}

function hocwp_option_theme_setting_defaults() {
	$alls     = hocwp_option_defaults();
	$defaults = hocwp_get_value_by_key( $alls, 'theme_setting' );
	if ( ! hocwp_array_has_value( $defaults ) ) {
		$defaults = array(
			'language' => 'vi'
		);
	}

	return apply_filters( 'hocwp_option_theme_setting_defaults', $defaults );
}

function hocwp_option_theme_setting() {
	$defaults = hocwp_option_theme_setting_defaults();
	$options  = get_option( 'hocwp_theme_setting' );
	$options  = wp_parse_args( $options, $defaults );

	return apply_filters( 'hocwp_option_theme_setting', $options );
}

function hocwp_theme_get_reading_options( $key ) {
	global $hocwp;
	if ( empty( $hocwp->theme->options->reading ) ) {
		$hocwp->theme->options->reading = hocwp_option_reading();
	}
	$result = hocwp_get_value_by_key( $hocwp->theme->options->reading, $key );

	return $result;
}