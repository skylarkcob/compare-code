<div class="<?php hocwp_wrap_class(); ?>">
	<?php hocwp_theme_get_module( 'search-cat' ); ?>
	<div class="fonts-box">
		<?php
		the_title( '<h1>', '</h1>' );
		$args  = array(
			'paged'    => hocwp_get_paged(),
			'meta_key' => 'downloads',
			'orderby'  => 'meta_value_num'
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			hocwp_pagination( array( 'query' => $query ) );
			hocwp_theme_get_module( 'custom-preview' );
			?>
			<div class="loops">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					hocwp_theme_get_loop( 'post' );
				}
				wp_reset_postdata();
				?>
			</div>
			<?php
			hocwp_pagination( array( 'query' => $query ) );
		} else {
			hocwp_theme_get_content_none();
		}
		?>
	</div>
</div>