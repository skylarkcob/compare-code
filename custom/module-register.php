<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$permalink = get_the_permalink();
?>
<div class="pixelify-register">
	<div class="register-form">
		<h2><?php _e( 'Sign up', 'pixelify' ); ?></h2>

		<form id="edd_register_form" class="edd_form" action="" method="post">
			<?php
			wp_nonce_field();

			$user_login = '';
			$user_email = '';

			$added = false;

			if ( isset( $_POST['edd_action'] ) && 'user_register' == $_POST['edd_action'] ) {
				$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

				if ( wp_verify_nonce( $nonce ) ) {
					$errors = new WP_Error();

					$user_login = isset( $_POST['edd_user_login'] ) ? $_POST['edd_user_login'] : '';

					$user_email = isset( $_POST['edd_user_email'] ) ? $_POST['edd_user_email'] : '';

					$user_pass = isset( $_POST['edd_user_pass'] ) ? $_POST['edd_user_pass'] : '';

					$pass2 = isset( $_POST['edd_user_pass2'] ) ? $_POST['edd_user_pass2'] : '';

					if ( empty( $user_login ) || empty( $user_email ) || empty( $user_pass ) ) {
						$errors->add( 'missing_field', __( 'Please enter your information.', 'pixelify' ) );
					} elseif ( ! is_email( $user_email ) ) {
						$errors->add( 'invalid_email', __( 'Invalid email.', 'pixelify' ) );
					} elseif ( $user_pass != $pass2 ) {
						$errors->add( 'invalid_password', __( 'Passwords do not match!', 'pixelify' ) );
					} elseif ( username_exists( $user_login ) || email_exists( $user_email ) ) {
						$errors->add( 'exists', __( 'Username or email exists!', 'pixelify' ) );
					} else {
						$data = array(
							'user_login' => $user_login,
							'user_email' => $user_email,
							'user_pass'  => $user_pass
						);

						$errors = wp_insert_user( $data );

						if ( $errors && ! ( $errors instanceof WP_Error ) ) {
							$added = true;
						}
					}

					if ( $errors instanceof WP_Error ) {
						$error = $errors->get_error_messages();

						if ( is_array( $error ) ) {
							foreach ( $error as $err ) {
								?>
								<p class="alert alert-error alert-danger"><?php echo $err; ?></p>
								<?php
							}
						} else {
							?>
							<p class="alert alert-error alert-danger"><?php echo $error; ?></p>
							<?php
						}
					}
				}
			}

			if ( $added ) {
				?>
				<p class="alert alert-success"><?php _e( 'Your account has been created successfully.', 'pixelify' ); ?></p>
				<script>
					setTimeout(function () {
						window.location.href = "<?php echo wp_login_url(); ?>";
					}, 2000);
				</script>
				<?php
			}

			if ( ! $added ) {
				?>
				<fieldset>
					<legend><?php _e( 'Register New Account', 'pixelify' ); ?></legend>
					<p>
						<label for="edd-user-login"><?php _e( 'Username', 'pixelify' ); ?></label>
						<input id="edd-user-login" class="required edd-input" type="text" name="edd_user_login"
						       value="<?php echo $user_login; ?>">
					</p>

					<p>
						<label for="edd-user-email"><?php _e( 'Email', 'pixelify' ); ?></label>
						<input id="edd-user-email" class="required edd-input" type="email" name="edd_user_email"
						       value="<?php echo $user_email; ?>">
					</p>

					<p>
						<label for="edd-user-pass"><?php _e( 'Password', 'pixelify' ); ?></label>
						<input id="edd-user-pass" class="password required edd-input" type="password"
						       name="edd_user_pass">
					</p>

					<p>
						<label for="edd-user-pass2"><?php _e( 'Confirm Password', 'pixelify' ); ?></label>
						<input id="edd-user-pass2" class="password required edd-input" type="password"
						       name="edd_user_pass2">
					</p>

					<p>
						<input type="hidden" name="edd_honeypot" value="">
						<input type="hidden" name="edd_action" value="user_register">
						<input type="hidden" name="edd_redirect" value="">
						<input class="edd-submit" name="edd_register_submit" type="submit"
						       value="<?php _e( 'Register', 'pixelify' ); ?>">
					</p>
				</fieldset>
				<?php
			}
			?>
		</form>
		<?php
		if ( ! $added ) {
			?>
			<div class="register-form-info">
				<?php
				$page_id = get_option( 'wp_page_for_privacy_policy' );

				if ( HP()->is_positive_number( $page_id ) ) {
					$page = get_post( $page_id );

					if ( $page instanceof WP_Post ) {
						?>
						<p>
							<?php printf( __( 'By creating an account, you agree to <a href="%s">terms and privacy policy.</a>', 'pixelify' ), get_permalink( $page ) ); ?>
						</p>
						<?php
					}
				}
				?>
				<p>
					<?php printf( __( 'Alredy have an account? <a href="%s">Sign in</a>', 'pixelify' ), wp_login_url() ); ?>
				</p>
			</div>
			<?php
		}
		?>
		<!--/register-form-info-->
	</div>
</div>