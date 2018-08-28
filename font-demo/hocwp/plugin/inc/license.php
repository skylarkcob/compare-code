<?php
function hocwp_fetch_plugin_license_ajax_callback() {
	$result  = array(
		'customer_email' => '',
		'license_code'   => ''
	);
	$use_for = isset( $_POST['use_for'] ) ? $_POST['use_for'] : '';
	if ( ! empty( $use_for ) ) {
		$use_for_key    = md5( $use_for );
		$option         = get_option( 'hocwp_plugin_licenses' );
		$customer_email = hocwp_get_value_by_key( $option, array( $use_for_key, 'customer_email' ) );
		if ( is_array( $customer_email ) || ! is_email( $customer_email ) ) {
			$customer_email = '';
		}
		$license_code = hocwp_get_value_by_key( $option, array( $use_for_key, 'license_code' ) );
		if ( is_array( $license_code ) || strlen( $license_code ) < 5 ) {
			$license_code = '';
		}
		$result['customer_email'] = $customer_email;
		$result['license_code']   = $license_code;
		update_option( 'test', $result );
	}
	echo json_encode( $result );
	die();
}

add_action( 'wp_ajax_hocwp_fetch_plugin_license', 'hocwp_fetch_plugin_license_ajax_callback' );