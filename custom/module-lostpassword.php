<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$permalink = get_the_permalink();
?>
<div id="edd_lostpassword_form_container" class="pixelify-lostpassword">
	<form id="lostpasswordform" action="" method="post" class="account-page-form">
		<?php
		if ( isset( $_POST['action'] ) && 'somfrp_lost_pass' == $_POST['action'] ) {
			$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

			if ( wp_verify_nonce( $nonce ) ) {
				$info = isset( $_POST['somfrp_user_info'] ) ? $_POST['somfrp_user_info'] : '';

				$_POST['user_login'] = $info;

				if ( ! function_exists( 'retrieve_password' ) ) {
					function retrieve_password() {
						$errors = new WP_Error();

						if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
							$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or email address.', 'pixelify' ) );
						} elseif ( strpos( $_POST['user_login'], '@' ) ) {
							$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
							if ( empty( $user_data ) ) {
								$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.', 'pixelify' ) );
							}
						} else {
							$login     = trim( $_POST['user_login'] );
							$user_data = get_user_by( 'login', $login );
						}

						/**
						 * Fires before errors are returned from a password reset request.
						 *
						 * @since 2.1.0
						 * @since 4.4.0 Added the `$errors` parameter.
						 *
						 * @param WP_Error $errors A WP_Error object containing any errors generated
						 *                         by using invalid credentials.
						 */
						do_action( 'lostpassword_post', $errors );

						if ( $errors->get_error_code() ) {
							return $errors;
						}

						if ( ! $user_data ) {
							$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or email.', 'pixelify' ) );

							return $errors;
						}

						// Redefining user_login ensures we return the right case in the email.
						$user_login = $user_data->user_login;
						$user_email = $user_data->user_email;
						$key        = get_password_reset_key( $user_data );

						if ( is_wp_error( $key ) ) {
							return $key;
						}

						if ( is_multisite() ) {
							$site_name = get_network()->site_name;
						} else {
							/*
							 * The blogname option is escaped with esc_html on the way into the database
							 * in sanitize_option we want to reverse this for the plain text arena of emails.
							 */
							$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
						}

						$message = __( 'Someone has requested a password reset for the following account:', 'pixelify' ) . "\r\n\r\n";
						/* translators: %s: site name */
						$message .= sprintf( __( 'Site Name: %s', 'pixelify' ), $site_name ) . "\r\n\r\n";
						/* translators: %s: user login */
						$message .= sprintf( __( 'Username: %s', 'pixelify' ), $user_login ) . "\r\n\r\n";
						$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'pixelify' ) . "\r\n\r\n";
						$message .= __( 'To reset your password, visit the following address:', 'pixelify' ) . "\r\n\r\n";
						$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

						/* translators: Password reset email subject. %s: Site name */
						$title = sprintf( __( '[%s] Password Reset', 'pixelify' ), $site_name );

						/**
						 * Filters the subject of the password reset email.
						 *
						 * @since 2.8.0
						 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
						 *
						 * @param string $title Default email title.
						 * @param string $user_login The username for the user.
						 * @param WP_User $user_data WP_User object.
						 */
						$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

						/**
						 * Filters the message body of the password reset mail.
						 *
						 * If the filtered message is empty, the password reset email will not be sent.
						 *
						 * @since 2.8.0
						 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
						 *
						 * @param string $message Default mail message.
						 * @param string $key The activation key.
						 * @param string $user_login The username for the user.
						 * @param WP_User $user_data WP_User object.
						 */
						$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

						if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
							$errors->add( 'email_not_sent', __( 'The email could not be sent.', 'pixelify' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function.', 'pixelify' ) );

							return $errors;
						}

						return true;
					}
				}

				$result = retrieve_password();

				if ( $result instanceof WP_Error ) {
					$error = $result->get_error_messages();

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
				} else {
					?>
					<p class="alert alert-success"><?php _e( 'Please check your email to reset new password.', 'pixelify' ); ?></p>
					<?php
				}
			}
		}
		?>
		<fieldset>
			<legend><?php _e( 'Reset Password', 'pixelify' ); ?></legend>

			<div class="somfrp-lost-pass-form-text">
				<p class="extra-space"><?php _e( 'Please enter your email address or username. You will receive a link to create a new password via email.', 'pixelify' ); ?></p>
			</div>

			<p class="no-margin">
				<label for="email"><?php _e( 'Email Address or Username', 'pixelify' ); ?></label>
				<input type="text" name="somfrp_user_info" id="somfrp_user_info">
			</p>

			<div class="lostpassword-submit">
				<?php wp_nonce_field(); ?>
				<input type="hidden" name="submitted" id="submitted" value="true">
				<input type="hidden" name="action" id="somfrp_post_action" value="somfrp_lost_pass">

				<p>
					<button type="submit" id="reset-pass-submit" name="reset-pass-submit" class="button big-btn">
						<?php _e( 'Reset Password', 'pixelify' ); ?>
					</button>
				</p>
			</div>
			<p class="links">
				<a href="<?php echo wp_login_url(); ?>"><?php _e( 'Login', 'pixelify' ); ?></a>
				<?php
				if ( Pixelify()->users_can_register() ) {
					?>
					<a href="<?php echo wp_registration_url(); ?>"><?php _e( 'Register', 'pixelify' ); ?></a>
					<?php
				}
				?>
			</p>
		</fieldset>
	</form>
</div>