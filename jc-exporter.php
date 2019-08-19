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
		add_action( 'init', array( $this, 'admin_init'));
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

	public function load_assets() {
		wp_register_script( $this->plugin_domain . '-bundle', plugin_dir_url( __FILE__ ) . 'dist/bundle.js', array(), $this->version, 'all' );

		wp_localize_script( $this->plugin_domain . '-bundle', 'wpApiSettings', array(
			'root' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'admin_base' => '/wp-admin/tools.php?page=' . $this->plugin_domain,
			'wprb_ajax_base' => rest_url('/wpe/v1'),
			'wprb_basic_auth' => defined( 'WPRB_AJAX_BASIC_AUTH' ) ? WPRB_AJAX_BASIC_AUTH : null,
		) );

		wp_enqueue_script( $this->plugin_domain . '-bundle' );
		wp_add_inline_script( $this->plugin_domain . '-bundle', '', 'before' );

		wp_enqueue_style( $this->plugin_domain . '-bundle-styles', plugin_dir_url( __FILE__ ) . 'dist/style.bundle.css', array(), $this->version, 'all' );
	}
}

new JC_Exporter_Plugin();