<div class="<?php hocwp_wrap_class(); ?>">
	<?php
	hocwp_theme_get_module( 'search-cat' );
	hocwp_article_before();
	the_title( '<h1>', '</h1>' );
	hocwp_entry_content();
	$categories = get_categories( array( 'hide_empty' => false ) );
	$licenses   = get_terms( array( 'hide_empty' => false, 'taxonomy' => 'license' ) );
	$email      = '';
	if ( is_user_logged_in() ) {
		$user  = wp_get_current_user();
		$email = $user->user_email;
	}
	$donate      = '';
	$category    = '';
	$note        = '';
	$license     = '';
	$license_url = '';
	$website     = '';
	$designer    = '';
	$name        = '';
	$messages    = array();
	$inserted    = false;
	if ( isset( $_POST['submitme'] ) ) {
		$email = $_POST['authorEmail'];
		if ( ! is_email( $email ) ) {
			$messages[] = '<p class="alert alert-danger">' . __( 'Invalid email address.', 'hocwp-theme' ) . '</p>';
		} else {
			$name = $_POST['fontName'];
			if ( empty( $name ) ) {
				$messages[] = '<p class="alert alert-danger">' . __( 'Please enter font name.', 'hocwp-theme' ) . '</p>';
			} else {
				$designer = $_POST['fontDesigner'];
				if ( empty( $designer ) ) {
					$messages[] = '<p class="alert alert-danger">' . __( 'Please enter designer name.', 'hocwp-theme' ) . '</p>';
				} else {
					$category = $_POST['fontCategories'];
					if ( empty( $category ) ) {
						$messages[] = '<p class="alert alert-danger">' . __( 'Please choose a category.', 'hocwp-theme' ) . '</p>';
					} else {
						$uploadedfile = $_FILES['fontFile'];
						$info         = pathinfo( isset( $uploadedfile['name'] ) ? $uploadedfile['name'] : '' );
						if ( 'zip' != $info['extension'] ) {
							$messages[] = '<p class="alert alert-danger">' . __( 'Please upload zip file only.', 'hocwp-theme' ) . '</p>';
						} else {
							$note        = $_POST['designersNote'];
							$license     = $_POST['fontLicense'];
							$license_url = $_POST['urlProfile'];
							$website     = $_POST['urlWebsite'];
							$donate      = $_POST['authorDonate'];
							if ( ! function_exists( 'wp_handle_upload' ) ) {
								require_once( ABSPATH . 'wp-admin/includes/file.php' );
							}
							$upload_overrides = array( 'test_form' => false );
							$movefile         = wp_handle_upload( $uploadedfile, $upload_overrides );
							if ( $movefile && ! isset( $movefile['error'] ) ) {
								$dir_info      = wp_upload_dir();
								$file          = $movefile['file'];
								$filename      = basename( $file );
								$file_contents = array();
								$file_url      = trailingslashit( $dir_info['url'] ) . $filename;
								$attachment_id = hocwp_theme_custom_generate_media_id( $file, $file_url );
								if ( ! is_wp_error( $attachment_id ) ) {
									$file_contents['id']  = $attachment_id;
									$file_contents['url'] = wp_get_attachment_url( $attachment_id );
								}
								$user = get_user_by( 'email', $email );
								if ( ! ( $user instanceof WP_User ) ) {
									$pass = wp_generate_password();
									$data = array(
										'user_login' => $email,
										'user_email' => $email,
										'user_pass'  => $pass
									);
									$user = wp_insert_user( $data );
									if ( $user instanceof WP_User ) {
										if ( empty( $designer ) ) {
											update_user_meta( $user->ID, 'donate', $donate );
										}
										wp_send_new_user_notifications( $user->ID, 'user' );
									}
								}
								if ( ! is_array( $category ) ) {
									$category = array( $category );
								}
								$data = array(
									'post_title'    => $name,
									'post_status'   => 'pending',
									'post_content'  => $note,
									'post_category' => $category
								);
								if ( $user instanceof WP_User ) {
									$data['post_author'] = $user->ID;
								}
								$post_id = wp_insert_post( $data );
								if ( hocwp_id_number_valid( $post_id ) ) {
									update_post_meta( $post_id, 'donate', $donate );
									update_post_meta( $post_id, 'website', $website );
									$term = get_term_by( 'name', $designer, 'designer' );
									if ( ! $term instanceof WP_Term ) {
										$added = wp_insert_term( $designer, 'designer' );
										if ( ! is_wp_error( $added ) || isset( $added['term_id'] ) ) {
											$designer = $added['term_id'];
											update_term_meta( $designer, 'donate', $donate );
										}
									} else {
										$designer = $term->term_id;
									}
									wp_set_post_terms( $post_id, $designer, 'designer', true );
									wp_set_post_terms( $post_id, $license, 'license', true );
									update_post_meta( $post_id, 'commercial_license', $license_url );
									update_post_meta( $post_id, 'file_contents', $file_contents );
									$demo       = hocwp_theme_custom_add_demo_from_file_contents( $post_id, $file_contents );
									$messages[] = '<p class="alert alert-success">' . __( 'Your font is saved successfully.', 'hocwp-theme' ) . '</p>';
									$inserted   = true;
								}
							} else {
								$messages[] = '<p class="alert alert-danger">' . $movefile['error'] . '</p>';
							}
						}
					}
				}
			}
		}
	}
	?>
	<div class="submitFontForm">
		<?php
		if ( hocwp_array_has_value( $messages ) ) {
			foreach ( $messages as $msg ) {
				echo $msg;
			}
		}
		if ( ! $inserted ) {
			?>
			<form name="submitFont" id="submitFont" method="POST" action="" enctype="multipart/form-data">
				<label for="authorEmail"><?php _e( 'Your Email Address:', 'hocwp-theme' ); ?></label>

				<div>
					<input name="authorEmail" id="authorEmail" value="<?php echo esc_attr( $email ); ?>" type="email">
				</div>
				<label for="authorDonate"><?php _e( 'Donate link:', 'hocwp-theme' ); ?></label>

				<div>
					<input name="authorDonate" id="authorDonate" value="<?php echo esc_attr( $donate ); ?>" type="text">
				</div>
				<label for="fontName"><?php _e( 'Font Name:', 'hocwp-theme' ); ?></label>

				<div>
					<input name="fontName" id="fontName" value="<?php echo esc_attr( $name ); ?>" type="text">
				</div>
				<label for="fontDesigner"><?php _e( 'Font Designer:', 'hocwp-theme' ); ?></label>

				<div>
					<input name="fontDesigner" id="fontDesigner" value="<?php echo esc_attr( $designer ); ?>"
					       type="text">
				</div>
				<label for="urlWebsite"><?php _e( 'Website Url (optional):', 'hocwp-theme' ); ?></label>

				<div>
					<input name="urlWebsite" id="urlWebsite" value="<?php echo esc_attr( $website ); ?>" type="url">
				</div>
				<label
					for="fontCategories"><?php _e( 'Suggested Categories (hold ctrl to select multiple)', 'hocwp_theme' ); ?></label>

				<div>
					<select name="fontCategories[]" id="fontCategories" multiple="" size="12">
						<?php
						foreach ( $categories as $term ) {
							if ( is_array( $category ) ) {
								if ( in_array( $term->term_id, $category ) ) {
									?>
									<option
										value="<?php echo $term->term_id; ?>"
										selected="selected"><?php echo $term->name; ?></option>
									<?php
								} else {
									?>
									<option
										value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
									<?php
								}
							} else {
								?>
								<option
									value="<?php echo $term->term_id; ?>" <?php selected( $license, $term->term_id ); ?>><?php echo $term->name; ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<label for="fontFile"><?php _e( 'Font Archive (zip only)', 'hocwp-theme' ); ?></label>

				<div>
					<input name="fontFile" id="fontFile" type="file">
				</div>
				<label for="fontLicense"><?php _e( 'Font License', 'hocwp-theme' ); ?></label>

				<div>
					<select name="fontLicense" id="fontLicense">
						<?php
						foreach ( $licenses as $term ) {
							?>
							<option
								value="<?php echo $term->term_id; ?>" <?php selected( $license, $term->term_id ); ?>><?php echo $term->name; ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<label for="urlProfile"><?php _e( 'Commerial License Url (optional):', 'hocwp-theme' ); ?></label>

				<div>
					<input name="urlProfile" id="urlProfile" value="<?php echo esc_attr( $license_url ); ?>" type="url">
				</div>
				<label for="designersNote"><?php _e( 'Designer Note (optional):', 'hocwp-theme' ); ?></label>

				<div>
					<textarea name="designersNote" id="designersNote"><?php echo $note; ?></textarea>
				</div>
				<label for="submitButton">&nbsp;</label>

				<div>
					<input name="submitme" id="submitme" value="1" type="hidden">
					<input name="submitButton" class="submitButton" id="submitButton"
					       value="<?php echo esc_attr( __( 'submit font', 'hocwp-theme' ) ); ?>" type="submit">
				</div>
			</form>
			<?php
		}
		?>
	</div>
	<?php
	hocwp_article_after();
	?>
</div>