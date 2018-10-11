<div class="<?php hocwp_wrap_class(); ?>">
	<?php hocwp_show_ads( 'site_footer' ); ?>
	<?php hocwp_addthis_toolbox(); ?>
	<?php hocwp_theme_custom_alphabe( 'Complete List of Fonts:' ); ?>
	<?php
	$searches = get_option( 'recent_searches' );
	if ( hocwp_array_has_value( $searches ) ) {
		?>
		<div class="recent-searches">
			<span><?php _e( 'Recent Searches:', 'hocwp-theme' ); ?></span>
			<?php
			foreach ( $searches as $search ) {
				?>
				<a href="<?php echo home_url( '/?s=' . $search ); ?>"><?php echo $search; ?></a>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
	<div class="footer-menus">
		<?php hocwp_theme_the_menu( 'footer' ); ?>
	</div>
</div>
<div class="footer-widgets">
	<div class="<?php hocwp_wrap_class(); ?>">
		<?php get_sidebar( 'footer' ); ?>
	</div>
</div>