<?php
$args  = array(
	'posts_per_page' => 5,
	'orderby'        => 'rand',
	'post__not_in'   => array( get_the_ID() )
);
$query = new WP_Query( $args );
if ( $query->have_posts() ) {
	unset( $_POST['submitPreviewSettings'] );
	unset( $_POST['customPreviewText'] );
	unset( $_POST['customPreviewSize'] );
	unset( $_POST['customPreviewTextColour'] );
	?>
	<div class="module random-posts">
		<div class="loops">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				hocwp_theme_get_loop( 'post' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php
}