<?php

namespace WPDiscourse\Export;

use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

class DiscourseExport {
	protected $option_key = 'wpde_options';
	protected $options;

	protected $wpde_options = array(
		'wpde_export_users' => 1,
		'wpde_export_users_are_active' => 1 ,
		'wpde_export_users_are_approved' => 1 ,
		'wpde_export_users_throttle' => 100 ,
		'wpde_export_users_whoa' => 5000 ,
		'wpde_export_posts' => 1,
		'wpde_export_posts_throttle' => 200 ,
		'wpde_export_posts_whoa' => 10000 ,
		'wpde_export_comments' => 1,
		'wpde_export_comments_throttle' => 300 ,
		'wpde_export_comments_whoa' => 15000 
	);

	public function __construct() {
		add_action( 'init', array( $this, 'initialize_plugin' ) );
		add_filter( 'wpdc_utilities_options_array', array( $this, 'add_options' ) );
	}

	public function initialize_plugin() {
		add_option( 'wpde_options', $this->wpde_options );
		$this->options = DiscourseUtilities::get_options();
	}

	/**
	 * Hooks into 'wpdc_utilities_options_array'.
	 *
	 * This function merges the plugins options with the options array that is created in
	 * WPDiscourse\Utilities\Utilities::get_options. Doing this makes it possible to use the FormHelper function in the plugin.
	 * If you aren't using the FormHelper function, there is no need to do this.
	 *
	 * @param array $wpdc_options The unfiltered Discourse options.
	 *
	 * @return array
	 */
	public function add_options( $wpde_options ) {
		static $merged_options = [];

		if ( empty( $merged_options ) ) {
			$added_options = get_option( $this->option_key );
			if ( is_array( $added_options ) ) {
				$merged_options = array_merge( $wpde_options, $added_options );
			} else {
				$merged_options = $wpde_options;
			}
		}

		return $merged_options;
	}
}