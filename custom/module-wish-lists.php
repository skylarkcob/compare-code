<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

$create = isset( $wp_query->query_vars['create'] ) ? true : false;

$view = get_query_var( 'view' );
$edit = get_query_var( 'edit' );

$user_id = get_current_user_id();
$user    = wp_get_current_user();

$permalink = get_the_permalink();

$dashboard = Pixelify()->get_option( 'dashboard_page' );

if ( HP()->is_positive_number( $dashboard ) ) {
	$permalink = get_permalink( $dashboard );
}

$redirect = false;

$task = isset( $_GET['task'] ) ? $_GET['task'] : 'wish-lists';
?>
<div class="pixelify-dashboard <?php echo sanitize_html_class( $task ); ?>">
	<div class="user-dashboard-menu">
		<div class="menu-vendor-menu-container text-center">
			<ul id="menu-vendor-menu" class="menu list-inline list-unstyled">
				<?php
				if ( ! $create && ! HP()->is_positive_number( $edit ) && ! HP()->is_positive_number( $view ) ) {
					$task = 'collections';
				}

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
	<div class="fes-vendor-dashboard">
		<?php
		if ( $create || HP()->is_positive_number( $edit ) ) {
			$msg = '';

			$title = __( 'Create Wish Lists', 'pixelify' );

			$button_text = __( 'Create', 'pixelify' );

			$done = false;

			$deleted = false;

			if ( HP()->is_positive_number( $edit ) ) {
				$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

				if ( 'delete' === $action ) {
					$title = __( 'Delete Wish Lists', 'pixelify' );
					$done  = wp_delete_post( $edit, true );

					if ( $done ) {
						$msg     = __( 'Wish list has been deleted successfully.', 'pixelify' );
						$deleted = true;
					}
				} else {
					$title       = __( 'Edit Wish Lists', 'pixelify' );
					$button_text = __( 'Update', 'pixelify' );
				}
			}
			?>
			<h2><?php echo $title; ?></h2>

			<div class="edd-wl-create">
				<?php
				$post_title   = isset( $_POST['list-title'] ) ? $_POST['list-title'] : '';
				$post_title   = wp_strip_all_tags( $post_title );
				$post_content = isset( $_POST['list-description'] ) ? $_POST['list-description'] : '';
				$post_status  = isset( $_POST['privacy'] ) ? $_POST['privacy'] : 'private';

				if ( isset( $_POST['submit'] ) ) {

					if ( empty( $post_title ) ) {
						?>
						<p class="alert alert-error alert-danger">
							<?php _e( 'You need to enter a title.', 'pixelify' ); ?>
						</p>
						<?php
					} else {
						$data = array(
							'post_author'  => $user_id,
							'post_status'  => $post_status,
							'post_content' => $post_content,
							'post_title'   => $post_title,
							'post_type'    => 'collection'
						);

						if ( HP()->is_positive_number( $edit ) ) {
							$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

							if ( 'delete' !== $action ) {
								$data['ID'] = $edit;
								$done       = wp_update_post( $data );
							}
						} else {
							$done = wp_insert_post( $data );
						}

						if ( $done ) {
							if ( empty( $msg ) ) {
								$msg = __( 'Wish list has been created successfully.', 'pixelify' );

								if ( HP()->is_positive_number( $edit ) ) {
									$msg = __( 'Wish list has been updated successfully.', 'pixelify' );
								}
							}
							?>
							<p class="alert alert-success">
								<?php echo $msg; ?>
							</p>
							<?php
							$redirect = true;
						}
					}
				} else {
					if ( HP()->is_positive_number( $edit ) && 'delete' !== $action ) {
						$collection = get_post( $edit );

						if ( empty( $post_title ) ) {
							$post_title = $collection->post_title;
						}

						if ( empty( $post_content ) ) {
							$post_content = $collection->post_content;
						}

						$post_status = $collection->post_status;
					}
				}

				if ( $deleted && ! empty( $msg ) ) {
					?>
					<p class="alert alert-success">
						<?php echo $msg; ?>
					</p>
					<?php
					$redirect = true;
				}

				if ( ! $deleted && ! $done ) {
					?>
					<form action="" class="wish-list-form" method="post">
						<p>
							<label for="list-title"><?php _e( 'Title:', 'pixelify' ); ?></label>
							<input type="text" name="list-title" id="list-title" value="<?php echo $post_title; ?>">
						</p>

						<p>
							<label for="list-description"><?php _e( 'Description:', 'pixelify' ); ?></label>
						<textarea name="list-description" id="list-description" rows="3"
						          cols="30"><?php echo $post_content; ?></textarea>
						</p>

						<p>
							<label for="privacy">
								<select name="privacy">
									<option
										value="private"<?php selected( 'private', $post_status ); ?>><?php _e( 'Private - only viewable by you', 'pixelify' ); ?></option>
									<option
										value="publish"<?php selected( 'publish', $post_status ); ?>><?php _e( 'Public - viewable by anyone', 'pixelify' ); ?></option>
								</select>
							</label>
						</p>
						<p>
							<input type="submit" value="<?php echo $button_text; ?>" class="button"
							       name="submit">
						</p>
						<?php
						wp_create_nonce();

						if ( HP()->is_positive_number( $edit ) ) {
							?>
							<p>
								<a href="?action=delete" class="edd-wl-delete-list"
								   title="<?php _e( 'Delete wish list', 'pixelify' ); ?>"><?php _e( 'Delete wish list', 'pixelify' ); ?></a>
							</p>
							<?php
						}
						?>
					</form>
					<?php
				}
				?>
			</div>
			<?php
		} elseif ( HP()->is_positive_number( $view ) ) {
			$collection = get_post( $view );

			if ( Pixelify()->is_collection( $collection ) ) {
				$childs = get_post_meta( $view, 'childs', true );

				if ( ! HP()->array_has_value( $childs ) ) {
					?>
					<p class="alert alert-info">
						<?php _e( 'Nothing here yet, how about adding some post?', 'pixelify' ); ?>
					</p>
					<?php
				}
				?>
				<div class="entry-content">
					<?php
					$content = apply_filters( 'the_content', $collection->post_content );
					echo $content;
					?>
				</div>
				<?php
				if ( HP()->array_has_value( $childs ) ) {
					$childs = array_map( 'get_post', $childs );

					?>
					<ul class="collection-posts edd-wish-list">
						<?php
						foreach ( $childs as $obj ) {
							if ( $obj instanceof WP_Post ) {
								?>
								<li class="wl-row">
									<a class="edd-wl-item-title" href="<?php echo get_permalink( $obj ); ?>"
									   title="<?php echo $obj->post_title; ?>"><?php echo $obj->post_title; ?></a>
									<a data-collection="<?php echo $collection->ID; ?>"
									   data-id="<?php echo $obj->ID; ?>" href="#"
									   class="edd-remove-from-wish-list edd-wl-item-remove"
									   title="<?php _e( 'Remove', 'pixelify' ); ?>">
										<i class="glyphicon glyphicon-remove"></i>
										<span class="hide-text"><?php _e( 'Remove', 'pixelify' ); ?></span>
									</a>
								</li>
								<?php
							}
						}
						?>
					</ul>
					<?php
				}
				?>
				<p class="">
					<a href="<?php echo get_edit_post_link( $collection ); ?>"
					   title="<?php _e( 'Edit settings', 'pixelify' ); ?>"><?php _e( 'Edit settings', 'pixelify' ); ?></a>
				</p>
				<?php
			} else {
				$redirect = true;
			}
		} else {
			$data = isset( $tasks[ $task ] ) ? $tasks[ $task ] : '';

			if ( isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
				call_user_func( $data['callback'] );
			}
		}
		?>
	</div>
	<?php
	if ( $redirect ) {
		?>
		<script>
			setTimeout(function () {
				window.location.href = "<?php the_permalink(); ?>";
			}, 2000);
		</script>
		<?php
	}
	?>
</div>
