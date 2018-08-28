<?php
if ( ! function_exists( 'hocwp_plugin_upgrader_process_complete' ) ) {
	function hocwp_plugin_upgrader_process_complete( $upgrader, $options ) {
		$type = hocwp_get_value_by_key( $options, 'type' );
		if ( 'plugin' == $type ) {
			$plugins = hocwp_get_value_by_key( $options, 'plugins' );
			if ( ! hocwp_array_has_value( $plugins ) ) {
				return;
			}
			foreach ( $plugins as $plugin ) {
				$slug           = hocwp_get_plugin_slug_from_file_path( $plugin );
				$transient_name = 'hocwp_plugins_api_' . $slug . '_plugin_information';
				$transient_name = hocwp_sanitize_id( $transient_name );
				delete_transient( $transient_name );
			}
		}
	}
}

add_action( 'hocwp_plugin_upgrader_process_complete', 'hocwp_plugin_upgrader_process_complete', 10, 2 );

function hocwp_plugin_add_option_to_sidebar_tab( $option ) {
	global $hocwp;
	$hocwp->plugin->core->add_option_to_sidebar_tab( $option );
}

require_once( HOCWP_PLUGIN_CORE_ADMIN_PATH . '/options/plugin-option.php' );