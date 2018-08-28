<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

function hocwp_is_font_demo_post( $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$license = hocwp_get_post_meta( 'license', $post_id );

	if ( ! empty( $license ) ) {
		return true;
	}

	$small_image_link = hocwp_get_post_meta( 'small_image_link', $post_id );

	if ( ! empty( $small_image_link ) ) {
		return true;
	}

	$more_link = hocwp_get_post_meta( 'more_link', $post_id );

	if ( ! empty( $more_link ) ) {
		return true;
	}
	
	$donate_link = hocwp_get_post_meta( 'donate_link', $post_id );

	if ( ! empty( $donate_link ) ) {
		return true;
	}

	$download = hocwp_get_post_meta( 'download', $post_id );

	if ( ! empty( $download ) ) {
		return true;
	}

	return false;
}

function hocwp_font_demo_get_demo_text( $post_id = null ) {
	$post_id = hocwp_return_post( $post_id, 'id' );
	$result  = hocwp_get_post_meta( 'demo_text', $post_id );

	if ( empty( $result ) ) {
		global $hocwp;
		$result = $hocwp->plugin->font_demo->get_option_value_by_key( 'demo_text' );

		if ( empty( $result ) ) {
			$result = 'The quick brown fox jumps over the lazy dog';
		}
	}

	return $result;
}