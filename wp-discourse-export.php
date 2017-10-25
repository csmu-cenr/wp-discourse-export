<?php
/**
 * Plugin Name: WP Discourse Export
 * Version: 0.1
 */

namespace WPDiscourse\Export;

use \WPDiscourse\Admin\OptionsPage as OptionsPage;
use \WPDiscourse\Admin\FormHelper as FormHelper;

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );

function init() {
	if ( class_exists( '\WPDiscourse\Discourse\Discourse' ) ) {

		if ( is_admin() ) {
			require_once( __DIR__ . '/admin/discourse-export.php' );
			require_once( __DIR__ . '/admin/admin.php' );
			require_once( __DIR__ . '/admin/settings-validator.php' );
			require_once( __DIR__ . '/admin/exporter.php' );

			$options_page = OptionsPage::get_instance();
			$form_helper = FormHelper::get_instance();

			new DiscourseExport();
			new Admin( $options_page, $form_helper );
			new SettingsValidator();
			new Exporter();
		}
	}
}