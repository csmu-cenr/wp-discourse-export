<?php

namespace WPDiscourse\Export;

use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

class Exporter {

	protected $options;

	public function __construct() {
		add_action( 'init', array( $this, 'setup_options' ), 10 );
		add_action( 'init', array( $this, 'export_users' ) );
	}

	public function setup_options() {
		$this->options = DiscourseUtilities::get_options();
	}

	public function export_users() {
		if ( ! empty( $this->options['wpde_export_users'] ) ) {
			$users = get_users();

			foreach( $users as $user ) {
				$this->create_discourse_user( $user );
			}

			// Only run this once. Will probably need to be run as a background task, and rate limited.
			$wpde_options = get_option( 'wpde_options' );
			$wpde_options['wpde_export_users'] = 0;

			update_option( 'wpde_options', $wpde_options );
		}
	}

	protected function create_discourse_user( $wp_user ) {
		$base_url = ! empty( $this->options['url'] ) ? $this->options['url'] : null;
		$api_key = ! empty( $this->options['api-key']) ? $this->options['api-key'] : null;
		$api_username = ! empty( $this->options['publish-username']) ? $this->options['publish-username'] : null;

		if ( empty( $base_url ) || empty( $api_key ) || empty( $api_username ) ) {

			return new \WP_Error( 'wpdc_configuration_error', 'The WP Discourse plugin is not configured.' );
		}

		$username = $wp_user->user_login;
		$name = $wp_user->display_name;
		$email = $wp_user->user_email;
		$password = wp_generate_password( 20 );
		$create_user_url = esc_url_raw( $base_url . '/users' );
		$response = wp_remote_post( $create_user_url, array(
			'method' => 'POST',
			'body' => array(
				'api_key' => $api_key,
				'api_username' => $api_username,
				'name' => $name,
				'email' => $email,
				'password' => $password,
				'username' => $username,

			),
		));

		if ( ! DiscourseUtilities::validate( $response ) ) {

			return new \WP_Error( 'wpde_invalid_response', 'An error was returned from Discourse after attempting to create a user.' );
		}

		$user_data = json_decode( wp_remote_retrieve_body( $response ), true );

		// Do something.
	}
}