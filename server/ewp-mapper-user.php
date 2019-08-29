<?php
class EWP_Mapper_User implements EWP_Mapper_Interface {

	/**
	 * @var WP_User_Query
	 */
	private $query;

	public function get_fields() {
		$core_fields = $this->get_core_fields();

		$custom_fields = array();

		global $wpdb;
		$meta_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM ".$wpdb->usermeta." WHERE user_id IN (SELECT DISTINCT ID FROM ".$wpdb->users." )");
		foreach($meta_fields as $field){
			$custom_fields[] = 'ewp_cf_' . $field;
		}

		return array_merge($core_fields, $custom_fields);
	}

	public function have_records() {
		$this->query = new WP_User_Query(array(
			'number' => -1
		));

		return $this->found_records() > 0;
	}

	public function found_records() {
		return $this->query->get_total();
	}

	public function get_record( $i, $columns ) {

		/**
		 * @var WP_User $record
		 */
		$record = $this->query->results[$i];

		// Core fields
		$core = $this->get_core_fields();
		$meta = get_user_meta($record->ID);

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

	private function get_core_fields() {
		return array(
			'ID',
			'user_login',
			'user_nicename',
			'user_email',
			'user_url',
			'user_registered',
			'user_status',
			'display_name'
		);
	}
}