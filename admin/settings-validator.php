<?php

namespace WPDiscourse\Export;

class SettingsValidator {
	public function __construct() {
		add_filter( 'wpdc_validate_wpde_export_users', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_posts', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_comments', array( $this, 'validate_checkbox' ) );
	}

	public function validate_checkbox( $input ) {
		return 1 === intval( $input ) ? 1 : 0;
	}
}