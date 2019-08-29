<?php
class EWP_Mapper_Post{

	private $post_type;

	public function __construct($post_type = 'post') {
		$this->post_type = $post_type;
	}

	public function get_core_fields(){
		return array(
			'ID',
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_title',
			'post_excerpt',
			'post_status',
			'comment_status',
			'ping_status',
			'post_password',
			'post_name',
			'to_ping',
			'pinged',
			'post_modified',
			'post_modified_gmt',
			'post_content_filtered',
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			'post_mime_type',
			'comment_count',
		);
	}

	public function get_fields(){

		global $wpdb;

		$core_fields = $this->get_core_fields();

		$custom_fields = array();

		// add post_thumbnail
		if(post_type_supports($this->post_type, 'thumbnail')){
			$custom_fields[] = 'post_thumbnail';
		}

		// post author
		$custom_fields[] = 'ewp_author_nicename';
		$custom_fields[] = 'ewp_author_nickname';
		$custom_fields[] = 'ewp_author_first_name';
		$custom_fields[] = 'ewp_author_last_name';
		$custom_fields[] = 'ewp_author_login';
		$custom_fields[] = 'ewp_author_desc';

		// post_meta
		$meta_fields = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT meta_key FROM ".$wpdb->postmeta." WHERE post_id IN (SELECT DISTINCT ID FROM ".$wpdb->posts." WHERE post_type='%s')", [$this->post_type]));
		foreach($meta_fields as $field){
			$custom_fields[] = 'ewp_cf_' . $field;
		}

		// taxonomies
		$taxonomies = get_object_taxonomies( $this->post_type, 'objects' );
		foreach($taxonomies as $tax){
			$custom_fields[] = 'ewp_tax_' . $tax->name;
			$custom_fields[] = 'ewp_tax_' . $tax->name . '_slug';
			$custom_fields[] = 'ewp_tax_' . $tax->name . '_id';
		}

		return array_merge($core_fields, $custom_fields);
	}

	/**
	 * @var WP_Query
	 */
	private $query;

	public function have_records(){
		$this->query = new WP_Query(array(
			'post_type' => $this->post_type,
			'posts_per_page' => -1
		));

		return $this->query->post_count > 0;
	}

	public function found_records(){
		return $this->query->post_count;
	}

	public function get_record($i, $columns){

		$post = $this->query->posts[$i];

		// Core fields
		$core = $this->get_core_fields();

		// Meta data
		$meta = get_post_meta($post->ID);

		// Author Details
		$author = $post->post_author;
		$user_data = get_userdata($author);

		$row = array();
		foreach($columns as $column){
			$output = '';

			$matches = null;
			if(preg_match('/^ewp_tax_(.*?)/', $column) == 1) {

				if(preg_match('/^ewp_tax_(.*?)_slug$/', $column, $matches) == 1) {

					$taxonomy    = $matches[1];
					$found_terms = array();
					$terms       = wp_get_object_terms( $post->ID, $taxonomy );
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {

							/**
							 * @var WP_Term $term
							 */
							$found_terms[] = $term->slug;
						}
					}

					$output = $found_terms;

				}elseif(preg_match('/^ewp_tax_(.*?)_id$/', $column, $matches) == 1) {

					$taxonomy    = $matches[1];
					$found_terms = array();
					$terms       = wp_get_object_terms( $post->ID, $taxonomy );
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {

							/**
							 * @var WP_Term $term
							 */
							$found_terms[] = $term->term_id;
						}
					}

					$output = $found_terms;

				}elseif(preg_match('/^ewp_tax_(.*?)$/', $column, $matches) == 1) {

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

					$output = $found_terms;

				}

			}elseif(preg_match('/^ewp_cf_(.*?)$/', $column, $matches) == 1){

				$meta_key = $matches[1];
				if(isset($meta[$meta_key])){
					$output = $meta[$meta_key];
				}

			}else{

				if(in_array($column, $core, true)){
					$output = $post->{$column};
				}else{
					switch($column){
						case 'post_thumbnail':
							if(has_post_thumbnail($post)){
								$output = wp_get_attachment_url(get_post_thumbnail_id($post));
							}
							break;
						case 'ewp_author_nicename':
							$output = $user_data->user_nicename;
							break;
						case 'ewp_author_nickname':
							$output = $user_data->nickname;
							break;
						case 'ewp_author_first_name':
							$output = $user_data->first_name;
							break;
						case 'ewp_author_last_name':
							$output = $user_data->last_name;
							break;
						case 'ewp_author_login':
							$output = $user_data->user_login;
							break;
						case 'ewp_author_desc':
							$output = $user_data->description;
							break;
					}
				}
			}

			$row[$column] = $output;
		}

		return $row;
	}
}