<?php
function hocwp_theme_custom_mime_types( $mimes ) {
	$mimes['ttf'] = 'application/x-font-ttf';

	return $mimes;
}

add_filter( 'mime_types', 'hocwp_theme_custom_mime_types' );

function hocwp_theme_custom_user_contactmethods( $methods ) {
	$methods['donate'] = __( 'Donate link', 'hocwp-theme' );

	return $methods;
}

add_filter( 'user_contactmethods', 'hocwp_theme_custom_user_contactmethods' );

function hocwp_theme_custom_recent_searches_track() {
	if ( is_search() ) {
		$q        = get_search_query();
		$searches = get_option( 'recent_searches' );
		if ( ! is_array( $searches ) ) {
			$searches = array();
		}
		array_unshift( $searches, $q );
		$searches = array_filter( $searches );
		$searches = array_unique( $searches );
		$searches = array_slice( $searches, 0, 15 );
		update_option( 'recent_searches', $searches );
	}
}

add_action( 'wp', 'hocwp_theme_custom_recent_searches_track' );

function hocwp_theme_custom_pagination_args( $args ) {
	$args['label'] = '';
	$args['prev']  = '<';
	$args['next']  = '>';
	$args['first'] = '';
	$args['last']  = '';

	return $args;
}

add_filter( 'hocwp_pagination_args', 'hocwp_theme_custom_pagination_args' );

function hocwp_theme_custom_pre_get_posts( WP_Query $query ) {
	if ( ! is_admin() && $query->is_main_query() ) {
		if ( isset( $_POST['submitPreviewSettings'] ) ) {
			if ( 'update' == $_POST['submitPreviewSettings'] ) {
				$per = $_POST['perPage'];
				if ( hocwp_is_positive_number( $per ) ) {
					$query->set( 'posts_per_page', $per );
				}
				$sort = $_POST['order'];
				switch ( $sort ) {
					case 'name':
						$query->set( 'orderby', 'post_title' );
						$query->set( 'order', 'asc' );
						break;
					case 'latest':
						$query->set( 'orderby', 'date' );
						$query->set( 'order', 'desc' );
						break;
					case 'dowloads':
						$query->set( 'meta_key', 'downloads' );
						$query->set( 'orderby', 'meta_value_num' );
						$query->set( 'order', 'desc' );
						break;
				}
			}
		}
		$alphabe = isset( $_GET['alphabe'] ) ? $_GET['alphabe'] : '';
		if ( ! empty( $alphabe ) ) {
			$query->set( 'meta_key', 'alphabe' );
			$query->set( 'meta_value', $alphabe );
		}
	}
}

add_action( 'pre_get_posts', 'hocwp_theme_custom_pre_get_posts' );

function hocwp_theme_custom_widgets_init() {
	$args = array();
	hocwp_register_sidebar( 'font_categories_1', __( 'Font Categories 1', 'hocwp-theme' ), __( 'Font categories menu on site header.', 'hocwp-theme' ) );
	hocwp_register_sidebar( 'font_categories_2', __( 'Font Categories 2', 'hocwp-theme' ), __( 'Font categories menu on site header.', 'hocwp-theme' ) );
	hocwp_register_sidebar( 'font_categories_3', __( 'Font Categories 3', 'hocwp-theme' ), __( 'Font categories menu on site header.', 'hocwp-theme' ) );
	hocwp_register_sidebar( 'font_categories_4', __( 'Font Categories 4', 'hocwp-theme' ), __( 'Font categories menu on site header.', 'hocwp-theme' ) );
	hocwp_register_sidebar( 'font_categories_5', __( 'Font Categories 5', 'hocwp-theme' ), __( 'Font categories menu on site header.', 'hocwp-theme' ) );
	hocwp_register_sidebar( 'font_categories_6', __( 'Font Categories 6', 'hocwp-theme' ), __( 'Font categories menu on site header.', 'hocwp-theme' ) );
}

add_action( 'widgets_init', 'hocwp_theme_custom_widgets_init' );

function hocwp_theme_custom_posts_where( $where, WP_Query $query ) {

	return $where;
}

//add_filter( 'posts_where', 'hocwp_theme_custom_posts_where', 10, 2 );

function hocwp_theme_custom_save_post_action( $post_id ) {
	if ( ! hocwp_can_save_post( $post_id ) ) {
		return;
	}

	$obj = get_post( $post_id );

	$alphabe = substr( $obj->post_title, 0, 1 );

	if ( is_numeric( $alphabe ) || preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $alphabe ) ) {
		$alphabe = '#';
	}

	update_post_meta( $post_id, 'alphabe', $alphabe );
	/*
	if ( ! isset( $_POST['demo'] ) ) {
		$demo = get_post_meta( $post_id, 'demo', true );
	} else {
		$demo = $_POST['demo'];
	}
	$demo = hocwp_sanitize_media_value( $demo );
	if ( empty( $demo['url'] ) && isset( $_POST['file_contents'] ) ) {
		$demo = hocwp_theme_custom_add_demo_from_file_contents( $post_id, $_POST['file_contents'] );
		update_post_meta( $post_id, 'demo', $demo );
		unset( $_POST['demo'] );
	}
	*/

	do_action( 'hocwp_theme_save_post_data', $post_id );

	HT_Custom()->check_post_meta_data( $post_id );
}

add_action( 'save_post', 'hocwp_theme_custom_save_post_action', 99 );

function hocwp_theme_custom_edited_term( $term_id, $tt_id, $taxonomy ) {
	$term = get_term( $term_id, $taxonomy );
	if ( $term instanceof WP_Term ) {
		$alphabe = substr( $term->name, 0, 1 );
		if ( is_numeric( $alphabe ) || preg_match( '/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $alphabe ) ) {
			$alphabe = '#';
		}
		update_term_meta( $term_id, 'alphabe', $alphabe );
	}
}

add_action( 'edited_term', 'hocwp_theme_custom_edited_term', 10, 3 );

function hocwp_theme_custom_admin_notices() {

}

add_action( 'admin_notices', 'hocwp_theme_custom_admin_notices', 99 );

function hcowp_theme_custom_admin_enqueue_scripts() {
	add_filter( 'hocwp_use_admin_style_and_script', '__return_true' );
	wp_enqueue_script( 'hocwp-custom-admin', get_template_directory_uri() . '/js/hocwp-custom-admin' . HOCWP_JS_SUFFIX, array(
		'jquery',
		'hocwp'
	), false, true );
}

add_action( 'admin_enqueue_scripts', 'hcowp_theme_custom_admin_enqueue_scripts' );

function hocwp_theme_custom_body_classes( $classes ) {
	$classes[] = 'v2';

	$classes[] = 'v3';

	return $classes;
}

add_filter( 'body_class', 'hocwp_theme_custom_body_classes' );

function hocwp_theme_custom_load_scripts() {

}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_custom_load_scripts' );

function hocwp_theme_custom_post_class_filter( $classes ) {
	$post_id = get_the_ID();

	$post_sliders = get_post_meta( $post_id, 'slider_ids', true );

	if ( ! empty( $post_sliders ) && is_array( $post_sliders ) ) {
		$classes[] = 'has-images';
	}

	return $classes;
}

add_filter( 'post_class', 'hocwp_theme_custom_post_class_filter' );