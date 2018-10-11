<?php
/*
* Template Name: Character Map
*/
$post_id = hocwp_get_method_value( 'post_id', 'request' );
$demo    = hocwp_get_post_meta( 'demo', $post_id );
$demo    = hocwp_sanitize_media_value( $demo );
if ( empty( $demo['url'] ) ) {
	$demo = hocwp_theme_custom_add_demo_from_file_contents( $post_id );
}
if ( ! empty( $demo['url'] ) ) {
	$fontfile = hocwp_get_media_file_path( $demo['id'] );
	hocwp_generate_font_character_map( $fontfile, 30, array(), null );
}