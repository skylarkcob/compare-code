<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;
$parent_slug = 'hocwp_theme_option';

$option = new HOCWP_Option( __( 'General', 'hocwp-theme' ), 'hocwp_theme_setting' );
$option->set_parent_slug( $parent_slug );
$option->set_use_style_and_script( true );
$option->set_use_media_upload( true );

$args = array(
	'id'             => 'language',
	'title'          => __( 'Language', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_select_language'
);
$option->add_field( $args );

$args = array(
	'id'             => 'favicon',
	'title'          => __( 'Favicon', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_media_upload'
);
$option->add_field( $args );

$args = array(
	'id'             => 'logo',
	'title'          => __( 'Logo', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_media_upload'
);
$option->add_field( $args );

$option->add_option_tab( $hocwp->theme->option->sidebar_tabs );
$option->set_page_header_callback( 'hocwp_theme_option_form_before' );
$option->set_page_footer_callback( 'hocwp_theme_option_form_after' );
$option->set_page_sidebar_callback( 'hocwp_theme_option_sidebar_tab' );

$option->init();

hocwp_option_add_object_to_list( $option );
unset( $parent_slug, $option, $args );