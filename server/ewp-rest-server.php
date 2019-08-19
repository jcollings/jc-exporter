<?php

require_once __DIR__ . '/ewp-exporter.php';

class EWP_Rest_Server extends WP_REST_Controller {

	public $namespace = 'wpe/';
	public $version = 'v1';

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		$namespace = $this->namespace . $this->version;

		register_rest_route( $namespace, '/exporter', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_exporter' ),
				'permission_callback' => array( $this, 'get_permission' )
			)
		) );

		register_rest_route( $namespace, '/exporters', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_exporters' ),
				'permission_callback' => array( $this, 'get_permission' )
			),
		) );

		register_rest_route( $namespace, '/exporter/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_exporter' ),
				'permission_callback' => array( $this, 'get_permission' )
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_exporter' ),
				'permission_callback' => array( $this, 'get_permission' )
			),
		) );

		register_rest_route( $namespace, '/exporter/(?P<id>\d+)/run', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'run_exporter' ),
				'permission_callback' => array( $this, 'get_permission' )
			),
		) );
	}

	public function get_permission() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to view exporters.', 'wp-react-boilerplate' ), array( 'status' => 401 ) );
		}

		return true;
	}

	public function get_exporters( WP_REST_Request $request ) {

		$result = array();
		$query  = new WP_Query( array(
			'post_type'      => JCE_POST_TYPE,
			'posts_per_page' => - 1,
		) );

		foreach ( $query->posts as $post ) {
			$exporter = new EWP_Exporter( $post );
			$result[] = $exporter->data();
		}

		return $result;
	}

	public function get_exporter( WP_REST_Request $request ) {

		$id       = intval( $request->get_param( 'id' ) );
		$exporter = new EWP_Exporter( $id );

		return $exporter->data();
	}

	public function save_exporter( WP_REST_Request $request ) {

		$post_data = $request->get_body_params();
		$id = isset($post_data['id']) ? $post_data['id'] : null;

		$exporter = new EWP_Exporter( $id );
		$exporter->setName( $post_data['name'] );
		$exporter->setFields( $post_data['fields'] );
		$exporter->setType( $post_data['type'] );
		$exporter->setFileType( $post_data['file_type'] );
		$result = $exporter->save();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $exporter->data();
	}

	public function run_exporter( WP_REST_Request $request ) {

		$id       = intval( $request->get_param( 'id' ) );
		$exporter = new EWP_Exporter( $id );
		return $exporter->export();
	}
}