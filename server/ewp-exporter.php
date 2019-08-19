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

		$query = new WP_Query(array(
			'post_type' => 'post',
			'posts_per_page' => -1
		));

		$previous_time = microtime(true);

		$fh = tmpfile();

		$path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR .'exportwp' . DIRECTORY_SEPARATOR;
		if(!file_exists($path)){
			mkdir($path);
		}

		$file_path = $path . $this->getId() . '.csv';

		$fh = fopen($file_path, 'w');

		$columns = $this->getFields();

		// write headers
		fputcsv($fh, $columns);

		if($query->have_posts()){

			$i = 0;
			$total = $query->found_posts;

			echo json_encode(array('progress' => 0, 'count' => $i, 'total' => $total));
			flush();
			ob_flush();

			for($i = 0; $i < count($query->posts); $i++){
				$post = $query->posts[$i];

				// Meta data
				$meta = get_post_meta($post->ID);

				// Author Details
				$author = $post->post_author;
				$user_data = get_userdata($author);

				$row = array();
				foreach($columns as $column){
					$output = '';

					$matches = null;
					if(preg_match('/^ewp_tax_(.*?)$/', $column, $matches) == 1) {

						$taxonomy    = $matches[1];
						$found_terms = array();
						$terms       = wp_get_object_terms( $post->ID, $taxonomy );
						if ( ! empty( $terms ) ) {
							foreach ( $terms as $term ) {

								/**
								 * @var WP_Term $term
								 */
								$found_terms[] = $term->name;
							}
						}

						$output = implode( '|', $found_terms );
					}elseif(preg_match('/^ewp_cf_(.*?)$/', $column, $matches) == 1){

						$meta_key = $matches[1];
						if(isset($meta[$meta_key])){
							$output = implode('|', $meta[$meta_key]);
						}

					}else{
						switch($column){
							case 'ID':
								$output = $post->ID;
								break;
							case 'post_name':
								$output = $post->post_title;
								break;
							case 'post_content':
								$output = $post->post_content;
								break;
							case 'post_excerpt':
								$output = $post->post_excerpt;
								break;
							case 'post_author':
								$output = $user_data->nickname;
								break;
							case 'post_thumbnail':
								if(has_post_thumbnail($post)){
									$output = wp_get_attachment_url(get_post_thumbnail_id($post));
								}
								break;
							case 'post_date':
								$output = $post->post_date;
								break;
							case 'post_status':
								$output = $post->post_status;
								break;
							case 'post_modified':
								$output = $post->post_modified;
								break;
							case 'post_parent':
								$output = $post->post_parent;
								break;
						}
					}


					$row[] = $output;
				}

				fputcsv($fh, $row);

				$current_time = microtime(true);
				$delta_time = $current_time - $previous_time;

				if($delta_time > 0.1) {
					echo json_encode( array(
						'progress' => round( ( $i / $total ) * 100 , 2),
						'count'    => $i,
						'total'    => $total
					) );
					flush();
					ob_flush();
					$previous_time = $current_time;
				}
			}
		}

		fclose($fh);

		return array(
			'file' => content_url('/uploads/exportwp/' . $this->getId() . '.csv')
		);
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