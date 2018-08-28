<?php
function hocwp_social_login_facebook_ajax_callback() {
	$result  = array(
		'redirect_to' => '',
		'logged_in'   => false
	);
	$data    = hocwp_get_method_value( 'data' );
	$data    = hocwp_json_string_to_array( $data );
	$connect = (bool) hocwp_get_method_value( 'connect' );
	if ( hocwp_array_has_value( $data ) ) {
		$verified           = (bool) hocwp_get_value_by_key( $data, 'verified' );
		$allow_not_verified = apply_filters( 'hocwp_allow_social_user_signup_not_verified', true );
		if ( $verified || $allow_not_verified ) {
			$id                    = hocwp_get_value_by_key( $data, 'id' );
			$requested_redirect_to = hocwp_get_method_value( 'redirect_to' );
			$redirect_to           = home_url( '/' );
			$transient_name        = hocwp_build_transient_name( 'hocwp_social_login_facebook_%s', $id );
			$user_id               = get_transient( $transient_name );
			$user                  = get_user_by( 'ID', $user_id );
			if ( $connect && is_user_logged_in() ) {
				$user    = wp_get_current_user();
				$user_id = $user->ID;
			}
			$find_users = get_users( array( 'meta_key' => 'facebook', 'meta_value' => $id ) );
			if ( hocwp_array_has_value( $find_users ) ) {
				$user    = $find_users[0];
				$user_id = $user->ID;
			}
			if ( false === $user_id || ! hocwp_id_number_valid( $user_id ) || ! is_a( $user, 'WP_User' ) || $connect ) {
				$avatar = hocwp_get_value_by_key( $data, array( 'picture', 'data', 'url' ) );
				if ( $connect ) {
					update_user_meta( $user_id, 'facebook', $id );
					update_user_meta( $user_id, 'facebook_data', $data );
					update_user_meta( $user_id, 'avatar', $avatar );
					$result['redirect_to'] = get_edit_profile_url( $user_id );
					$result['logged_in']   = true;
				} else {
					$email = hocwp_get_value_by_key( $data, 'email' );
					if ( is_email( $email ) ) {
						$name       = hocwp_get_value_by_key( $data, 'name' );
						$first_name = hocwp_get_value_by_key( $data, 'first_name' );
						$last_name  = hocwp_get_value_by_key( $data, 'last_name' );

						$password = wp_generate_password();
						$user_id  = null;
						if ( username_exists( $email ) ) {
							$user    = get_user_by( 'login', $email );
							$user_id = $user->ID;
						} elseif ( email_exists( $email ) ) {
							$user    = get_user_by( 'email', $email );
							$user_id = $user->ID;
						}
						$old_user = true;
						if ( ! hocwp_id_number_valid( $user_id ) ) {
							$user_data = array(
								'username' => $email,
								'email'    => $email,
								'password' => $password
							);
							$user_id   = hocwp_add_user( $user_data );
							if ( hocwp_id_number_valid( $user_id ) ) {
								$old_user = false;
							}
						}
						if ( hocwp_id_number_valid( $user_id ) ) {
							$user        = get_user_by( 'id', $user_id );
							$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
							if ( ! $old_user ) {
								update_user_meta( $user_id, 'facebook', $id );
								$user_data = array(
									'ID'           => $user_id,
									'display_name' => $name,
									'first_name'   => $first_name,
									'last_name'    => $last_name
								);
								wp_update_user( $user_data );
								update_user_meta( $user_id, 'avatar', $avatar );
								update_user_meta( $user_id, 'facebook_data', $data );
							}
							hocwp_user_force_login( $user_id );
							$result['redirect_to'] = $redirect_to;
							$result['logged_in']   = true;
							set_transient( $transient_name, $user_id, DAY_IN_SECONDS );
						}
					}
				}
			} else {
				update_user_meta( $user_id, 'facebook_data', $data );
				$user        = get_user_by( 'id', $user_id );
				$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
				hocwp_user_force_login( $user_id );
				$result['redirect_to'] = $redirect_to;
				$result['logged_in']   = true;
			}
		}
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_social_login_facebook', 'hocwp_social_login_facebook_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_social_login_facebook', 'hocwp_social_login_facebook_ajax_callback' );

function hocwp_social_login_google_ajax_callback() {
	$result  = array(
		'redirect_to' => '',
		'logged_in'   => false
	);
	$data    = hocwp_get_method_value( 'data' );
	$data    = hocwp_json_string_to_array( $data );
	$connect = hocwp_get_method_value( 'connect' );
	if ( hocwp_array_has_value( $data ) ) {
		$verified           = (bool) hocwp_get_value_by_key( $data, 'verified' );
		$allow_not_verified = apply_filters( 'hocwp_allow_social_user_signup_not_verified', true );
		if ( $verified || $allow_not_verified ) {
			$id                    = hocwp_get_value_by_key( $data, 'id' );
			$requested_redirect_to = hocwp_get_method_value( 'redirect_to' );
			$redirect_to           = home_url( '/' );
			$transient_name        = hocwp_build_transient_name( 'hocwp_social_login_google_%s', $id );
			$user_id               = get_transient( $transient_name );
			$user                  = get_user_by( 'id', $user_id );
			if ( $connect && is_user_logged_in() ) {
				$user    = wp_get_current_user();
				$user_id = $user->ID;
			}
			$find_users = get_users( array( 'meta_key' => 'google', 'meta_value' => $id ) );
			if ( hocwp_array_has_value( $find_users ) ) {
				$user    = $find_users[0];
				$user_id = $user->ID;
			}
			if ( false === $user_id || ! hocwp_id_number_valid( $user_id ) || ! is_a( $user, 'WP_User' ) || $connect ) {
				$avatar = hocwp_get_value_by_key( $data, array( 'image', 'url' ) );
				if ( $connect ) {
					update_user_meta( $user_id, 'google', $id );
					update_user_meta( $user_id, 'avatar', $avatar );
					update_user_meta( $user_id, 'google_data', $data );
					$result['redirect_to'] = get_edit_profile_url( $user_id );
					$result['logged_in']   = true;
				} else {
					$email = hocwp_get_value_by_key( $data, array( 'emails', 0, 'value' ) );
					if ( is_email( $email ) ) {
						$name       = hocwp_get_value_by_key( $data, 'displayName' );
						$first_name = hocwp_get_value_by_key( $data, array( 'name', 'givenName' ) );
						$last_name  = hocwp_get_value_by_key( $data, array( 'name', 'familyName' ) );
						$password   = wp_generate_password();
						$user_id    = null;
						if ( username_exists( $email ) ) {
							$user    = get_user_by( 'login', $email );
							$user_id = $user->ID;
						} elseif ( email_exists( $email ) ) {
							$user    = get_user_by( 'email', $email );
							$user_id = $user->ID;
						}
						$old_user = true;
						if ( ! hocwp_id_number_valid( $user_id ) ) {
							$user_data = array(
								'username' => $email,
								'email'    => $email,
								'password' => $password
							);
							$user_id   = hocwp_add_user( $user_data );
							if ( hocwp_id_number_valid( $user_id ) ) {
								$old_user = false;
							}
						}
						if ( hocwp_id_number_valid( $user_id ) ) {
							$user        = get_user_by( 'id', $user_id );
							$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
							if ( ! $old_user ) {
								update_user_meta( $user_id, 'google', $id );
								$user_data = array(
									'ID'           => $user_id,
									'display_name' => $name,
									'first_name'   => $first_name,
									'last_name'    => $last_name
								);
								wp_update_user( $user_data );
								update_user_meta( $user_id, 'avatar', $avatar );
								update_user_meta( $user_id, 'google_data', $data );
							}
							hocwp_user_force_login( $user_id );
							$result['redirect_to'] = $redirect_to;
							$result['logged_in']   = true;
							set_transient( $transient_name, $user_id, DAY_IN_SECONDS );
						}
					}
				}
			} else {
				update_user_meta( $user_id, 'google_data', $data );
				$user        = get_user_by( 'id', $user_id );
				$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
				hocwp_user_force_login( $user_id );
				$result['redirect_to'] = $redirect_to;
				$result['logged_in']   = true;
			}
		}
	}
	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_social_login_google', 'hocwp_social_login_google_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_social_login_google', 'hocwp_social_login_google_ajax_callback' );

function hocwp_disconnect_social_account_ajax_callback() {
	$social  = hocwp_get_method_value( 'social' );
	$user_id = hocwp_get_method_value( 'user_id' );
	if ( hocwp_id_number_valid( $user_id ) ) {
		switch ( $social ) {
			case 'facebook':
				delete_user_meta( $user_id, 'facebook' );
				delete_user_meta( $user_id, 'facebook_data' );
				break;
			case 'google':
				delete_user_meta( $user_id, 'google' );
				delete_user_meta( $user_id, 'google_data' );
				break;
		}
	}
	exit;
}

add_action( 'wp_ajax_hocwp_disconnect_social_account', 'hocwp_disconnect_social_account_ajax_callback' );