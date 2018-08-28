<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;
$parent_slug = 'hocwp_theme_option';

$options        = hocwp_option_home_setting();
$posts_per_page = hocwp_get_value_by_key( $options, 'posts_per_page' );
$pagination     = hocwp_get_value_by_key( $options, 'pagination' );
$recent_posts   = hocwp_get_value_by_key( $options, 'recent_posts' );

$option = new HOCWP_Option( __( 'Home Settings', 'hocwp-theme' ), 'hocwp_home_setting' );
$option->set_parent_slug( $parent_slug );
$option->set_use_style_and_script( true );
$option->set_use_media_upload( true );

$args = array(
	'id'             => 'recent_posts',
	'title'          => __( 'Recent Posts', 'hocwp-theme' ),
	'label'          => __( 'Show recent posts on home page?', 'hocwp-theme' ),
	'value'          => $recent_posts,
	'field_callback' => 'hocwp_field_input_checkbox'
);
$option->add_field( $args );

$args = array(
	'id'             => 'posts_per_page',
	'title'          => __( 'Posts Number', 'hocwp-theme' ),
	'value'          => $posts_per_page,
	'field_callback' => 'hocwp_field_input_number'
);
$option->add_field( $args );

$args = array(
	'id'             => 'pagination',
	'title'          => __( 'Pagination', 'hocwp-theme' ),
	'label'          => __( 'Show pagination on home page?', 'hocwp-theme' ),
	'value'          => $pagination,
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