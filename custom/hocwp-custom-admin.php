<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

$args = array(
	'id'             => 'breadcrumb_text',
	'title'          => 'Breadcrumb Text',
	'field_callback' => 'hocwp_field_input'
);
hocwp_theme_add_setting_field( $args );

$args = array(
	'id'             => 'new_fonts',
	'title'          => 'New Fonts',
	'field_callback' => 'hocwp_field_select_page'
);
hocwp_theme_add_setting_field( $args );

$args = array(
	'id'             => 'top_fonts',
	'title'          => 'Top Fonts',
	'field_callback' => 'hocwp_field_select_page'
);
hocwp_theme_add_setting_field( $args );

$args = array(
	'id'             => 'designers',
	'title'          => 'Designers',
	'field_callback' => 'hocwp_field_select_page'
);
hocwp_theme_add_setting_field( $args );

$args = array(
	'id'             => 'notices',
	'title'          => 'Notices',
	'field_callback' => 'hocwp_field_editor'
);
hocwp_theme_add_setting_field( $args );

hocwp_theme_add_setting_field_sortable_category( array(), false );