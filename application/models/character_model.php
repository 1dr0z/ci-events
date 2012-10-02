<?php
class Character_model extends CI_Model {

	private $id   = null;
	private $name = null;

	public function __construct() {
		parent::__construct();
		$this->load->model('attributes_model');
	}

	/**
	 * Check if a given character id exists in the database.
	 * @param integer $id
	 * @return boolean
	 */
	public function character_exists( $id = null ) {
		// We need the character id for this
		if ( is_null($id) ) $id = $this->id;
		if ( is_null($id) ) return FALSE;

		$character = $this->db
				->select('id')
				->from('CHARACTERS')
				->where('id', $id)
				->get();
		return ( $character->num_rows() > 0 );
	}

	public function set_character_id( $id ) {
		$this->id = sanitize_int( $id );
	}
	public function set_character_name( $name ) {
		$this->name = sanitize_string( $name );
	}

	public function get_character() {
		if ( !$this->id &&
			 !$this->name ) 
		{
			return FALSE;
		}
		
		$this->db
				->select('*, r.name as reality')
				->from('REALITIES AS r')
				->join('CHARACTERS AS c', 'r.id = c.reality')
				->limit(1);

		// Filter by whatever we have
		if ( $this->name ) $this->db->where('c.name', $this->name);
		if ( $this->id ) $this->db->where('c.id', $this->id);
		
		// Get the character
		$character = $this->db->get()->row();
		if ( !$character ) return FALSE;

		// Get attributes
		$this->attributes_model->set_character_id( $character->id );
		$character->attributes = $this->attributes_model->get_attributes();
		
		
		return $character;
	}
}
/* End character.php */