<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;
$parent_slug = 'hocwp_theme_option';

$option = new HOCWP_Option( __( 'Optimize', 'hocwp-theme' ), 'hocwp_optimize' );
$option->set_parent_slug( $parent_slug );

$args = array(
	'id'             => 'use_jquery_cdn',
	'title'          => __( 'jQuery CDN', 'hocwp-theme' ),
	'label'          => __( 'Load jQuery from Google CDN server.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'default'        => 1
);
$option->add_field( $args );

$args = array(
	'id'             => 'use_bootstrap_cdn',
	'title'          => __( 'Bootstrap CDN', 'hocwp-theme' ),
	'label'          => __( 'Load Bootstrap from Max CDN server.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'default'        => 1
);
$option->add_field( $args );

$args = array(
	'id'             => 'use_fontawesome_cdn',
	'title'          => __( 'FontAwesome CDN', 'hocwp-theme' ),
	'label'          => __( 'Load FontAwesome from Max CDN server.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'default'        => 1
);
$option->add_field( $args );

$args = array(
	'id'             => 'use_superfish_cdn',
	'title'          => __( 'Superfish CDN', 'hocwp-theme' ),
	'label'          => __( 'Load Superfish from CloudFlare CDN server.', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'default'        => 1
);
$option->add_field( $args );

$option->add_option_tab( $hocwp->theme->option->sidebar_tabs );

$option->set_page_header_callback( 'hocwp_theme_option_form_before' );
$option->set_page_footer_callback( 'hocwp_theme_option_form_after' );
$option->set_page_sidebar_callback( 'hocwp_theme_option_sidebar_tab' );

$option->init();

hocwp_option_add_object_to_list( $option );
unset( $option, $args, $parent_slug );