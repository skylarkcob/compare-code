<?php
$new_fonts = hocwp_theme_custom_get_page_option( 'new_fonts', 'page-templates/new-fonts.php' );
$top_fonts = hocwp_theme_custom_get_page_option( 'top_fonts', 'page-templates/top-fonts.php' );
$cats      = hocwp_theme_custom_get_sortable_fonts();
$count     = count( $cats );
$column    = 8;
if ( $count < $column ) {
	$column = $count;
}
$column = round( $count / $column, 0, PHP_ROUND_HALF_DOWN );
if ( 0 < $column ) {
	$cats = array_chunk( $cats, $column );
} else {
	$cats = array( $cats );
}
?>
<div class="module search-cats">
	<div class="module-body">
		<?php hocwp_theme_custom_alphabe( 'Alphabetically Organized Free Fonts:' ); ?>
		<div class="cat-cols clearfix">
			<?php
			if ( $new_fonts instanceof WP_Post || $top_fonts instanceof WP_Post ) {
				?>
				<div class="left-col">
					<?php
					if ( $new_fonts instanceof WP_Post ) {
						?>
						<a class="hover-link"
						   href="<?php echo get_permalink( $new_fonts ); ?>"><?php echo $new_fonts->post_title; ?></a>
						<?php
					}
					if ( $top_fonts instanceof WP_Post ) {
						?>
						<a class="hover-link"
						   href="<?php echo get_permalink( $top_fonts ); ?>"><?php echo $top_fonts->post_title; ?></a>
						<?php
					}
					?>
				</div>
				<?php
			}
			if ( hocwp_array_has_value( $cats ) ) {
				foreach ( $cats as $lists ) {
					?>
					<div class="cat-col">
						<?php
						foreach ( $lists as $cat ) {
							?>
							<a class="hover-link"
							   href="<?php echo get_category_link( $cat ); ?>"><?php echo $cat->name; ?></a>
							<?php
						}
						?>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>
</div>
<?php hocwp_theme_get_module( 'notices' ); ?>
