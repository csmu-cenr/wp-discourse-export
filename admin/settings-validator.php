<?php

namespace WPDiscourse\Export;

class SettingsValidator {
	
	public function __construct() {
		
		add_filter( 'wpdc_validate_wpde_export_users', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_users_are_active', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_users_are_approved', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_users_throttle', array( $this, 'validate_number' ) );
		add_filter( 'wpdc_validate_wpde_export_users_whoa', array( $this, 'validate_number' ) );		
		
		add_filter( 'wpdc_validate_wpde_export_posts', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_posts_throttle', array( $this, 'validate_number' ) );
		add_filter( 'wpdc_validate_wpde_export_posts_whoa', array( $this, 'validate_number' ) );		
		
		add_filter( 'wpdc_validate_wpde_export_comments', array( $this, 'validate_checkbox' ) );
		add_filter( 'wpdc_validate_wpde_export_comments_throttle', array( $this, 'validate_number' ) );
		add_filter( 'wpdc_validate_wpde_export_comments_whoa', array( $this, 'validate_number' ) );		

	}

	public function validate_checkbox( $input ) {
		return 1 === intval( $input ) ? 1 : 0;
	}
	
	public function validate_number( $input ) {
		return (is_numeric($input) && $input > 0 && $input == round($input, 0)) ;
	}
	
	
}