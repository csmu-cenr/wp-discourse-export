<?php

	global $wpdb;

	namespace WPDiscourse\Export;

	use WPDiscourse\Utilities\Utilities as DiscourseUtilities;
	
	class CommandLineExporter {
		
		protected $options ;
		protected $rate_limits ;
		
		public function __construct() {
			$this->set_options ;
		}
		
		public function get_api_key() {
			return $this->options['api-key'] ;
		}
		
		public function set_api_key($value) {
			$this->options['api-key'] = $value ;
		}
		
		public function get_options() {
			return $this->options ;
		}
		
		public function set_options($value) {
			$this->options = $value ;
		}
		
		public function get_publish_user_name() {
			return $this->options['publish-username'] ;
		}
		
		public function set_publish_user_name($value) {
			$this->options['publish-username'] = $value ;
		}
		
		public function get_rate_limits() {
			return $this->rate_limits ;
		}
		
		public function set_rate_limits($value) {
			$this->rate_limits = $value ;
		}
		
		public function get_url() {
			return $this->options['url'] ;
		}
		
		public function set_url($value) {
			$this->options['url'] = $value ;
		}
		
		/*
			Assumes that url, api-key and publish-username are valid
			*/
		function get_rate_limits_from_server() {
			
			$associative_array = true ;
			$url = $this->get_url() ;
			$api_key = $this->get_api_key() ;
			$publish_user_name = $this->get_publish_user_name() ;
			
			$link = "$url/admin/site_settings/category/rate_limits.json?api_key=$api_key&api_username=$publish_user_name" ;
			$results = json_decode(file_get_contents($link),$associative_array) ;
			
			return $results ;
		}
		
		function create_disourse_users_table() {
			$charset_collate = $wpdb->get_charset_collate() ;
		}
		
	}
	
	$commandLineExporter = new CommandLineExporter() ;
	$commandLineExporter->set_options(DiscourseUtilities::get_options()) ;
	$url = $commandLineExporter->get_url() ;
	$api_key = $commandLineExporter->get_api_key() ;
	echo(json_encode(array('url' => $url, 'api_key' => $api_key),JSON_PRETTY_PRINT)."\n") ;
	$commandLineExporter->set_rate_limits( $commandLineExporter->get_rate_limits_from_server() ) ;

	
	
?>