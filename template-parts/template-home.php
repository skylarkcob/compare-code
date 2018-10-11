<div class="<?php hocwp_wrap_class(); ?>">
	<?php hocwp_theme_get_module( 'search-cat' ); ?>
	<?php hocwp_theme_site_main_before(); ?>
	<div class="fonts-box">
		<?php
		if ( have_posts() ) {
			hocwp_pagination();
			hocwp_theme_get_module( 'custom-preview' );
			?>
			<div class="loops">
				<?php
				while ( have_posts() ) {
					the_post();
					hocwp_theme_get_loop( 'post' );
				}
				?>
			</div>
			<?php
			hocwp_pagination();
		} else {
			hocwp_theme_get_content_none();
		}
		?>
	</div>
	<?php hocwp_theme_site_main_after(); ?>
	<?php get_sidebar(); ?>
</div>