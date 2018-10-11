<div class="<?php hocwp_wrap_class(); ?>">
	<?php
	hocwp_theme_get_module( 'search-cat' );
	hocwp_article_before();
	the_title( '<h1>', '</h1>' );
	hocwp_entry_content();
	hocwp_article_after();
	?>
</div>