<?php
/*
* Template Name: Font
*/
$post_id = hocwp_get_method_value( 'post_id', 'request' );

$t = hocwp_get_method_value( 'text', 'request' );
$s = hocwp_get_method_value( 'size', 'request' );

if ( ! is_numeric( $s ) ) {
	$s = 80;
}

$c = hocwp_get_method_value( 'color', 'request' );

if ( empty( $c ) ) {
	$c = '#000';
} else {
	$c = '#' . $c;
}

if ( hocwp_id_number_valid( $post_id ) ) {
	$post = get_post( $post_id );

	if ( empty( $t ) ) {
		$t = get_post_meta( $post_id, 'name', true );

		if ( empty( $t ) && $post instanceof WP_Post ) {
			$t = $post->post_title;
		}
	}
}

$seconds_to_cache = 60 * 60 * 24;

header( "Pragma: cache" );
header( "Cache-Control: max-age=" . $seconds_to_cache );
hocwp_generate_font_preview( $post_id, $t, $s, $c );