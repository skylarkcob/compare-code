<?php
if ( class_exists( 'HOCWP_Font_Demo' ) ) {
	return;
}

require dirname( __FILE__ ) . '/hocwp/plugin/class-hocwp-plugin.php';

if ( ! defined( 'HOCWP_URL' ) ) {
	define( 'HOCWP_URL', Pixelify()->get_baseurl() . '/font-demo/hocwp' );
}

class HOCWP_Font_Demo extends HOCWP_Plugin {
	public $name = 'hocwp_font_demo';
	public $textdomain = 'pixelify';
	public $version = '1.0.2';
	public $file = __FILE__;

	public function __construct() {
		$this->option_name = 'hocwp_font_demo';
		$this->setting_url = 'admin.php?page=' . $this->option_name;
		parent::__construct();
		$this->core_custom();
	}

	public function license_data() {
		$data = array(
			array(
				'hashed'  => '$P$BPi4G1X8ouwEPhmfUP3taGiHuC.mnW1',
				'key_map' => 'a:5:{i:0;s:5:"email";i:1;s:7:"use_for";i:2;s:4:"code";i:3;s:6:"domain";i:4;s:15:"hashed_password";}',
				'domain'  => 'befonts.com'
			),
			array(
				'hashed'  => '$P$BEffanjEgfqr5laz823LF8uj3O7b/J0',
				'key_map' => 'a:5:{i:0;s:5:"email";i:1;s:4:"code";i:2;s:7:"use_for";i:3;s:6:"domain";i:4;s:15:"hashed_password";}',
				'domain'  => 'befonts.com'
			),
			array(
				'hashed'  => '$P$BNJ4F1z6.0qjc/ed0ZWc3Ldb.inimZ1',
				'key_map' => 'a:5:{i:0;s:6:"domain";i:1;s:5:"email";i:2;s:4:"code";i:3;s:7:"use_for";i:4;s:15:"hashed_password";}',
				'domain'  => 'itypeface.com'
			),
			array(
				'hashed'  => '$P$BPxfxxJGIIF9JG3mwUsnEfmMPbGmCA1',
				'key_map' => 'a:5:{i:0;s:5:"email";i:1;s:6:"domain";i:2;s:4:"code";i:3;s:7:"use_for";i:4;s:15:"hashed_password";}',
				'domain'  => 'itypeface.com'
			),
			array(
				'hashed'  => '$P$BqNX4sPNJXJnhqiyZDEZT11wfKywhc.',
				'key_map' => 'a:5:{i:0;s:6:"domain";i:1;s:4:"code";i:2;s:5:"email";i:3;s:7:"use_for";i:4;s:15:"hashed_password";}',
				'domain'  => 'itypeface.com'
			),
			array(
				'hashed'  => '$P$BG5L0PaCBnw5GqGcLl0gUbKO2EP2kh1',
				'key_map' => 'a:5:{i:0;s:5:"email";i:1;s:6:"domain";i:2;s:7:"use_for";i:3;s:4:"code";i:4;s:15:"hashed_password";}',
				'domain'  => 'hocwp.tk'
			),
			array(
				'hashed'  => '$P$BFqvJsmPcT2D.akiUL2lFOQmcec1fV.',
				'key_map' => 'a:5:{i:0;s:7:"use_for";i:1;s:5:"email";i:2;s:4:"code";i:3;s:6:"domain";i:4;s:15:"hashed_password";}',
				'domain'  => 'hocwp.tk'
			),
			array(
				'hashed'  => '$P$B1neQlvxzF.lN.lYsoqUr9ojMo/yY6/',
				'key_map' => 'a:5:{i:0;s:7:"use_for";i:1;s:6:"domain";i:2;s:5:"email";i:3;s:4:"code";i:4;s:15:"hashed_password";}',
				'domain'  => '192.168.1.66'
			)
		);

		return $data;
	}

	public function core_custom() {
		hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-functions.php' );
		hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-shortcode.php' );
		hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-post-type-and-taxonomy.php' );
		hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-hook.php' );
		hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-ajax.php' );
		hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-translation.php' );
		if ( is_admin() ) {
			hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-admin.php' );
			hocwp_require_if_file_exists( $this->custom_path . '/hocwp-plugin-meta.php' );
		}
	}
}

global $hocwp;

if ( ! is_object( $hocwp ) ) {
	$hocwp = new stdClass();
}

if ( empty( $hocwp->plugin ) || ! is_object( $hocwp->plugin ) ) {
	$hocwp->plugin = new stdClass();
}

$hocwp->plugin->font_demo = new HOCWP_Font_Demo();