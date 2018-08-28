<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$permalink = get_the_permalink();

$task = isset( $_GET['task'] ) ? $_GET['task'] : 'products';
?>
<div class="pixelify-dashboard <?php echo sanitize_html_class( $task ); ?>">
	<div class="user-dashboard-menu">
		<div class="menu-vendor-menu-container text-center">
			<ul id="menu-vendor-menu" class="menu list-inline list-unstyled">
				<?php
				$tasks = Pixelify()->dashboard_tasks();

				foreach ( $tasks as $key => $data ) {
					$url = add_query_arg( 'task', $key, $permalink );

					$class = 'menu-item menu-item-type-custom menu-item-object-custom';

					if ( $key == $task ) {
						$class .= ' current-menu-item active';
					}
					?>
					<li class="<?php echo $class; ?>">
						<a href="<?php echo esc_url( $url ); ?>"><?php echo $data['label']; ?></a>
					</li>
					<?php
				}
				?>
				<li class="menu-item menu-item-type-custom menu-item-object-custom">
					<a href="<?php echo wp_logout_url(); ?>"><?php _e( 'Sign out', 'pixelify' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
	<?php
	if ( 'edit-product' == $task ) {
		include Pixelify()->get_basedir() . '/custom/module-edit-product.php';
	} else {
		$data = isset( $tasks[ $task ] ) ? $tasks[ $task ] : '';

		if ( isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
			call_user_func( $data['callback'] );
		}
	}
	?>
</div>