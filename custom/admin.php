<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_pxf_pre_get_posts( $query ) {
	if ( $query instanceof WP_Query ) {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			if ( $query->is_main_query() ) {
				global $pagenow;

				if ( 'edit.php' == $pagenow ) {
					$query->set( 'author', get_current_user_id() );
				}
			}
		}
	}
}

add_action( 'pre_get_posts', 'hocwp_pxf_pre_get_posts' );