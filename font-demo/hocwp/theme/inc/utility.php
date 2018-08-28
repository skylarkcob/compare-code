<?php
$utilities    = get_option( 'hocwp_utilities' );
$link_manager = hocwp_get_value_by_key( $utilities, 'link_manager' );
$auto_update  = hocwp_get_value_by_key( $utilities, 'auto_update' );

if ( (bool) $link_manager ) {
	add_filter( 'pre_option_link_manager_enabled', '__return_true' );
}

if ( (bool) $auto_update ) {
	add_filter( 'auto_update_translation', '__return_true' );
	add_filter( 'auto_update_plugin', '__return_true' );
	add_filter( 'auto_update_theme', '__return_true' );
	add_filter( 'auto_update_core', '__return_true' );
}