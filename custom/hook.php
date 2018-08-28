<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_pxf_widgets_init_action() {
	register_sidebar( array(
		'id'          => 'addthis',
		'name'        => __( 'AddThis', 'pixelify' ),
		'description' => __( 'AddThis widgets.', 'pixelify' )
	) );
}

add_action( 'widgets_init', 'hocwp_pxf_widgets_init_action' );

function hocwp_pxf_login_url( $login_url ) {
	$page = Pixelify()->get_login_page();

	if ( $page instanceof WP_Post ) {
		$login_url = get_permalink( $page );
	}

	return $login_url;
}

add_filter( 'login_url', 'hocwp_pxf_login_url' );

function hocwp_pxf_lostpassword_url( $url ) {
	$page = Pixelify()->get_lostpassword_page();

	if ( $page instanceof WP_Post ) {
		$url = get_permalink( $page );
	}

	return $url;
}

add_filter( 'lostpassword_url', 'hocwp_pxf_lostpassword_url' );

function hocwp_pxf_register_url( $url ) {
	$page = Pixelify()->get_register_page();

	if ( $page instanceof WP_Post ) {
		$url = get_permalink( $page );
	}

	return $url;
}

add_filter( 'register_url', 'hocwp_pxf_register_url' );

function hocwp_pxf_pre_get_avatar_filter( $avatar, $id_or_email, $args ) {
	$user = HP()->get_user( $id_or_email );

	if ( $user instanceof WP_User ) {
		$avatar_id = get_user_meta( $user->ID, 'avatar_id', true );

		if ( HP()->is_positive_number( $avatar_id ) ) {
			$avatar = '<img class="avatar" src="' . wp_get_attachment_image_url( $avatar_id, 'full' ) . '" alt="" width="' . $args['width'] . '" height="' . $args['height'] . '">';
		}
	}

	return $avatar;
}

add_filter( 'pre_get_avatar', 'hocwp_pxf_pre_get_avatar_filter', 10, 3 );