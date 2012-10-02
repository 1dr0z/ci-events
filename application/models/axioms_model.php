<?php
class Axioms_model extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Return a list of all axioms.
	 * @return array	id => axiom
	 */
	public function list_axioms() {
		$result = $this->db->get('AXIOMS')->result();

		$axioms = array();
		foreach ( $result as $axiom ) {
			$axioms[ $axiom->id ] = $axiom->name;
		}

		return $axioms;
	}
	
	/**
	 * Adds an axiom.
	 * @param string $axiom_name		The display name for the axiom
	 * @param array $level_desc_array	Array of level => description pairs
	 */
	public function add_axiom( $axiom_name, $level_desc_array ) {
		$axiom_name = sanitize_string($axiom_name);
		$level_desc_array = sanitize_string_array( $level_desc_array );

		// start transaction
		$this->db->trans_start();

		// Insert into AXIOMS
		$this->db->insert('AXIOMS', array('name' => $axiom_name) );
		$axiom_id = $this->db->insert_id();

		// Build the batch insert array
		$axioms_insert = array();
		foreach ( $level_desc_array as $level => $description ) {
			$axioms_insert[] = array(
				'axiom'       => $axiom_id,
				'level'       => $level,
				'description' => $description
			);
		}

		// Insert into the AXIOMS_DESCRIPTIONS table
		$this->db->insert_batch('AXIOMS_DESCRIPTIONS', $axioms_insert);
		$this->db->trans_complete();

		// Return transaction status
		return $this->db->trans_status();
	}
}
/* End axioms_model.php */