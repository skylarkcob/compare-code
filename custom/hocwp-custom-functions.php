<?php
function hocwp_theme_custom_get_page_option( $option_name, $page_template ) {
	$page = hocwp_theme_get_option( $option_name );
	if ( ! hocwp_id_number_valid( $page ) ) {
		return hocwp_get_page_by_template( $page_template );
	}

	return get_post( $page );
}

function hocwp_theme_custom_get_sortable_fonts() {
	$cats = hocwp_theme_get_option( 'sortable_category' );
	if ( ! empty( $cats ) ) {
		$cats   = hocwp_json_string_to_array( $cats );
		$result = array();
		foreach ( $cats as $data ) {
			$result[] = get_category( $data['id'] );
		}

		return $result;
	}

	return get_categories( array( 'hide_empty' => false ) );
}

function hocwp_read_zip( $target_file ) {
	$zip = zip_open( $target_file );
	if ( $zip ) {
		while ( $zip_entry = zip_read( $zip ) ) {

		}
		zip_close( $zip );
	}
}

function hocwp_theme_custom_is_font_file( $file ) {
	$exts = array( 'ttf', 'woff' );
	$info = pathinfo( $file );

	return isset( $info['extension'] ) && in_array( $info['extension'], $exts );
}

function hocwp_theme_custom_alphabe( $title = '' ) {
	$strings = 'abcdefghijklmnopqrstuvwxyz#';
	$strings = str_split( $strings );
	?>
	<div class="alphabe">
		<span><?php echo $title; ?></span>
		<?php
		foreach ( $strings as $char ) {
			?>
			<a class="hover-link" href="<?php echo home_url( '/?alphabe=' . $char ); ?>"><?php echo $char; ?></a>
			<?php
		}
		?>
	</div>
	<?php
}

function hocwp_theme_custom_generate_media_id( $file, $file_url ) {
	$attachment_id = hocwp_get_media_id( $file_url );

	if ( ! hocwp_id_number_valid( $attachment_id ) ) {
		$filename    = basename( $file );
		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'guid'           => $file_url,
			'post_mime_type' => $wp_filetype['type'],
			'post_parent'    => '',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attachment_id = wp_insert_attachment( $attachment, $file );
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
	}

	$attach_data = wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) );
	wp_update_attachment_metadata( $attachment_id, $attach_data );

	return $attachment_id;
}

function hocwp_theme_custom_get_demo_dir( $post_id, $file_contents = '', $url = false ) {
	if ( empty( $file_contents ) && hocwp_id_number_valid( $post_id ) ) {
		$file_contents = get_post_meta( $post_id, 'file_contents', true );
	}

	$file_contents = hocwp_sanitize_media_value( $file_contents );

	$dir_info = wp_upload_dir();
	$basedir  = $dir_info['basedir'];
	$baseurl  = $dir_info['baseurl'];

	$dir = trailingslashit( $basedir );
	$dir .= 'demo';

	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}

	$file_id = $file_contents['id'];

	$exists = false;

	$filename = '';

	if ( ! hocwp_id_number_valid( $file_id ) ) {
		$link = $file_contents['url'];

		$info     = pathinfo( $link );
		$filename = $info['filename'];
		$filename = sanitize_file_name( $filename );

		$check_dir = trailingslashit( $dir );
		$check_dir .= $filename;

		if ( is_dir( $check_dir ) ) {
			$dir    = $check_dir;
			$exists = true;
		} else {
			mkdir( $check_dir );

			$temp_file = trailingslashit( $check_dir );
			$temp_file .= $filename;
			$temp_file .= '.zip';

			if ( ! file_exists( $temp_file ) ) {
				$upload_name = $filename . '.zip';

				$copy = @copy( $link, $temp_file );

				$zip = new ZipArchive;

				if ( ! empty( $temp_file ) && $zip->open( $temp_file ) === true ) {
					$zip->extractTo( $check_dir );
				}
			}
		}

		if ( ! $exists ) {
			if ( $url ) {
				return array(
					'dir' => '',
					'url' => ''
				);
			}

			return '';
		}
	};

	if ( ! $exists ) {
		$zip_path = hocwp_get_media_file_path( $file_id );
		$zip_info = pathinfo( $zip_path );
		$dir      = trailingslashit( $dir );
		$dir .= hocwp_sanitize_file_name( $zip_info['filename'] );
	}

	if ( $url ) {
		$url = trailingslashit( $baseurl );
		$url .= 'demo';

		$url = trailingslashit( $url );
		$url .= hocwp_sanitize_file_name( $filename );

		return array(
			'dir' => $dir,
			'url' => $url
		);
	}

	return $dir;
}

function hocwp_theme_custom_add_demo_from_file_contents( $post_id, $file_contents = '' ) {
	if ( empty( $file_contents ) && hocwp_id_number_valid( $post_id ) ) {
		$file_contents = get_post_meta( $post_id, 'file_contents', true );
	}

	$file_contents = hocwp_sanitize_media_value( $file_contents );

	$result = array();

	$dir_info = wp_upload_dir();
	$dir      = trailingslashit( $dir_info['basedir'] ) . 'demo';

	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}

	$exists = false;

	if ( ! hocwp_id_number_valid( $file_contents['id'] ) ) {
		$link = $file_contents['url'];

		$info     = pathinfo( $link );
		$filename = $info['filename'];

		$check_dir = trailingslashit( $dir );
		$check_dir .= $filename;

		if ( is_dir( $check_dir ) ) {
			$dir    = $check_dir;
			$exists = true;
		}

		if ( ! $exists ) {
			return hocwp_sanitize_media_value( $result );
		}
	};

	$zip_path = hocwp_get_media_file_path( $file_contents['id'] );
	$zip      = new ZipArchive;

	if ( ! empty( $zip_path ) && $zip->open( $zip_path ) === true ) {
		$zip_info = pathinfo( $zip_path );
		$dir      = trailingslashit( $dir ) . hocwp_sanitize_file_name( $zip_info['filename'] );

		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0777, true );
		}

		$zip->extractTo( $dir );

		$exists = true;

		$zip->close();
	}

	if ( $exists ) {
		$files = scandir( $dir );

		foreach ( $files as $file ) {
			$info = pathinfo( $file );

			if ( isset( $info['extension'] ) && 'ttf' == $info['extension'] ) {
				$demo = trailingslashit( $dir ) . $file;
				$dest = trailingslashit( $dir_info['path'] ) . hocwp_sanitize_file_name( $file );

				if ( ! file_exists( $dest ) ) {
					@copy( $demo, $dest );
				}

				$file          = $dest;
				$filename      = basename( $file );
				$file_url      = trailingslashit( $dir_info['url'] ) . $filename;
				$attachment_id = hocwp_theme_custom_generate_media_id( $file, $file_url );

				if ( ! is_wp_error( $attachment_id ) && hocwp_id_number_valid( $attachment_id ) ) {
					$demo = array(
						'id'  => $attachment_id,
						'url' => $file_url
					);

					$result = $demo;
					update_post_meta( $post_id, 'demo', $demo );

					if ( ! isset( $_POST['character_map'] ) || empty( $_POST['character_map'] ) ) {
						$path = hocwp_get_media_file_path( $attachment_id );
						$info = pathinfo( $path );
						$name = basename( $path );

						$file_name = $info['filename'];
						$file_name = hocwp_sanitize_file_name( $file_name );

						if ( empty( $file_name ) ) {
							return '';
						}

						$file_name .= '-character-map.png';
						$new_path = dirname( $path );
						$new_path = trailingslashit( $new_path ) . $file_name;

						$colors = array();

						$char_map = hocwp_generate_font_character_map( $path, 30, array(), $new_path, $colors );

						if ( $char_map ) {
							$src = trailingslashit( dirname( $file_url ) ) . $file_name;
							$id  = hocwp_theme_custom_generate_media_id( $new_path, $src );

							if ( hocwp_id_number_valid( $id ) ) {
								hocwp_update_attachment_meta( $id, $new_path );
							}

							$char_map = '<img src="' . $src . '" alt="">';
							update_post_meta( $post_id, 'character_map', $char_map );
						}
					}
				}
			}
		}

		if ( is_page() || is_admin() ) {
			//hocwp_theme_custom_empty_dir( $dir );
		}
	}

	return hocwp_sanitize_media_value( $result );
}

function hocwp_theme_custom_empty_dir( $dir ) {
	$files = scandir( $dir );

	foreach ( $files as $file ) {
		if ( '.' == $file || '..' == $file ) {
			continue;
		}

		$file = trailingslashit( $dir ) . $file;

		if ( is_file( $file ) ) {
			unlink( $file );
		} else {
			@rmdir( $file );

			if ( is_dir( $file ) ) {
				hocwp_theme_custom_empty_dir( $dir );
			}
		}
	}
}

function hocwp_str_split_unicode( $str, $l = 0 ) {
	if ( $l > 0 ) {
		$ret = array();
		$len = mb_strlen( $str, "UTF-8" );
		for ( $i = 0; $i < $len; $i += $l ) {
			$ret[] = mb_substr( $str, $i, $l, "UTF-8" );
		}

		return $ret;
	}

	return preg_split( "//u", $str, - 1, PREG_SPLIT_NO_EMPTY );
}

function hocwp_generate_font_character_map( $font_path, $font_size = 12, $chars = array(), $output = '*.png|9', $colors = array(), $padding = 8 ) {
	$font_path = realpath( $font_path );

	if ( $font_path === false || ! is_readable( $font_path ) || ! empty( $font_path ) ) {
		return false;
	}

	$path_info      = pathinfo( $font_path );
	$font_file_name = basename( $font_path, '.' . $path_info['extension'] );

	if ( ! is_int( $font_size ) ) {
		$font_size = 12;
	}

	if ( ! is_array( $chars ) || empty( $chars ) ) {
		$lists = array();

		$string  = 'ABCDEFJHIJKLMNOPQRSTUVWXYZ';
		$string  = str_split( $string );
		$lists[] = $string;

		$string  = array_map( 'strtolower', $string );
		$lists[] = $string;

		$string  = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜß';
		$string  = hocwp_str_split_unicode( $string );
		$lists[] = $string;

		$string  = array_map( 'mb_strtolower', $string );
		$lists[] = $string;

		$string  = '0123456789';
		$string  = str_split( $string );
		$lists[] = $string;

		$string  = '`-=[]\\;\',./';
		$string  = str_split( $string );
		$lists[] = $string;

		$string  = '~!@#$%^&*()_+{}|:"<>?';
		$string  = str_split( $string );
		$lists[] = $string;

		$string  = '¶©ÿ¢µð';
		$string  = hocwp_str_split_unicode( $string );
		$lists[] = $string;

		$chars = $lists;
	}

	$textColor       = isset( $colors['text'] ) ? $colors['text'] : array( 0, 0, 0 );
	$borderColor     = isset( $colors['border'] ) ? $colors['border'] : array( 210, 210, 210 );
	$backgroundColor = isset( $colors['background'] ) ? $colors['background'] : array( 255, 255, 255 );
	$indexColor      = isset( $colors['index'] ) ? $colors['index'] : $borderColor;
	$data            = array();
	$charWidths      = array();
	$charHeights     = array();
	$i               = 0;

	foreach ( $chars as $group ) {
		foreach ( $group as $char ) {
			$bbox = imagettfbbox( $font_size, 0, $font_path, $char );

			$data[ $i ] = array(
				'string'     => $char,
				'code_point' => $char,
				'ascent'     => abs( $bbox[7] ),
				'descent'    => abs( $bbox[1] ),
				'width'      => abs( $bbox[0] ) + abs( $bbox[2] ),
				'height'     => abs( $bbox[7] ) + abs( $bbox[1] )
			);

			$charWidths[]  = $data[ $i ]['width'];
			$charHeights[] = $data[ $i ]['height'];
			$i ++;
		}
	}

	$total  = count( $data );
	$row    = 13;
	$column = ceil( $total / $row );
	//$cellWidth = ceil( ( ( max( $charWidths ) + min( $charWidths ) ) / 2 ) * 2 ) + $padding * 2;
	//$cellWidth += 20;
	$cellWidth = 100;
	//$cellHeight = ceil( ( ( max( $charHeights ) + min( $charHeights ) ) / 2 ) * 2 ) + $padding * 2;
	//$cellHeight += 20;
	$cellHeight  = 100;
	$imageWidth  = $cellWidth * $row + 1;
	$imageHeight = $cellHeight * $column + 1;
	$image       = @imagecreatetruecolor( $imageWidth, $imageHeight );

	if ( ! $image ) {
		return false;
	}

	if ( function_exists( 'imageantialias' ) ) {
		@imageantialias( $image, true );
	}

	$colorText       = imagecolorallocate( $image, $textColor[0], $textColor[1], $textColor[2] );
	$colorBorder     = imagecolorallocate( $image, $borderColor[0], $borderColor[1], $borderColor[2] );
	$colorBackground = imagecolorallocate( $image, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2] );
	$colorIndex      = imagecolorallocate( $image, $indexColor[0], $indexColor[1], $indexColor[2] );

	imagefill( $image, 0, 0, $colorBackground );

	$indexSize = 1;

	for ( $i = 1; $i <= 5; $i ++ ) {
		$imageFontWidth = imagefontwidth( $i ) * 8;

		if ( $cellWidth > $imageFontWidth ) {
			$indexSize = $i;
		}
	}

	$row_space = 0;
	$points    = array();

	// Row Line
	for ( $j = 0; $j <= $column; $j ++ ) {
		$x1 = 0;
		$y1 = $j * $cellHeight;
		$x2 = $imageWidth;
		$y2 = $y1;
		imageline( $image, $x1, $y1, $x2, $y2, $colorBorder );

		if ( 0 != $j && $j % 2 == 0 ) {
			$points[] = array(
				$x1,
				$y1,
				$x2,
				$y2
			);

			$y1 += $row_space;
			$y2 += $row_space;

			imageline( $image, $x1, $y1, $x2, $y2, $colorBorder );
		}
	}

	// Column Line
	$count_point = count( $points );

	for ( $c = 0; $c < $count_point; $c ++ ) {
		$y2 = $points[ $c ][1];

		for ( $i = 0; $i <= $row; $i ++ ) {
			$x1 = $i * $cellWidth;
			$y1 = ( $c * 2 * $cellHeight ) + $row_space;
			$x2 = $x1;

			imageline( $image, $x1, $y1, $x2, $y2, $colorBorder );
		}
	}

	$k = 0;

	for ( $j = 0; $j < $column; $j ++ ) {
		for ( $i = 0; $i < $row; $i ++ ) {

			if ( ! isset( $data[ $k ] ) ) {
				break;
			}

			$x = $i * $cellWidth + 5;
			$y = $j * $cellHeight + 5 + ( $indexSize * 3 );

			if ( $j % 2 == 0 ) {
				//$y -= $row_space;
			}

			$string = $data[ $k ]['code_point'];
			$string = utf8_decode( $string );
			//imagestring( $image, $indexSize, $x, $y, $string, $colorIndex );
			$font_dir = get_template_directory() . '/fonts/arial.ttf';
			imagettftext( $image, $indexSize * 3, 0, $x, $y, $colorIndex, $font_dir, $string );
			$x = $i * $cellWidth + ceil( ( $cellWidth - $data[ $k ]['width'] ) / 2 );
			$y = $j * $cellHeight + ceil( ( $cellHeight - $data[ $k ]['height'] ) / 2 ) + $data[ $k ]['ascent'];

			if ( 0 != $j && $j % 2 == 0 ) {
				$y += ( $row_space / 2 );
			}

			$text = $data[ $k ]['string'];
			imagettftext( $image, $font_size, 0, $x, $y, $colorText, $font_path, $text );
			$k ++;
		}
	}

	$file_name = null;
	$quality   = null;

	if ( strpos( $output, '|' ) !== false ) {
		list( $file, $quality ) = explode( '|', trim( $output ), 2 );
	} else {
		$file = trim( $output );
	}

	if ( strpos( $file, '.' ) !== false ) {
		$path_info = pathinfo( $file );
		$file_type = $path_info['extension'];
		$file_name = substr( $path_info['basename'], 0, strrpos( $path_info['basename'], '.' ) );

		if ( $file_name == '*' ) {
			$file_name = $path_info['dirname'] . '/' . $font_file_name;
		} else {
			$file_name = $file;
		}
	} else {
		$file_type = $file;
	}

	switch ( strtolower( $file_type ) ) {
		case 'gif':
			if ( is_null( $file_name ) ) {
				header( 'Content-Disposition: inline;filename=' . $font_file_name . '.' . $file_type );
				header( 'Content-Type: image/gif' );
				imagegif( $image, null );
			} else {
				imagegif( $image, $file_name );
			}

			break;
		case 'jpg':
		case 'jpeg':
			$quality = is_null( $quality ) ? 85 : $quality;

			if ( is_null( $file_name ) ) {
				header( 'Content-Disposition: inline;filename=' . $font_file_name . '.' . $file_type );
				header( 'Content-Type: image/jpeg' );
				imagejpeg( $image, null, $quality );
			} else {
				imagejpeg( $image, $file_name, $quality );
			}
			break;
		case 'png':
		default:
			$quality = is_null( $quality ) ? 9 : $quality;

			if ( is_null( $file_name ) ) {
				header( 'Content-Disposition: inline;filename=' . $font_file_name . '.png' );
				header( 'Content-Type: image/png' );
				imagepng( $image, null, $quality, PNG_NO_FILTER );
			} else {
				imagepng( $image, $file_name, $quality, PNG_NO_FILTER );
			}

			break;
	}
	imagedestroy( $image );

	return true;
}

function hocwp_generate_font_preview( $post_id, $t = '', $s = 80, $c = '000', $save = false, $font = '', $post_data = '' ) {
	$att_key = $post_id;
	$att_key .= $t;
	$att_key .= $s;
	$att_key .= $c;
	$att_key .= $font;
	$att_key .= maybe_serialize( $post_data );

	$att_key = md5( $att_key );

	$att_id = 0;

	$query = hocwp_get_post_by_meta( 'att_key', $att_key, array( 'post_type' => 'attachment', 'fields' => 'ids' ) );

	if ( $query->have_posts() ) {
		$first  = array_shift( $query->posts );
		$att_id = $first->ID;
	}

	if ( hocwp_id_number_valid( $att_id ) ) {
		$obj = get_post( $att_id );

		if ( $obj instanceof WP_Post && 'attachment' == $obj->post_type ) {
			return $att_id;
		}
	}

	if ( ! is_numeric( $s ) ) {
		$s = 80;
	}

	$c = ltrim( $c, '#' );

	$is_custom = false;

	if ( ! empty( $post_data ) ) {
		$post_data = base64_decode( $post_data );
		$post_data = json_decode( $post_data );
		$post_data = (array) $post_data;

		if ( isset( $post_data['customPreviewText'] ) ) {
			if ( ! empty( $post_data['customPreviewText'] ) ) {
				$is_custom = true;
			}
		}
	}

	if ( 80 != $s || ( '000' != $c && '000000' != $c ) ) {
		$is_custom = true;
	}

	$text_len = mb_strlen( $t );

	if ( $text_len > 25 ) {
		$s -= ( $s * 0.25 );
	}

	if ( empty( $c ) ) {
		$c = '#000';
	} else {
		$c = '#' . $c;
	}

	$c = hocwp_color_hex_to_rgb( $c );

	$post = get_post( $post_id );

	if ( $post instanceof WP_Post ) {
		if ( 'revision' == $post->post_type || 'inherit' == $post->post_type || 'auto-draft' == $post->post_status || 'trash' == $post->post_status || 'draft' == $post->post_status ) {
			return false;
		} elseif ( 'attachment' == $post->post_type ) {
			return false;
		}
	}

	HT_Custom()->check_post_meta_data( $post_id );

	if ( hocwp_id_number_valid( $post_id ) ) {
		if ( empty( $t ) ) {
			$t = get_post_meta( $post_id, 'name', true );

			if ( ( empty( $t ) || has_post_thumbnail( $post_id ) ) && $post instanceof WP_Post ) {
				$t = $post->post_title;
			}
		}
	}

	$padding = 10;
	$width   = 1500 + ( $padding * 2 );
	$height  = $s + ( $padding * 2 );

	$font_height = $height;

	$file_id = 0;

	$font_demos = get_post_meta( $post_id, 'font_demos', true );

	if ( ! empty( $font_demos ) && is_array( $font_demos ) ) {
		$first   = current( $font_demos );
		$file_id = isset( $first['id'] ) ? $first['id'] : 0;
	} else {
		$demo    = hocwp_theme_custom_add_demo_from_file_contents( $post_id );
		$file_id = $demo['id'];
	}

	$fontfile = hocwp_get_media_file_path( $file_id );

	if ( ! empty( $font ) ) {
		$font = base64_decode( $font );

		if ( is_readable( $font ) ) {
			$fontfile = $font;
		}
	}

	if ( empty( $fontfile ) ) {
		return false;
	}

	$info = wp_upload_dir();

	$c = str_replace( 'rgb', '', $c );
	$c = str_replace( '(', '', $c );
	$c = str_replace( ')', '', $c );
	$c = explode( ',', $c );

	if ( ! empty( $font ) ) {
		if ( $is_custom ) {
			$file_name = sanitize_file_name( basename( $font ) );
			$file_name = rtrim( $file_name, '-' );
			$file_name .= '-font-preview.png';
			$dirname = trailingslashit( dirname( $font ) );
			$file    = $dirname . $file_name;
			$sub     = substr( $dirname, strpos( $dirname, '/wp-content' ) );
			$sub     = trim( $sub, '/' );
			$url     = home_url( $sub );
			$url     = trailingslashit( $url ) . $file_name;

			if ( is_readable( $file ) ) {
				return $url;
			}
		}
	}

	if ( is_readable( $fontfile ) ) {
		$bbox        = imagettfbbox( $s, 0, $fontfile, $t );
		$font_height = abs( $bbox[5] - $bbox[1] ) + ceil( $padding * 2 );
		$scale       = round( $height / $font_height, 2 );
		$new_size    = round( $s * $scale );

		if ( $new_size < $s ) {
			$s = $new_size;
		}
	}

	$im = @imagecreatetruecolor( $width, $height );

	imagesavealpha( $im, true );
	imagealphablending( $im, false );
	$white = imagecolorallocatealpha( $im, 255, 255, 255, 127 );
	imagefill( $im, 0, 0, $white );
	$color = imagecolorallocate( $im, $c[0], $c[1], $c[2] );

	if ( is_readable( $fontfile ) ) {
		$bbox = imagettfbbox( $s, 0, $fontfile, $t );

		$data = array(
			'ascent'  => abs( $bbox[7] ),
			'descent' => abs( $bbox[1] ),
			'width'   => abs( $bbox[0] ) + abs( $bbox[2] ),
			'height'  => abs( $bbox[7] ) + abs( $bbox[1] )
		);

		$x = 10;
		$y = $height + ceil( ( $height - $data['height'] ) / 2 ) + $data['ascent'];
		$y = ceil( $y / 2 ) - ( $padding * 2 );

		imagettftext( $im, $s - 5, 0, $x, $y, $color, $fontfile, $t );
	}

	if ( $save ) {
		$file_name = sanitize_title( $post->post_title );
		$file_name .= '-' . sanitize_title( basename( $fontfile ) );
		$file_name = rtrim( $file_name, '-' );
		$file_name .= '-font-preview.png';
		$file = trailingslashit( $info['path'] ) . $file_name;
		imagepng( $im, $file, 9, PNG_NO_FILTER );

		$att_id = hocwp_theme_custom_generate_media_id( $file, trailingslashit( $info['url'] ) . $file_name );

		if ( hocwp_id_number_valid( $att_id ) ) {
			update_post_meta( $att_id, 'att_key', $att_key );
		}

		return $att_id;
	} else {
		if ( ! empty( $font ) ) {
			$prefix = sanitize_file_name( basename( $font ) );

			if ( $is_custom ) {
				$prefix .= '-';
				$prefix .= sanitize_file_name( md5( $t . $s . json_encode( $c ) ) );
			}

			$prefix = rtrim( $prefix, '-' );

			$file_name = $prefix . '-font-preview.png';
			$dirname   = trailingslashit( dirname( $font ) );
			$file      = $dirname . $file_name;
			$sub       = substr( $dirname, strpos( $dirname, '/wp-content' ) );
			$sub       = trim( $sub, '/' );
			$url       = home_url( $sub );
			$url       = trailingslashit( $url ) . $file_name;

			if ( ! is_readable( $file ) ) {
				imagepng( $im, $file, 9, PNG_NO_FILTER );
			}

			return $url;
		}

		header( "Content-type: image/png" );
		imagepng( $im );
	}

	imagedestroy( $im );

	return false;
}

function hocwp_theme_custom_get_preview_url() {
	return esc_url_raw( get_template_directory_uri() . '/preview.php' );
}

class HOCWP_Theme_Custom {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_url() {
		return HOCWP_THEME_CUSTOM_URL;
	}

	public function get_path() {
		return HOCWP_THEME_CUSTOM_PATH;
	}

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		add_action( 'hocwp_theme_save_post_data', array( $this, 'on_save_post_action' ) );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme_action' ) );
	}

	public function after_setup_theme_action() {
		add_image_size( 'demo_thumb', 290, 170 );
	}

	public function on_save_post_action( $post_id ) {
		$this->generate_font_preview_thumbnail( $post_id );
	}

	public function get_donate_url( $post_id = null ) {
		if ( null == $post_id || ! is_numeric( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$obj = get_post( $post_id );

		if ( $obj instanceof WP_Post ) {
			$author_id = get_the_author_meta( 'ID' );
			$donate    = get_user_meta( $author_id, 'donate', true );
			$donate    = esc_url( $donate );

			if ( has_term( '', 'designer', $post_id ) ) {
				$terms = wp_get_post_terms( $post_id, 'designer' );

				foreach ( $terms as $term ) {
					$dn = get_term_meta( $term->term_id, 'donate', true );

					if ( ! empty( $dn ) ) {
						$donate = $dn;
						break;
					}
				}
			}

			$donate_p = get_post_meta( $post_id, 'donate', true );

			if ( ! empty( $donate_p ) ) {
				$donate = $donate_p;
			}

			if ( empty( $donate ) ) {
				$user   = wp_get_current_user();
				$donate = $user->user_email;
			}
		}

		return '';
	}

	public function show_donate_button( $post_id = null ) {
		$donate = $this->get_donate_url( $post_id );

		if ( ! empty( $donate ) ) {
			if ( is_email( $donate ) ) {
				$return_url = home_url( '/' );

				if ( is_single() || is_singular() || is_page() ) {
					$return_url = get_the_permalink();
				}

				$item_name = 'Donation via ' . hocwp_uppercase_first_char( hocwp_get_domain_name( home_url() ) );
				?>
				<form name="donate_form" action="https://www.paypal.com/cgi-bin/webscr" method="post"
				      target="_blank">
					<input type="hidden" name="cmd" value="_donations">
					<input type="hidden" name="cancel_return" value="<?php echo $return_url; ?>">
					<input type="hidden" name="return" value="<?php echo $return_url; ?>">
					<input type="hidden" name="business" value="<?php echo $donate; ?>">
					<input type="hidden" name="lcu" value="C2">
					<input type="hidden" name="item_name" value="<?php echo $item_name; ?>">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="button_subtype" value="services">
					<input type="hidden" name="no_note" value="0">
					<input type="submit" name="submit"
					       value="<?php _e( 'Donate To Designer', 'hocwp-theme' ); ?>"
					       class="btn-light button">
				</form>
				<?php
			}
			?>
			<div class="donate">
				<?php
				if ( is_email( $donate ) ) {

				} else {
					?>
					<a class="btn-light button"
					   href="<?php echo $donate; ?>"
					   target="_blank"><?php _e( 'Donate To Designer', 'hocwp-theme' ); ?></a>
					<?php
				}
				?>
			</div>
			<?php
		}
	}

	public function generate_font_preview_thumbnail( $post_id ) {
		if ( hocwp_id_number_valid( $post_id ) ) {
			$thumb_id = hocwp_generate_font_preview( $post_id, '', 80, '000', true );
			$data     = hocwp_sanitize_media_value( $thumb_id );

			$size = $data['size'];

			if ( is_numeric( $size ) ) {
				$size /= 1024;
			}

			if ( hocwp_id_number_valid( $thumb_id ) && is_numeric( $size ) && 1 <= $size ) {
				set_post_thumbnail( $post_id, $thumb_id );
			}
		}
	}

	public function check_post_meta_data( $post_id ) {
		$font_demos = get_post_meta( $post_id, 'font_demos', true );

		if ( empty( $font_demos ) ) {
			$demo = get_post_meta( $post_id, 'demo', true );

			if ( ! empty( $demo ) ) {
				$demo = hocwp_sanitize_media_value( $demo );

				if ( isset( $demo['url'] ) && ! empty( $demo['url'] ) ) {
					$info = pathinfo( $demo['url'] );

					$font_demos = array(
						array(
							'name' => $info['filename'],
							'url'  => $demo['url'],
							'id'   => isset( $demo['id'] ) ? $demo['id'] : ''
						)
					);

					update_post_meta( $post_id, 'font_demos', $font_demos );
				}
			}
		}

		$download = get_post_meta( $post_id, 'download', true );

		if ( ! hocwp_id_number_valid( $download ) ) {
			$file_contents = get_post_meta( $post_id, 'file_contents', true );

			if ( ! empty( $file_contents ) ) {
				$file_contents = hocwp_sanitize_media_value( $file_contents );

				if ( isset( $file_contents['id'] ) && hocwp_id_number_valid( $file_contents['id'] ) ) {
					update_post_meta( $post_id, 'download', $file_contents['id'] );
				}
			} else {
				$demo = get_post_meta( $post_id, 'demo', true );

				if ( ! empty( $demo ) ) {
					$demo = hocwp_sanitize_media_value( $demo );

					if ( isset( $demo['id'] ) && ! empty( $demo['id'] ) && hocwp_id_number_valid( $demo['id'] ) ) {
						update_post_meta( $post_id, 'download', $demo['id'] );
					}
				}
			}
		}
	}
}

function HT_Custom() {
	return HOCWP_Theme_Custom::get_instance();
}

HT_Custom();