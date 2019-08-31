<?php

class EWP_Mapper_Comment implements EWP_Mapper_Interface {

	private $post_type;

	/**
	 * @var WP_Comment_Query
	 */
	private $query;

	private function get_core_fields(){
		return array(
			'comment_ID',
			'comment_post_ID',
			'comment_author',
			'comment_author_email',
			'comment_author_url',
			'comment_author_IP',
			'comment_date',
			'comment_date_gmt',
			'comment_content',
			'comment_karma',
			'comment_approved',
			'comment_agent',
			'comment_type',
			'comment_parent',
			'user_id',
		);
	}

	public function __construct($post_type) {
		$this->post_type = $post_type;
	}

	public function get_fields() {

		$core = $this->get_core_fields();
		$custom_fields = array();

		// get comment custom fields
		global $wpdb;
		$meta_fields = $wpdb->get_col( $wpdb->prepare("SELECT DISTINCT cm.meta_key FROM ".$wpdb->comments." as c INNER JOIN ".$wpdb->commentmeta." as cm ON c.comment_ID = cm.comment_id WHERE c.comment_post_ID IN (SELECT ID from ".$wpdb->posts." WHERE post_type=%s)", [$this->post_type]));
		foreach($meta_fields as $field){
			$custom_fields[] = 'ewp_cf_' . $field;
		}

		return array_merge($core, $custom_fields);
	}

	public function have_records() {
		$this->query = new WP_Comment_Query(array(
			'post_type' => $this->post_type
		));

		return $this->found_records() > 0;
	}

	public function found_records() {
		return count($this->query->comments);
	}

	public function get_record( $i, $columns ) {

		$record = $this->query->comments[$i];

		// Core fields
		$core = $this->get_core_fields();

		// Meta data
		$meta = get_comment_meta($record->comment_ID);

		$row = array();
		foreach($columns as $column) {
			$output = '';

			if(preg_match('/^ewp_cf_(.*?)$/', $column, $matches) == 1){

				$meta_key = $matches[1];
				if(isset($meta[$meta_key])){
					$output = $meta[$meta_key];
				}

			}else {

				if ( in_array( $column, $core, true ) ) {
					$output = $record->{$column};
				}

			}

			$row[$column] = $output;
		}

		return $row;

	}
}