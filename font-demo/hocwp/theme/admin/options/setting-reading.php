<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;
$parent_slug = 'hocwp_theme_option';

$options = $hocwp->theme->options->reading;

$option = new HOCWP_Option( __( 'Reading', 'hocwp-theme' ), 'hocwp_reading' );
$option->set_parent_slug( $parent_slug );
$option->set_use_media_upload( true );

$args = array(
	'id'             => 'statistics',
	'title'          => __( 'Statistics', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Check here if you want to enable user statistics on your site.', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'trending',
	'title'          => __( 'Trending', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Track trending post?', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'search_tracking',
	'title'          => __( 'Search Tracking', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Tracking search query on your site?', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'post_statistics',
	'title'          => __( 'Post Statistics', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Track post views on your site.', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'sticky_widget',
	'title'          => __( 'Sticky Widget', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Make last widget fixed when scroll down.', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'redirect_404',
	'title'          => __( 'Redirect 404', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Auto redirect 404 page to homepage.', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'bold_first_paragraph',
	'title'          => __( 'Bold First Paragraph', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Automatically bold first paragraph of content?', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'             => 'enlarge_thumbnail',
	'title'          => __( 'Enlarge Thumbnail', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Enlarge post thumbnail when using mobile?', 'hocwp-theme' )
);
$option->add_field( $args );

$args = array(
	'id'    => 'content_none_title',
	'title' => __( 'Content None Title', 'hocwp-theme' )
);
$option->add_field( $args );

$value = hocwp_get_value_by_key( $options, 'excerpt_length' );
$args  = array(
	'id'             => 'excerpt_length',
	'title'          => __( 'Excerpt Length', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_number',
	'value'          => $value
);
$option->add_field( $args );

if ( hocwp_wc_installed() || hocwp_is_shop_site() ) {
	$value = hocwp_get_product_posts_per_page();
	$args  = array(
		'id'             => 'products_per_page',
		'title'          => __( 'Product Each Page', 'hocwp-theme' ),
		'field_callback' => 'hocwp_field_input_number',
		'value'          => $value
	);
	$option->add_field();
}

$args = array(
	'id'          => 'breadcrumb',
	'title'       => __( 'Breadcrumb', 'hocwp-theme' ),
	'description' => __( 'Custom breadcrumb on your site.', 'hocwp-theme' )
);
$option->add_section( $args );

$args = array(
	'id'      => 'breadcrumb_label',
	'title'   => __( 'Breadcrumb Label', 'hocwp-theme' ),
	'value'   => hocwp_wpseo_internallink_value( 'breadcrumbs-prefix' ),
	'section' => 'breadcrumb'
);
$option->add_field( $args );

$args = array(
	'id'             => 'disable_post_title_breadcrumb',
	'title'          => __( 'Disable Post Title', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Prevent post title to be shown on last item.', 'hocwp-theme' ),
	'section'        => 'breadcrumb'
);
$option->add_field( $args );

$args = array(
	'id'             => 'link_last_item_breadcrumb',
	'title'          => __( 'Link Last Item', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Add link to last item instead of text.', 'hocwp-theme' ),
	'section'        => 'breadcrumb'
);
$option->add_field( $args );

$thumbnail_image_sizes = $options['thumbnail_image_sizes'];
$thumbnail_image_sizes = apply_filters( 'hocwp_thumbnail_image_sizes', $thumbnail_image_sizes );
$thumbnail_image_sizes = hocwp_sanitize_array( $thumbnail_image_sizes );
if ( hocwp_array_has_value( $thumbnail_image_sizes ) ) {
	$args = array(
		'id'          => 'thumbnail_images',
		'title'       => __( 'Thumbnail Images', 'hocwp-theme' ),
		'description' => __( 'These settings affect the display and dimensions of images in your catalog – the display on the front-end will still be affected by CSS styles.', 'hocwp-theme' )
	);
	$option->add_section( $args );
	foreach ( $thumbnail_image_sizes as $thumbnail_size ) {
		$thumbnail_size['section']        = 'thumbnail_images';
		$thumbnail_size['field_callback'] = 'hocwp_field_size';
		$option->add_field( $thumbnail_size );
	}
}

$args = array(
	'id'          => 'scroll_top_section',
	'title'       => __( 'Scroll To Top', 'hocwp-theme' ),
	'description' => __( 'This option can help you to display scroll to top button on your site.', 'hocwp-theme' )
);
$option->add_section( $args );

$args = array(
	'id'             => 'go_to_top',
	'title'          => __( 'Scroll Top Button', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Display scroll to top button on the bottom of site.', 'hocwp-theme' ),
	'section'        => 'scroll_top_section'
);
$option->add_field( $args );

$args = array(
	'id'             => 'go_to_top_on_left',
	'title'          => __( 'Diplay On Left', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_input_checkbox',
	'label'          => __( 'Display scroll to top button on the left of site.', 'hocwp-theme' ),
	'section'        => 'scroll_top_section'
);
$option->add_field( $args );

$args = array(
	'id'             => 'scroll_top_icon',
	'title'          => __( 'Button Icon', 'hocwp-theme' ),
	'field_callback' => 'hocwp_field_media_upload',
	'section'        => 'scroll_top_section'
);
$option->add_field( $args );

$option->add_option_tab( $hocwp->theme->option->sidebar_tabs );
$option->set_page_header_callback( 'hocwp_theme_option_form_before' );
$option->set_page_footer_callback( 'hocwp_theme_option_form_after' );
$option->set_page_sidebar_callback( 'hocwp_theme_option_sidebar_tab' );

$option->init();

hocwp_option_add_object_to_list( $option );

function hocwp_option_reading_update( $input ) {
	$breadcrumb_label = hocwp_get_value_by_key( $input, 'breadcrumb_label' );
	if ( ! empty( $breadcrumb_label ) ) {
		$breadcrumb_label = hocwp_remove_last_char( $breadcrumb_label, ':' );
		$breadcrumb_label .= ':';
		$wpseo_internallinks                       = get_option( 'wpseo_internallinks' );
		$wpseo_internallinks['breadcrumbs-prefix'] = $breadcrumb_label;
		update_option( 'wpseo_internallinks', $wpseo_internallinks );
		unset( $wpseo_internallinks );
	}
	$posts_per_page = hocwp_get_value_by_key( $input, 'products_per_page' );
	if ( hocwp_is_positive_number( $posts_per_page ) ) {
		update_option( 'hocwp_product_posts_per_page', $posts_per_page );
	}
	unset( $breadcrumb_label, $posts_per_page );
}

add_action( 'hocwp_sanitize_' . $option->get_option_name_no_prefix() . '_option', 'hocwp_option_reading_update' );
unset( $option, $parent_slug, $options, $args, $thumbnail_image_sizes, $thumbnail_size );
