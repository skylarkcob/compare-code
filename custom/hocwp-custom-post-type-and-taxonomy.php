<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

function hocwp_theme_custom_type_taxonomy() {
	$args = array(
		'name'          => 'Designers',
		'singular_name' => 'Designer',
		'post_type'     => 'post',
		'slug'          => 'designer'
	);
	hocwp_register_taxonomy( $args );

	$args = array(
		'name'          => 'Licenses',
		'singular_name' => 'License',
		'post_type'     => 'post',
		'slug'          => 'license'
	);
	hocwp_register_taxonomy_private( $args );
}

add_action( 'init', 'hocwp_theme_custom_type_taxonomy' );