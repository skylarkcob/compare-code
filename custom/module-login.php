<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$permalink = get_the_permalink();
?>
<div id="edd_login_form_container" class="pixelify-login">
	<form id="edd_login_form" class="edd_form" action="" method="post">
		<?php
		if ( isset( $_POST['edd_action'] ) && isset( $_POST['error'] ) ) {
			$error = $_POST['error'];

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
		?>
		<fieldset>
			<legend><?php _e( 'Log into Your Account', 'pixelify' ); ?></legend>
			<p class="edd-login-username">
				<label for="edd_user_login"><?php _e( 'Username or Email', 'pixelify' ); ?></label>
				<input name="edd_user_login" id="edd_user_login" class="edd-required edd-input" type="text">
			</p>

			<p class="edd-login-password">
				<label for="edd_user_pass"><?php _e( 'Password', 'pixelify' ); ?></label>
				<input name="edd_user_pass" id="edd_user_pass" class="edd-password edd-required edd-input"
				       type="password">
			</p>

			<p class="edd-login-remember">
				<label><input name="rememberme" type="checkbox" id="rememberme"
				              value="forever"> <?php _e( 'Remember Me', 'pixelify' ); ?></label>
			</p>

			<p class="edd-login-submit">
				<input type="hidden" name="edd_redirect" value="">
				<?php
				wp_nonce_field();
				?>
				<input type="hidden" name="edd_action" value="user_login">
				<input id="edd_login_submit" type="submit" class="edd-submit"
				       value="<?php _e( 'Log In', 'pixelify' ); ?>">
			</p>

			<p class="edd-lost-password links">
				<a href="<?php echo wp_lostpassword_url(); ?>"><?php _e( 'Lost Password?', 'pixelify' ); ?></a>
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