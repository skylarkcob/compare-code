<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

$meta = new HOCWP_Meta( 'post' );
$meta->set_post_types( array( 'post', 'page' ) );
$meta->set_title( 'Media Information' );

$args = array(
	'id'    => 'file_format',
	'label' => 'File Format'
);
$meta->add_field( $args );

$args = array(
	'id'    => 'license_text',
	'label' => 'License Text'
);
$meta->add_field( $args );

$args = array(
	'id'    => 'small_image_link',
	'label' => 'Small Image Link'
);
$meta->add_field( $args );

$args = array(
	'id'    => 'more_link',
	'label' => 'More Link'
);
$meta->add_field( $args );

$args = array(
	'id'    => 'donate_link',
	'label' => 'Donate Link'
);
$meta->add_field( $args );

$args = array(
	'id'             => 'download_url',
	'label'          => 'Download Url',
	'field_callback' => 'hocwp_field_media_upload',
	'container'      => true
);
$meta->add_field( $args );

$args = array(
	'id'    => 'demo_text',
	'label' => 'Demo Text'
);
$meta->add_field( $args );

$meta->init();

$args = array(
	'post_type'  => array( 'post', 'page' ),
	'title'      => 'Media Description',
	'id'         => 'hocwp_media_description',
	'field_name' => 'media_description'
);
hocwp_meta_box_editor( $args );

$args = array(
	'post_type'  => array( 'post', 'page' ),
	'title'      => 'Media Images',
	'id'         => 'hocwp_media_images',
	'field_name' => 'media_images'
);
hocwp_meta_box_editor( $args );

$args['title']      = 'Small Image';
$args['id']         = 'hocwp_small_image';
$args['field_name'] = 'small_image';
$args['post_type']  = array( 'post', 'page' );
hocwp_meta_box_side_image( $args );

$meta = new HOCWP_Meta( 'post' );
$meta->set_post_types( array( 'post', 'page' ) );
$meta->set_title( 'Fonts Demo' );
$meta->set_id( 'hocwp_fonts_demo' );

$meta->init();

function hocwp_font_demo_post_fonts_demo_meta_fields( $meta ) {
	if ( $meta instanceof HOCWP_Meta && 'hocwp_fonts_demo' == $meta->get_id() ) {
		global $hocwp;
		hocwp_plugin_get_module( $hocwp->plugin->font_demo->path, 'meta-box-fonts-demo' );
	}
}

add_action( 'hocwp_post_meta_box_field', 'hocwp_font_demo_post_fonts_demo_meta_fields', 99 );

function hocwp_font_demo_on_save_post( $post_id ) {
	if ( ! hocwp_can_save_post( $post_id ) ) {
		return;
	}
	if ( isset( $_POST['font_demos'] ) ) {
		update_post_meta( $post_id, 'font_demos', $_REQUEST['font_demos'] );
	}
}

add_action( 'save_post', 'hocwp_font_demo_on_save_post' );