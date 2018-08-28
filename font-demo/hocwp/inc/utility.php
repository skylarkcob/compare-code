<?php
function hocwp_sanitize_media_value_ajax_callback() {
	$id     = isset( $_POST['id'] ) ? $_POST['id'] : 0;
	$url    = isset( $_POST['url'] ) ? $_POST['url'] : '';
	$result = array( 'id' => $id, 'url' => $url );
	$result = hocwp_sanitize_media_value( $result );
	echo json_encode( $result );
	exit;
}

add_action( 'wp_ajax_hocwp_sanitize_media_value', 'hocwp_sanitize_media_value_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_sanitize_media_value', 'hocwp_sanitize_media_value_ajax_callback' );

function hocwp_change_captcha_image_ajax_callback() {
	$result  = array(
		'success' => false
	);
	$captcha = new HOCWP_Captcha();
	$url     = $captcha->generate_image();
	if ( ! empty( $url ) ) {
		$result['success']           = true;
		$result['captcha_image_url'] = $url;
	} else {
		$result['message'] = __( 'Sorry, cannot generate captcha image, please try again or contact administrator!', 'hocwp-theme' );
	}
	echo json_encode( $result );
	die();
}

add_action( 'wp_ajax_hocwp_change_captcha_image', 'hocwp_change_captcha_image_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_change_captcha_image', 'hocwp_change_captcha_image_ajax_callback' );

function hocwp_get_current_language() {
	if ( function_exists( 'qtranxf_getLanguage' ) ) {
		return qtranxf_getLanguage();
	}

	return hocwp_get_language();
}

function hocwp_is_post_type_archive( $post_type ) {
	if ( is_tax( get_object_taxonomies( $post_type ) ) || is_post_type_archive( $post_type ) ) {
		return true;
	}

	return false;
}

function hocwp_dashboard_widget_loading() {
	$loading = '<p class="hocwp-widget-loading widget-loading hide-if-no-js">' . __( 'Loading&#8230;', 'hocwp-theme' ) . '</p>';
	$loading .= '<p class="hide-if-js">' . __( 'This widget requires JavaScript.', 'hocwp-theme' ) . '</p>';

	return apply_filters( 'hocwp_dashboard_widget_loading', $loading );
}

function hocwp_add_bulk_action( $actions = array() ) {
	$code = 'jQuery(document).ready(function($) {';
	foreach ( $actions as $key => $text ) {
		$code .= '$(\'<option>\').val(\'' . $key . '\').text(\'' . $text . '\').appendTo("select[name=\'action\']");';
		$code .= '$(\'<option>\').val(\'' . $key . '\').text(\'' . $text . '\').appendTo("select[name=\'action2\']")';
	}
	$code .= '});';
	hocwp_inline_script( $code );
}

function hocwp_get_text_base_on_number( $single, $plural, $number ) {
	return sprintf( _n( $single, $plural, $number ), number_format_i18n( $number ) );
}

function hocwp_build_transient_name( $format, $dynamic ) {
	if ( ! is_string( $dynamic ) ) {
		$dynamic = json_encode( $dynamic );
	}
	$dynamic .= HOCWP_VERSION;
	$dynamic        = md5( $dynamic );
	$transient_name = sprintf( $format, $dynamic );
	$transient_name = apply_filters( 'hocwp_build_transient_name', $transient_name, $format, $dynamic );

	return $transient_name;
}

function hocwp_dashboard_widget_cache( $widget_id, $callback, $args = array() ) {
	$loading        = hocwp_dashboard_widget_loading();
	$locale         = get_locale();
	$transient_name = 'hocwp_dashboard_%s';
	$transient_name = hocwp_build_transient_name( $transient_name, $widget_id . '_' . $locale );
	if ( false !== ( $output = get_transient( $transient_name ) ) && ! empty( $output ) ) {
		echo $output;

		return true;
	}
	if ( ! HOCWP_DOING_AJAX ) {
		echo $loading;
	}
	if ( hocwp_callback_exists( $callback ) ) {
		ob_start();
		call_user_func( $callback, $args );
		$html_data = ob_get_clean();
		if ( ! empty( $html_data ) ) {
			set_transient( $transient_name, $html_data, 12 * HOUR_IN_SECONDS );
		}
	} else {
		echo hocwp_build_message( __( 'Please set a valid callback for this widget!', 'hocwp-theme' ), '' );
	}

	return true;
}

function hocwp_dashboard_widget_rss_cache( $args = array() ) {
	echo '<div class="rss-widget">';
	$url = '';
	if ( is_string( $args ) ) {
		$url = $args;
	} elseif ( is_array( $args ) && isset( $args['url'] ) ) {
		$url = $args['url'];
	}
	if ( ! empty( $url ) ) {
		$number    = hocwp_get_value_by_key( $args, 'number' );
		$feed_args = array( 'url' => $url );
		if ( hocwp_id_number_valid( $number ) ) {
			$feed_args['number'] = $number;
		}
		$rss = hocwp_get_feed_items( $feed_args );
		if ( is_wp_error( $rss ) ) {
			$error_code = $rss->get_error_code();
			if ( 'feed_down' === $error_code ) {
				echo '<ul><li>' . $rss->get_error_message() . '</li></ul>';
			} else {
				if ( is_admin() || current_user_can( 'manage_options' ) ) {
					echo '<p>' . sprintf( __( '<strong>RSS Error</strong>: %s' ), $rss->get_error_message() ) . '</p>';
				}
			}

			return;
		}
		if ( hocwp_array_has_value( $rss ) ) {
			echo '<ul>';
			foreach ( $rss as $item ) {
				$li = new HOCWP_HTML( 'li' );
				$a  = new HOCWP_HTML( 'a' );
				$a->set_href( $item['permalink'] );
				$a->set_text( $item['title'] );
				$a->set_attribute( 'target', '_blank' );
				$li->set_text( $a->build() );
				$li->output();
			}
			echo '</ul>';
		}
	} else {
		echo hocwp_build_message( __( 'Please set a valid feed url for this widget!', 'hocwp-theme' ), '' );
	}
	echo '</div>';
}

function hocwp_wrap_tag( $text, $tag, $class = '' ) {
	if ( empty( $text ) ) {
		return $text;
	}
	$html = new HOCWP_HTML( $tag );
	$html->set_text( $text );
	if ( ! empty( $class ) ) {
		$html->set_class( $class );
	}

	return $html->build();
}

function hocwp_fetch_feed( $args = array() ) {
	$number = absint( hocwp_get_value_by_key( $args, 'number', 5 ) );
	$offset = hocwp_get_value_by_key( $args, 'offset', 0 );
	$url    = hocwp_get_value_by_key( $args, 'url' );
	if ( empty( $url ) ) {
		return '';
	}
	if ( ! function_exists( 'fetch_feed' ) ) {
		include_once( ABSPATH . WPINC . '/feed.php' );
	}
	$rss = fetch_feed( $url );
	if ( ! is_wp_error( $rss ) ) {
		if ( ! $rss->get_item_quantity() ) {
			$error = new WP_Error( 'feed_down', __( 'An error has occurred, which probably means the feed is down. Try again later.' ) );
			$rss->__destruct();
			unset( $rss );

			return $error;
		}
		$max    = $rss->get_item_quantity( $number );
		$result = $rss->get_items( $offset, $max );

	} else {
		$result = $rss;
	}

	return $result;
}

function hocwp_sanitize_bookmark_link_image( $bookmarks ) {
	if ( ! is_array( $bookmarks ) ) {
		return $bookmarks;
	}
	foreach ( $bookmarks as $bookmark ) {
		$thumbnail = hocwp_get_link_meta( $bookmark->link_id, 'thumbnail' );
		$thumbnail = hocwp_sanitize_media_value( $thumbnail );
		$thumbnail = $thumbnail['url'];
		if ( ! empty( $thumbnail ) ) {
			$bookmark->link_image = $thumbnail;
		}
	}

	return $bookmarks;
}

function hocwp_dashboard_widget_script() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			var $hocwp_widget_loading = $('.index-php div.inside:visible .hocwp-widget-loading'),
				ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
			$hocwp_widget_loading.each(function (i, el) {
				var $element = $(el),
					$post_box = $element.closest('.postbox'),
					$parent = $element.parent(),
					widget_id = '';
				if ($post_box.length) {
					widget_id = $post_box.attr('id');
				}
				if ($.trim(widget_id)) {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: ajax_url,
						cache: true,
						data: {
							action: 'hocwp_dashboard_widget',
							widget: widget_id
						},
						success: function (response) {
							if (response.html_data) {
								$parent.html('');
								$parent.hide().slideDown();
								$parent.html(response.html_data);
							}
						}
					});
				}
			});
		});
	</script>
	<?php
}

function hocwp_get_feed_items( $args = array() ) {
	$url = hocwp_get_value_by_key( $args, 'url' );
	if ( empty( $url ) ) {
		return '';
	}
	$number         = hocwp_get_value_by_key( $args, 'number' );
	$expiration     = hocwp_get_value_by_key( $args, 'expiration', 12 * HOUR_IN_SECONDS );
	$transient_name = 'hocwp_fetch_feed_%s';
	$transient_name = hocwp_build_transient_name( $transient_name, $args );
	if ( hocwp_id_number_valid( $number ) ) {
		$transient_name .= '_' . $number;
	}
	if ( false === ( $result = get_transient( $transient_name ) ) ) {
		$items = hocwp_fetch_feed( $args );
		if ( hocwp_array_has_value( $items ) ) {
			$result = array();
			foreach ( $items as $item ) {
				if ( ! hocwp_object_valid( $item ) ) {
					continue;
				}
				$description = $item->get_description();
				$thumbnail   = hocwp_get_first_image_source( $description );
				$description = wp_strip_all_tags( $description );
				$content     = $item->get_content();
				if ( empty( $thumbnail ) ) {
					$thumbnail = hocwp_get_first_image_source( $content );
				}
				$value = array(
					'permalink'   => $item->get_permalink(),
					'title'       => $item->get_title(),
					'date'        => $item->get_date(),
					'image_url'   => $thumbnail,
					'description' => $description,
					'content'     => $content
				);
				array_push( $result, $value );
			}
			if ( hocwp_array_has_value( $result ) ) {
				set_transient( $transient_name, $result, $expiration );
			}
		} else {
			return $items;
		}
	}

	return $result;
}

function hocwp_show_float_ads() {
	?>
	<div class="<?php hocwp_wrap_class( 'float-ads' ); ?>">
		<div class="pull-left">
			<?php hocwp_show_ads( 'float_left' ); ?>
		</div>
		<div class="pull-right">
			<?php hocwp_show_ads( 'float_right' ); ?>
		</div>
	</div>
	<?php
}

function hocwp_rest_api_get( $base_url, $object = 'posts', $query = '' ) {
	$base_url = trailingslashit( $base_url ) . 'wp-json/wp/v2/' . $object;
	if ( ! empty( $query ) ) {
		$base_url .= '?' . $query;
	}
	$data = @file_get_contents( $base_url );
	if ( ! empty( $data ) ) {
		$data = json_decode( $data );
	}

	return $data;
}

function hocwp_read_xml( $xml, $is_url = false ) {
	if ( $is_url ) {
		$transient_name = 'hocwp_read_xml_%s';
		$transient_name = hocwp_build_transient_name( $transient_name, $xml );
		if ( false === ( $saved = get_transient( $transient_name ) ) ) {
			$saved = @file_get_contents( $xml );
			set_transient( $transient_name, $saved, HOUR_IN_SECONDS );
		}
		$xml = $saved;
	}
	$object = new SimpleXMLElement( $xml );

	return $object;
}

function hocwp_build_message( $message, $type = 'info' ) {
	$p = new HOCWP_HTML( 'p' );
	if ( ! empty( $type ) ) {
		$p->set_class( 'text-left alert alert-' . $type );
	}
	$p->set_text( $message );

	return $p->build();
}

function hocwp_generate_reset_key() {
	return wp_generate_password( 20, false );
}

function hocwp_generate_verify_link( $key ) {
	$url = home_url( '/' );
	$url = add_query_arg( array( 'key' => $key, 'action' => 'verify_subscription' ), $url );
	$a   = new HOCWP_HTML( 'a' );
	$a->set_href( $url );
	$a->set_text( $url );
	$a->set_attribute( 'target', '_blank' );

	return $a->build();
}

function hocwp_generate_unsubscribe_link( $email, $text = '' ) {
	if ( ! is_email( $email ) ) {
		return '';
	}
	$query = hocwp_get_post_by_meta( 'subscriber_email', $email, array( 'post_type' => 'hocwp_subscriber' ) );
	$key   = '';
	$post  = null;
	if ( $query->have_posts() ) {
		$post = array_shift( $query->posts );
		$key  = hocwp_get_post_meta( 'subscriber_deactivate_key', $post->ID );
	}
	if ( empty( $key ) ) {
		$key = hocwp_generate_reset_key();
		if ( is_a( $post, 'WP_Post' ) ) {
			update_post_meta( $post->ID, 'subscriber_deactivate_key', $key );
		}
	}
	$url = home_url( '/' );
	$url = add_query_arg( array( 'key' => $key, 'action' => 'unsubscribe', 'email' => $email ), $url );
	$a   = new HOCWP_HTML( 'a' );
	$a->set_href( $url );
	if ( empty( $text ) ) {
		$text = $url;
	}
	$a->set_text( $text );
	$a->set_attribute( 'target', '_blank' );

	return $a->build();
}

function hocwp_mail_unsubscribe_link_footer( $message, $email ) {
	$unsubscribe_link = hocwp_generate_unsubscribe_link( $email, 'Click here to unsubscribe' );
	if ( ! empty( $unsubscribe_link ) ) {
		$message .= '<table cellpadding="0" cellspacing="0" border="0" style="margin-top: 20px;">';
		$message .= '<tbody><tr><td style="';
		$message .= 'font-family:Helvetica,arial,sans-serif;font-size:11px;color:#000000;text-align:center;line-height:15px;font-weight:500;font-style:italic';
		$message .= '">This email was sent to <a target="_blank" href="';
		$message .= $email . '">' . $email . '</a>. ';
		$message .= $unsubscribe_link . '.';
		$message .= '</td></tr></tbody></table>';
	}

	return $message;
}

function hocwp_loading_image( $args = array() ) {
	$name  = hocwp_get_value_by_key( $args, 'name', 'icon-loading-circle-16.gif' );
	$class = hocwp_get_value_by_key( $args, 'class' );
	hocwp_add_string_with_space_before( $class, 'img-loading' );
	$alt       = hocwp_get_value_by_key( $args, 'alt' );
	$display   = hocwp_get_value_by_key( $args, 'display', 'none' );
	$style     = 'display: ' . $display;
	$img       = new HOCWP_HTML( 'img' );
	$image_url = hocwp_get_image_url( $name );
	$img->set_image_alt( $alt );
	$img->set_class( $class );
	$img->set_attribute( 'style', $style );
	$img->set_image_src( $image_url );
	$img->output();
}

function hocwp_get_allowed_image_mime_types() {
	$types  = get_allowed_mime_types();
	$result = array();
	foreach ( $types as $key => $text ) {
		if ( false !== strpos( $text, 'image' ) ) {
			$result[ $key ] = $text;
		}
	}

	return $result;
}

function hocwp_auto_reload_script( $delay = 2000 ) {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			var time = new Date().getTime();

			function refresh() {
				if (new Date().getTime() - time >= <?php echo $delay; ?>) {
					location.reload();
				} else {
					setTimeout(refresh, 1000);
				}
			}

			setTimeout(refresh, 1000);
		});
	</script>
	<?php
}

function hocwp_get_sidebar_info( $post ) {
	$post_id    = $post->ID;
	$active     = (bool) hocwp_get_post_meta( 'active', $post_id );
	$sidebar_id = hocwp_get_post_meta( 'sidebar_id', $post_id );
	$default    = (bool) hocwp_get_post_meta( 'sidebar_default', $post_id );
	if ( empty( $sidebar_id ) ) {
		$sidebar_id = $post->post_name;
	}
	$sidebar_name = hocwp_get_value_by_key( 'sidebar_name', $post_id );
	if ( empty( $sidebar_name ) ) {
		$sidebar_name = $post->post_title;
	}
	if ( hocwp_qtranslate_x_installed() ) {
		$sidebar_name = apply_filters( 'translate_text', $sidebar_name, $lang = null, $flags = 0 );
	}
	$sidebar_description = hocwp_get_post_meta( 'sidebar_description', $post_id );
	$sidebar_tag         = hocwp_get_post_meta( 'sidebar_tag', $post_id );
	if ( empty( $sidebar_tag ) ) {
		$sidebar_tag = 'div';
	}
	$args = array(
		'id'          => hocwp_sanitize_id( $sidebar_id ),
		'name'        => strip_tags( $sidebar_name ),
		'description' => strip_tags( $sidebar_description ),
		'tag'         => strtolower( $sidebar_tag ),
		'active'      => $active,
		'default'     => $default
	);

	return $args;
}

function hocwp_get_post_class( $post_id = null, $class = '' ) {
	if ( ! hocwp_id_number_valid( $post_id ) ) {
		$post_id = get_the_ID();
	}

	return join( ' ', get_post_class( $class, $post_id ) );
}

function hocwp_use_comment_form_captcha() {
	$use = get_option( 'hocwp_discussion' );
	$use = hocwp_get_value_by_key( $use, 'use_captcha' );
	$use = apply_filters( 'hocwp_use_comment_form_captcha', $use );

	return (bool) $use;
}

function hocwp_user_not_use_comment_form_captcha() {
	$use = get_option( 'hocwp_discussion' );
	$use = hocwp_get_value_by_key( $use, 'user_no_captcha', 1 );
	$use = apply_filters( 'hocwp_user_not_use_comment_form_captcha', $use );

	return (bool) $use;
}

function hocwp_use_comment_form_captcha_custom_position() {
	return apply_filters( 'hocwp_use_comment_form_captcha_custom_position', false );
}

function hocwp_build_license_transient_name( $type, $use_for ) {
	$name = 'hocwp_' . $type . '_' . $use_for . '_license_valid';

	return hocwp_build_transient_name( 'hocwp_check_license_%s', $name );
}

function hocwp_replace_text_placeholder( $text ) {
	remove_filter( 'hocwp_replace_text_placeholder', 'hocwp_replace_text_placeholder' );
	$text = apply_filters( 'hocwp_replace_text_placeholder', $text );
	add_filter( 'hocwp_replace_text_placeholder', 'hocwp_replace_text_placeholder' );
	$text_placeholders   = array(
		'%DOMAIN%',
		'%CURRENT_YEAR%',
		'%PAGED%',
		'%HOME_URL%',
		'%SITE_NAME%'
	);
	$text_placeholders   = apply_filters( 'hocwp_text_placeholders', $text_placeholders );
	$placeholder_replace = array(
		hocwp_get_domain_name( home_url() ),
		date( 'Y' ),
		hocwp_get_paged(),
		home_url( '/' ),
		get_bloginfo( 'name' )
	);
	$placeholder_replace = apply_filters( 'hocwp_text_placeholders_replace', $placeholder_replace );
	$text                = str_replace( $text_placeholders, $placeholder_replace, $text );

	return $text;
}

function hocwp_redirect_home() {
	wp_redirect( home_url( '/' ) );
	exit;
}

function hocwp_widget_item_full_width_result( $full_width_value, $total_item_count, $loop_count ) {
	$full_width = false;
	$loop_count = absint( $loop_count );
	$loop_count ++;
	switch ( $full_width_value ) {
		case 'all':
			$full_width = true;
			break;
		case 'first':
			if ( 0 == $loop_count ) {
				$full_width = true;
			}
			break;
		case 'last':
			if ( $loop_count == $total_item_count ) {
				$full_width = true;
			}
			break;
		case 'first_last':
			if ( 0 == $loop_count || $loop_count == $total_item_count ) {
				$full_width = true;
			}
			break;
		case 'odd':
			if ( ( $loop_count % 2 ) != 0 ) {
				$full_width = true;
			}
			break;
		case 'even':
			if ( ( $loop_count % 2 ) == 0 ) {
				$full_width = true;
			}
			break;
	}

	return $full_width;
}

function hocwp_the_social_list( $args = array() ) {
	$option_socials = hocwp_option_defaults();
	$option_socials = $option_socials['social'];
	$order          = hocwp_get_value_by_key( $args, 'order', hocwp_get_value_by_key( $option_socials, 'order' ) );
	$orders         = explode( ',', $order );
	$orders         = array_map( 'trim', $orders );
	$orders         = hocwp_sanitize_array( $orders );
	$option_names   = $option_socials['option_names'];
	$options        = hocwp_get_option( 'option_social' );
	$icons          = $option_socials['icons'];
	$list           = (bool) hocwp_get_value_by_key( $args, 'list' );
	if ( hocwp_array_has_value( $orders ) ) {
		if ( $list ) {
			echo '<ul class="list-socials list-unstyled list-inline">';
			foreach ( $orders as $social ) {
				$option_name = hocwp_get_value_by_key( $option_names, $social );
				$item        = hocwp_get_value_by_key( $options, $option_name );
				if ( ! empty( $item ) ) {
					$icon = '<i class="fa ' . $icons[ $social ] . '"></i>';
					$a    = new HOCWP_HTML( 'a' );
					$a->set_href( $item );
					$a->set_class( 'social-item link-' . $social );
					$a->set_text( $icon );
					$li = new HOCWP_HTML( 'li' );
					$li->set_text( $a );
					$li->output();
				}
			}
			echo '</ul>';
		} else {
			foreach ( $orders as $social ) {
				$option_name = hocwp_get_value_by_key( $option_names, $social );
				$item        = hocwp_get_value_by_key( $options, $option_name );
				if ( ! empty( $item ) ) {
					$icon = '<i class="fa ' . $icons[ $social ] . '"></i>';
					$a    = new HOCWP_HTML( 'a' );
					$a->set_href( $item );
					$a->set_class( 'social-item link-' . $social );
					$a->set_text( $icon );
					$a->output();
				}
			}
		}
	}
}

function hocwp_in_maintenance_mode() {
	$option = get_option( 'hocwp_maintenance' );
	$result = hocwp_get_value_by_key( $option, 'enabled' );
	$result = (bool) $result;
	$result = apply_filters( 'hocwp_enable_maintenance_mode', $result );
	if ( hocwp_maintenance_mode_exclude_condition() || hocwp_is_login_page() ) {
		$result = false;
	}

	return $result;
}

function hocwp_in_maintenance_mode_notice() {
	if ( hocwp_in_maintenance_mode() ) {
		$page = hocwp_get_current_admin_page();
		if ( 'hocwp_maintenance' != $page ) {
			$args = array(
				'text' => sprintf( __( 'Your site is running in maintenance mode, so you can go to %s and turn it off when done.', 'hocwp-theme' ), '<a href="' . admin_url( 'tools.php?page=hocwp_maintenance' ) . '">' . __( 'setting page', 'hocwp-theme' ) . '</a>' )
			);
			hocwp_admin_notice( $args );
		}
	}
}

function hocwp_get_table_prefix() {
	global $wpdb;
	if ( is_multisite() ) {
		return $wpdb->base_prefix;
	} else {
		return $wpdb->get_blog_prefix( 0 );
	}
}

function hocwp_get_curl_version() {
	if ( function_exists( 'curl_version' ) && function_exists( 'curl_exec' ) ) {
		$cv  = curl_version();
		$cvs = $cv['version'] . ' / SSL: ' . $cv['ssl_version'] . ' / libz: ' . $cv['libz_version'];
	} else {
		$cvs = __( 'Not installed', 'hocwp-theme' ) . ' (' . __( 'required for some remote storage providers', 'hocwp-theme' ) . ')';
	}

	return htmlspecialchars( $cvs );
}

function hocwp_maintenance_mode_exclude_condition() {
	$condition = hocwp_is_admin();

	return apply_filters( 'hocwp_maintenance_mode_exclude_condition', $condition );
}

function hocwp_get_views_template( $slug, $name = '' ) {
	$template = $slug;
	$template = str_replace( '.php', '', $template );
	if ( ! empty( $name ) ) {
		$name = str_replace( '.php', '', $name );
		$template .= '-' . $name;
	}
	$template .= '.php';
	$template = HOCWP_PATH . '/views/' . $template;
	if ( file_exists( $template ) ) {
		include( $template );
	}
}

function hocwp_use_jquery_cdn( $value = null ) {
	if ( null == $value ) {
		$option = hocwp_get_optimize_option();
		$use    = hocwp_get_value_by_key( $option, 'use_jquery_cdn', 1 );
		$value  = (bool) $use;
	}
	$value = apply_filters( 'hocwp_use_jquery_google_cdn', $value );

	return $value;
}

function hocwp_load_jquery_from_cdn() {
	if ( ! is_admin() ) {
		$use = hocwp_use_jquery_cdn();
		if ( $use ) {
			global $wp_version, $wp_scripts;
			$handle   = ( version_compare( $wp_version, '3.6-alpha1', '>=' ) ) ? 'jquery-core' : 'jquery';
			$enqueued = wp_script_is( $handle );
			wp_enqueue_script( $handle );
			$version           = '';
			$jquery_url        = '';
			$google_not_exists = array(
				'1.12.3'
			);
			if ( is_a( $wp_scripts, 'WP_Scripts' ) ) {
				$registered = $wp_scripts->registered;
				if ( isset( $registered[ $handle ] ) ) {
					$version = $registered[ $handle ]->ver;
					if ( in_array( $version, $google_not_exists ) ) {
						$jquery_url = '//code.jquery.com/jquery-' . $version . '.min.js';
					}
				}
			}
			if ( empty( $version ) ) {
				$version = HOCWP_JQUERY_LATEST_VERSION;
			}
			if ( empty( $jquery_url ) ) {
				$jquery_url = '//ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery.min.js';
			}
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
			wp_register_script( $handle, $jquery_url );
			if ( $enqueued ) {
				wp_enqueue_script( $handle );
				add_action( 'hocwp_before_wp_head', 'hocwp_jquery_google_cdn_fallback' );
			}
		}
	}
}

function hocwp_jquery_google_cdn_fallback() {
	echo '<script>window.jQuery || document.write(\'<script src="' . includes_url( 'js/jquery/jquery.js' ) . '"><\/script>\')</script>' . "\n";
}

function hocwp_plugin_wpsupercache_installed() {
	return function_exists( 'wpsupercache_activate' );
}

function hocwp_plugins_api( $action, $args = array() ) {
	if ( ! function_exists( 'plugins_api' ) ) {
		require( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

	return plugins_api( $action, $args );
}

function hocwp_plugins_api_get_information( $args = array() ) {
	$slug = hocwp_get_value_by_key( $args, 'slug' );
	if ( empty( $slug ) ) {
		return new WP_Error( 'missing_slug', __( 'Please set slug for this plugin.', 'hocwp-theme' ) );
	}
	$transient_name = 'hocwp_plugins_api_%s_plugin_information';
	$transient_name = hocwp_build_transient_name( $transient_name, $args );
	if ( false === ( $data = get_transient( $transient_name ) ) ) {
		$defaults = array(
			'fields' => array(
				'short_description' => true,
				'screenshots'       => false,
				'changelog'         => false,
				'installation'      => false,
				'description'       => false,
				'sections'          => false,
				'tags'              => false,
				'icons'             => true,
				'active_installs'   => true,
				'versions'          => true
			)
		);
		$args     = wp_parse_args( $args, $defaults );
		$data     = hocwp_plugins_api( 'plugin_information', $args );
		set_transient( $transient_name, $data, MONTH_IN_SECONDS );
	}

	return $data;
}

function hocwp_plugin_install_status( $plugin ) {
	if ( ! function_exists( 'install_plugin_install_status' ) ) {
		require( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

	return install_plugin_install_status( $plugin );
}

function hocwp_setup_loop_data( $args ) {
	global $hocwp_loop_data;
	$hocwp_loop_data = $args;

	return $hocwp_loop_data;
}

function hocwp_get_loop_data() {
	global $hocwp_loop_data;

	return $hocwp_loop_data;
}

function hocwp_get_plugin_icon_url( $plugin ) {
	if ( is_object( $plugin ) ) {
		$plugin = (array) $plugin;
	}
	if ( ! empty( $plugin['icons']['svg'] ) ) {
		$plugin_icon_url = $plugin['icons']['svg'];
	} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
		$plugin_icon_url = $plugin['icons']['2x'];
	} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
		$plugin_icon_url = $plugin['icons']['1x'];
	} else {
		$plugin_icon_url = hocwp_get_value_by_key( $plugin, array( 'icons', 'default' ) );
	}
	if ( empty( $plugin_icon_url ) ) {
		$plugin_icon_url = hocwp_plugin_random_icon();
	}

	return $plugin_icon_url;
}

function hocwp_get_image_url( $name ) {
	return HOCWP_URL . '/images/' . $name;
}

function hocwp_sanitize_first_and_last_name( $name ) {
	$result = array(
		'first_name' => $name,
		'last_name'  => $name
	);
	if ( false !== strpos( $name, ' ' ) ) {
		$parts = explode( ' ', $name );
		if ( 'vi' == hocwp_get_language() ) {
			$first_name = array_pop( $parts );
		} else {
			$first_name = array_shift( $parts );
		}
		$last_name            = implode( ' ', $parts );
		$result['first_name'] = $first_name;
		$result['last_name']  = $last_name;
	}
	$result = apply_filters( 'hocwp_sanitize_first_and_last_name', $result, $name );

	return $result;
}

function hocwp_get_rich_text( $text ) {
	return do_shortcode( wpautop( $text ) );
}

function hocwp_widget_title( $args, $instance, $echo = true ) {
	if ( ! isset( $instance['title'] ) ) {
		$instance['title'] = '';
	}
	$id_base      = hocwp_get_value_by_key( $args, 'id_base' );
	$title        = apply_filters( 'widget_title', $instance['title'], $instance, $id_base );
	$before_title = hocwp_get_value_by_key( $args, 'before_title' );
	$after_title  = hocwp_get_value_by_key( $args, 'after_title' );
	if ( ! empty( $title ) ) {
		$title = $before_title . $title . $after_title;
	}
	$title = apply_filters( 'hocwp_widget_title_html', $title, $args, $instance, $id_base );
	if ( (bool) $echo ) {
		echo $title;
	}

	return $title;
}

function hocwp_checkbox_post_data_value( $data, $key, $deprecated = null ) {
	if ( $deprecated ) {
		_deprecated_argument( __FUNCTION__, '3.4.5' );
	}

	return ( isset( $data[ $key ] ) && 0 != $data[ $key ] ) ? 1 : 0;
}

function hocwp_change_nav_menu_css_class( $terms, $classes, $item ) {
	if ( hocwp_array_has_value( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( $term->term_id == $item->object_id ) {
				$classes[] = 'current-menu-item';
				break;
			}
		}
	}

	return $classes;
}

function hocwp_remove_wpseo_breadcrumb_xmlns( $output ) {
	$output = str_replace( ' xmlns:v="http://rdf.data-vocabulary.org/#"', '', $output );

	return $output;
}

function hocwp_widget_before( $args, $instance, $show_title = true ) {
	if ( isset( $args['before_widget'] ) ) {
		echo $args['before_widget'];
	}
	if ( $show_title ) {
		hocwp_widget_title( $args, $instance );
	}
	echo '<div class="widget-content">';
}

function hocwp_widget_after( $args, $instance ) {
	echo '</div>';
	if ( isset( $args['after_widget'] ) ) {
		echo $args['after_widget'];
	}
}

function hocwp_get_installed_plugins( $folder = '' ) {
	return hocwp_get_plugins( $folder );
}

function hocwp_get_plugin_slug_from_file_path( $file ) {
	if ( 'hello.php' == $file ) {
		$file = 'hello-dolly';
	}
	$slug = explode( '/', $file );
	$slug = current( $slug );

	return $slug;
}

function hocwp_loop_plugin_card( $plugin, $allow_tags = array(), $base_name = '' ) {
	$is_local = false;
	if ( is_object( $plugin ) ) {
		$plugin = (array) $plugin;
	}
	$title = wp_kses( hocwp_get_value_by_key( $plugin, 'name' ), $allow_tags );
	if ( empty( $title ) ) {
		$is_local = true;
	}
	$description = strip_tags( hocwp_get_value_by_key( $plugin, 'short_description' ) );
	$version     = wp_kses( hocwp_get_value_by_key( $plugin, 'version' ), $allow_tags );
	$name        = strip_tags( $title . ' ' . $version );
	$author      = wp_kses( hocwp_get_value_by_key( $plugin, 'author' ), $allow_tags );
	if ( ! empty( $author ) ) {
		$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
	}
	$action_links = array();
	if ( ! $is_local && ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) ) {
		$status = hocwp_plugin_install_status( $plugin );
		switch ( $status['status'] ) {
			case 'install':
				if ( $status['url'] ) {
					$action_links[] = '<a class="install-now button" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Install Now' ) . '</a>';
				}
				break;
			case 'update_available':
				if ( $status['url'] ) {
					$action_links[] = '<a class="update-now button" data-plugin="' . esc_attr( $status['file'] ) . '" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Update Now' ) . '</a>';
				}
				break;
			case 'latest_installed':
			case 'newer_installed':
				$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( 'This plugin is already installed and is up to date' ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
				break;
		}
	}
	$details_link           = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . hocwp_get_value_by_key( $plugin, 'slug' ) . '&amp;TB_iframe=true&amp;width=600&amp;height=550' );
	$action_links[]         = '<a target="_blank" href="' . esc_url( $details_link ) . '" class="thickbox" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';
	$plugin_icon_url        = hocwp_get_plugin_icon_url( $plugin );
	$action_links           = apply_filters( 'plugin_install_action_links', $action_links, $plugin );
	$date_format            = __( 'M j, Y @ H:i' );
	$last_updated_timestamp = strtotime( hocwp_get_value_by_key( $plugin, 'last_updated' ) );
	if ( empty( $title ) && ! empty( $base_name ) ) {
		$local_plugin = hocwp_get_plugin_info( $base_name );
		$title        = wp_kses( $local_plugin['Name'], $allow_tags );
		$description  = strip_tags( $local_plugin['Description'] );
		$description  = str_replace( ' By HocWP.', '', $description );
		$action_links = array();
		//$version = wp_kses($local_plugin['Version'], $allow_tags);
		//$name = strip_tags($title . ' ' . $version);
		$author = wp_kses( $local_plugin['Author'], $allow_tags );
		if ( ! empty( $author ) ) {
			$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
		}
	}
	if ( empty( $title ) ) {
		return;
	}
	?>
	<div
		class="plugin-card plugin-card-<?php echo sanitize_html_class( hocwp_get_value_by_key( $plugin, 'slug' ) ); ?>">
		<div class="plugin-card-top">
			<div class="name column-name">
				<h3>
					<a target="_blank" href="<?php echo esc_url( $details_link ); ?>" class="thickbox">
						<?php echo $title; ?>
						<img src="<?php echo esc_attr( $plugin_icon_url ) ?>" class="plugin-icon" alt="">
					</a>
				</h3>
			</div>
			<div class="action-links">
				<?php
				if ( $action_links ) {
					echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
				}
				?>
			</div>
			<div class="desc column-description">
				<p><?php echo $description; ?></p>

				<p class="authors"><?php echo $author; ?></p>
			</div>
		</div>
		<div class="plugin-card-bottom">
			<?php if ( ! $is_local ) : ?>
				<div class="vers column-rating">
					<?php wp_star_rating( array(
						'rating' => $plugin['rating'],
						'type'   => 'percent',
						'number' => $plugin['num_ratings']
					) ); ?>
					<span class="num-ratings">(<?php echo number_format_i18n( $plugin['num_ratings'] ); ?>)</span>
				</div>
				<div class="column-updated">
					<strong><?php _e( 'Last Updated:' ); ?></strong> <span
						title="<?php echo esc_attr( date_i18n( $date_format, $last_updated_timestamp ) ); ?>">
						<?php printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) ); ?>
					</span>
				</div>
				<div class="column-downloaded">
					<?php
					if ( $plugin['active_installs'] >= 1000000 ) {
						$active_installs_text = _x( '1+ Million', 'Active plugin installs' );
					} else {
						$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
					}
					printf( __( '%s Active Installs' ), $active_installs_text );
					?>
				</div>
				<div class="column-compatibility">
					<?php
					if ( ! empty( $plugin['tested'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['tested'] ) ), $plugin['tested'], '>' ) ) {
						echo '<span class="compatibility-untested">' . __( 'Untested with your version of WordPress' ) . '</span>';
					} elseif ( ! empty( $plugin['requires'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['requires'] ) ), $plugin['requires'], '<' ) ) {
						echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress' ) . '</span>';
					} else {
						echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress' ) . '</span>';
					}
					?>
				</div>
			<?php else : ?>
				<p><?php _e( 'This is a local plugin so there is no stats for it.', 'hocwp-theme' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

function hocwp_facebook_like_button( $args = array() ) {
	$post_id   = isset( $args['post_id'] ) ? $args['post_id'] : get_the_ID();
	$permalink = hocwp_get_value_by_key( $args, 'permalink', get_permalink( $post_id ) );
	if ( empty( $permalink ) ) {
		$permalink = home_url( '/' );
	}
	$class = isset( $args['class'] ) ? $args['class'] : '';
	hocwp_add_string_with_space_before( $class, 'fb-like' );
	$layout     = isset( $args['layout'] ) ? $args['layout'] : 'button_count';
	$action     = isset( $args['action'] ) ? $args['action'] : 'like';
	$show_faces = isset( $args['show_faces'] ) ? $args['show_faces'] : false;
	$show_faces = hocwp_bool_to_string( $show_faces );
	$share      = isset( $args['share'] ) ? $args['share'] : true;
	$share      = hocwp_bool_to_string( $share );
	?>
	<div class="<?php echo $class; ?>" data-href="<?php echo esc_url( $permalink ); ?>"
	     data-layout="<?php echo $layout; ?>"
	     data-action="<?php echo $action; ?>" data-show-faces="<?php echo $show_faces; ?>"
	     data-share="<?php echo $share; ?>"></div>
	<?php
}

function hocwp_google_plus_one_button( $args = array() ) {
	$post_id    = hocwp_get_value_by_key( $args, 'post_id', get_the_ID() );
	$permalink  = hocwp_get_value_by_key( $args, 'permalink', get_permalink( $post_id ) );
	$size       = hocwp_get_value_by_key( $args, 'size', 'medium' );
	$annotation = hocwp_get_value_by_key( $args, 'annotation', 'bubble' );
	$width      = hocwp_get_value_by_key( $args, 'width', 300 );
	$language   = hocwp_get_value_by_key( $args, 'language', hocwp_get_language() );
	if ( empty( $permalink ) ) {
		$permalink = home_url( '/' );
	}
	?>
	<!-- Place this tag where you want the +1 button to render. -->
	<div class="g-plusone" data-width="<?php echo $width; ?>" data-annotation="<?php echo $annotation; ?>"
	     data-size="<?php echo $size; ?>"
	     data-href="<?php echo esc_url( $permalink ); ?>"></div>

	<!-- Place this tag after the last +1 button tag. -->
	<script type="text/javascript">
		window.___gcfg = {lang: '<?php echo $language; ?>'};

		(function () {
			var po = document.createElement('script');
			po.type = 'text/javascript';
			po.async = true;
			po.src = 'https://apis.google.com/js/platform.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(po, s);
		})();
	</script>
	<?php
}

function hocwp_twitter_follow_button( $args = array() ) {
	$username = hocwp_get_value_by_key( $args, 'username' );
	if ( empty( $username ) ) {
		$username = hocwp_get_value_by_key( $args, 'account' );
	}
	$permalink = hocwp_get_value_by_key( $args, 'permalink' );
	if ( empty( $permalink ) ) {
		$permalink = $username;
	}
	if ( ! empty( $permalink ) && ! hocwp_is_url( $permalink ) ) {
		$permalink = 'https://twitter.com/' . $permalink;
	}
	if ( empty( $permalink ) ) {
		$permalink = hocwp_get_option_by_name( 'hocwp_option_social', 'twitter_site' );
	}
	if ( empty( $permalink ) ) {
		return;
	}
	$show_screen_name = hocwp_get_value_by_key( $args, 'show_screen_name' );
	$show_count       = hocwp_get_value_by_key( $args, 'show_count' );
	$show_count       = hocwp_bool_to_string( $show_count );
	if ( empty( $username ) ) {
		$username = hocwp_get_last_part_in_url( $permalink );
	}
	if ( ! empty( $username ) ) {
		$first_char = hocwp_get_first_char( $username );
		if ( '@' != $first_char ) {
			$username = '@' . $username;
		}
	}
	$text = __( 'Follow', 'hocwp-theme' );
	if ( $show_screen_name ) {
		$username = $text . ' ' . $username;
	} else {
		$username = $text;
	}
	$username         = trim( $username );
	$size             = hocwp_get_value_by_key( $args, 'size' );
	$show_screen_name = hocwp_bool_to_string( $show_screen_name );
	?>
	<a data-show-screen-name="<?php echo $show_screen_name; ?>" data-size="<?php echo $size; ?>"
	   href="<?php echo esc_url( $permalink ); ?>" class="twitter-follow-button"
	   data-show-count="<?php echo $show_count; ?>"><?php echo $username; ?></a>
	<script type="text/javascript">
		window.twttr = (function (d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0],
				t = window.twttr || {};
			if (d.getElementById(id)) return t;
			js = d.createElement(s);
			js.id = id;
			js.src = "https://platform.twitter.com/widgets.js";
			fjs.parentNode.insertBefore(js, fjs);

			t._e = [];
			t.ready = function (f) {
				t._e.push(f);
			};

			return t;
		}(document, "script", "twitter-wjs"));
	</script>
	<?php
}

function hocwp_facebook_like_and_recommend_button( $args = array() ) {
	$url = isset( $args['url'] ) ? $args['url'] : '';
	if ( empty( $url ) ) {
		$url = get_permalink();
	}
	$app_id = hocwp_get_wpseo_social_facebook_app_id();
	?>
	<div class="fb-like-buttons like-recommend like-recommend-buttons">
		<div class="item">
			<div data-share="false" data-show-faces="false" data-action="like" data-layout="button_count"
			     data-href="<?php echo $url; ?>" class="fb-like fb_iframe_widget" fb-xfbml-state="rendered"
			     fb-iframe-plugin-query="action=like&amp;app_id=<?php echo $app_id; ?>&amp;container_width=0&amp;href=<?php echo $url; ?>&amp;layout=button_count&amp;locale=en_US&amp;sdk=joey&amp;share=false&amp;show_faces=false"></div>
		</div>
		<div class="item">
			<div data-share="true" data-show-faces="false" data-action="recommend" data-layout="button_count"
			     data-href="<?php echo $url; ?>" class="fb-like fb_iframe_widget" fb-xfbml-state="rendered"
			     fb-iframe-plugin-query="action=recommend&amp;app_id=<?php echo $app_id; ?>&amp;container_width=0&amp;href=<?php echo $url; ?>&amp;layout=button_count&amp;locale=en_US&amp;sdk=joey&amp;share=false&amp;show_faces=false"></div>
		</div>
	</div>
	<?php
}

function hocwp_facebook_share_and_like_buttons( $args = array() ) {
	$url = isset( $args['url'] ) ? $args['url'] : '';
	if ( empty( $url ) ) {
		$url = get_permalink();
	}
	$layout     = isset( $args['layout'] ) ? $args['layout'] : 'button_count';
	$action     = isset( $args['action'] ) ? $args['action'] : 'like';
	$show_faces = isset( $args['show_faces'] ) ? $args['show_faces'] : false;
	$show_faces = hocwp_bool_to_string( $show_faces );
	$share      = isset( $args['share'] ) ? $args['share'] : true;
	$share      = hocwp_bool_to_string( $share );
	?>
	<div class="fb-like-buttons like-share">
		<div class="item">
			<div class="fb-like" data-href="<?php echo $url; ?>" data-layout="<?php echo $layout; ?>"
			     data-action="<?php echo $action; ?>" data-show-faces="<?php echo $show_faces; ?>"
			     data-share="<?php echo $share; ?>"></div>
		</div>
	</div>
	<?php
}

function hocwp_plugin_random_icon() {
	return hocwp_random_image_data();
}

function hocwp_newsletter_time_range() {
	$range = apply_filters( 'hocwp_newsletter_time_range', array( 17, 21 ) );
	if ( ! is_array( $range ) || count( $range ) != 2 ) {
		$range = array( 17, 21 );
	}

	return $range;
}

function hocwp_prevent_author_see_another_post() {
	$use = false;
	$use = apply_filters( 'hocwp_prevent_author_see_another_post', $use );

	return $use;
}

function hocwp_delete_old_file( $path, $interval ) {
	$files = scandir( $path );
	$now   = time();
	foreach ( $files as $file ) {
		$file = trailingslashit( $path ) . $file;
		if ( is_file( $file ) ) {
			$file_time = filemtime( $file );
			if ( ( $now - $file_time ) >= $interval ) {
				chmod( $file, 0777 );
				@unlink( $file );
			}
		}
	}
}

function hocwp_is_table_exists( $table_name ) {
	global $wpdb;
	if ( ! hocwp_string_contain( $table_name, $wpdb->prefix ) ) {
		$table_name = $wpdb->prefix . $table_name;
	}
	$result = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" );
	if ( empty( $result ) ) {
		return false;
	}

	return true;
}

function hocwp_get_url_params( $url = null ) {
	$params = array();
	if ( empty( $url ) ) {
		$url = $_SERVER['REQUEST_URI'];
	}
	$current_url = basename( $url );
	if ( ! empty( $current_url ) ) {
		$parts = explode( '&', $current_url );
		foreach ( $parts as $part ) {
			$p = explode( '=', $part );
			if ( isset( $p[0] ) && ! empty( $p[0] ) ) {
				$param = $p[0];
				$param = trim( $param, '?' );
				if ( false !== strpos( $param, '?' ) ) {
					$tmp   = explode( '?', $param );
					$param = array_pop( $tmp );
				}
				$params[ $param ] = isset( $p[1] ) ? $p[1] : '';
			}
		}
	}

	return $params;
}

function hocwp_form_hidden_params( $params = null, $skip_params = array() ) {
	if ( ! is_array( $params ) ) {
		$params = hocwp_get_url_params();
	}
	if ( is_array( $params ) ) {
		foreach ( $params as $key => $value ) {
			if ( in_array( $key, $skip_params ) ) {
				continue;
			}
			?>
			<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
			<?php
		}
	}
}

function hocwp_star_ratings( $post_id = null ) {
	if ( function_exists( 'kk_star_ratings' ) ) {
		$post_id = hocwp_return_post( $post_id, 'id' );
		echo kk_star_ratings( $post_id );
	}
}

function hocwp_star_rating_result( $args = array() ) {
	if ( ! function_exists( 'wp_star_rating' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/template.php' );
	}
	$votes = 0;
	if ( ! isset( $args['rating'] ) ) {
		$id    = hocwp_get_value_by_key( $args, 'post_id', get_the_ID() );
		$score = get_post_meta( $id, '_kksr_ratings', true );
		if ( ! hocwp_is_positive_number( $score ) ) {
			$score = 0;
		}
		if ( ! isset( $args['number'] ) ) {
			$votes = get_post_meta( $id, '_kksr_casts', true );
			if ( ! hocwp_is_positive_number( $votes ) ) {
				$votes = 0;
			}
		}
		$args['number'] = $votes;
		if ( $votes != 0 ) {
			$avg   = (float) ( $score / $votes );
			$score = $score ? round( $avg, 2 ) : 0;
		}
		$args['rating'] = $score;
	}
	$hide_empty = hocwp_get_value_by_key( $args, 'hide_empty' );
	if ( $hide_empty ) {
		$rating = $args['rating'];
		if ( ! hocwp_is_positive_number( $rating ) ) {
			return;
		}
	}
	$before = hocwp_get_value_by_key( $args, 'before' );
	echo $before;
	wp_star_rating( $args );
	$show_count = hocwp_get_value_by_key( $args, 'show_count', true );
	$number     = hocwp_get_value_by_key( $args, 'number' );
	$number     = absint( $number );
	if ( $show_count ) {
		echo '<span aria-hidden="true" class="num-ratings">(' . $number . ')</span>';
	}
	$after = hocwp_get_value_by_key( $args, 'after' );
	echo $after;
}

function hocwp_bootstrap_color_select_options() {
	$options = array(
		'default' => __( 'Default', 'hocwp-theme' ),
		'primary' => __( 'Primary', 'hocwp-theme' ),
		'success' => __( 'Success', 'hocwp-theme' ),
		'info'    => __( 'Info', 'hocwp-theme' ),
		'warning' => __( 'Warning', 'hocwp-theme' ),
		'danger'  => __( 'Danger', 'hocwp-theme' )
	);
	$options = apply_filters( 'hocwp_bootstrap_color_select_options', $options );

	return $options;
}

function hocwp_newsletter_plugin_installed() {
	if ( class_exists( 'Newsletter' ) || class_exists( 'NewsletterModule' ) ) {
		return true;
	}

	return false;
}

function hocwp_add_to_newsletter_list( $args = array() ) {
	if ( hocwp_newsletter_plugin_installed() ) {
		global $newsletter;
		if ( ! isset( $newsletter ) ) {
			$newsletter = new Newsletter();
		}
		if ( isset( $newsletter->options['api_key'] ) && ! empty( $newsletter->options['api_key'] ) ) {
			$api_key = hocwp_get_value_by_key( $args, 'api_key' );
			if ( empty( $api_key ) ) {
				$api_key = $newsletter->options['api_key'];
			}
			$email = hocwp_get_value_by_key( $args, 'email' );
			if ( is_email( $email ) ) {
				$base_url     = NEWSLETTER_URL . '/api/add.php';
				$params       = array(
					'ne' => $email,
					'nk' => $api_key
				);
				$name         = hocwp_get_value_by_key( $args, 'name' );
				$surname      = hocwp_get_method_value( $args, 'surname' );
				$params['nn'] = $name;
				$params['ns'] = $surname;
				$base_url     = add_query_arg( $params, $base_url );
				$result       = @file_get_contents( $base_url );
			}
		}
	}
}

function hocwp_use_core_style() {
	return apply_filters( 'hocwp_use_core_style', true );
}

function hocwp_use_superfish_menu() {
	return apply_filters( 'hocwp_use_superfish_menu', true );
}

function hocwp_maintenance_mode_settings() {
	$defaults = hocwp_maintenance_mode_default_settings();
	$args     = get_option( 'hocwp_maintenance' );
	$args     = wp_parse_args( $args, $defaults );

	return apply_filters( 'hocwp_maintenance_mode_settings', $args );
}

function hocwp_google_login_script( $args = array() ) {
	$connect = hocwp_get_value_by_key( $args, 'connect' );
	if ( is_user_logged_in() && ! $connect ) {
		return;
	}
	$clientid = hocwp_get_value_by_key( $args, 'clientid', hocwp_get_google_client_id() );
	if ( empty( $clientid ) ) {
		hocwp_debug_log( __( 'Please set your Google Client ID first.', 'hocwp-theme' ) );

		return;
	}
	?>
	<script type="text/javascript">
		function hocwp_google_login() {
			var params = {
				clientid: '<?php echo $clientid; ?>',
				cookiepolicy: 'single_host_origin',
				callback: 'hocwp_google_login_on_signin',
				scope: 'email',
				theme: 'dark'
			};
			gapi.auth.signIn(params);
		}
		function hocwp_google_login_on_signin(response) {
			if (response['status']['signed_in'] && !response['_aa']) {
				gapi.client.load('plus', 'v1', hocwp_google_login_client_loaded);
			}
		}
		function hocwp_google_login_client_loaded(response) {
			var request = gapi.client.plus.people.get({userId: 'me'});
			request.execute(function (response) {
				hocwp_google_login_connected_callback(response);
			});
		}
		function hocwp_google_logout() {
			gapi.auth.signOut();
			location.reload();
		}
		function hocwp_google_login_connected_callback(response) {
			(function ($) {
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: hocwp.ajax_url,
					cache: true,
					data: {
						action: 'hocwp_social_login_google',
						data: JSON.stringify(response),
						connect: <?php echo hocwp_bool_to_int($connect); ?>
					},
					success: function (response) {
						var href = window.location.href;
						if ($.trim(response.redirect_to)) {
							href = response.redirect_to;
						}
						if (response.logged_in) {
							window.location.href = href;
						}
					}
				});
			})(jQuery);
		}
	</script>
	<?php
}

function hocwp_facebook_login_script( $args = array() ) {
	$connect = hocwp_get_value_by_key( $args, 'connect' );
	if ( is_user_logged_in() && ! $connect ) {
		return;
	}
	$lang     = hocwp_get_language();
	$language = hocwp_get_value_by_key( $args, 'language' );
	if ( empty( $language ) && 'vi' === $lang ) {
		$language = 'vi_VN';
	}
	$app_id = hocwp_get_wpseo_social_facebook_app_id();
	if ( empty( $app_id ) ) {
		hocwp_debug_log( __( 'Please set your Facebook APP ID first.', 'hocwp-theme' ) );

		return;
	}
	?>
	<script type="text/javascript">
		window.hocwp = window.hocwp || {};
		function hocwp_facebook_login_status_callback(response) {
			if (response.status === 'connected') {
				hocwp_facebook_login_connected_callback();
			} else if (response.status === 'not_authorized') {

			} else {

			}
		}
		function hocwp_facebook_login() {
			FB.login(function (response) {
				hocwp_facebook_login_status_callback(response);
			}, {scope: 'email,public_profile,user_friends'});
		}
		window.fbAsyncInit = function () {
			FB.init({
				appId: '<?php echo $app_id; ?>',
				cookie: true,
				xfbml: true,
				version: 'v<?php echo HOCWP_FACEBOOK_GRAPH_API_VERSION; ?>'
			});
		};
		if (typeof FB === 'undefined') {
			(function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s);
				js.id = id;
				js.src = "//connect.facebook.net/<?php echo $language; ?>/sdk.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		}
		function hocwp_facebook_login_connected_callback() {
			FB.api('/me', {fields: 'id,name,first_name,last_name,picture,verified,email'}, function (response) {
				(function ($) {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: hocwp.ajax_url,
						cache: true,
						data: {
							action: 'hocwp_social_login_facebook',
							data: JSON.stringify(response),
							connect: <?php echo hocwp_bool_to_int($connect); ?>
						},
						success: function (response) {
							var href = window.location.href;
							if ($.trim(response.redirect_to)) {
								href = response.redirect_to;
							}
							if (response.logged_in) {
								window.location.href = href;
							}
						}
					});
				})(jQuery);
			});
		}
	</script>
	<?php
}

function hocwp_is_bots() {
	if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'] ) ) {
		return true;
	}

	return false;
}

function hocwp_get_default_lat_long() {
	$lat_long = array(
		'lat' => '37.42200662799378',
		'lng' => '-122.08403290000001'
	);
	$data     = get_option( 'hocwp_geo' );
	$lat      = hocwp_get_value_by_key( $data, 'default_lat' );
	$lng      = hocwp_get_value_by_key( $data, 'default_lng' );
	if ( ! empty( $lat ) && ! empty( $lng ) ) {
		$lat_long['lat'] = $lat;
		$lat_long['lng'] = $lng;
	} else {
		if ( 'vi' == hocwp_get_language() ) {
			$lat_long['lat'] = '21.003118';
			$lat_long['lng'] = '105.820141';
		}
	}

	return apply_filters( 'hocwp_default_lat_lng', $lat_long );
}

function hocwp_register_taxonomy_filter( $post_type = '' ) {
	$args = array(
		'name'          => __( 'Filters', 'hocwp-theme' ),
		'singular_name' => __( 'Filter', 'hocwp-theme' ),
		'taxonomy'      => 'hocwp_filter',
		'slug'          => 'filter',
		'post_types'    => $post_type
	);
	hocwp_register_taxonomy( $args );
}

function hocwp_register_post_type_partner() {
	$args = array(
		'name'          => __( 'Partners', 'hocwp-theme' ),
		'singular_name' => __( 'Partner', 'hocwp-theme' ),
		'slug'          => 'partner'
	);
	hocwp_register_post_type( $args );
}

function hocwp_register_post_type_album() {
	$args = array(
		'name'          => __( 'Albums', 'hocwp-theme' ),
		'singular_name' => __( 'Album', 'hocwp-theme' ),
		'slug'          => 'album',
		'supports'      => array( 'thumbnail', 'editor' )
	);
	hocwp_register_post_type( $args );
}

function hocwp_register_post_type_news( $args = array() ) {
	$lang = hocwp_get_language();
	$slug = 'news';
	if ( 'vi' == $lang ) {
		$slug = 'tin-tuc';
	}
	$slug     = apply_filters( 'hocwp_post_type_news_base_slug', $slug );
	$slug     = apply_filters( 'hocwp_post_type_news_slug', $slug );
	$defaults = array(
		'name'              => __( 'News', 'hocwp-theme' ),
		'slug'              => $slug,
		'post_type'         => 'news',
		'show_in_admin_bar' => true,
		'supports'          => array( 'editor', 'thumbnail', 'comments' )
	);
	$args     = wp_parse_args( $args, $defaults );
	hocwp_register_post_type( $args );
	$slug = 'news-cat';
	if ( 'vi' == $lang ) {
		$slug = 'chuyen-muc';
	}
	$slug = apply_filters( 'hocwp_taxonomy_news_category_base_slug', $slug );
	$args = array(
		'name'          => __( 'News Categories', 'hocwp-theme' ),
		'singular_name' => __( 'News Category', 'hocwp-theme' ),
		'post_types'    => 'news',
		'menu_name'     => __( 'Categories', 'hocwp-theme' ),
		'slug'          => $slug,
		'taxonomy'      => 'news_cat'
	);
	hocwp_register_taxonomy( $args );
	$news_tag = apply_filters( 'hocwp_post_type_news_tag', false );
	if ( $news_tag ) {
		$slug = 'news-tag';
		if ( 'vi' == $lang ) {
			$slug = 'the';
		}
		$slug = apply_filters( 'hocwp_taxonomy_news_tag_base_slug', $slug );
		$args = array(
			'name'          => __( 'News Tags', 'hocwp-theme' ),
			'singular_name' => __( 'News Tag', 'hocwp-theme' ),
			'post_types'    => 'news',
			'menu_name'     => __( 'Tags', 'hocwp-theme' ),
			'slug'          => $slug,
			'hierarchical'  => false,
			'taxonomy'      => 'news_tag'
		);
		hocwp_register_taxonomy( $args );
	}
}

function hocwp_register_post_type_mega_menu( $args = array() ) {
	$defaults = array(
		'name'          => __( 'Mega Menus', 'hocwp-theme' ),
		'singular_name' => __( 'Mega Menu', 'hocwp-theme' ),
		'slug'          => 'hocwp_mega_menu'
	);
	$args     = wp_parse_args( $defaults, $args );
	hocwp_register_post_type_private( $args );
}

function hocwp_register_lib_google_maps( $api_key = null ) {
	if ( empty( $api_key ) ) {
		$options = get_option( 'hocwp_option_social' );
		$api_key = hocwp_get_value_by_key( $options, 'google_api_key' );
	}
	if ( empty( $api_key ) ) {
		return;
	}
	wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key, array(), false, true );
}

function hocwp_register_lib_tinymce() {
	wp_enqueue_script( 'tinymce', '//cdn.tinymce.com/' . HOCWP_TINYMCE_VERSION . '/tinymce.min.js', array(), false, true );
}

function hocwp_inline_css( $elements, $properties ) {
	$css = hocwp_build_css_rule( $elements, $properties );
	if ( ! empty( $css ) ) {
		$style = new HOCWP_HTML( 'style' );
		$style->set_attribute( 'type', 'text/css' );
		$css = hocwp_minify_css( $css );
		$style->set_text( $css );
		if ( ! empty( $css ) ) {
			$style->output();
		}
	}
}

function hocwp_inline_script( $code ) {
	$script = new HOCWP_HTML( 'script' );
	$script->set_attribute( 'type', 'text/javascript' );
	$script->set_text( $code );
	$script->output();
}

function hocwp_favorite_post_button_text( $args = array() ) {
	if ( ! is_array( $args ) ) {
		$post_id = $args;
	} else {
		$post_id = hocwp_get_value_by_key( $args, 'post_id' );
	}
	$save_text = hocwp_get_value_by_key( $args, 'save_text', '' );
	if ( empty( $save_text ) ) {
		$save_text = __( 'Favorite', 'hocwp-theme' );
	}
	$unsave_text = hocwp_get_value_by_key( $args, 'unsave_text' );
	if ( empty( $unsave_text ) ) {
		$unsave_text = __( 'Favorited', 'hocwp-theme' );
	}
	if ( ! hocwp_id_number_valid( $post_id ) ) {
		$post_id = get_the_ID();
	}
	$text = '<i class="fa fa-heart-o"></i> ' . $save_text;
	if ( is_user_logged_in() ) {
		$user     = wp_get_current_user();
		$favorite = hocwp_get_user_favorite_posts( $user->ID );
		if ( in_array( $post_id, $favorite ) ) {
			$text = '<i class="fa fa-heart"></i> ' . $unsave_text;
		}
	}
	$text = apply_filters( 'hocwp_favorite_post_button_text', $text, $args );
	$echo = hocwp_get_value_by_key( $args, 'echo', true );
	if ( $echo ) {
		echo $text;
	}

	return $text;
}

function hocwp_save_post_button_text( $args = array() ) {
	if ( ! is_array( $args ) ) {
		$post_id = $args;
	} else {
		$post_id = hocwp_get_value_by_key( $args, 'post_id' );
	}
	$save_text = hocwp_get_value_by_key( $args, 'save_text', '' );
	if ( empty( $save_text ) ) {
		$save_text = __( 'Save', 'hocwp-theme' );
	}
	$unsave_text = hocwp_get_value_by_key( $args, 'unsave_text' );
	if ( empty( $unsave_text ) ) {
		$unsave_text = __( 'Saved', 'hocwp-theme' );
	}
	if ( ! hocwp_id_number_valid( $post_id ) ) {
		$post_id = get_the_ID();
	}
	$text  = '<i class="fa fa-heart-o"></i> ' . $save_text;
	$saved = hocwp_get_value_by_key( $args, 'saved' );
	if ( (bool) $saved ) {
		$text = '<i class="fa fa-heart"></i> ' . $unsave_text;
	}
	$text = apply_filters( 'hocwp_save_post_button_text', $text, $args );
	$echo = hocwp_get_value_by_key( $args, 'echo', true );
	if ( $echo ) {
		echo $text;
	}

	return $text;
}

function hocwp_get_geo_code( $args = array() ) {
	if ( ! is_array( $args ) && ! empty( $args ) ) {
		$args = array(
			'address' => $args
		);
	}
	$options  = get_option( 'hocwp_option_social' );
	$api_key  = hocwp_get_value_by_key( $options, 'google_api_key' );
	$defaults = array(
		'sensor' => false,
		'region' => 'Vietnam',
		'key'    => $api_key
	);
	$args     = wp_parse_args( $args, $defaults );
	$address  = hocwp_get_value_by_key( $args, 'address' );
	if ( empty( $address ) ) {
		return '';
	}
	$address         = str_replace( ' ', '+', $address );
	$args['address'] = $address;
	$transient_name  = 'hocwp_geo_code_%s';
	$transient_name  = hocwp_build_transient_name( $transient_name, $args );
	if ( false === ( $results = get_transient( $transient_name ) ) ) {
		$base    = 'https://maps.googleapis.com/maps/api/geocode/json';
		$base    = add_query_arg( $args, $base );
		$json    = @file_get_contents( $base );
		$results = json_decode( $json );
		if ( 'OK' === $results->status ) {
			set_transient( $transient_name, $results, MONTH_IN_SECONDS );
		}
	}

	return $results;
}

function hocwp_generate_min_file( $file, $extension = 'js', $compress_min_file = false, $force_compress = false ) {
	$transient_name = 'hocwp_minified_%s';
	$transient_name = hocwp_build_transient_name( $transient_name, $file );
	if ( false === get_transient( $transient_name ) || $force_compress ) {
		if ( file_exists( $file ) ) {
			$extension = strtolower( $extension );
			if ( 'js' === $extension ) {
				$minified = hocwp_minify_js( $file );
			} else {
				$minified = hocwp_minify_css( $file, true );
			}
			if ( ! empty( $minified ) ) {
				if ( $compress_min_file ) {
					if ( ! file_exists( $file ) ) {
						$handler = fopen( $file, 'w' );
						fwrite( $handler, $minified );
						fclose( $handler );
					} else {
						@file_put_contents( $file, $minified );
					}
				} else {
					$info      = pathinfo( $file );
					$basename  = $info['basename'];
					$filename  = $info['filename'];
					$extension = $info['extension'];
					$min_name  = $filename;
					$min_name .= '.min';
					if ( ! empty( $extension ) ) {
						$min_name .= '.' . $extension;
					}
					$min_file = str_replace( $basename, $min_name, $file );
					$handler  = fopen( $min_file, 'w' );
					fwrite( $handler, $minified );
					fclose( $handler );
				}
				set_transient( $transient_name, 1, 15 * MINUTE_IN_SECONDS );
				hocwp_debug_log( sprintf( __( 'File %s is compressed successfully!', 'hocwp-theme' ), $file ) );
			}
		}
	}
}

function hocwp_compress_style( $dir, $compress_min_file = false, $force_compress = false ) {
	$files     = scandir( $dir );
	$my_files  = array();
	$min_files = array();
	foreach ( $files as $file ) {
		$info = pathinfo( $file );
		if ( isset( $info['extension'] ) && 'css' == $info['extension'] ) {
			$base_name = $info['basename'];
			if ( false !== strpos( $base_name, '.min' ) ) {
				if ( $compress_min_file ) {
					$min_files[] = trailingslashit( $dir ) . $file;
				}
				continue;
			}
			$my_files[] = trailingslashit( $dir ) . $file;
		}
	}
	if ( hocwp_array_has_value( $min_files ) || $compress_min_file ) {
		foreach ( $min_files as $file ) {
			hocwp_generate_min_file( $file, 'css', true, $force_compress );
		}

		return;
	}
	if ( hocwp_array_has_value( $my_files ) ) {
		foreach ( $my_files as $file ) {
			hocwp_generate_min_file( $file, 'css', false, $force_compress );
		}
	}
}

function hocwp_compress_script( $dir, $compress_min_file = false, $force_compress = false ) {
	$files     = scandir( $dir );
	$my_files  = array();
	$min_files = array();
	foreach ( $files as $file ) {
		$info = pathinfo( $file );
		if ( isset( $info['extension'] ) && 'js' == $info['extension'] ) {
			$base_name = $info['basename'];
			if ( false !== strpos( $base_name, '.min' ) ) {
				if ( $compress_min_file ) {
					$min_files[] = trailingslashit( $dir ) . $file;
				}
				continue;
			}
			$my_files[] = trailingslashit( $dir ) . $file;
		}
	}
	if ( hocwp_array_has_value( $min_files ) || $compress_min_file ) {
		foreach ( $min_files as $file ) {
			hocwp_generate_min_file( $file, 'js', true, $force_compress );
		}

		return;
	}
	if ( hocwp_array_has_value( $my_files ) ) {
		foreach ( $my_files as $file ) {
			hocwp_generate_min_file( $file, 'js', false, $force_compress );
		}
	}
}

function hocwp_theme_copy_style_to_min_file() {
	if ( ! defined( 'HOCWP_THEME_PATH' ) ) {
		return;
	}
	$hocwp_css_path = HOCWP_THEME_PATH . '/css';
	$min_file       = $hocwp_css_path . '/hocwp-custom-front-end.min.css';
	if ( ! file_exists( $min_file ) ) {
		hocwp_create_file( $min_file );
	}
	$old_content = @file_get_contents( $min_file );
	$old_content = trim( $old_content );
	if ( empty( $old_content ) ) {
		$tmp_file = $hocwp_css_path . '/hocwp-custom-front-end.css';
		if ( file_exists( $tmp_file ) ) {
			$old_content = @file_get_contents( $tmp_file );
			$old_content = hocwp_minify_css( $old_content );
			$old_content = trim( $old_content );
		}
	}
	if ( ! empty( $old_content ) ) {
		$temp_file = HOCWP_PATH . '/css/hocwp-front-end.min.css';
		if ( file_exists( $temp_file ) ) {
			$temp_content = @file_get_contents( $temp_file );
			$temp_content = trim( $temp_content );
			$old_content  = $temp_content . $old_content;
		}
		$temp_file = HOCWP_PATH . '/css/hocwp.min.css';
		if ( file_exists( $temp_file ) ) {
			$temp_content = @file_get_contents( $temp_file );
			$temp_content = trim( $temp_content );
			$old_content  = $temp_content . $old_content;
		}
		$temp_file = HOCWP_THEME_PATH . '/css/hocwp-custom-font.min.css';
		if ( file_exists( $temp_file ) ) {
			$temp_content = @file_get_contents( $temp_file );
			$temp_content = trim( $temp_content );
			$old_content  = $temp_content . $old_content;
		}
		$old_content = trim( $old_content );
	}
	@file_put_contents( $min_file, $old_content );
}

function hocwp_theme_copy_script_to_min_file() {
	if ( ! defined( 'HOCWP_THEME_PATH' ) ) {
		return;
	}
	$hocwp_js_path = HOCWP_THEME_PATH . '/js';
	$min_file      = $hocwp_js_path . '/hocwp-custom-front-end.min.js';
	if ( ! file_exists( $min_file ) ) {
		hocwp_create_file( $min_file );
	}
	$old_content = @file_get_contents( $min_file );
	$old_content = trim( $old_content );
	if ( empty( $old_content ) ) {
		$tmp_file = $hocwp_js_path . '/hocwp-custom-front-end.js';
		if ( file_exists( $tmp_file ) ) {
			$old_content = @file_get_contents( $tmp_file );
			$old_content = hocwp_minify_js( $old_content );
			$old_content = trim( $old_content );
		}
	}
	if ( ! empty( $old_content ) ) {
		$temp_file = HOCWP_PATH . '/js/hocwp-front-end.min.js';
		if ( file_exists( $temp_file ) ) {
			$temp_content = @file_get_contents( $temp_file );
			$temp_content = trim( $temp_content );
			$old_content  = $temp_content . $old_content;
		}
		$temp_file = HOCWP_PATH . '/js/hocwp.min.js';
		if ( file_exists( $temp_file ) ) {
			$temp_content = @file_get_contents( $temp_file );
			$temp_content = trim( $temp_content );
			$old_content  = $temp_content . $old_content;
		}
		$old_content = trim( $old_content );
	}
	@file_put_contents( $min_file, $old_content );
}

function hocwp_compress_style_and_script( $args = array() ) {
	$type           = hocwp_get_value_by_key( $args, 'type' );
	$force_compress = hocwp_get_value_by_key( $args, 'force_compress' );
	$compress_core  = hocwp_get_value_by_key( $args, 'compress_core' );
	$recompress     = false;
	if ( hocwp_array_has_value( $type ) ) {
		$compress_css = false;
		if ( in_array( 'css', $type ) ) {
			$compress_css = true;
			if ( $compress_core ) {
				$hocwp_css_path = HOCWP_PATH . '/css';
				hocwp_compress_style( $hocwp_css_path, false, $force_compress );
			}
			if ( defined( 'HOCWP_THEME_VERSION' ) ) {
				$hocwp_css_path = HOCWP_THEME_PATH . '/css';
				hocwp_compress_style( $hocwp_css_path, false, $force_compress );
				hocwp_theme_copy_style_to_min_file();
			}
		}
		$compress_js = false;
		if ( in_array( 'js', $type ) ) {
			$compress_js = true;
			if ( $compress_core ) {
				$hocwp_js_path = HOCWP_PATH . '/js';
				hocwp_compress_script( $hocwp_js_path, false, $force_compress );
			}
			if ( defined( 'HOCWP_THEME_VERSION' ) ) {
				$hocwp_js_path = HOCWP_THEME_PATH . '/js';
				hocwp_compress_script( $hocwp_js_path, false, $force_compress );
				hocwp_theme_copy_script_to_min_file();
			}
		}
		if ( $compress_css || $compress_js ) {
			unset( $type['recompress'] );
		}
		if ( in_array( 'recompress', $type ) ) {
			if ( defined( 'HOCWP_THEME_VERSION' ) ) {
				$hocwp_js_path = HOCWP_THEME_PATH . '/js';
				hocwp_compress_script( $hocwp_js_path, true, $force_compress );
				$hocwp_css_path = HOCWP_THEME_PATH . '/css';
				hocwp_compress_style( $hocwp_css_path, true, $force_compress );
			}
		}
		$compress_paths = apply_filters( 'hocwp_compress_paths', array() );
		foreach ( $compress_paths as $path ) {
			$css_path     = trailingslashit( $path ) . 'css';
			$js_path      = trailingslashit( $path ) . 'js';
			$compress_css = false;
			if ( in_array( 'css', $type ) ) {
				$compress_css = true;
				hocwp_compress_style( $css_path, false, $force_compress );
			}
			$compress_js = false;
			if ( in_array( 'js', $type ) ) {
				$compress_js = true;
				hocwp_compress_script( $js_path, false, $force_compress );
			}
			if ( $compress_css || $compress_js ) {
				unset( $type['recompress'] );
			}
			if ( in_array( 'recompress', $type ) ) {
				hocwp_compress_script( $js_path, true, $force_compress );
				hocwp_compress_style( $css_path, true, $force_compress );
			}
		}
	}
}

function hocwp_php_thumb() {

}

function hocwp_post_rating_ajax_callback() {
	$result  = array(
		'success' => false
	);
	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;
	if ( hocwp_id_number_valid( $post_id ) ) {
		$score = isset( $_POST['score'] ) ? $_POST['score'] : 0;
		if ( is_numeric( $score ) && $score > 0 ) {
			$number      = isset( $_POST['number'] ) ? $_POST['number'] : 5;
			$number_max  = isset( $_POST['number_max'] ) ? $_POST['number_max'] : 5;
			$high_number = $number;
			if ( $number > $number_max ) {
				$high_number = $number_max;
			}
			$ratings_score = floatval( get_post_meta( $post_id, 'ratings_score', true ) );
			$ratings_score += $score;
			$ratings_users = absint( get_post_meta( $post_id, 'ratings_users', true ) );
			$ratings_users ++;
			$high_ratings_users = absint( get_post_meta( $post_id, 'high_ratings_users', true ) );
			if ( $score == $high_number ) {
				$high_ratings_users ++;
				update_post_meta( $post_id, 'high_ratings_users', $high_ratings_users );
			}
			$ratings_average = $score;
			update_post_meta( $post_id, 'ratings_users', $ratings_users );
			update_post_meta( $post_id, 'ratings_score', $ratings_score );
			if ( $ratings_users > 0 ) {
				$ratings_average = $ratings_score / $ratings_users;
			}
			update_post_meta( $post_id, 'ratings_average', $ratings_average );
			$result['success']        = true;
			$result['score']          = $ratings_average;
			$session_key              = 'hocwp_post_' . $post_id . '_rated';
			$_SESSION[ $session_key ] = 1;
			do_action( 'hocwp_post_rated', $score, $post_id );
		}
	}

	return $result;
}

function hocwp_change_url( $new_url, $old_url = '', $force_update = false ) {
	$transient_name = 'hocwp_update_data_after_url_changed_%s';
	$transient_name = hocwp_build_transient_name( $transient_name, '' );
	$site_url       = trailingslashit( get_bloginfo( 'url' ) );
	if ( ! empty( $old_url ) ) {
		$old_url = trailingslashit( $old_url );
		if ( $old_url != $site_url && ! $force_update ) {
			return;
		}
	} else {
		$old_url = $site_url;
	}
	$new_url = trailingslashit( $new_url );
	if ( $old_url == $new_url && ! $force_update ) {
		return;
	}
	if ( false === get_transient( $transient_name ) || $force_update ) {
		global $wpdb;
		$wpdb->query( "UPDATE $wpdb->options SET option_value = replace(option_value, '$old_url', '$new_url') WHERE option_name = 'home' OR option_name = 'siteurl'" );
		$wpdb->query( "UPDATE $wpdb->posts SET guid = (REPLACE (guid, '$old_url', '$new_url'))" );
		$wpdb->query( "UPDATE $wpdb->posts SET post_content = (REPLACE (post_content, '$old_url', '$new_url'))" );

		$wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = (REPLACE (meta_value, '$old_url', '$new_url'))" );
		$wpdb->query( "UPDATE $wpdb->termmeta SET meta_value = (REPLACE (meta_value, '$old_url', '$new_url'))" );
		$wpdb->query( "UPDATE $wpdb->commentmeta SET meta_value = (REPLACE (meta_value, '$old_url', '$new_url'))" );
		$wpdb->query( "UPDATE $wpdb->usermeta SET meta_value = (REPLACE (meta_value, '$old_url', '$new_url'))" );
		if ( is_multisite() ) {
			$wpdb->query( "UPDATE $wpdb->sitemeta SET meta_value = (REPLACE (meta_value, '$old_url', '$new_url'))" );
		}
		set_transient( $transient_name, 1, 5 * MINUTE_IN_SECONDS );
	}
}

function hocwp_disable_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}

function hocwp_filter_custom_content( $content ) {
	$content = apply_filters( 'hocwp_the_custom_content', $content );

	return $content;
}

function hocwp_the_custom_content( $content ) {
	$content = hocwp_filter_custom_content( $content );
	echo $content;
}