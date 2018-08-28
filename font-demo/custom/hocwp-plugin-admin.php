<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

$option = new HOCWP_Option( 'Font Demo', 'hocwp_font_demo' );
$option->set_parent_slug( 'hocwp_plugin_option' );

$args = array(
	'id'    => 'demo_text',
	'title' => 'Demo Text'
);
$option->add_field( $args );

$args = array(
	'id'             => 'small_ads',
	'title'          => 'Small Ads',
	'field_callback' => 'hocwp_field_textarea'
);
$option->add_field( $args );

hocwp_plugin_add_option_to_sidebar_tab( $option );

$option->init();