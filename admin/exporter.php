<?php

namespace WPDiscourse\Export;

use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

class Exporter {

	protected $options;

	public function __construct() {
		add_action( 'init', array( $this, 'setup_options' ), 10 );
		add_action( 'init', array( $this, 'export_users' ), 15 );
		add_action( 'init', array( $this, 'export_posts' ), 20 );
		add_action( 'init', array( $this, 'export_comments' ), 25 );
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

	public function export_posts() {
		if ( ! empty( $this->options['wpde_export_posts'] ) ) {
			$args = array(
				'numberposts' => -1,
			);
			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				update_post_meta( $post->ID, 'publish_to_discourse', 1 );
				wp_update_post( $post );
			}

			// Only run this once. Will probably need to be run as a background task, and rate limited.
			$wpde_options = get_option( 'wpde_options' );
			$wpde_options['wpde_export_posts'] = 0;

			update_option( 'wpde_options', $wpde_options );

		}
	}

	public function export_comments() {
		if ( ! empty( $this->options['wpde_export_comments'] ) ) {
			$api_key = $this->options['api-key'];
			$base_url = $this->options['url'];
			$api_username = $this->options['publish-username'];
			$post_args = array(
				'numberposts' => -1,
			);
			$posts = get_posts( $post_args );
			foreach ( $posts as $post ) {
				$comment_args = array( 'post_id' => $post->ID, 'order' => 'ASC' );
				$comments = get_comments( $comment_args );
				$topic_id = get_post_meta( $post->ID, 'discourse_topic_id', true );
				$post_url = $base_url . "/posts";
				$comment_post_ids = [];
				foreach ( $comments as $comment ) {
				//write_log( 'comment', $comment );
					$raw = $comment->comment_content;
					$author_email = $comment->comment_author_email;
					$author = get_user_by( 'email', $author_email);
					$comment_id = $comment->comment_ID;
					// This needs to resolve multi-level comments down to 2 threads.
					$comment_parent = $comment->comment_parent;
					$reply_to_post_number = ! empty( $comment_post_ids[ $comment_parent ] ) ? $comment_post_ids[ $comment_parent ] : null;
					//write_log('reply to post number', $reply_to_post_number);
					$username = ! empty( $author->user_login ) ? $author->user_login : 'system';
					$user_id = $this->get_discourse_user( $author );


					$api_key_url = $base_url . "/admin/users/{$user_id}/generate_api_key";
					$data = array(
						'api_key' => $api_key,
						'api_username' => $api_username,
					);
					$post_options = array(
						'timeout' => 30,
						'method' => 'POST',
						'body' => http_build_query( $data ),
					);

					$result = wp_remote_post( $api_key_url, $post_options );
					$response_body = json_decode( wp_remote_retrieve_body( $result ) );
					// This needs to be saved. Maybe not to the database, but at least for the duration of the foreach loop.
					// If the user can't be found, publish as system?
					$user_api_key = $response_body->api_key->key;

					$data = array(
						'api_key' => $user_api_key,
						'api_username' => $username,
						'topic_id' => $topic_id,
						'raw' => $raw,
						'reply_to_post_number' => $reply_to_post_number,
					);
					$post_options = array(
						'timeout' => 30,
						'method' => 'POST',
						'body' => http_build_query( $data ),
					);

					$result = wp_remote_post( $post_url, $post_options );
					$response_body = json_decode( wp_remote_retrieve_body( $result ) );
				//	write_log( 'discourse post response', $response_body );
					if ( DiscourseUtilities::validate( $result ) ) {
						//$discourse_post_id = $response_body->id;
						$post_number = $response_body->post_number;

						$comment_post_ids[ $comment_id ] = $post_number;
					}
				}
			}

			$wpde_options = get_option( 'wpde_options' );
			$wpde_options['wpde_export_comments'] = 0;

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
		//write_log( 'user data', $user_data );

		// Do something.
	}

	protected function get_discourse_user( $user ) {
		$base_url     = $this->options['url'];
		$api_key      = $this->options['api-key'];
		$api_username = $this->options['publish-username'];
		if ( ! $base_url || ! $api_key || ! $api_username ) {
			return new \WP_Error( 'discourse_configuration_error', 'The WP Discourse plugin has not been properly configured.' );
		}
		// Try to get the user by external_id.
		$external_user_url = esc_url_raw( $base_url . "/users/by-external/$user->ID.json" );
		$external_user_url = add_query_arg( array(
			'api_key'      => $api_key,
			'api_username' => $api_username,
		), $external_user_url );
		$response          = wp_remote_get( $external_user_url );
		if ( DiscourseUtilities::validate( $response ) ) {
			$user_data = json_decode( wp_remote_retrieve_body( $response ), true );
			return $user_data['user']['id'];
		}
		// Try to get the user by email from active.json.
		$users_url = esc_url_raw( $base_url . '/admin/users/list/active.json' );
		$users_url = add_query_arg( array(
			'filter'       => rawurlencode( $user->user_email ),
			'api_key'      => $api_key,
			'api_username' => $api_username,
		), $users_url );
		$response = wp_remote_get( $users_url );
		if ( DiscourseUtilities::validate( $response ) ) {
			$user_data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $user_data[0] ) && isset( $user_data[0]['id'] ) ) {
				return $user_data[0]['id'];
			}
		}
		// The user doesn't exist yet.
		return null;
	}


}
