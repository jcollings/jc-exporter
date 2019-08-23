<?php

/*
Plugin Name: ExportWP
Plugin URI:
Description: ExportWP allows you to select which WordPress data you want to export csv and xml files using the visual data select tool.
Author: James Collings
Version: 0.1
Author URI: https://www.importwp.com
Network: True
*/

define('JCE_POST_TYPE', 'jc-exporter');

class JC_Exporter_Plugin {

	public $plugin_domain;
	public $views_dir;
	public $version;

	public function __construct() {
		$this->plugin_domain = 'jc-exporter';
		$this->views_dir     = trailingslashit( dirname( __FILE__ ) ) . 'server/views';
		$this->version       = '1.0';

		require_once __DIR__ . '/server/ewp-rest-server.php';
		$ewp_rest_server = new EWP_Rest_Server();
		$ewp_rest_server->init();

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'init', array( $this, 'admin_init' ) );
		add_action( 'tool_box', array( $this, 'tool_box' ) );
	}

	public function admin_init(){
		register_post_type( JCE_POST_TYPE, array(
			'public'            => false,
			'has_archive'       => false,
			'show_in_nav_menus' => false,
			'label'             => 'Exporter',
		) );
	}

	public function admin_menu() {
		$title = __( 'ExportWP', $this->plugin_domain );

		$hook_suffix = add_management_page( $title, $title, 'export', $this->plugin_domain, array(
			$this,
			'load_admin_view',
		) );

		add_action( 'load-' . $hook_suffix, array( $this, 'load_assets' ) );
	}

	public function load_view( $view ) {
		$path = trailingslashit( $this->views_dir ) . $view;

		if ( file_exists( $path ) ) {
			include $path;
		}
	}

	public function load_admin_view() {
		$this->load_view( 'admin.php' );
	}

	public function tool_box(){
		$this->load_view('tools-card.php');
	}

	public function load_assets() {
		wp_register_script( $this->plugin_domain . '-bundle', plugin_dir_url( __FILE__ ) . 'dist/bundle.js', array(), $this->version, 'all' );

		wp_localize_script( $this->plugin_domain . '-bundle', 'wpApiSettings', array(
			'root' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'admin_base' => str_replace( site_url(), '', admin_url('/tools.php?page=' . $this->plugin_domain)),
			'ajax_base' => rest_url('/ewp/v1'),
			'fields' => $this->get_fields()
		) );

		wp_enqueue_script( $this->plugin_domain . '-bundle' );
		wp_add_inline_script( $this->plugin_domain . '-bundle', '', 'before' );

		wp_enqueue_style( $this->plugin_domain . '-bundle-styles', plugin_dir_url( __FILE__ ) . 'dist/style.bundle.css', array(), $this->version, 'all' );
	}

	private function get_fields(){

		$fields = array();

		$post_types = get_post_types();
		foreach($post_types as $post_type => $label){
			$mapper = new EWP_Mapper_Post($post_type);
			$fields[] = array(
				'id' => $post_type,
				'label' => $label,
				'fields' => $mapper->get_fields()
			);
		}

		return $fields;
	}
}

new JC_Exporter_Plugin();