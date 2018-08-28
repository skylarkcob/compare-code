<?php
/**
 * Plugin Name: Pixelify Font Demo
 * Plugin URI: http://hocwp.net/project/
 * Description: This plugin is created by HocWP Team.
 * Author: HocWP Team
 * Version: 1.1.0
 * Author URI: http://facebook.com/hocwpnet/
 * Text Domain: pixelify
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once dirname( __FILE__ ) . '/hocwp/class-hocwp-plugin.php';

class HOCWP_Pixelify extends HOCWP_Plugin_Core {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self( __FILE__ );
		}

		return self::$instance;
	}

	public $max_image = 5;
	public $max_tag = 30;

	public $download_extension = array( '.zip' );
	public $font_extension = array( '.otf', '.ttf' );

	public function get_version() {
		$data = get_plugin_data( __FILE__ );

		return $data['Version'];
	}

	public function __construct( $file_path ) {
		if ( self::$instance instanceof self ) {
			return;
		}

		$this->set_textdomain( 'pixelify' );
		$this->short_name = 'pxf_';

		parent::__construct( $file_path );

		$labels = array(
			'action_link_text' => __( 'Settings', 'pixelify' ),
			'options_page'     => array(
				'page_title' => __( 'Pixelify by HocWP Team', 'pixelify' ),
				'menu_title' => __( 'Pixelify', 'pixelify' )
			),
			'license'          => array(
				'notify'      => array(
					'email_subject' => __( 'Notify plugin license', 'pixelify' )
				),
				'die_message' => __( 'Your plugin is blocked.', 'pixelify' ),
				'die_title'   => __( 'Plugin Invalid License', 'pixelify' )
			)
		);

		$this->set_labels( $labels );
		$this->set_option_name( 'pixelify' );
		$this->init();

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_filter( 'user_contactmethods', array( $this, 'custom_user_contactmethods_filter' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'template_redirect', array( $this, 'custom_template_redirect_action' ) );
		}

		add_shortcode( 'pixelify_dashboard', array( $this, 'shortcode_dashboard' ) );
		add_shortcode( 'pixelify_wish_lists', array( $this, 'shortcode_wish_lists' ) );
		add_shortcode( 'pixelify_list_authors', array( $this, 'shortcode_list_authors' ) );
		add_shortcode( 'pixelify_login', array( $this, 'shortcode_login' ) );
		add_shortcode( 'pixelify_register', array( $this, 'shortcode_register' ) );
		add_shortcode( 'pixelify_lostpassword', array( $this, 'shortcode_lostpassword' ) );

		add_filter( 'get_edit_post_link', array( $this, 'custom_get_edit_post_link_filter' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'custom_post_type_link_filter' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post_action' ) );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme_action' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer_action' ) );
		add_filter( 'body_class', array( $this, 'body_class_filter' ) );
		add_filter( 'post_class', array( $this, 'post_class_filter' ) );
		add_action( 'init', array( $this, 'init_action' ) );
		add_action( 'wp', array( $this, 'wp_action' ) );
	}

	public function wp_action() {
		$downfile = get_query_var( 'downfile' );

		if ( ! empty( $downfile ) ) {
			$downfile = str_replace( '.html', '', $downfile );

			$parts = explode( '.', $downfile );

			$post_id = array_pop( $parts );

			$obj = get_post( $post_id );

			if ( $obj instanceof WP_Post ) {
				$url = $this->get_download_url( $post_id );

				if ( ! empty( $url ) ) {
					wp_redirect( $url );
					exit;
				}
			}
		}
	}

	public function init_action() {
		add_rewrite_endpoint( 'downfile', EP_ALL );
	}

	public function body_class_filter( $classes ) {
		$page = $this->get_login_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) ) {
			$classes[] = 'pixel-page center-title pixel-signin';
		}

		$page = $this->get_register_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) ) {
			$classes[] = 'pixel-page center-title pixel-signup';
		}

		$page = $this->get_lostpassword_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) ) {
			$classes[] = 'pixel-page center-title pixel-lostpass';
		}

		$page = $this->get_dashboard_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) ) {
			$classes[] = 'pixel-dashboard';
			$classes[] = isset( $_GET['task'] ) ? $_GET['task'] : 'pixel-dash';
		}

		return $classes;
	}

	public function is_font_demo_post() {
		return ( ( is_single( get_the_ID() ) || is_page( get_the_ID() ) ) && hocwp_is_font_demo_post() );
	}

	public function post_tool_buttons( $post_id = null ) {
		if ( null == $post_id || ! is_numeric( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$download = hocwp_get_post_meta( 'download', $post_id );

		if ( empty( $download ) ) {
			$download = get_post_meta( $post_id, 'download_url', true );
		}

		$download = hocwp_sanitize_media_value( $download );
	}

	public function get_current_post_id( $post_id = null ) {
		if ( null == $post_id || ! is_numeric( $post_id ) ) {
			$post_id = get_the_ID();
		}

		return $post_id;
	}

	public function has_slider_images( $post_id = null ) {
		$post_id = $this->get_current_post_id( $post_id );

		$post_sliders = get_post_meta( $post_id, 'slider_ids', true );

		return ( ! empty( $post_sliders ) && is_array( $post_sliders ) );
	}

	public function post_class_filter( $classes ) {
		$post_id = get_the_ID();

		if ( hocwp_is_font_demo_post( $post_id ) && $this->has_slider_images( $post_id ) ) {
			$classes[] = 'has-images';
		}

		return $classes;
	}

	public function font_details_table() {
		$post_id = get_the_ID();

		$current = get_post( $post_id );

		$license = __( ' Free for Personal Use', 'hocwp-theme' );

		$l1 = get_post_meta( $post_id, 'license1', true );

		if ( 1 != $l1 ) {
			$license = __( 'Free for Commercial Use', 'hocwp-theme' );
		}

		$com_lic = get_post_meta( $post_id, 'commercial_license', true );
		$com_lic = esc_url( $com_lic );

		$author_id = $current->post_author;
		$author    = get_user_by( 'id', $author_id );

		if ( has_term( '', 'designer' ) || ! empty( $license ) || ! empty( $com_lic ) || $author instanceof WP_User ) {
			?>
			<div class="post-group fontDesigner font">
				<h2><?php _e( 'Font info', 'hocwp-theme' ); ?></h2>
				<table width="100%" cellspacing="0" cellpadding="0">
					<tbody>
					<?php
					if ( has_term( '', 'designer' ) ) {
						?>
						<tr>
							<td style="width: 200px;"><?php _e( 'Designer Name:', 'hocwp-theme' ); ?></td>
							<td><?php the_terms( get_the_ID(), 'designer' ); ?></td>
						</tr>
						<?php
					}

					?>
					<tr>
						<td style="width: 200px;"><?php _e( 'Author:', 'hocwp-theme' ); ?></td>
						<td>
							<a href="<?php echo get_author_posts_url( $author_id ); ?>"><?php echo $author->display_name; ?></a>
						</td>
					</tr>
					<?php

					if ( ! empty( $license ) ) {
						?>
						<tr>
							<td style="width: 120px;"><?php _e( 'License:', 'hocwp-theme' ); ?></td>
							<td><?php echo $license; ?></td>
						</tr>
						<?php
					}

					if ( ! empty( $com_lic ) ) {
						?>
						<tr>
							<td><?php _e( 'Commercial License:', 'hocwp-theme' ); ?></td>
							<td><a href="<?php echo $com_lic; ?>" target="_blank"><?php echo $com_lic; ?></a>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}

	public function wp_footer_action() {
		include $this->get_basedir() . '/custom/module-modal-add-collection.php';
	}

	public function after_setup_theme_action() {
		add_image_size( 'auto-slider', 800, 600, true );
	}

	public function save_post_action( $post_id ) {
		if ( ! has_post_thumbnail( $post_id ) ) {
			$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

			if ( HP()->array_has_value( $slider_ids ) ) {
				$id = array_shift( $slider_ids );
				set_post_thumbnail( $post_id, $id );
			}
		}
	}

	public function custom_post_thumbnail( $post_id, $size = array( 200, 100 ) ) {
		$slider_ids = get_post_meta( $post_id, 'slider_ids', true );
		$thumb_id   = 0;

		if ( ! empty( $slider_ids ) && is_array( $slider_ids ) ) {
			$thumb_id = current( $slider_ids );
		}

		if ( is_numeric( $thumb_id ) && 0 < $thumb_id ) {
			echo wp_get_attachment_image( $thumb_id, $size );
		} else {
			the_post_thumbnail();
		}
	}

	public function users_can_register() {
		return (bool) get_option( 'users_can_register' );
	}

	public function shortcode_lostpassword() {
		ob_start();
		include $this->get_basedir() . '/custom/module-lostpassword.php';

		return ob_get_clean();
	}

	public function shortcode_register() {
		ob_start();
		include $this->get_basedir() . '/custom/module-register.php';

		return ob_get_clean();
	}

	public function shortcode_login() {
		ob_start();
		include $this->get_basedir() . '/custom/module-login.php';

		return ob_get_clean();
	}

	public function shortcode_profile_menu() {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			ob_start();
			?>
			<div class="profile-menu">
				<ul class="profile-nav level-1 list-reset list-unstyled" role="menubar" aria-hidden="false">
					<li class="has-subnav" role="menuitem" aria-haspopup="true">
						<a href="<?php echo get_edit_profile_url(); ?>">
							<div class="user-name">
								<?php
								echo get_avatar( $user->ID );
								echo $user->display_name;
								?>
							</div>
							<!--/user-name-->
						</a>
						<ul class="level-2 list-reset" data-test="true" aria-hidden="true" role="menu">
							<li>
								<a href="<?php echo get_edit_profile_url(); ?>"><?php _e( 'Edit Profile', 'pixelify' ); ?></a>
							</li>
							<?php
							$page = $this->get_dashboard_page();

							if ( $page instanceof WP_Post ) {
								$tasks = $this->dashboard_tasks();

								$permalink = get_permalink( $page );

								foreach ( $tasks as $key => $data ) {
									if ( 'profile' == $key ) {
										continue;
									}

									$url = add_query_arg( 'task', $key, $permalink );
									?>
									<li>
										<a href="<?php echo esc_url( $url ); ?>"><?php echo $data['label']; ?></a>
									</li>
									<?php
								}
							}
							?>
							<li>
								<a href="<?php echo wp_logout_url(); ?>"><?php _e( 'Sign out', 'pixelify' ); ?></a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
			<?php
			return ob_get_clean();
		}

		return '';
	}

	public function shortcode_list_authors( $atts = array() ) {
		$atts = shortcode_atts( array(
			'orderby' => 'post_count',
			'order'   => 'desc',
			'number'  => 10
		), $atts );

		$query = new WP_User_Query( $atts );
		$users = $query->get_results();

		$result = '';

		if ( HP()->array_has_value( $users ) ) {
			?>
			<ul class="list-unstyled list-inline text-center list-authors">
				<?php
				foreach ( $users as $user ) {
					$author_id = $user->ID;
					?>
					<li>
						<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
							<?php echo get_avatar( $author_id ); ?>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
			$result = ob_get_clean();
		}

		return $result;
	}

	public function custom_user_contactmethods_filter( $methods ) {
		$methods['city_country'] = __( 'City/Country', 'pixelify' );

		return $methods;
	}

	public function custom_template_redirect_action() {
		$login = $this->get_login_page();

		if ( $login instanceof WP_Post && is_page( $login->ID ) && is_user_logged_in() ) {
			wp_redirect( get_edit_profile_url() );
			exit;
		}

		$page = $this->get_lostpassword_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) && is_user_logged_in() ) {
			wp_redirect( get_edit_profile_url() );
			exit;
		}

		$page = $this->get_register_page();

		if ( $page instanceof WP_Post && is_page( $page->ID ) && is_user_logged_in() ) {
			wp_redirect( get_edit_profile_url() );
			exit;
		}

		global $wp_query;

		$dashboard  = $this->get_dashboard_page();
		$wish_lists = $this->get_wish_lists_page();

		if ( ( $dashboard instanceof WP_Post && is_page( $dashboard->ID ) ) || ( $wish_lists instanceof WP_Post && is_page( $wish_lists->ID ) ) ) {
			if ( ! is_user_logged_in() ) {
				wp_redirect( wp_login_url( get_the_permalink() ) );
				exit;
			}
		}

		if ( isset( $wp_query->query_vars['view'] ) ) {
			$view       = get_query_var( 'view' );
			$collection = get_post( $view );

			if ( ! ( $collection instanceof WP_Post ) || 'collection' != $collection->post_type ) {
				wp_redirect( get_the_permalink() );
				exit;
			}
		} elseif ( isset( $wp_query->query_vars['edit'] ) ) {
			$edit       = get_query_var( 'edit' );
			$collection = get_post( $edit );

			if ( ! ( $collection instanceof WP_Post ) || 'collection' != $collection->post_type ) {
				wp_redirect( get_the_permalink() );
				exit;
			}
		}
	}

	public function is_collection( $post ) {
		return ( $post instanceof WP_Post && 'collection' == $post->post_type );
	}

	public function custom_post_type_link_filter( $link, $post ) {
		if ( 'collection' == $post->post_type ) {
			$page = $this->get_wish_lists_page();

			if ( $page instanceof WP_Post ) {
				$link = get_permalink( $page );
				$link = trailingslashit( $link ) . 'view/' . $post->ID;
				$link = trailingslashit( $link );
			}
		}

		return $link;
	}

	public function get_login_page() {
		return $this->get_option_post( 'login_page' );
	}

	public function get_register_page() {
		return $this->get_option_post( 'register_page' );
	}

	public function get_lostpassword_page() {
		return $this->get_option_post( 'lostpassword_page' );
	}

	public function get_dashboard_page() {
		return $this->get_option_post( 'dashboard_page' );
	}

	public function get_credit_page() {
		return $this->get_option_post( 'credit_page' );
	}

	public function get_wish_lists_page() {
		return $this->get_option_post( 'wish_lists_page' );
	}

	public function custom_get_edit_post_link_filter( $link, $post_id ) {
		$obj = get_post( $post_id );

		if ( 'collection' == $obj->post_type ) {
			$page = $this->get_wish_lists_page();

			if ( $page instanceof WP_Post ) {
				$link = get_permalink( $page );
				$link = trailingslashit( $link ) . 'edit/' . $post_id;
				$link = trailingslashit( $link );
			}
		} elseif ( 'post' == $obj->post_type ) {
			if ( ! is_admin() ) {
				$page = $this->get_dashboard_page();

				if ( $page instanceof WP_Post ) {
					$link = get_permalink( $page );
					$link = add_query_arg( array(
						'task'    => 'edit-product',
						'post_id' => $post_id
					), $link );
				}
			}
		}

		return $link;
	}

	public function is_editing_product() {
		$edit = false;

		$task = isset( $_GET['task'] ) ? $_GET['task'] : '';

		if ( 'edit-product' == $task ) {
			$edit = true;
		}

		$post_id = isset( $_GET['post_id'] ) ? $_GET['post_id'] : '';

		if ( ! is_numeric( $post_id ) ) {
			$edit = false;
		}

		return $edit;
	}

	public function get_posts_per_page() {
		return get_option( 'posts_per_page' );
	}

	public function get_paged() {
		return ( is_int( get_query_var( 'paged' ) ) ) ? get_query_var( 'paged' ) : 1;
	}

	public function upload_file( $file_name, $bits, $check_bytes = 100 ) {
		$upload = wp_upload_bits( $file_name, null, $bits );

		if ( isset( $upload['file'] ) && file_exists( $upload['file'] ) ) {
			if ( HP()->is_positive_number( $check_bytes ) ) {
				$bytes = filesize( $upload['file'] );

				if ( ! $bytes || ! is_numeric( $bytes ) || $bytes < $check_bytes ) {
					unlink( $upload['file'] );

					return $this->upload_file( $file_name, @file_get_contents( $bits ), null );
				}
			}

			$filename = basename( $file_name );

			$filetype = wp_check_filetype( $filename, null );

			$attachment = array(
				'guid'           => $upload['url'],
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $upload['file'] );

			$upload['id'] = $attach_id;

			if ( HP()->is_positive_number( $attach_id ) ) {
				if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
					load_template( ABSPATH . 'wp-admin/includes/image.php' );
				}

				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				$upload['data'] = $attach_data;

				unset( $attach_data );
			}

			unset( $filename, $attachment, $attach_id );
		}

		return $upload;
	}

	public function shortcode_wish_lists() {
		ob_start();
		include $this->get_basedir() . '/custom/module-wish-lists.php';

		return ob_get_clean();
	}

	public function shortcode_dashboard() {
		ob_start();
		include $this->get_basedir() . '/custom/module-dashboard.php';

		return ob_get_clean();
	}

	public function wp_enqueue_scripts() {

	}

	public function admin_notices() {

	}

	public function sanitize_callback( $input ) {
		if ( ! $this->check_nonce() ) {
			$input = $this->get_options();
		}

		return $input;
	}

	public function admin_init() {
		$this->add_settings_field( 'dashboard_page', __( 'Dashboard Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );

		$this->add_settings_field( 'wish_lists_page', __( 'Wish Lists Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );

		$this->add_settings_field( 'credit_page', __( 'Credit Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );

		$this->add_settings_field( 'licenses_page', __( 'License Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );

		$this->add_settings_field( 'login_page', __( 'Login Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );

		$this->add_settings_field( 'register_page', __( 'Register Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );

		$this->add_settings_field( 'lostpassword_page', __( 'Reset Password Page', 'pixelify' ), array(
			$this,
			'dashboard_page_callback'
		) );
	}

	public function dashboard_page_callback( $args ) {
		$selected = isset( $args['value'] ) ? $args['value'] : $this->get_option( $args['id'] );

		$args = array(
			'name'             => $args['name'],
			'show_option_none' => __( '-- Choose page --', 'pixelify' ),
			'selected'         => $selected
		);

		wp_dropdown_pages( $args );
	}

	public function get_sidebar() {
		include $this->get_basedir() . '/custom/module-sidebar.php';
	}

	public function dashboard_tasks() {
		$tasks = array(
			'products'         => array(
				'label'    => __( 'Your Products', 'pixelify' ),
				'callback' => array( $this, 'dashboard_task_products' )
			),
			'new-product'      => array(
				'label'    => __( 'Add New Product', 'pixelify' ),
				'callback' => array( $this, 'dashboard_task_new_product' )
			),
			'followed-artists' => array(
				'label'    => __( 'Followed Artists', 'pixelify' ),
				'callback' => array( $this, 'dashboard_task_followed_artists' )
			),
			'your-downloads'   => array(
				'label'    => __( 'Your Downloads', 'pixelify' ),
				'callback' => array( $this, 'dashboard_task_your_downloads' )
			),
			'collections'      => array(
				'label'    => __( 'Collections', 'pixelify' ),
				'callback' => array( $this, 'dashboard_task_collections' )
			),
			'profile'          => array(
				'label'    => __( 'Edit Profile', 'pixelify' ),
				'callback' => array( $this, 'dashboard_task_profile' )
			)
		);

		return $tasks;
	}

	public function dashboard_task_products() {
		include $this->get_basedir() . '/custom/module-dashboard-products.php';
	}

	public function dashboard_task_new_product() {
		include $this->get_basedir() . '/custom/module-dashboard-new-product.php';
	}

	public function dashboard_task_followed_artists() {
		include $this->get_basedir() . '/custom/module-dashboard-followed-artists.php';
	}

	public function dashboard_task_your_downloads() {
		include $this->get_basedir() . '/custom/module-dashboard-your-downloads.php';
	}

	public function dashboard_task_collections() {
		include $this->get_basedir() . '/custom/module-dashboard-collections.php';
	}

	public function dashboard_task_profile() {
		include $this->get_basedir() . '/custom/module-dashboard-profile.php';
	}

	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 */
	function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Get size information for a specific image size.
	 *
	 * @uses   get_image_sizes()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|array $size Size data about an image size or false if the size doesn't exist.
	 */
	function get_image_size( $size ) {
		$sizes = $this->get_image_sizes();

		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		}

		return false;
	}

	/**
	 * Get the width of a specific image size.
	 *
	 * @uses   get_image_size()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|string $size Width of an image size or false if the size doesn't exist.
	 */
	function get_image_width( $size ) {
		if ( ! $size = $this->get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['width'] ) ) {
			return $size['width'];
		}

		return false;
	}

	/**
	 * Get the height of a specific image size.
	 *
	 * @uses   get_image_size()
	 *
	 * @param  string $size The image size for which to retrieve data.
	 *
	 * @return bool|string $size Height of an image size or false if the size doesn't exist.
	 */
	function get_image_height( $size ) {
		if ( ! $size = $this->get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['height'] ) ) {
			return $size['height'];
		}

		return false;
	}

	public function convert_to_boolean( $value ) {
		if ( is_numeric( $value ) ) {
			if ( 0 == $value ) {
				return false;
			}

			return true;
		}
		if ( is_string( $value ) ) {
			if ( 'false' == strtolower( $value ) ) {
				return false;
			}

			return true;
		}

		return (bool) $value;
	}

	public function pagination( $args = array() ) {
		if ( $args instanceof WP_Query ) {
			$args = array( 'query' => $args );
		}

		if ( function_exists( 'hocwp_pagination' ) ) {
			hocwp_pagination( $args );

			return;
		}

		$defaults = array(
			'query'         => $GLOBALS['wp_query'],
			'dynamic_size'  => 1,
			'show_all'      => false,
			'label'         => '',
			'end_size'      => 1,
			'mid_size'      => 2,
			'first_last'    => 0,
			'current_total' => 0,
			'class'         => 'hocwp-pagination'
		);

		$args  = wp_parse_args( $args, $defaults );
		$args  = apply_filters( 'hocwp_theme_pagination_args', $args );
		$query = $args['query'];

		if ( ! ( $query instanceof WP_Query ) ) {
			return;
		}

		$total = $query->max_num_pages;

		if ( 2 > $total ) {
			return;
		}

		$big = 999999999;

		if ( isset( $args['paged'] ) && is_numeric( $args['paged'] ) ) {
			$paged = $args['paged'];
		} else {
			$paged = $this->get_paged();
		}

		$current = max( 1, $paged );

		$pla = array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => $current,
			'total'   => $total,
			'type'    => 'array'
		);

		$args = wp_parse_args( $args, $pla );
		$next = isset( $args['next'] ) ? $args['next'] : '';

		if ( empty( $next ) ) {
			$next = isset( $args['next_text'] ) ? $args['next_text'] : '';
		}

		$prev = isset( $args['prev'] ) ? $args['prev'] : '';

		if ( empty( $prev ) ) {
			$prev = isset( $args['prev_text'] ) ? $args['prev_text'] : '';
		}

		if ( ! empty( $next ) || ! empty( $prev ) ) {
			$args['prev_next'] = true;

			if ( is_string( $next ) && ! empty( $next ) ) {
				$args['next_text'] = $next;
			}

			if ( is_string( $prev ) && ! empty( $prev ) ) {
				$args['prev_text'] = $prev;
			}
		}

		if ( empty( $next ) && empty( $prev ) ) {
			$args['prev_next'] = false;
		}

		$dynamic_size = $this->convert_to_boolean( $args['dynamic_size'] );

		$first_last = isset( $args['first_last'] ) ? (bool) $args['first_last'] : false;

		if ( ! $first_last ) {
			if ( isset( $args['first'] ) && isset( $args['last'] ) ) {
				$first_last = true;
			}
		}

		if ( $dynamic_size ) {
			$show_all = $this->convert_to_boolean( $args['show_all'] );

			if ( $show_all ) {
				$count = 0;
				$label = $args['label'];

				if ( ! empty( $label ) ) {
					$count ++;
				}

				$end_size = absint( $args['end_size'] );
				$count += $end_size;
				$mid_size = absint( $args['mid_size'] );
				$count += $mid_size;
				$prev_next = $args['prev_next'];

				if ( 1 == $prev_next ) {
					$prev_text = $args['prev_text'];

					if ( ! empty( $prev_text ) ) {
						$count ++;
					}

					$next_text = $args['next_text'];

					if ( ! empty( $next_text ) ) {
						$count ++;
					}
				}

				if ( 1 == $first_last || true == $first_last ) {
					$first_text = $args['first_text'];

					if ( ! empty( $first_text ) ) {
						$count ++;
					}

					$last_text = $args['last_text'];

					if ( ! empty( $last_text ) ) {
						$count ++;
					}
				}

				$current_total = $args['current_total'];

				if ( ! empty( $current_total ) ) {
					$count ++;
				}

				if ( 1 == $paged && 11 > $count ) {
					$end_size += ( 11 - $count );
				} elseif ( 3 < $paged && 7 < $count && $paged < $total ) {
					$mid_size = 0;
				} elseif ( $paged == $total && 11 > $count ) {
					$end_size += ( 11 - $count - 1 );
				}

				$args['end_size'] = $end_size;
				$args['mid_size'] = $mid_size;
			}
		}

		$items = paginate_links( $args );

		if ( HP()->array_has_value( $items ) ) {
			$class = $args['class'];
			$class = sanitize_html_class( $class );
			$class .= ' pagination';
			$class = trim( $class );

			echo '<ul class="' . $class . '">';

			if ( isset( $args['label'] ) && ! empty( $args['label'] ) ) {
				echo '<li class="label-item page-item"><span class="page-numbers label page-link">' . $args['label'] . '</span></li>';
			}

			if ( $first_last ) {
				$first = isset( $args['first'] ) ? $args['first'] : isset( $args['first_text'] ) ? $args['first_text'] : '';

				if ( ! empty( $first ) && 2 < $current ) {
					if ( true === $first ) {
						$first = __( 'First', 'pixelify' );
					}

					$url = get_pagenum_link( 1 );
					echo '<li class="page-item"><a class="first page-numbers page-link" href="' . esc_url( $url ) . '">' . $first . '</a></li>';
				}
			}

			foreach ( $items as $item ) {
				echo '<li class="page-item">' . $item . '</li>';
			}

			if ( $first_last ) {
				$last = isset( $args['last'] ) ? $args['last'] : isset( $args['last_text'] ) ? $args['last_text'] : '';

				if ( ! empty( $last ) && $current < ( $total - 1 ) ) {
					if ( true === $last ) {
						$last = __( 'Last', 'pixelify' );
					}

					$url = get_pagenum_link( $total );
					echo '<li class="page-item"><a class="last page-numbers page-link" href="' . esc_url( $url ) . '">' . $last . '</a></li>';
				}
			}

			$current_total = isset( $args['current_total'] ) ? $args['current_total'] : false;

			if ( $current_total ) {
				if ( ! is_string( $current_total ) || ( ! $this->string_contain( $current_total, '[CURRENT]' ) && ! $this->string_contain( $current_total, '[TOTAL]' ) ) ) {
					$current_total = __( 'Page [CURRENT]/[TOTAL]', 'pixelify' );
				}

				$search = array(
					'[CURRENT]',
					'[TOTAL]'
				);

				$replace = array(
					$paged,
					$query->max_num_pages
				);

				$current_total = str_replace( $search, $replace, $current_total );
				?>
				<li class="page-item current-total">
					<a class="page-numbers page-link" href="javascript:" title=""><?php echo $current_total; ?></a>
				</li>
				<?php
			}

			echo '</ul>';
		}
	}

	public function post_buttons_tool( $obj = null, $only_view = false, $baseurl = '' ) {
		if ( ! $obj instanceof WP_Post ) {
			$obj = get_post( get_the_ID() );
		}

		if ( 'publish' == $obj->post_status ) {
			?>
			<a href="<?php the_permalink( $obj ); ?>" title="<?php _e( 'View', 'pixelify' ); ?>"
			   class="edd-fes-action view-product-fes"><?php _e( 'View', 'pixelify' ); ?></a>
			<?php
		}

		if ( $only_view ) {
			return;
		}

		if ( ( current_user_can( 'edit_published_posts', $obj->ID ) && 'publish' == $obj->post_status ) || ( current_user_can( 'edit_posts', $obj->ID ) && 'publish' != $obj->post_status ) ) {
			?>
			<a href="<?php echo get_edit_post_link( $obj->ID ); ?>" title="<?php _e( 'Edit', 'pixelify' ); ?>"
			   class="edd-fes-action edit-product-fes"><?php _e( 'Edit', 'pixelify' ); ?></a>
			<?php
		}

		if ( ( current_user_can( 'delete_published_posts', $obj->ID ) && 'publish' == $obj->post_status ) || ( current_user_can( 'delete_posts', $obj->ID ) && 'publish' != $obj->post_status ) ) {
			if ( empty( $baseurl ) ) {
				$baseurl = get_the_permalink();
			}

			$delete = add_query_arg(
				array(
					'action'  => 'delete-post',
					'post_id' => get_the_ID(),
					'nonce'   => wp_create_nonce()
				),
				$baseurl
			);
			?>
			<a href="<?php echo $delete; ?>" title="<?php _e( 'Delete', 'pixelify' ); ?>"
			   class="edd-fes-action edit-product-fes delete-post"><?php _e( 'Delete', 'pixelify' ); ?></a>
			<?php
		}
	}

	public function string_contain( $haystack, $needle, $offset = 0, $output = 'boolean' ) {
		$pos = strpos( $haystack, $needle, $offset );

		if ( false === $pos && function_exists( 'mb_strpos' ) ) {
			$pos = mb_strpos( $haystack, $needle, $offset );
		}

		if ( 'int' == $output || 'integer' == $output || 'numeric' == $output ) {
			return $pos;
		}

		return ( false !== $pos );
	}

	public function get_virtual_download_url( $post_id = null ) {
		$post_id = $this->get_current_post_id( $post_id );
		$md5     = 'downfile/';
		$md5 .= md5( get_the_title( $post_id ) );
		$md5 .= '.' . $post_id;

		return home_url( $md5 );
	}

	public function get_download_url( $post_id = null ) {
		$post_id = $this->get_current_post_id( $post_id );

		$download_url = get_post_meta( $post_id, 'download_url', true );

		if ( empty( $download_url ) ) {
			$download = get_post_meta( $post_id, 'download', true );

			if ( is_numeric( $download ) ) {
				$download_url = wp_get_attachment_url( $download );
			}
		} else {
			if ( is_array( $download_url ) ) {
				$download_url = $this->sanitize_media_value( $download_url );

				$download_url = isset( $download_url['url'] ) ? $download_url['url'] : '';
			}
		}

		return $download_url;
	}

	public function sanitize_media_value( $value ) {
		if ( function_exists( 'HT_Sanitize' ) ) {
			return HT_Sanitize()->media_value( $value );
		}

		if ( function_exists( 'hocwp_sanitize_media_value' ) ) {
			return hocwp_sanitize_media_value( $value );
		}

		return $value;
	}

	public function the_date( $format = '', $post = null, $time = true ) {
		if ( empty( $format ) ) {
			$format = get_option( 'date_format' );

			if ( $time ) {
				$format .= ' ' . get_option( 'time_format' );
			}
		}

		echo get_the_time( $format, $post );
	}

	public function file_preview_html( $att_id = '', $post_id = '', $key = 'font_demos[]' ) {
		$class = 'dz-preview dz-file-preview';
		$style = 'display: none';
		$name  = '';

		$size = '';

		if ( HP()->is_positive_number( $att_id ) ) {
			$class .= ' has-media dz-complete';
			$style = '';
			$file  = get_attached_file( $att_id );
			$name  = basename( $file );
			$size  = size_format( filesize( $file ), 2 );
		}

		if ( HP()->is_positive_number( $att_id ) && empty( $name ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="<?php echo $class; ?>" style="<?php echo $style; ?>" data-id="<?php echo $att_id; ?>"
		     data-post-id="<?php echo $post_id; ?>">
			<div class="dz-font-demo">
				<input type="hidden" class="media-id" name="<?php echo $key; ?>" value="<?php echo $att_id; ?>">
			</div>
			<div class="dz-details">
				<div class="dz-size">
					<span data-dz-size=""><?php echo $size; ?></span>
				</div>
				<div class="dz-filename">
					<span data-dz-name=""><?php echo $name; ?></span>
				</div>
			</div>
			<div class="dz-progress" style="">
				<span class="dz-upload" data-dz-uploadprogress="" style=""></span>
			</div>
			<a class="dz-remove" href="javascript:"
			   data-dz-remove=""><?php _e( 'Remove file', 'pixelify' ); ?></a>
		</div>
		<?php
		return ob_get_clean();
	}

	public function image_preview_html( $att_id = '', $post_id = '' ) {
		$class = 'dz-preview dz-image-preview';
		$style = 'display: none';
		$src   = '';

		if ( HP()->is_positive_number( $att_id ) ) {
			$class .= ' has-image';
			$style = '';
			$src   = wp_get_attachment_image_url( $att_id );
		}

		ob_start();
		?>
		<div class="<?php echo $class; ?>" style="<?php echo $style; ?>" data-id="<?php echo $att_id; ?>"
		     data-post-id="<?php echo $post_id; ?>">
			<div class="dz-image">
				<img data-dz-thumbnail="" alt="" src="<?php echo $src; ?>">
				<input type="hidden" class="media-id" name="post_images[]" value="<?php echo $att_id; ?>">
			</div>
			<div class="dz-details">
				<div class="dz-size">
					<span data-dz-size=""></span>
				</div>
				<div class="dz-filename">
					<span data-dz-name=""></span>
				</div>
			</div>
			<div class="dz-progress" style="">
				<span class="dz-upload" data-dz-uploadprogress="" style=""></span>
			</div>
			<div class="dz-error-message" style="display: none">
				<span data-dz-errormessage=""><?php _e( 'Invalid dimension.', 'pixelify' ); ?></span>
			</div>
			<div class="dz-success-mark">
			</div>
			<div class="dz-error-mark">
				<img src="<?php echo Pixelify()->get_baseurl() . '/images/icon-error.png'; ?>">
			</div>
			<a class="dz-remove" href="javascript:"
			   data-dz-remove=""><?php _e( 'Remove file', 'pixelify' ); ?></a>
		</div>
		<?php
		return ob_get_clean();
	}

	public function get_attachment_id( $url ) {
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) );

		return isset( $attachment[0] ) ? $attachment[0] : '';
	}
}

if ( ! function_exists( 'Pixelify' ) ) {
	function Pixelify() {
		return HOCWP_Pixelify::get_instance();
	}
}

add_action( 'plugins_loaded', function () {
	global $hocwp_plugin;

	if ( ! is_object( $hocwp_plugin ) ) {
		$hocwp_plugin = new stdClass();
	}

	$hocwp_plugin->pixelify = Pixelify();
	load_plugin_textdomain( $hocwp_plugin->pixelify->get_textdomain(), false, basename( dirname( $hocwp_plugin->pixelify->get_root_file_path() ) ) . '/languages/' );
} );

function hocwp_plugin_load_font_demo() {
	if ( ! is_plugin_active( 'hocwp-font-demo/hocwp-plugin-main.php' ) ) {
		require Pixelify()->get_basedir() . '/font-demo/hocwp-plugin-main.php';
	}
}

add_action( 'after_setup_theme', 'hocwp_plugin_load_font_demo', 999 );

function hocwp_pxf_on_activation_hook() {
	$role = get_role( 'subscriber' );
	$role->add_cap( 'edit_published_posts' );
	$role->add_cap( 'edit_posts' );

	$version = Pixelify()->get_version();

	if ( version_compare( $version, '1.0.2', '<=' ) ) {
		$args  = array( 'role' => 'subscriber' );
		$query = new WP_User_Query( $args );
		$users = $query->get_results();

		foreach ( $users as $user ) {
			if ( $user instanceof WP_User ) {
				$user->add_cap( 'edit_posts' );
				$user->add_cap( 'edit_published_posts' );
			}
		}
	}
}

register_activation_hook( __FILE__, 'hocwp_pxf_on_activation_hook' );

register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

class Pixelify_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'pixelify',
			'description' => 'Pixelfiy Sidebar.',
		);

		parent::__construct( 'pixelify', 'Pixelify', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		include Pixelify()->get_basedir() . '/custom/module-sidebar.php';
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

add_action( 'widgets_init', function () {
	register_widget( 'Pixelify_Widget' );
} );