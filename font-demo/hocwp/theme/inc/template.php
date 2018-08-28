<?php
function hocwp_theme_get_views( $file_name ) {
	include( HOCWP_THEME_CORE_INC_PATH . '/views/' . $file_name . '.php' );
}

function hocwp_theme_get_header() {
	hocwp_theme_get_views( 'header' );
}

function hocwp_theme_get_footer() {
	hocwp_theme_get_views( 'footer' );
}

function hocwp_theme_get_template_part( $slug, $name = '' ) {
	$slug = 'template-parts/' . $slug;
	get_template_part( $slug, $name );
}

function hocwp_get_theme_template( $name ) {
	hocwp_theme_get_template_part( 'template', $name );
}

function hocwp_theme_get_content( $name ) {
	hocwp_theme_get_template_part( 'content/content', $name );
}

function hocwp_theme_get_content_none() {
	hocwp_theme_get_content( 'none' );
}

function hocwp_theme_get_template_page( $name ) {
	hocwp_theme_get_template_part( 'page/page', $name );
}

function hocwp_theme_get_module( $name ) {
	hocwp_theme_get_template_part( 'module/module', $name );
}

function hocwp_theme_get_ajax( $name ) {
	hocwp_theme_get_template_part( 'ajax/ajax', $name );
}

function hocwp_theme_get_carousel( $name ) {
	hocwp_theme_get_template_part( 'carousel/carousel', $name );
}

function hocwp_theme_get_meta( $name ) {
	hocwp_theme_get_template_part( 'meta/meta', $name );
}

function hocwp_theme_get_modal( $name ) {
	hocwp_theme_get_template_part( 'modal/modal', $name );
}

function hocwp_theme_get_loop( $name ) {
	hocwp_theme_get_template_part( 'loop/loop', $name );
}