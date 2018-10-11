<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}
do_action( 'hocwp_before_doctype' );
$maintenance_mode = hocwp_in_maintenance_mode();

if ( ! function_exists( 'hocwp_html_tag_attributes' ) ) {
	Pixelify()->load_plugin_theme_file( '/font-demo/hocwp/inc/deprecated.php', '/hocwp/utils.php' );
}

if ( ! function_exists( 'hocwp_html_tag_attributes' ) ) {
	function hocwp_html_tag_attributes( $tag, $context = '' ) {
		if ( current_theme_supports( 'hocwp-schema' ) ) {
			$base      = 'http://schema.org/';
			$item_type = apply_filters( 'hocwp_html_tag_attribute_item_type', '', $tag, $context );
			if ( ! empty( $item_type ) ) {
				$schema = ' itemscope itemtype="' . $base . $item_type . '"';
				echo $schema;
			}
			$item_prop = apply_filters( 'hocwp_html_tag_attribute_item_prop', '', $tag, $context );
			if ( ! empty( $item_prop ) ) {
				$schema = ' itemprop="' . $item_prop . '"';
				echo $schema;
			}
		}
		$attributes = apply_filters( 'hocwp_html_tag_attributes', '', $tag, $context );
		$attributes = trim( $attributes );
		if ( ! empty( $attributes ) ) {
			echo ' ' . $attributes;
		}
	}
}
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js"<?php hocwp_html_tag_attributes( 'html' ); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php
	do_action( 'hocwp_before_wp_head' );
	wp_head();
	do_action( 'hocwp_after_wp_head' );
	if ( $maintenance_mode ) {
		do_action( 'hocwp_maintenance_head' );
	}
	?>
</head>
<body <?php body_class(); ?><?php hocwp_html_tag_attributes( 'body' ); ?>>
<?php
do_action( 'hocwp_open_body' );
do_action( 'hocwp_before_site' );
?>
<div id="page" class="hfeed site">
	<div class="site-inner">
		<?php if ( ! $maintenance_mode ) : ?>
			<?php do_action( 'hocwp_before_site_header' ); ?>
			<header id="masthead"
			        class="site-header clearfix"<?php hocwp_html_tag_attributes( 'header', 'masthead' ); ?>>
				<?php hocwp_theme_get_module( 'header' ); ?>
			</header><!-- .site-header -->
			<?php do_action( 'hocwp_after_site_header' ); ?>
		<?php endif; ?>
		<?php do_action( 'hocwp_before_site_content' ); ?>
		<div id="content" class="site-content clearfix">