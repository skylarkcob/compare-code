<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;
$parent_slug = 'hocwp_plugin_option';

$option = new HOCWP_Option( __( 'Custom CSS', 'hocwp-theme' ), 'hocwp_plugin_custom_css' );
$option->set_parent_slug( $parent_slug );

$args = array(
	'id'             => 'code',
	'title'          => __( 'Custom Style Sheet', 'hocwp-theme' ),
	'class'          => 'widefat',
	'row'            => 30,
	'field_callback' => 'hocwp_field_textarea'
);
$option->add_field( $args );

hocwp_plugin_add_option_to_sidebar_tab( $option );

$option->init();

hocwp_option_add_object_to_list( $option );
$hocwp->plugin->option->custom_css = $option;
unset( $option );