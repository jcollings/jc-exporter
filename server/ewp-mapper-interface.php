<?php
interface EWP_Mapper_Interface{
	public function get_fields();
	public function have_records();
	public function found_records();
	public function get_record($i, $columns);
}