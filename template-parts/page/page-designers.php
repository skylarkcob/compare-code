<div class="<?php hocwp_wrap_class(); ?>">
	<?php hocwp_theme_get_module( 'search-cat' ); ?>
	<?php
	$dalphabe = isset( $_GET['dalphabe'] ) ? $_GET['dalphabe'] : 'a';
	$strings  = 'abcdefghijklmnopqrstuvwxyz#';
	$strings  = str_split( $strings );
	$dalphabe = strtoupper( $dalphabe );
	$args     = array(
		'hide_empty' => false,
		'taxonomy'   => 'designer',
		'meta_key'   => 'alphabe',
		'meta_value' => $dalphabe
	);
	$terms    = get_terms( $args );
	?>
	<h1><?php printf( __( 'Designers Starting With %s', 'hocwp-theme' ), $dalphabe ); ?></h1>

	<div class="chars">
		<?php
		$page = hocwp_theme_custom_get_page_option( 'designers', 'page-templates/designers.php' );
		$link = home_url();
		if ( $page instanceof WP_Post ) {
			$link = trailingslashit( get_permalink( $page ) );
		}
		foreach ( $strings as $char ) {
			$link  = add_query_arg( array( 'dalphabe' => $char ), $link );
			$class = 'hover-link';
			$char  = strtoupper( $char );
			if ( $char == $dalphabe ) {
				$class .= ' current';
			}
			?>
			<a class="<?php echo $class; ?>" href="<?php echo esc_url( $link ); ?>"><?php echo $char; ?></a>
			<?php
		}
		?>
	</div>
	<ul class="list-designers">
		<?php
		if ( hocwp_array_has_value( $terms ) ) {
			foreach ( $terms as $term ) {
				?>
				<li>
					<a href="<?php echo get_term_link( $term ); ?>"><?php echo $term->name; ?></a>
				</li>
				<?php
			}
		}
		?>
	</ul>
</div>