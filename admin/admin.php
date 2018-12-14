<?php

namespace WPDiscourse\Export;

use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

class Admin {
	/**
	 * The WPDiscourse options page.
	 *
	 * Use this class to hook into the WPDiscourse options page.
	 *
	 * @access protected
	 * @var \WPDiscourse\Admin\OptionsPage
	 */
	protected $options_page;

	/**
	 * The WPDiscourse FormHelper.
	 *
	 * Use of this class is optional, it makes it simple to build common form elements and save their values
	 * to option arrays.
	 *
	 * @access protected
	 * @var \WPDiscourse\Admin\FormHelper
	 */
	protected $form_helper;

	/**
	 * An array of WP Discourse options.
	 *
	 * @access protected
	 * @var array
	 */
	protected $options;

	/**
	 * Admin constructor.
	 *
	 * @param \WPDiscourse\Admin\OptionsPage $options_page An instance of the OptionsPage class.
	 * @param \WPDiscourse\Admin\FormHelper  $form_helper An instance of the FormHelper class.
	 */
	public function __construct( $options_page, $form_helper ) {
		$this->options_page = $options_page;
		$this->form_helper = $form_helper;

		add_action( 'init', array( $this, 'setup_options' ) );
		add_action( 'admin_init', array( $this, 'register_export_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_export_page' ) );
		add_action( 'wpdc_options_page_append_settings_tabs', array( $this, 'settings_tab' ), 4, 1 );
		add_action( 'wpdc_options_page_after_tab_switch', array( $this, 'wpde_settings_fields' ) );

	}

	public function setup_options() {
		$this->options = DiscourseUtilities::get_options();
	}

	public function register_export_settings() {
		add_settings_section( 'wpde_settings_section', __( 'WP Discourse Export Settings', 'wpde' ), array(
			$this,
			'export_settings_details',
		), 'wpde_options' );

		add_settings_field( 'wpde_export_users', __( 'Export Discourse Users', 'wpde' ), array(
			$this,
			'export_users_checkbox',
		), 'wpde_options', 'wpde_settings_section' );

		add_settings_field( 'wpde_exported_users_are_active', __( 'Users are active', 'wpde' ), array(
			$this,
			'exported_users_are_active_checkbox',
		), 'wpde_options', 'wpde_settings_section' );

		add_settings_field( 'wpde_exported_users_are_approved', __( 'Users are approved', 'wpde' ), array(
			$this,
			'exported_users_are_approved_checkbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		add_settings_field( 'wpde_export_users_throttle', __( 'Users Throttle', 'wpde' ), array(
			$this,
			'export_users_throttle_editbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		add_settings_field( 'wpde_export_users_whoa', __( 'Users Whoa', 'wpde' ), array(
			$this,
			'export_users_whoa_editbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		add_settings_field( 'wpds_export_posts', __( 'Export Posts', 'wpde' ), array(
			$this,
			'export_posts_checkbox',
		), 'wpde_options', 'wpde_settings_section' );

		add_settings_field( 'wpde_export_posts_throttle', __( 'Posts Throttle', 'wpde' ), array(
			$this,
			'export_posts_throttle_editbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		add_settings_field( 'wpde_export_posts_whoa', __( 'Posts Whoa', 'wpde' ), array(
			$this,
			'export_posts_whoa_editbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		add_settings_field( 'wpds_export_comments', __( 'Export Comments', 'wpde' ), array(
		        $this,
            'export_comments_checkbox',
        ), 'wpde_options', 'wpde_settings_section' );

		add_settings_field( 'wpde_export_posts_throttle', __( 'Posts Throttle', 'wpde' ), array(
			$this,
			'export_comments_throttle_editbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		add_settings_field( 'wpde_export_comments_whoa', __( 'Comments Whoa', 'wpde' ), array(
			$this,
			'export_comments_whoa_editbox',
		), 'wpde_options', 'wpde_settings_section' );
		
		register_setting( 'wpde_options', 'wpde_options', array( $this->form_helper, 'validate_options' ) );

	}

	public function add_export_page() {
		$latest_topics_settings = add_submenu_page(
		// The parent page from the wp-discourse plugin.
			'wp_discourse_options',
			__( 'Export', 'wpde' ),
			__( 'Export', 'wpde' ),
			'manage_options',
			'wpde_options',
			array( $this, 'wpde_options_page' )
		);
	}

	public function wpde_options_page() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->options_page->display( 'wpde_options' );
		}
	}

	public function settings_tab( $tab ) {
		$active = 'wpde_options' === $tab;
		?>
		<a href="?page=wp_discourse_options&tab=wpde_options"
		   class="nav-tab <?php echo $active ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Export', 'wpde' ); ?>
		</a>
		<a href="?page=wp_discourse_options&tab=wpde_options"
		   class="nav-tab <?php echo $active ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Rate Limits', 'wpde' ); ?>
		</a>
		<?php
	}

	public function export_settings_details() {}

	public function wpde_settings_fields( $tab ) {
		if ( 'wpde_options' === $tab ) {
			settings_fields( 'wpde_options' );
			do_settings_sections( 'wpde_options' );
		}
	}

	public function export_users_checkbox() {
		$this->form_helper->checkbox_input( 'wpde_export_users', 'wpde_options', __( 'Export users.', 'wpde' ) );
	}

	public function exported_users_are_active_checkbox() {
		$this->form_helper->checkbox_input( 'wpde_exported_users_are_active', 'wpde_options', __( 'Set users active state when exporting.', 'wpde' ) );
	}

	public function export_users_throttle_editbox() {
		$this->form_helper->input( 'wpde_export_users_throttle', 'wpde_options', __( 'The number of milliseconds to wait between calls when exporting users.', 'wpde' ) );
	}
	
	public function export_users_whoa_editbox() {
		$this->form_helper->input( 'wpde_export_users_whoa', 'wpde_options', __( 'The number of milliseconds to wait on a 429 response.', 'wpde' ) );
	}
	
	public function export_posts_throttle_editbox() {
		$this->form_helper->input( 'wpde_export_posts_throttle', 'wpde_options', __( 'The number of milliseconds to wait between calls when exporting posts.', 'wpde' ) );
	}
	
	public function export_posts_whoa_editbox() {
		$this->form_helper->input( 'wpde_export_posts_whoa', 'wpde_options', __( 'The number of milliseconds to wait on a 429 response.', 'wpde' ) );
	}
	
	public function export_comments_throttle_editbox() {
		$this->form_helper->input( 'wpde_export_comments_throttle', 'wpde_options', __( 'The number of milliseconds to wait between calls when exporting comments.', 'wpde' ) );
	}
	
	public function export_comments_whoa_editbox() {
		$this->form_helper->input( 'wpde_export_comments_whoa', 'wpde_options', __( 'The number of milliseconds to wait on a 429 response.', 'wpde' ) );
	}
	
	public function exported_users_are_approved_checkbox() {
		$this->form_helper->checkbox_input( 'wpde_exported_users_are_approved', 'wpde_options', __( 'Set users approved state when importing.', 'wpde' ) );
	}
	
	public function export_posts_checkbox() {
		$this->form_helper->checkbox_input( 'wpde_export_posts', 'wpde_options', __( 'Export posts.', 'wpde' ) );
	}

	public function export_comments_checkbox() {
	    $this->form_helper->checkbox_input( 'wpde_export_comments', 'wpde_options', __( 'Export comments', 'wpde' ) );
    }
}