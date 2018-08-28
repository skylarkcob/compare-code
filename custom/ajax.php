<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_pxf_add_collection_ajax_callback() {
	$data = array(
		'redirect' => ''
	);

	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

	if ( HP()->is_positive_number( $post_id ) ) {
		if ( ! is_user_logged_in() ) {
			$data['redirect'] = wp_login_url( get_permalink( $post_id ) );
		} else {
			$list_option = isset( $_POST['list_option'] ) ? $_POST['list_option'] : '';

			$list = '';

			if ( 'existing-list' == $list_option ) {
				$list = isset( $_POST['list'] ) ? $_POST['list'] : '';
			} else {
				$post_title = isset( $_POST['post_title'] ) ? $_POST['post_title'] : '';

				if ( ! empty( $post_title ) ) {
					$user_id     = get_current_user_id();
					$post_title  = wp_strip_all_tags( $post_title );
					$post_status = isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'private';

					$data = array(
						'post_author'  => $user_id,
						'post_status'  => $post_status,
						'post_content' => '',
						'post_title'   => $post_title,
						'post_type'    => 'collection'
					);

					$list = wp_insert_post( $data );
				}
			}

			if ( HP()->is_positive_number( $list ) ) {
				$collection = get_post( $list );

				if ( Pixelify()->is_collection( $collection ) ) {
					$childs = get_post_meta( $list, 'childs', true );
					$childs = (array) $childs;

					$childs[] = $post_id;

					$childs = array_filter( $childs );
					$childs = array_unique( $childs );

					$updated = update_post_meta( $list, 'childs', $childs );

					if ( $updated ) {
						$data['html'] = '<p class="success">' . sprintf( __( 'Successfully added to <strong><a href="%s">%s</a></strong>.', 'pixelify' ), get_permalink( $collection ), $collection->post_title ) . '</p>';
						wp_send_json_success( $data );
					}
				}
			}
		}
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_pxf_add_collection', 'hocwp_pxf_add_collection_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_pxf_add_collection', 'hocwp_pxf_add_collection_ajax_callback' );

function hocwp_pxf_download_file_ajax_callback() {
	$data = array();

	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

	if ( HP()->is_positive_number( $post_id ) ) {
		$email = isset( $_POST['email'] ) ? $_POST['email'] : '';

		if ( is_email( $email ) || empty( $email ) ) {
			$download = get_post_meta( $post_id, 'download', true );

			$url = Pixelify()->get_download_url( $post_id );

			if ( HP()->is_positive_number( $download ) || ! empty( $url ) ) {
				$file = get_attached_file( $download );

				if ( $file || ! empty( $url ) ) {
					// Count post downloads
					$downloads = get_post_meta( $post_id, 'downloads', true );
					$downloads = absint( $downloads );
					$downloads ++;
					update_post_meta( $post_id, 'downloads', $downloads );

					$obj       = get_post( $post_id );
					$author_id = $obj->post_author;

					$user_id = $author_id;

					// Count post author downloads
					$downloads = get_user_meta( $user_id, 'downloads', true );
					$downloads = absint( $downloads );

					$downloads ++;

					update_user_meta( $user_id, 'downloads', $downloads );

					if ( is_user_logged_in() ) {
						$user_id = get_current_user_id();

						// Save user download
						$download = get_user_meta( $user_id, 'download', true );
						$download = (array) $download;

						$download[] = $post_id;

						$download = array_filter( $download );
						$download = array_unique( $download );

						update_user_meta( $user_id, 'download', $download );
					}

					if ( ! empty( $url ) ) {
						$data['file'] = $url;
					} else {
						$data['file'] = wp_get_attachment_url( $download );
					}

					wp_send_json_success( $data );
				} else {
					$data['message'] = __( 'Invalid download file!', 'pixelify' );
				}
			} else {
				$data['message'] = __( 'Invalid download file!', 'pixelify' );
			}
		} else {
			$data['message'] = __( 'Invalid email address!', 'pixelify' );
		}
	} else {
		$data['message'] = __( 'Invalid post ID!', 'pixelify' );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_pxf_download_file', 'hocwp_pxf_download_file_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_pxf_download_file', 'hocwp_pxf_download_file_ajax_callback' );

function hocwp_pxf_like_post_ajax_callback() {
	$data = array();

	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

	if ( HP()->is_positive_number( $post_id ) ) {
		$status = isset( $_POST['status'] ) ? $_POST['status'] : 0;

		$likes = get_post_meta( $post_id, 'likes', true );
		$likes = absint( $likes );

		if ( 1 == $status ) {
			$likes --;
		} else {
			$likes ++;
		}

		update_post_meta( $post_id, 'likes', $likes );

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			$ulikes = get_user_meta( $user_id, 'likes', true );
			$ulikes = (array) $ulikes;

			if ( 1 == $status ) {
				unset( $ulikes[ array_search( $post_id, $ulikes ) ] );
			} else {
				$ulikes[] = $post_id;
			}

			$ulikes = array_filter( $ulikes );
			$ulikes = array_unique( $ulikes );

			update_user_meta( $user_id, 'likes', $ulikes );
		}

		$likes = absint( $likes );

		$data['formatted_number'] = number_format( $likes );
		wp_send_json_success( $data );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_pxf_like_post', 'hocwp_pxf_like_post_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_pxf_like_post', 'hocwp_pxf_like_post_ajax_callback' );

function hocwp_pxf_remove_collection_ajax_callback() {
	$post_id    = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';
	$collection = isset( $_POST['collection'] ) ? $_POST['collection'] : '';

	if ( HP()->is_positive_number( $post_id ) && HP()->is_positive_number( $collection ) ) {
		$childs = get_post_meta( $collection, 'childs', true );
		$childs = (array) $childs;

		unset( $childs[ array_search( $post_id, $childs ) ] );

		$childs = array_filter( $childs );
		$childs = array_unique( $childs );

		$updated = update_post_meta( $collection, 'childs', $childs );
	}

	wp_send_json_success();
}

add_action( 'wp_ajax_hocwp_pxf_remove_collection', 'hocwp_pxf_remove_collection_ajax_callback' );

function hocwp_pxf_follow_author_ajax_callback() {
	if ( is_user_logged_in() ) {
		$author_id = isset( $_POST['author_id'] ) ? $_POST['author_id'] : '';

		if ( HP()->is_positive_number( $author_id ) ) {
			$user_id = get_current_user_id();

			if ( $user_id != $author_id ) {
				$status = isset( $_POST['status'] ) ? $_POST['status'] : 0;

				$followed_authors = get_user_meta( $user_id, 'followed_authors', true );
				$followed_authors = (array) $followed_authors;

				if ( 0 == $status ) {
					$followed_authors[] = $author_id;
				} else {
					unset( $followed_authors[ array_search( $author_id, $followed_authors ) ] );
				}

				$followed_authors = array_filter( $followed_authors );
				$followed_authors = array_unique( $followed_authors );
				update_user_meta( $user_id, 'followed_authors', $followed_authors );

				$followers = get_user_meta( $author_id, 'followers', true );
				$followers = absint( $followers );

				if ( 0 == $status ) {
					$followers ++;
				} else {
					$followers --;
				}

				update_user_meta( $author_id, 'followers', $followers );
			}
		}
	}

	wp_send_json_success();
}

add_action( 'wp_ajax_hocwp_pxf_follow_author', 'hocwp_pxf_follow_author_ajax_callback' );

function hocwp_pxf_upload_file_ajax_callback() {
	$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '';
	$file    = isset( $_POST['file'] ) ? $_POST['file'] : isset( $_FILES['file'] ) ? $_FILES['file'] : '';
	$key     = isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : '';

	$data = array();

	if ( ! empty( $file ) ) {
		$file_info = pathinfo( $file['name'] );

		if ( isset( $file_info['extension'] ) && ! empty( $file_info['extension'] ) ) {
			$accept = isset( $_POST['accept'] ) ? $_POST['accept'] : '';

			if ( ! empty( $accept ) ) {
				$accept = explode( ',', $accept );
				$accept = array_map( 'trim', $accept );

				$ext = '.' . $file_info['extension'];

				if ( ! in_array( $ext, $accept ) ) {
					$data['message'] = __( 'Invalid file!', 'pixelify' );
					wp_send_json_error( $data );
				}
			}
		}

		$upload = Pixelify()->upload_file( basename( $file['name'] ), $file['tmp_name'] );

		if ( isset( $upload['error'] ) && ! isset( $upload['file'] ) ) {
			wp_send_json_error( $upload );
		} else {
			if ( HP()->is_positive_number( $post_id ) ) {
				$att_id = $upload['id'];

				if ( 'download' == $key ) {
					update_post_meta( $post_id, 'download', $att_id );
				} elseif ( 'font_demos' == $key ) {
					$font_demos = get_post_meta( $post_id, $key, true );

					if ( ! is_array( $font_demos ) ) {
						$font_demos = array();
					}

					$keys  = array_keys( $font_demos );
					$index = 0;

					if ( HP()->array_has_value( $keys ) ) {
						$index = max( $keys );
					}

					$index = absint( $index );
					$index ++;

					$font_demos[ $index ] = array(
						'name' => basename( $upload['file'] ),
						'url'  => $upload['url'],
						'id'   => $upload['id']
					);

					update_post_meta( $post_id, $key, $font_demos );
				} else {
					if ( ! has_post_thumbnail( $post_id ) ) {
						update_post_meta( $post_id, '_thumbnail_id', $att_id );
					}

					$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

					if ( ! is_array( $slider_ids ) ) {
						$slider_ids = array();
					}

					$slider_ids[] = $att_id;

					update_post_meta( $post_id, 'slider_ids', $slider_ids );

					$imgs = array();

					foreach ( $slider_ids as $att_id ) {
						$imgs[] = wp_get_attachment_image( $att_id, 'full' );
					}

					$imgs = implode( "\n\n", $imgs );

					update_post_meta( $post_id, 'post_slider', $imgs );
				}
			}

			wp_send_json_success( $upload );
		}
	} else {
		$data['message'] = __( 'Invalid file!', 'pixelify' );
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_pxf_upload_file', 'hocwp_pxf_upload_file_ajax_callback' );

function hocwp_pxf_remove_file_ajax_callback() {
	$id = isset( $_POST['id'] ) ? $_POST['id'] : '';

	$data = array();

	if ( HP()->is_positive_number( $id ) ) {
		wp_delete_attachment( $id, true );

		$post_id = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '';

		if ( HP()->is_positive_number( $post_id ) ) {
			$key = isset( $_REQUEST['key'] ) ? $_REQUEST['key'] : '';

			if ( 'download' == $key ) {
				update_post_meta( $post_id, 'download', '' );
			} elseif ( 'font_demos' == $key ) {
				$font_demos = get_post_meta( $post_id, $key, true );

				if ( ! is_array( $font_demos ) ) {
					$font_demos = array();
				}

				foreach ( $font_demos as $index => $data ) {
					$ai = isset( $data['id'] ) ? $data['id'] : '';

					if ( $id == $ai ) {
						unset( $font_demos[ $index ] );
						break;
					}
				}

				update_post_meta( $post_id, $key, $font_demos );
			} else {
				$slider_ids = get_post_meta( $post_id, 'slider_ids', true );

				if ( is_array( $slider_ids ) ) {
					unset( $slider_ids[ array_search( $id, $slider_ids ) ] );
				} else {
					$post_slider = get_post_meta( $post_id, 'post_slider', true );

					if ( ! empty( $post_slider ) ) {
						$slider_ids = array();

						$doc = new DOMDocument();
						@$doc->loadHTML( $post_slider );

						$tags = $doc->getElementsByTagName( 'img' );

						foreach ( $tags as $tag ) {
							$src = $tag->getAttribute( 'src' );

							$att_id = Pixelify()->get_attachment_id( $src );

							if ( HP()->is_positive_number( $att_id ) ) {
								if ( HP()->is_positive_number( $att_id ) && $id == $att_id ) {
									continue;
								} else {
									$slider_ids[] = $att_id;
								}
							}
						}
					}
				}

				if ( HP()->array_has_value( $slider_ids ) ) {
					if ( ! has_post_thumbnail( $post_id ) ) {
						$att_id = array_shift( $slider_ids );
						update_post_meta( $post_id, '_thumbnail_id', $att_id );
					}

					$imgs = array();

					foreach ( $slider_ids as $att_id ) {
						$imgs[] = wp_get_attachment_image( $att_id, 'full' );
					}

					$imgs = implode( "\n\n", $imgs );

					update_post_meta( $post_id, 'post_slider', $imgs );
				}

				update_post_meta( $post_id, 'slider_ids', $slider_ids );
			}
		}
	}

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_hocwp_pxf_remove_file', 'hocwp_pxf_remove_file_ajax_callback' );

function hocwp_pxf_upload_avatar_ajax_callback() {
	$data    = array();
	$user_id = isset( $_POST['user_id'] ) ? $_POST['user_id'] : '';

	if ( HP()->is_positive_number( $user_id ) ) {
		$file = isset( $_FILES['file'] ) ? $_FILES['file'] : '';

		if ( isset( $file['name'] ) ) {
			$upload = Pixelify()->upload_file( basename( $file['name'] ), $file['tmp_name'] );

			if ( isset( $upload['id'] ) ) {
				$data['url'] = wp_get_attachment_image_url( $upload['id'] );
				update_user_meta( $user_id, 'avatar_id', $upload['id'] );
				wp_send_json_success( $data );
			}
		}
	}

	wp_send_json_error( $data );
}

add_action( 'wp_ajax_hocwp_pxf_upload_avatar', 'hocwp_pxf_upload_avatar_ajax_callback' );