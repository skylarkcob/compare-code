<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_pxf_post_type_and_taxonomy() {
	$args = array(
		'public' => false
	);
	register_post_type( 'collection', $args );

	add_rewrite_endpoint( 'create', EP_ALL );
	add_rewrite_endpoint( 'edit', EP_ALL );
	add_rewrite_endpoint( 'view', EP_ALL );
}

add_action( 'init', 'hocwp_pxf_post_type_and_taxonomy' );