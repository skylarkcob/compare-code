<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}

if ( ! defined( 'HOCWP_PATH' ) ) {
	define( 'HOCWP_PATH', dirname( dirname( __FILE__ ) ) );
}

define( 'HOCWP_PLUGIN_CORE_PATH', dirname( dirname( __FILE__ ) ) . '/plugin' );
define( 'HOCWP_PLUGIN_CORE_INC_PATH', HOCWP_PLUGIN_CORE_PATH . '/inc' );
define( 'HOCWP_PLUGIN_CORE_ADMIN_PATH', HOCWP_PLUGIN_CORE_PATH . '/admin' );

if ( ! defined( 'HOCWP_PLUGIN_LICENSE_OPTION_NAME' ) ) {
	define( 'HOCWP_PLUGIN_LICENSE_OPTION_NAME', 'hocwp_plugin_licenses' );
}

if ( ! defined( 'HOCWP_PLUGIN_LICENSE_ADMIN_URL' ) ) {
	define( 'HOCWP_PLUGIN_LICENSE_ADMIN_URL', admin_url( 'admin.php?page=hocwp_plugin_license' ) );
}

require dirname( dirname( __FILE__ ) ) . '/class-hocwp.php';

/*
require HOCWP_PATH . '/inc/functions.php';
require HOCWP_PATH . '/inc/core-functions.php';
require HOCWP_PATH . '/inc/utility.php';
require HOCWP_PATH . '/inc/class-hocwp-license.php';
require HOCWP_PATH . '/inc/class-hocwp-html.php';
require HOCWP_PATH . '/ext/option.php';
require HOCWP_PATH . '/inc/class-hocwp-option.php';
require HOCWP_PATH . '/ext/post.php';
require HOCWP_PATH . '/ext/media.php';
require HOCWP_PATH . '/ext/meta.php';
require HOCWP_PATH . '/ext/shortcode.php';
require HOCWP_PATH . '/ext/html-field.php';
require HOCWP_PATH . '/ext/i18n.php';
require HOCWP_PATH . '/inc/class-hocwp-meta.php';
*/

class HOCWP_Plugin extends HOCWP {
	public $name;
	public $version;
	public $file = __FILE__;
	public $path;
	public $url;
	public $inc_path;
	public $custom_path;
	public $basename;
	public $dirname;
	public $option_name;
	public $setting_url;
	public $use_session;
	public $option_defaults;
	public $license_data;
	public $textdomain;
	public $load_core_scripts;
	public $admin_menu_parent = 'hocwp_plugin_option';
	public $options;

	public function __construct() {
		parent::__construct();
		global $hocwp;
		if ( empty( $hocwp->plugin->core ) ) {
			$hocwp->plugin->core = $this;
		}
		$this->init();
		$this->pre_hook();
		$this->core();
		$this->admin();
		$this->hook();
	}

	private function init() {
		$this->path        = Pixelify()->get_basedir() . '/font-demo';
		$this->url         = Pixelify()->get_baseurl() . '/font-demo';
		$this->inc_path    = $this->path . '/inc';
		$this->custom_path = $this->path . '/custom';
		$this->basename    = plugin_basename( $this->file );
		$this->dirname     = dirname( $this->basename );
		if ( ! empty( $this->option_name ) ) {
			$this->setting_url = admin_url( 'admin.php?page=' . $this->option_name );
		}
	}

	private function pre_hook() {
		add_filter( 'hocwp_compress_paths', array( $this, 'minify_file_path' ) );
		$this->option_defaults   = apply_filters( $this->name . '_option_defaults', $this->option_defaults );
		$this->load_core_scripts = apply_filters( $this->name . '_load_core_scripts', $this->load_core_scripts );
	}

	private function hook() {
		if ( $this->use_session ) {
			add_action( 'init', 'hocwp_session_start' );
		}

		register_activation_hook( $this->file, array( $this, 'activation' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivation' ) );
		add_action( 'hocwp_check_license', array( $this, 'check_license' ) );
		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'settings_link' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		if ( ! is_admin() ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 99 );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );

		if ( ! is_admin() ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu_front_end' ), 99 );
		}
	}

	public function admin_bar_menu_front_end( $wp_admin_bar ) {

	}

	public function body_class( $classes ) {
		$classes[] = 'hocwp';
		if ( ! $this->license_valid() ) {
			$classes[] = 'hocwp-invalid-license';
		}

		return $classes;
	}

	public function enqueue_scripts() {
		hocwp_register_core_style_and_script();
		$localize_object = hocwp_default_script_localize_object();
		unset( $localize_object['shortcodes'] );

		if ( hocwp_is_debugging() ) {
			wp_localize_script( 'hocwp', 'hocwp', $localize_object );
			wp_register_script( 'hocwp-front-end', HOCWP_URL . '/js/hocwp-front-end' . HOCWP_JS_SUFFIX, array( 'hocwp' ), false, true );
			wp_register_script( 'font-demo', $this->url . '/js/hocwp-plugin' . HOCWP_JS_SUFFIX, array( 'hocwp-front-end' ), false, true );
		} else {
			wp_register_script( 'font-demo', $this->url . '/js/hocwp-plugin' . HOCWP_JS_SUFFIX, array(), $this->version, true );
			wp_localize_script( 'font-demo', 'hocwp', $localize_object );
		}

		wp_register_style( 'font-demo-style', $this->url . '/css/hocwp-plugin' . HOCWP_CSS_SUFFIX, array(), $this->version );
		wp_enqueue_style( 'font-demo-style' );
		$script = apply_filters( 'hocwp_font_demo_use_scripts', true );

		if ( $script ) {
			wp_enqueue_script( 'font-demo' );
			wp_localize_script( 'font-demo', 'hocwp', $localize_object );
		}
	}

	public function admin_enqueue_scripts() {
		hocwp_register_core_style_and_script();
		hocwp_admin_enqueue_scripts();
		wp_register_style( 'hocwp-admin-style', HOCWP_URL . '/css/hocwp-admin' . HOCWP_CSS_SUFFIX, array( 'hocwp-style' ), $this->version );
		wp_register_script( 'hocwp-admin', HOCWP_URL . '/js/hocwp-admin' . HOCWP_JS_SUFFIX, array(
			'jquery',
			'hocwp'
		), $this->version, true );
		wp_register_style( 'font-demo-style', $this->url . '/css/hocwp-plugin-admin' . HOCWP_CSS_SUFFIX, array( 'hocwp-admin-style' ), $this->version );
		wp_register_script( 'font-demo', $this->url . '/js/hocwp-plugin-admin' . HOCWP_JS_SUFFIX, array( 'hocwp-admin' ), $this->version, true );
		wp_localize_script( 'font-demo', 'hocwp', hocwp_default_script_localize_object() );
		wp_enqueue_style( 'font-demo-style' );
		wp_enqueue_script( 'font-demo' );
	}

	public function check_license() {
		if ( ! isset( $_POST['submit'] ) && ! hocwp_is_login_page() ) {
			if ( ! $this->license_valid() && ! HOCWP_DOING_CRON && ! HOCWP_DOING_AJAX && ! HOCWP_DOING_AUTO_SAVE ) {
				if ( ! is_admin() && current_user_can( 'manage_options' ) ) {
					wp_redirect( HOCWP_PLUGIN_LICENSE_ADMIN_URL );
					exit;
				}
				add_action( 'admin_notices', array( $this, 'invalid_license_notice' ) );
			}
		}
	}

	final function admin_bar_menu( $wp_admin_bar ) {
		$args = array(
			'id'     => 'plugin-license',
			'title'  => __( 'Plugin Licenses', 'hocwp-theme' ),
			'href'   => HOCWP_PLUGIN_LICENSE_ADMIN_URL,
			'parent' => 'plugins'
		);
		$wp_admin_bar->add_node( $args );
	}

	public function invalid_license_notice() {
		$plugin_name = hocwp_get_plugin_name( $this->file, $this->basename );
		$plugin_name = hocwp_wrap_tag( $plugin_name, 'strong' );
		$format      = __( 'Plugin %1$s is using an invalid license key! If you does not have one, please contact %2$s via email address %3$s for more information.', 'hocwp-theme' );
		$args        = array(
			'error' => true,
			'title' => __( 'Error', 'hocwp-theme' ),
			'text'  => sprintf( $format, $plugin_name, '<strong>' . HOCWP_NAME . '</strong>', '<a href="mailto:' . esc_attr( HOCWP_EMAIL ) . '">' . HOCWP_EMAIL . '</a>' )
		);
		hocwp_admin_notice( $args );
	}

	public function get_license_data() {
		$data = apply_filters( $this->name . '_license_defined_data', $this->license_data, $this );

		return $data;
	}

	public function minify_file_path( $paths ) {
		if ( ! is_array( $paths ) ) {
			$paths = array();
		}
		$paths[] = $this->path;

		return $paths;
	}

	public function core() {
		require( $this->custom_path . '/hocwp-plugin-pre-hook.php' );

		require_once( HOCWP_PLUGIN_CORE_INC_PATH . '/functions.php' );

		require( HOCWP_PLUGIN_CORE_INC_PATH . '/setup-plugin.php' );
	}

	public function get_option() {
		$defaults = $this->option_defaults;
		$option   = get_option( $this->option_name );
		if ( ! hocwp_array_has_value( $option ) ) {
			$option = array();
		}
		$option = wp_parse_args( $option, $defaults );

		return apply_filters( $this->option_name . '_options', $option );
	}

	public function license_data() {
		$data = array(
			'hashed'  => '',
			'key_map' => '',
			'domain'  => ''
		);

		return $data;
	}

	public function license_valid() {
		$license = new HOCWP_License();
		$license->set_type( 'plugin' );
		$license->set_use_for( $this->basename );
		$license->set_option_name( 'hocwp_plugin_license' );
		$data = $this->license_data;
		if ( ! hocwp_array_has_value( $data ) ) {
			$data = $this->license_data();
		}
		$result = $license->check_valid( $data );
		unset( $license );

		return $result;
	}

	public function activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		flush_rewrite_rules();
		do_action( $this->name . '_activation' );
	}

	public function deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		flush_rewrite_rules();
		do_action( $this->name . '_deactivation' );
	}

	final function settings_link( $links ) {
		if ( ! empty( $this->setting_url ) ) {
			$settings_link = sprintf( '<a href="' . $this->setting_url . '">%s</a>', __( 'Settings', 'hocwp-theme' ) );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	final function load_textdomain() {
		//load_plugin_textdomain( $this->textdomain, false, $this->path . '/languages/' );
	}

	final function admin_init() {
		$plugin_base_name = md5( $this->basename );
		$option_name      = 'plugin_' . $plugin_base_name . '_version';
		$version          = get_option( $option_name );
		if ( $version != $this->version ) {
			update_option( $option_name, $this->version );
			flush_rewrite_rules();
		}
	}

	final function add_option_to_sidebar_tab( HOCWP_Option $option ) {
		if ( ! hocwp_menu_page_exists( $this->option_name ) ) {
			global $hocwp;
			$option->set_parent_slug( $this->admin_menu_parent );
			$option->add_option_tab( $hocwp->plugin->option->sidebar_tabs );
			$option->set_page_header_callback( 'hocwp_plugin_option_page_header' );
			$option->set_page_footer_callback( 'hocwp_plugin_option_page_footer' );
			$option->set_page_sidebar_callback( 'hocwp_plugin_option_page_sidebar' );
		}
	}

	final function admin() {
		if ( is_admin() ) {
			require( HOCWP_PLUGIN_CORE_ADMIN_PATH . '/admin.php' );
		}
	}

	final function get_options() {
		return $this->get_option();
	}

	final function get_option_value_by_key( $key ) {
		$options = $this->get_options();

		return hocwp_get_value_by_key( $options, $key );
	}
}

global $hocwp;

if ( ! is_object( $hocwp ) ) {
	$hocwp = new stdClass();
}

if ( ! isset( $hocwp->plugin ) || empty( $hocwp->plugin ) ) {
	$hocwp->plugin = new stdClass();
}