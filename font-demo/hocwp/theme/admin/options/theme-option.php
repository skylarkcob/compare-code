<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

global $hocwp;

if ( ! empty( $hocwp->theme->option ) ) {
	return;
}

$option = new HOCWP_Option( __( 'Theme Options', 'hocwp-theme' ), 'hocwp_theme_option' );
$option->set_parent_slug( '' );
$option->set_icon_url( 'dashicons-admin-generic' );
$option->set_position( 61 );
$option->set_use_style_and_script( true );

$option->init();

$hocwp->theme->option       = new stdClass();
$hocwp->theme->option->core = $option;

$option = new HOCWP_Option( __( 'Private Types', 'hocwp-theme' ), 'hocwp_private_types' );
$option->set_parent_slug( '' );
$option->set_icon_url( 'dashicons-admin-post' );
$option->set_position( 90 );
$option->set_use_style_and_script( false );

$option->init();

unset( $option );

require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-setting.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-home.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-custom.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-custom-css.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-add-to-head.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-add-to-footer.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-optimize.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-social.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-login.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-smtp-email.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-writing.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-reading.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-discussion.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-permalink.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-utilities.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-geo.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-license.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-maintenance.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-recommend-plugin.php' );
require( HOCWP_THEME_CORE_ADMIN_PATH . '/options/setting-theme-about.php' );