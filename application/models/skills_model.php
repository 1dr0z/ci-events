<?php
class Skills_model extends CI_Model {

	private $id;
	public $error;

	public function __construct() {
		parent::__construct();
	}

	public function set_character_id( $id ) {
		$this->id = sanitize_int($id);
	}

	public function add_skills( $skills ) {
		if ( !is_array($skills) ) return FALSE;

		// Skills MUST specify minimum levels for EVERY axiom.
		$AXIOMS       = $this->axioms_model->list_axioms();

		foreach ( $skills as $skill ) {
			$skill_axioms = $skill['axioms'];

			// Check that we have data for every axiom
			foreach ( $AXIOMS as $id => $min_level ) {
				if ( !isset( $skill_axioms[$id] ) ) {
					$missing['axioms'][] = $axioms[$id];
				}
			}

			// Fail out if we did not receive all of the required axiom data
			if ( isset($missing['axioms']) && count( $missing['axioms'] ) > 0 ) {
				$this->error = 'Missing skill min_level for axioms: ' . implode(', ', $missing['axioms']);
				return FALSE;
			}

			// Validate the skill data
			$required_data = array('attribute', 'name', 'unskilled', 'penalty', 'rated_only', 'active');
			$skill_data    = $skill['skill'];

			foreach ( $required_data as $data ) {
				if ( !isset($skill_data[$data])) {
					$missing['data'][] = $data;
				}
			}

			// Fail out if we did not receive all required data
			if ( isset($missing['data']) && count( $missing['data'] ) > 0 ) {
				$this->error = 'Missing skill data in specification: ' . implode(', ', $missing['data']);
				return FALSE;
			}

		}

		$this->db->trans_start();
		foreach ( $skills as $skill ) {
			$id = $this->add_skill( $skill['skill'] );

			// If this insert failed, roll back
			if ( $id === FALSE ) {
				$this->db->trans_rollback();
				return FALSE;
			}

			// Build the SKILLS_AXIOM insert
			$SKILLS_AXIOM = array();
			foreach ( $skill['axioms'] as $axiom => $level ) {
				if ( $level == 0 ) continue; // Do nothing with zero level
				$SKILLS_AXIOM[] = array(
					'skill'     => $id,
					'axiom'     => $axiom,
					'min_level' => $level,
				);
			}

			if ( count($SKILLS_AXIOM) > 0 )
				$this->add_skill_axioms($SKILLS_AXIOM);
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/**
	 * Add a skill array into the SKILLS table
	 * @param array $skill
	 * @return mixed	insert_id or false
	 */
	public function add_skill( $skill ) {
		//attribute, name, unskilled, penalty, rated_only, active;

		// Data validation
		foreach ( $skill as $key => $col ) {
			if ( is_string($col) && strlen($col) < 1 ) {
				$this->error = "{$key} is an empty string. Aborting mission.";
				return FALSE;
			}
		}

		$this->db->insert('SKILLS', $skill);
		return $this->db->insert_id();
	}

	/**
	 * Add a skill_axiom array into the SKILLS_AXIOMS table
	 * @param array $axioms
	 * @return mixed	insert_id or false
	 */
	public function add_skill_axioms( $axioms ) {
		$this->db->insert_batch('SKILLS_AXIOMS', $axioms);
		return $this->db->insert_id();
	}

	public function add_skills_batch( $skills ) {
		$this->db->insert_batch('CHARACTERS_SKILLS', $skills);
		return $this->db->insert_id();
	}

	private function add_character_skill( $ability ) {
		if ( !$this->id ) return FALSE;

		// Fail out if the skill is improperly formatted
		if (!isset($ability['skill']['id']) ||
			!isset($ability['skill']['adds']))
		{ return FALSE; }

		$skill_id = sanitize_int($ability['skill']['id']);
		$char_id  = sanitize_int($this->id);

		// Build array of skills
		$skills = array(
			'character' => $char_id,
			'skill'     => $skill_id,
			'adds'      => sanitize_int($ability['skill']['adds']),
		);

		// Build array of specialties
		if ( isset($ability['specialty']) ) {
			$specialties = array(
				'character'  => $char_id,
				'name'       => $ability['specialty']['name'],
				'adds'       => 1, // All specialties have an add value of 1 ( This may change later )
			);
		}

		// Build array of trademarks
		if ( isset($ability['trademark']) ) {
			$trademarks = array(
				'character'  => $char_id,
				'name'       => $ability['trademark']['name'],
				'adds'       => 2, // All trademarks have an add value of 2 ( This may change later )
			);
		}

		// Insert into CHARACTERS_SKILLS
		$this->db->insert('CHARACTERS_SKILLS', $skills);
		$meta_skill = $this->db->insert_id();

		// Insert into CHARACTERS_SPECIALTIES
		if ( isset($specialties) ) {
			// Add meta_skill to specialty (unique)
			$specialties['meta_skill'] = $meta_skill;
			$this->db->insert('CHARACTERS_SPECIALTIES', $specialties);
		}

		// Insert into CHARACTERS_TRADEMARKS
		if ( isset($trademarks) ) {
			// Add meta_skill to trademark (unique)
			$trademarks['meta_skill'] = $meta_skill;
			$this->db->insert('CHARACTERS_TRADEMARKS', $trademarks);
		}
	}

	/**
	 * Add character skills, specialties, and trademarks.
	 * @param array $abilities	Array of skill, specialty, trademark arrays
	 * @return boolean			Transaction status
	 */
	public function add_character_skills( $abilities ) {
		if ( !$this->id ) return FALSE;
		if ( !is_array($abilities) ) return FALSE;

		$this->db->trans_start();

		// Add the various skills
		foreach ( $abilities as $ability ) {
			$this->add_character_skill($ability);
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

}
/* End skills_model.php */