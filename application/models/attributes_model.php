<?php
class Attributes_model extends CI_Model {
	private $id   = null;
	private $name = null;

	public function set_character_id( $id ) {
		$this->id = sanitize_int( $id );
	}

	/**
	 * List all of the available attributes.
	 * @param boolean $display_name		if true will return display name instead of name
	 * @return array					attribute => id pairs
	 */
	public function list_attributes( $display_name = FALSE ) {
		$result = $this->db->get('ATTRIBUTES')->result();
		$key    = $display_name ? 'display_name' : 'name';

		// Build attributes array
		foreach ( $result as $row ) {
			$attributes[ $row->{$key} ] = $row->id;
		}

		return $attributes;
	}
	
	public function get_character_attributes() {
		if ( !$this->id  ) {
			return FALSE;
		}

		$attributes = $this->db
				->select('a.name, a.display_name, ca.level')
				->from('CHARACTERS_ATTRIBUTES AS ca')
				->join('ATTRIBUTES AS  a', 'ca.attribute = a.id')
				->where('ca.character', $this->id)
				->get()
				->result();
		
		$result = new stdClass();
		foreach ( $attributes as $attribute ) {
			$result->{$attribute->name}         = $attribute->level;
			//$result->{$attribute->display_name} = $attribute->level;
		}
		
		return $result;
	}

}
/* End attributes.php */