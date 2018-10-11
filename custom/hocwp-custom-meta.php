<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $pagenow;

if ( 'edit-tags.php' == $pagenow || 'term.php' == $pagenow ) {
	$meta = new HOCWP_Meta( 'term' );
	$meta->set_id( 'term_information' );
	$meta->add_taxonomy( 'designer' );

	$args = array(
		'id'    => 'donate',
		'name'  => 'donate',
		'label' => 'Donate Url:'
	);

	$meta->add_field( $args );

	$args = array(
		'id'    => 'email',
		'name'  => 'email',
		'label' => 'Email:',
		'type'  => 'email'
	);

	$meta->add_field( $args );

	$meta->init();
}

if ( 'edit.php' == $pagenow || 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
	$meta = new HOCWP_Meta( 'post' );
	$meta->set_title( 'Font Information' );
	$meta->add_post_type( 'post' );
	$meta->set_id( 'font-information' );

	$args = array(
		'id'    => 'name',
		'label' => 'Name'
	);
	$meta->add_field( $args );

	$args = array(
		'id'    => 'website',
		'label' => 'Website'
	);
	$meta->add_field( $args );

	$args = array(
		'id'    => 'commercial_license',
		'label' => 'Commercial License'
	);
	$meta->add_field( $args );

	$args = array(
		'id'    => 'donate',
		'label' => 'Donate link'
	);
	$meta->add_field( $args );

	$meta->init();

	$meta = new HOCWP_Meta( 'post' );
	$meta->set_title( 'Font Files' );
	$meta->set_id( 'font_files_box' );
	$meta->add_post_type( 'post' );

	$args = array(
		'id'             => 'file_contents',
		'label'          => 'File Contents',
		'field_callback' => 'hocwp_field_media_upload'
	);
	$meta->add_field( $args );

	$args = array(
		'id'             => 'demo',
		'label'          => 'Demo',
		'field_callback' => 'hocwp_field_media_upload'
	);
	$meta->add_field( $args );

	$meta->init();

	$args = array(
		'title'      => 'Character Map',
		'id'         => 'character_map_box',
		'post_type'  => 'post',
		'field_name' => 'character_map'
	);
	hocwp_meta_box_editor( $args );
}