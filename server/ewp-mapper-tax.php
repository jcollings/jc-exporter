<?php

class EWP_Mapper_Tax implements EWP_Mapper_Interface{

	private $taxonomy;

	/**
	 * @var WP_Term_Query
	 */
	private $query;

	private function get_core_fields(){
		return array(
			'term_id',
			'name',
			'slug'
		);
	}

	public function __construct($taxonomy) {
		$this->taxonomy = $taxonomy;
	}

	public function get_fields() {

		$core = $this->get_core_fields();
		$custom_fields = array();

		return array_merge($core, $custom_fields);
	}

	public function have_records() {
		$this->query = new WP_Term_Query(array(
			'taxonomy' => $this->taxonomy
		));

		return $this->found_records() > 0;
	}

	public function found_records() {
		return count($this->query->terms);
	}

	public function get_record( $i, $columns ) {

		$record = $this->query->terms[$i];

		// Core fields
		$core = $this->get_core_fields();

		// Meta data
		$meta = get_term_meta($record->term_id);

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