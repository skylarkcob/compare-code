<?php
if ( hocwp_is_login_page() ) {
	add_action( 'login_form', 'hocwp_setup_theme_social_login_button' );
	add_action( 'register_form', 'hocwp_setup_theme_social_login_button' );
}