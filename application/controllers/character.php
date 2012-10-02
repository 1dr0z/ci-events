<?php

class Character extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('character_model');
		$this->load->model('attributes_model');
		$this->load->model('race_model');
		$this->load->model('skills_model');
		$this->load->model('axioms_model');
	}

	/**
	 * Add a new character to the database
	 * @return boolean
	 */
	public function add_post() {
		// The character MUST post values for every possible attribute
		$attributes = $this->attributes_model->list_attributes(); // By name i.e. DEX
		$char_attr  = $this->input->post('attributes');
		foreach ( $attributes as $attr => $id ) {
			if ( isset($char_attr[$attr]) )
				$char_attributes[] = array(
					'attribute' => $id,
					'level'     => $char_attr[$attr]
				);
			else
				$missing['attr'][] = $attr;
		}

		// Fail out if we did not receive all of the required attributes
		if ( isset($missing['attr']) && count( $missing['attr'] ) > 0 ) {
			return $this->response('Missing attributes in character specification: ' . implode(', ', $missing['attr']), 400);
		}

		// The character MUST provide some basic data
		$required_data = array('name', 'race', 'reality');
		foreach ( $required_data as $data ) {
			if ( is_null($character[$data] = $this->input->post($data)) ) {
				$missing['data'][] = $data;
			}
		}

		// Fail out if we did not receive all required character data.
		if ( isset($missing['data']) && count( $missing['data'] ) > 0 ) {
			return $this->response('Missing character data in specification: ' . implode(', ', $missing['data']), 400);
		}

		// Optional / Hardcoded data
		$character['owner']  = $this->input->post('owner');
		$character['active'] = 0; // Inactive until approved by GM
		$character['possibilities'] = 10;

		/// INSERT ///
		$this->db->trans_start();

		// Insert character into CHARACTERS
		$this->db->insert('CHARACTERS', $character);
		$character_id = $this->db->insert_id();

		// Add character id to attributes data
		foreach ( $char_attributes as &$attribute ) {
			$attribute['character'] = $character_id;
		}

		// Insert character attributes into CHARACTERS_ATTRIBUTES
		$this->db->insert_batch('CHARACTERS_ATTRIBUTES', $char_attributes);
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function add_skills_get() {
		$this->add_skills_post();
	}
	
	/**
	 * Add skills for an existing character
	 * @return boolean
	 */
	public function add_skills_post() {
		$_POST = array(
			'character' => 7,
			'skills'    => array(
				array(
					'skill' => array('id' => 4, 'adds' => 2),
					'specialty' => array('name' => 'River Dance'),
				),
				array(
					'skill' => array('id' => 8, 'adds' => 1),
					'trademark' => array('name' => 'Vera'),
				),
				array(
					'skill' => array('id' => 25, 'adds' => 3),
					'specialty' => array('name' => 'Helicopters'),
					'trademark' => array('name' => 'Blue Meanie'),
				)
			)
		);

		$character_id = $this->input->post('character');
		$skills       = $this->input->post('skills');

		if ( $this->character_model->character_exists($character_id) ) {
			$this->skills_model->set_character_id( $character_id );
			$status = $this->skills_model->add_character_skills( $skills );
			$this->response($status);
		} else {
			$this->response("Character {$character_id} does not exist. Unable to add skills.", 400);
		}

		return FALSE;
	}

	public function test_get() {
		$axioms = $this->axioms_model->list_axioms();
	}
}
/* End character.php */