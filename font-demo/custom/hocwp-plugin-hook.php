<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

function hocwp_font_demo_add_custom_mime_types( $mimes ) {
	$new_mimes = array(
		'ttf' => 'application/x-font-woff'
	);

	return array_merge( $mimes, $new_mimes );
}

add_filter( 'upload_mimes', 'hocwp_font_demo_add_custom_mime_types' );

function hocwp_font_demo_wp_hook() {

}

add_action( 'wp', 'hocwp_font_demo_wp_hook' );

function hocwp_font_demo_body_classes( $classes ) {
	if ( is_single() || is_page() ) {
		if ( hocwp_is_font_demo_post() ) {
			$classes[] = 'font-demo';
		}
	}

	return $classes;
}

add_filter( 'body_class', 'hocwp_font_demo_body_classes' );

function hocwp_font_demo_template_include( $template ) {
	if ( is_single() ) {
		if ( hocwp_is_font_demo_post() ) {
			global $hocwp;
			//$template = $hocwp->plugin->font_demo->path . '/template-parts/template-single.php';
		}
	}

	return $template;
}

add_filter( 'template_include', 'hocwp_font_demo_template_include' );

function hocwp_font_demo_the_content( $content ) {
	if ( ( is_single( get_the_ID() ) || is_page( get_the_ID() ) ) && hocwp_is_font_demo_post() ) {
		remove_filter( 'the_content', 'hocwp_font_demo_the_content', 99 );
		global $hocwp;
		$template = $hocwp->plugin->font_demo->path . '/template-parts/template-single.php';
		ob_start();
		include( $template );
		$content .= ob_get_clean();
	}

	return $content;
}

add_filter( 'the_content', 'hocwp_font_demo_the_content', 99 );

function hocwp_font_demo_enqueue_scripts() {
	if ( is_single() || is_page() ) {
		hocwp_enqueue_jquery_ui_style();
		wp_enqueue_script( 'jquery-ui-slider' );
	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_font_demo_enqueue_scripts' );

function hocwp_font_demo_upload_mimes( $mime_types ) {
	$mime_types['svg']   = 'image/svg+xml';
	$mime_types['ttf']   = 'application/x-font-ttf';
	$mime_types['otf']   = 'application/x-font-opentype';
	$mime_types['woff']  = 'application/font-woff';
	$mime_types['woff2'] = 'pplication/font-woff2';
	$mime_types['eot']   = 'application/vnd.ms-fontobject';
	$mime_types['sfnt']  = 'application/font-sfnt';

	return $mime_types;
}

add_filter( 'upload_mimes', 'hocwp_font_demo_upload_mimes' );