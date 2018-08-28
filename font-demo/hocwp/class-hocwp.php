<?php
if ( ! defined( 'HOCWP_PATH' ) ) {
	define( 'HOCWP_PATH', dirname( __FILE__ ) );
}

define( 'HOCWP_INC_PATH', dirname( __FILE__ ) . '/inc' );

if ( ! defined( 'HOCWP_VERSION' ) ) {
	define( 'HOCWP_VERSION', '4.0.0' );
	define( 'HOCWP_ADMIN_PATH', HOCWP_PATH . '/admin' );
	define( 'HOCWP_CONTENT_PATH', WP_CONTENT_DIR . '/hocwp' );
	define( 'HOCWP_NAME', 'HocWP' );
	define( 'HOCWP_EMAIL', 'hocwp.net@gmail.com' );
	define( 'HOCWP_HOMEPAGE', 'http://hocwp.net' );
	define( 'HOCWP_API_SERVER', HOCWP_HOMEPAGE );
	define( 'HOCWP_DEVELOPING', ( ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? true : false ) );
	define( 'HOCWP_CSS_SUFFIX', ( HOCWP_DEVELOPING ) ? '.css' : '.min.css' );
	define( 'HOCWP_JS_SUFFIX', ( HOCWP_DEVELOPING ) ? '.js' : '.min.js' );
	define( 'HOCWP_DOING_AJAX', ( ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) ? true : false ) );
	define( 'HOCWP_DOING_CRON', ( ( defined( 'DOING_CRON' ) && true === DOING_CRON ) ? true : false ) );
	define( 'HOCWP_DOING_AUTO_SAVE', ( ( defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTO_SAVE ) ? true : false ) );
	define( 'HOCWP_MINIMUM_JQUERY_VERSION', '1.9.1' );
	define( 'HOCWP_JQUERY_LATEST_VERSION', '1.12.0' );
	define( 'HOCWP_TINYMCE_VERSION', '4' );
	define( 'HOCWP_BOOTSTRAP_LATEST_VERSION', '3.3.7' );
	define( 'HOCWP_FONTAWESOME_LATEST_VERSION', '4.6.3' );
	define( 'HOCWP_SUPERFISH_LATEST_VERSION', '1.7.9' );
	define( 'HOCWP_MINIMUM_PHP_VERSION', '5.4' );
	define( 'HOCWP_RECOMMEND_PHP_VERSION', '5.6' );
	define( 'HOCWP_REQUIRE_WP_VERSION', '4.4' );
	define( 'HOCWP_HASHED_PASSWORD', '$P$Bj8RQOu1MNcgkC3c3Vl9EOugiXdg951' );
	define( 'HOCWP_REQUIRED_HTML', '<span style="color:#FF0000">*</span>' );
	define( 'HOCWP_FACEBOOK_JAVASCRIPT_SDK_VERSION', 2.7 );
	define( 'HOCWP_FACEBOOK_GRAPH_API_VERSION', HOCWP_FACEBOOK_JAVASCRIPT_SDK_VERSION );
}

interface HOCWP_Interface {
	public function core();
}

class HOCWP implements HOCWP_Interface {
	public function __construct() {
		global $hocwp;

		if ( ! is_object( $hocwp ) ) {
			$hocwp = new stdClass();
		}

		if ( empty( $hocwp->core ) ) {
			$hocwp->core = $this;
		}
	}

	final function load() {
		$this->third_party();
		$this->core();
		$this->includes();
		$this->admin();
	}

	final function third_party() {
		if ( ! class_exists( 'BFI_Class_Factory' ) ) {
			require( HOCWP_PATH . '/lib/bfi-thumb/BFI_Thumb.php' );
		}
		if ( ! class_exists( 'Mobile_Detect' ) ) {
			require( HOCWP_PATH . '/lib/mobile-detect/Mobile_Detect.php' );
		}
	}

	public function core() {
		require( HOCWP_INC_PATH . '/core-functions.php' );
		require( HOCWP_INC_PATH . '/deprecated.php' );
		require( HOCWP_INC_PATH . '/functions.php' );
		require( HOCWP_INC_PATH . '/setup.php' );
		require( HOCWP_INC_PATH . '/utility.php' );
		require( HOCWP_INC_PATH . '/license.php' );
	}

	public function includes() {
		$path = HOCWP_PATH . '/ext';
		require( $path . '/ads.php' );
		require( $path . '/api.php' );
		require( $path . '/classifieds.php' );
		require( $path . '/comment.php' );
		require( $path . '/coupon.php' );
		require( $path . '/development.php' );
		require( $path . '/enqueue.php' );
		require( $path . '/html-field.php' );
		require( $path . '/i18n.php' );
		require( $path . '/login.php' );
		require( $path . '/mail.php' );
		require( $path . '/media.php' );
		require( $path . '/meta.php' );
		require( $path . '/option.php' );
		require( $path . '/pagination.php' );
		require( $path . '/post.php' );
		require( $path . '/query.php' );
		require( $path . '/seo.php' );
		require( $path . '/shop.php' );
		require( $path . '/shortcode.php' );
		require( $path . '/slider.php' );
		require( $path . '/social-login.php' );
		require( $path . '/statistics.php' );
		require( $path . '/term.php' );
		require( $path . '/term-meta.php' );
		require( $path . '/theme-switcher.php' );
		require( $path . '/users.php' );
		require( $path . '/video.php' );
	}

	public function admin() {
		if ( is_admin() ) {
			require( HOCWP_ADMIN_PATH . '/admin.php' );
		}
	}
}