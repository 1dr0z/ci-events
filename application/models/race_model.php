<?php
class Race_model extends CI_Model {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function insert_race_limits() {
		$race = 10;
		$attr = array(
			1  => 13, // Dexterity
			2  => 13, // Strength
			3  => 13, // Toughness
			4  => 13, // Perception
			5  => 13, // Mind
			6  => 13, // Charisma
			7  => 13, // Spirit
			8  =>  6, // Run
			9  =>  4, // Swim
			10 =>  2, // Jump
			11 =>  1, // Climb
			12 => 13, // Lift
			13 => 13, // Hold Breath
			14 =>  0, // Flight
		);
		
		$limits = array();
		foreach ( $attr as $attribute => $limit ) {
			$limits[] = array(
				'attribute' => $attribute,
				'race'      => $race,
				'limit'     => $limit,
			);
		}
		
		$this->db->insert_batch('ATTRIBUTES_RACE_LIMIT', $limits);
	}
}

?>
