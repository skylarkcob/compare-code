<?php
$font = isset( $_GET['font'] ) ? $_GET['font'] : '';
if ( empty( $font ) ) {
	exit;
}
$font = base64_decode( $font );
if ( ! is_readable( $font ) ) {
	exit;
}
$size = isset( $_GET['size'] ) ? $_GET['size'] : 80;
if ( ! is_numeric( $size ) ) {
	$size = 80;
}
$color    = isset( $_GET['color'] ) ? $_GET['color'] : '000';
$color    = ltrim( $color, '#' );
$text     = isset( $_GET['text'] ) ? $_GET['text'] : '';
$text_len = mb_strlen( $text );
if ( $text_len > 25 ) {
	$size -= ( $size * 0.25 );
	$text = mb_substr( $text, 0, 80 ) . '...';
}
if ( empty( $color ) ) {
	$color = '#000';
} else {
	$color = '#' . $color;
}
if ( ! function_exists( 'hocwp_color_hex_to_rgb' ) ) {
	function hocwp_color_hex_to_rgb( $color, $opacity = false ) {
		$default = 'rgb(0,0,0)';
		if ( empty( $color ) ) {
			return $default;
		}
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}
		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}
		$rgb = array_map( 'hexdec', $hex );
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		return $output;
	}
}
$color       = hocwp_color_hex_to_rgb( $color );
$color       = str_replace( 'rgb', '', $color );
$color       = str_replace( '(', '', $color );
$color       = str_replace( ')', '', $color );
$colors      = explode( ',', $color );
$padding     = 10;
$width       = 1500 + ( $padding * 2 );
$height      = $size + ( $padding * 2 );
$font_height = $height;
$regenerate  = true;
$fontname    = basename( $font );
$filename    = md5( $fontname . $text . $size . $color ) . '.png';
$dir         = dirname( __FILE__ );
$dir         = dirname( $dir );
$dir         = dirname( $dir );
$dir         = rtrim( $dir, '/' );
$dir .= '/uploads';
$location = $dir . '/images/fonts-cache/';
if ( ! is_dir( $location ) ) {
	mkdir( $location, 0777, true );
}
$file_path = $location . $filename;
if ( is_readable( $file_path ) && filemtime( $file_path ) >= strtotime( '-14 days' ) ) {
	$regenerate = false;
}
if ( $regenerate ) {
	$bbox        = imagettfbbox( $size, 0, $font, $text );
	$font_height = abs( $bbox[5] - $bbox[1] ) + ceil( $padding * 2 );
	$scale       = round( $height / $font_height, 2 );
	$new_size    = round( $size * $scale );
	if ( $new_size < $size ) {
		$size = $new_size;
	}
	$im = @imagecreatetruecolor( $width, $height );
	imagesavealpha( $im, true );
	imagealphablending( $im, false );
	$white = imagecolorallocatealpha( $im, 255, 255, 255, 127 );
	imagefill( $im, 0, 0, $white );
	$color = imagecolorallocate( $im, $colors[0], $colors[1], $colors[2] );
	$data  = array(
		'ascent'  => abs( $bbox[7] ),
		'descent' => abs( $bbox[1] ),
		'width'   => abs( $bbox[0] ) + abs( $bbox[2] ),
		'height'  => abs( $bbox[7] ) + abs( $bbox[1] )
	);
	$x     = 10;
	$y     = $height + ceil( ( $height - $data['height'] ) / 2 ) + $data['ascent'];
	$y     = ceil( $y / 2 ) - ( $padding * 2 );
	imagettftext( $im, $size, 0, $x, $y, $color, $font, $text );
	imagepng( $im, $file_path, 9, PNG_NO_FILTER );
	imagedestroy( $img );
}
function setModifiedDate( $contentDate ) {
	$ifModifiedSince = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? stripslashes( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : false;
	if ( $ifModifiedSince && strtotime( $ifModifiedSince ) >= $contentDate ) {
		header( 'HTTP/1.0 304 Not Modified' );
		exit;
	}
	$lastModified = gmdate( 'D, d M Y H:i:s', $contentDate ) . ' GMT';
	header( 'Last-Modified: ' . $lastModified );
}

clearstatcache();
setModifiedDate( filemtime( $file_path ) );
header( 'Content-type: image/png' );
header( 'Cache-Control: must-revalidate' );
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', strtotime( '+14 days' ) ) . ' GMT' );
ob_clean();
flush();
readfile( $file_path );