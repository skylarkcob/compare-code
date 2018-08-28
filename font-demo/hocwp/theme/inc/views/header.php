<?php
if ( ! function_exists( 'add_filter' ) ) {
	exit;
}
do_action( 'hocwp_before_doctype' );
$maintenance_mode = hocwp_in_maintenance_mode();
?>
<!doctype html>
<html <?php hocwp_attr( 'html' ); ?>>
<head>
	<?php
	do_action( 'hocwp_before_wp_head' );
	wp_head();
	do_action( 'hocwp_after_wp_head' );
	if ( $maintenance_mode ) {
		do_action( 'hocwp_maintenance_head' );
	}
	?>
</head>
<body <?php hocwp_attr( 'body' ); ?>>
<?php
do_action( 'hocwp_open_body' );
do_action( 'hocwp_before_site' );
?>
<div id="page" class="hfeed site">
	<div class="site-inner">
		<?php
		if ( ! $maintenance_mode ) {
			do_action( 'hocwp_before_site_header' );
			$atts = array(
				'class' => 'clearfix site-header'
			);
			?>
			<header <?php hocwp_attr( 'header', 'masthead', $atts ); ?>>
				<?php hocwp_theme_get_module( 'header' ); ?>
			</header><!-- .site-header -->
			<?php
			do_action( 'hocwp_after_site_header' );
		}
		do_action( 'hocwp_before_site_content' );
		?>
		<div id="siteContent" class="site-content clearfix">