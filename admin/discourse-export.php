<?php

namespace WPDiscourse\Export;

use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

class DiscourseExport {
	protected $option_key = 'wpde_options';
	protected $options;

	protected $wpde_options = array(
		'wpde_export_users' => 0,
		'wpde_export_posts' => 0,
		'wpde_export_comments' => 0,
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
	public function add_options( $wpdc_options ) {
		static $merged_options = [];

		if ( empty( $merged_options ) ) {
			$added_options = get_option( $this->option_key );
			if ( is_array( $added_options ) ) {
				$merged_options = array_merge( $wpdc_options, $added_options );
			} else {
				$merged_options = $wpdc_options;
			}
		}

		return $merged_options;
	}
}