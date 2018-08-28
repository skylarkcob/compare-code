<?php
if ( defined( 'HOCWP_THEME_CORE_VERSION' ) ) {
	return;
}

define( 'HOCWP_THEME_CORE_VERSION', '6.0.0' );
define( 'HOCWP_THEME_REQUIRE_CORE_VERSION', '4.0.0' );
define( 'HOCWP_THEME_CORE_PATH', dirname( __FILE__ ) );
define( 'HOCWP_THEME_CORE_INC_PATH', HOCWP_THEME_CORE_PATH . '/inc' );
define( 'HOCWP_THEME_CORE_EXT_PATH', HOCWP_THEME_CORE_PATH . '/ext' );
define( 'HOCWP_THEME_CORE_ADMIN_PATH', HOCWP_THEME_CORE_PATH . '/admin' );
define( 'HOCWP_THEME_PATH', get_template_directory() );
define( 'HOCWP_THEME_CHILD_PATH', get_stylesheet_directory() );
define( 'HOCWP_THEME_CUSTOM_PATH', HOCWP_THEME_PATH . '/custom' );

require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-pre-hook.php' );

if ( ! defined( 'HOCWP_PATH' ) ) {
	if ( ! file_exists( HOCWP_THEME_PATH . '/hocwp/load.php' ) ) {
		if ( is_admin() ) {
			function hocwp_theme_missing_core_notice() {
				$message = __( 'Current theme cannot be run properly because of missing core.', 'hocwp-theme' );
				$format  = '<strong>%1$s</strong> %2$s';
				$notice  = sprintf( $format, __( 'Error:', 'pixelify' ), $message );
				$format  = '<div class="updated notice settings-error error"><p>%s</p></div>';
				$notice  = sprintf( $format, $notice );
				echo $notice;
			}

			add_action( 'admin_notices', 'hocwp_theme_missing_core_notice' );
		} else {
			$format  = '<strong>%1$s</strong> %2$s';
			$message = __( 'Theme cannot be displayed because of missing core. Please contact administrator for assistance.', 'hocwp-theme' );
			$message = sprintf( $format, __( 'Error:', 'hocwp-theme' ), $message );
			wp_die( $message, __( 'Missing Core', 'hocwp-theme' ) );
			exit;
		}

		return;
	}
	require_once( HOCWP_THEME_PATH . '/hocwp/load.php' );
}

if ( version_compare( $GLOBALS['wp_version'], HOCWP_REQUIRE_WP_VERSION, '<' ) ) {
	require( HOCWP_THEME_CORE_INC_PATH . '/back-compat.php' );

	return;
}

if ( version_compare( HOCWP_VERSION, HOCWP_THEME_REQUIRE_CORE_VERSION, '<' ) ) {
	global $pagenow;
	if ( is_admin() ) {
		function hocwp_theme_invalid_core_version_notice() {
			$format  = '<strong>%1$s</strong> %2$s';
			$message = __( 'Current theme cannot be run properly because of using invalid core version. Please update core to latest version or contact administrator for assistance.', 'hocwp-theme' );
			$message = sprintf( $format, __( 'Error:', 'hocwp-theme' ), $message );
			echo $message;
		}

		add_action( 'admin_notices', 'hocwp_theme_invalid_core_version_notice' );
	} else {
		if ( 'wp-login.php' != $pagenow ) {
			$format  = '<strong>%1$s</strong> %2$s';
			$message = __( 'Theme cannot be displayed because of using invalid core version. Please contact administrator for assistance.', 'hocwp-theme' );
			$message = sprintf( $format, __( 'Error:', 'hocwp-theme' ), $message );
			wp_die( $message, __( 'Invalid Core Version', 'hocwp-theme' ) );
			exit;
		}
	}

	return;
}

define( 'HOCWP_THEME_INC_PATH', HOCWP_THEME_PATH . '/inc' );
define( 'HOCWP_THEME_URL', get_template_directory_uri() );
define( 'HOCWP_THEME_CHILD_URL', get_stylesheet_directory_uri() );
define( 'HOCWP_THEME_INC_URL', HOCWP_THEME_URL . '/inc' );
define( 'HOCWP_THEME_TEMPLATE_PARTS_PATH', HOCWP_THEME_PATH . '/template-parts' );

if ( ! defined( 'HOCWP_URL' ) ) {
	define( 'HOCWP_URL', untrailingslashit( HOCWP_THEME_URL ) . '/hocwp' );
}

class HOCWP_Theme extends HOCWP {
	public function __construct() {
		parent::__construct();
		add_action( 'after_setup_theme', array( $this, 'core' ), - 95 );
		add_action( 'after_setup_theme', array( $this, 'options' ), - 95 );
		add_action( 'after_setup_theme', array( $this, 'theme_support' ), 12 );
		add_action( 'after_setup_theme', array( $this, 'includes' ), 13 );
		add_action( 'after_setup_theme', array( $this, 'extensions' ), 14 );
		add_action( 'after_setup_theme', array( $this, 'admin' ), 95 );
		add_action( 'after_setup_theme', array( $this, 'clean' ), 95 );
	}

	public function core() {
		require( HOCWP_THEME_CORE_INC_PATH . '/deprecated.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/theme-functions.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/utility.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/options.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/setup-theme.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/template.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/template-general.php' );
		$this->core_custom();
		require( HOCWP_THEME_CORE_INC_PATH . '/setup-theme-after.php' );
		require( HOCWP_THEME_CORE_INC_PATH . '/i18n.php' );
		require( HOCWP_THEME_CORE_EXT_PATH . '/user-login.php' );
	}

	public function core_custom() {
		require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-functions.php' );
		require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-shortcode.php' );
		require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-post-type-and-taxonomy.php' );
		require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-hook.php' );
		require( HOCWP_THEME_CUSTOM_PATH . '/hocwp-custom-translation.php' );
	}

	public function options() {
		global $hocwp;

		if ( empty( $hocwp->theme->options ) ) {
			$hocwp->theme->options = new stdClass();
		}

		$hocwp->theme->options->reading = hocwp_option_reading();

		if ( empty( $hocwp->theme->admin ) ) {
			$hocwp->theme->admin = new stdClass();
		}
	}

	public function theme_support() {
		add_theme_support( 'bfi-thumb' );
		add_theme_support( 'hocwp-option' );
		add_theme_support( 'hocwp-i18n' );
		add_theme_support( 'hocwp-user' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		$support_args = array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption'
		);
		add_theme_support( 'html5', $support_args );
		$support_args = array(
			'aside',
			'image',
			'video',
			'quote',
			'link',
			'gallery',
			'status',
			'audio',
			'chat'
		);
		add_theme_support( 'post-formats', $support_args );
	}

	public function includes() {
		require( HOCWP_THEME_CORE_INC_PATH . '/theme-translation.php' );
		require( HOCWP_THEME_CORE_EXT_PATH . '/maintenance.php' );
	}

	public function extensions() {

	}

	public function admin() {
		if ( is_admin() ) {
			require( HOCWP_THEME_CORE_PATH . '/admin/admin.php' );
		}
	}

	public function clean() {
		unset( $GLOBALS['wpdb']->dbpassword );
		unset( $GLOBALS['wpdb']->dbname );
		remove_action( 'wp_head', 'wp_generator' );
	}
}