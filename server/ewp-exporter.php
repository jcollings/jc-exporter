<?php

class EWP_Exporter {

	/**
	 * @var int $id
	 */
	protected $id;

	/**
	 * @var string $name
	 */
	protected $name;

	/**
	 * @var string $type
	 */
	protected $type;

	/**
	 * @var string $file_type
	 */
	protected $file_type;

	/**
	 * @var array $fields
	 */
	protected $fields;

	public function __construct( $data = null ) {
		$this->setup_data( $data );
	}

	private function setup_data( $data ) {

		if ( is_array( $data ) ) {

			// fetch data from array
			$this->id        = isset( $data['id'] ) && intval( $data['id'] ) > 0 ? intval( $data['id'] ) : null;
			$this->name      = $data['name'];
			$this->type      = $data['type'];
			$this->file_type = $data['file_type'];
			$this->fields    = $data['fields'];

		} elseif ( ! is_null( $data ) ) {

			$post = false;

			if ( $data instanceof WP_Post ) {

				// fetch data from post
				$post = $data;

			} elseif ( intval( $data ) > 0 ) {

				// fetch data from id
				$this->id = intval( $data );
				$post     = get_post( $this->id );

			}

			if ( $post && $post->post_type === JCE_POST_TYPE ) {

				$json            = json_decode( $post->post_content, true );
				$this->id        = $post->ID;
				$this->name      = $post->post_title;
				$this->type      = $json['type'];
				$this->fields    = (array) $json['fields'];
				$this->file_type = $json['file_type'];
			}
		}
	}

	public function data() {

		return array(
			'id'        => $this->id,
			'name'      => $this->name,
			'type'      => $this->type,
			'file_type' => $this->file_type,
			'fields'    => $this->fields,
		);
	}

	public function save() {

		$postarr = array(
			'post_title'   => $this->name,
			'post_content' => json_encode( array(
				'type'      => $this->type,
				'fields'    => (array) $this->fields,
				'file_type' => $this->file_type
			) ),
		);

		if ( is_null( $this->id ) ) {
			$postarr['post_type']   = JCE_POST_TYPE;
			$postarr['post_status'] = 'publish';

			$result = wp_insert_post( $postarr, true );

		} else {
			$postarr['ID'] = $this->id;
			$result        = wp_update_post( $postarr, true );
		}

		if ( ! is_wp_error( $result ) ) {
			$this->setup_data( $result );
		}

		return $result;
	}

	public function export(){

        header('Content-Type: text/event-stream');
		header("Content-Encoding: none");
        header('Cache-Control: no-cache');

		$previous_time = microtime(true);

		if($this->getFileType() === 'csv'){
			$file = new EWP_File_CSV($this);
		}elseif($this->getFileType() === 'xml'){
			$file = new EWP_File_XML($this);
		}else{
			$file = new EWP_File_JSON($this);
		}

		$file->start();

		$mapper = new EWP_Mapper_Post($this->getType());
		$columns = $this->getFields();

		if($mapper->have_records()){

			$i = 0;
			$total = $mapper->found_records();

			echo json_encode(array('progress' => 0, 'count' => $i, 'total' => $total)) ."\n";
			flush();
			ob_flush();

			for($i = 0; $i < $total; $i++){

				$file->add($mapper->get_record($i, $columns));

				$current_time = microtime(true);
				$delta_time = $current_time - $previous_time;

				if($delta_time > 0.1) {
					echo json_encode( array(
						'progress' => round( ( $i / $total ) * 100 , 2),
						'count'    => $i,
						'total'    => $total
					) ) ."\n";
					flush();
					ob_flush();
					$previous_time = $current_time;
				}
			}
		}

		$file->end();

		$key = md5(time());
		add_post_meta($this->id, '_ewp_file_' . $key, array(
			'url' => $file->get_file_url(),
			'path' => $file->get_file_path(),
			'type' => $this->getFileType()
		));

		echo json_encode(array(
            'progress' => 100,
            'count'    => $total,
            'total'    => $total,
			'file' => $key
		));

        flush();
        ob_flush();
		die();
	}

	public function delete(){
		if ( get_post_type( $this->getId() ) === JCE_POST_TYPE ) {
			wp_delete_post( $this->getId(), true );
		}
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType( $type ) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getFileType() {
		return $this->file_type;
	}

	/**
	 * @param string $file_type
	 */
	public function setFileType( $file_type ) {
		$this->file_type = $file_type;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param array $fields
	 */
	public function setFields( $fields ) {
		$this->fields = $fields;
	}
}