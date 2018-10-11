<?php
/*
 * Template Name: New Fonts
 */
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}
get_header();
while ( have_posts() ) {
	the_post();
	hocwp_theme_get_template_page( 'new-fonts' );
}
get_footer();