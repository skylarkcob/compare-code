<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_pxf_wp_enqueue_scripts_action() {
	global $hocwp_plugin;
	$plugin = $hocwp_plugin->pixelify;

	if ( ! $plugin instanceof HOCWP_Plugin_Core ) {
		return;
	}

	$src = $plugin->get_baseurl();

	if ( is_single() || is_page() ) {
		if ( is_single() ) {
			$post_id = get_the_ID();

			$post_slider = get_post_meta( $post_id, 'post_slider', true );

			if ( ! empty( $post_slider ) ) {
				wp_enqueue_style( 'slick-style', $src . '/lib/slick/slick.css' );
				wp_enqueue_script( 'slick', $src . '/lib/slick/slick.min.js', array( 'jquery' ), false, true );
			}
		} elseif ( is_page() ) {
			wp_enqueue_script( 'suggest' );
			//wp_enqueue_script( 'dropzone', $src . '/js/dropzone.js', array( 'jquery' ), false, true );
		}
	}

	wp_enqueue_script( 'pixelify', $src . '/js/custom.js', array( 'jquery' ), false, true );

	$l10n = array(
		'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
		'loginUrl'   => wp_login_url(),
		'spinnerUrl' => home_url( 'wp-includes/images/wpspin-2x.gif' ),
		'text'       => array(
			'are_you_sure' => __( 'Are you sure?', 'pixelify' )
		)
	);

	$l10n['imagePreview']    = Pixelify()->image_preview_html();
	$l10n['filePreview']     = Pixelify()->file_preview_html();
	$l10n['downloadPreview'] = Pixelify()->file_preview_html( '', '', 'download' );

	wp_localize_script( 'pixelify', 'pixelify', $l10n );

	wp_enqueue_style( 'pixelify-style', $src . '/css/custom.css' );

	if ( is_page() ) {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_pxf_wp_enqueue_scripts_action' );

function hocwp_pxf_the_content_filter( $content ) {
	global $hocwp_plugin;
	$plugin = $hocwp_plugin->pixelify;

	if ( ! $plugin instanceof HOCWP_Plugin_Core ) {
		return $content;
	}

	if ( is_single() ) {
		$post_id = get_the_ID();

		$post_slider = get_post_meta( $post_id, 'post_slider', true );

		if ( ! empty( $post_slider ) ) {
			$path = $plugin->get_basedir();
			ob_start();
			include $path . '/custom/module-slider.php';
			$html    = ob_get_clean();
			$content = $html . $content;
		}
	}

	return $content;
}

add_filter( 'the_content', 'hocwp_pxf_the_content_filter' );

function hocwp_pxf_edit_profile_url_filter( $url ) {
	$page = Pixelify()->get_option( 'dashboard_page' );

	if ( HP()->is_positive_number( $page ) ) {
		$url = get_permalink( $page );
		$url = add_query_arg( 'task', 'profile', $url );
	}

	return $url;
}

add_filter( 'edit_profile_url', 'hocwp_pxf_edit_profile_url_filter' );

function hocwp_pxf_wp_dropdown_cats_filter( $output, $args ) {
	$name = isset( $args['name'] ) ? $args['name'] : '';

	if ( ! empty( $name ) && false !== strpos( $name, '[]' ) ) {
		$output = preg_replace( '/<select (.*?) >/', '<select $1 multiple>', $output );

		$edit = Pixelify()->is_editing_product();

		if ( isset( $_POST['create_post'] ) || $edit ) {
			$category = isset( $_POST['category'] ) ? $_POST['category'] : '';

			if ( ! isset( $_POST['create_post'] ) ) {
				$post_id  = $_GET['post_id'];
				$category = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
			}

			if ( ! empty( $category ) ) {
				$category = (array) $category;
				$category = array_filter( $category );
				$category = array_unique( $category );

				if ( HP()->array_has_value( $category ) ) {
					$output = str_replace( 'selected="selected"', '', $output );

					foreach ( $category as $term_id ) {
						$output = str_replace( 'value="' . $term_id . '"', 'value="' . $term_id . '" selected="selected"', $output );
					}
				}
			}
		}
	}

	return $output;
}

add_filter( 'wp_dropdown_cats', 'hocwp_pxf_wp_dropdown_cats_filter', 10, 2 );

function hocwp_pxf_custom_front_end_init_action() {
	global $pagenow;

	if ( 'wp-login.php' == $pagenow ) {
		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

		if ( 'logout' != $action && 'rp' != $action || ( 'rp' == $action && ( ! isset( $_GET['key'] ) || empty( $_GET['key'] ) ) ) ) {
			if ( 'lostpassword' == $action ) {
				$page = Pixelify()->get_lostpassword_page();
			} elseif ( 'register' == $action ) {
				$page = Pixelify()->get_register_page();
			} else {
				$page = Pixelify()->get_login_page();
			}

			if ( $page instanceof WP_Post && 'rp' != $action && 'resetpass' != $action && 'logout' != $action ) {
				wp_redirect( get_permalink( $page ) );
				exit;
			}
		}
	}

	if ( isset( $_POST['edd_action'] ) && 'user_login' == $_POST['edd_action'] ) {
		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

		if ( wp_verify_nonce( $nonce ) ) {
			$login = isset( $_POST['edd_user_login'] ) ? $_POST['edd_user_login'] : '';
			$pass  = isset( $_POST['edd_user_pass'] ) ? $_POST['edd_user_pass'] : '';
			$rem   = isset( $_POST['rememberme'] ) ? $_POST['rememberme'] : '';

			$credentials = array(
				'user_login'    => $login,
				'user_password' => $pass,
				'remember'      => $rem
			);

			$result = wp_signon( $credentials );

			if ( $result instanceof WP_User ) {
				wp_redirect( get_edit_profile_url() );
				exit;
			} else {
				$_POST['error'] = $result->get_error_messages();
			}
		}
	}
}

add_action( 'init', 'hocwp_pxf_custom_front_end_init_action' );

function hocwp_pxf_number_format_i18n_filter( $formatted ) {
	if ( is_page() ) {
		$page = Pixelify()->get_dashboard_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) ) {
			$formatted = '<strong>' . $formatted . '</strong>';
		}
	}

	return $formatted;
}

add_filter( 'number_format_i18n', 'hocwp_pxf_number_format_i18n_filter' );