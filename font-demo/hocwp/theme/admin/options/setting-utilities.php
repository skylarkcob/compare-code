<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;
$parent_slug = 'hocwp_theme_option';

$options = hocwp_option_utilities();

$dashboard_widget    = hocwp_get_value_by_key( $options, 'dashboard_widget' );
$force_admin_english = hocwp_get_value_by_key( $options, 'force_admin_english' );
$auto_update         = hocwp_get_value_by_key( $options, 'auto_update' );

$option = new HOCWP_Option( __( 'Utilities', 'hocwp-theme' ), 'hocwp_utilities' );
$option->set_parent_slug( $parent_slug );

$args = array(
	'id'             => 'link_manager',
	'title'          => __( 'Link Manager', 'hocwp-theme' ),
	'label'          => __( 'Enable link manager on your site.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox'
);
$option->add_field( $args );

$args = array(
	'id'             => 'dashboard_widget',
	'title'          => __( 'Dashboard Widgets', 'hocwp-theme' ),
	'default'        => 1,
	'value'          => $dashboard_widget,
	'label'          => __( 'Display custom widget on Dashboard for Services News.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox'
);
$option->add_field( $args );

$args = array(
	'id'             => 'force_admin_english',
	'title'          => __( 'Force Admin English', 'hocwp-theme' ),
	'default'        => 1,
	'value'          => $force_admin_english,
	'label'          => __( 'Force to use English language for backend.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox'
);
//$option->add_field( $args );

$args = array(
	'id'             => 'auto_update',
	'title'          => __( 'Auto Update', 'hocwp-theme' ),
	'value'          => $auto_update,
	'label'          => __( 'Update WordPress theme, plugin, core and translation automatically.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox'
);
$option->add_field( $args );

$option->add_option_tab( $hocwp->theme->option->sidebar_tabs );
$option->set_page_header_callback( 'hocwp_theme_option_form_before' );
$option->set_page_footer_callback( 'hocwp_theme_option_form_after' );
$option->set_page_sidebar_callback( 'hocwp_theme_option_sidebar_tab' );

$option->init();

hocwp_option_add_object_to_list( $option );
unset( $parent_slug, $option, $args );