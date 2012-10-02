<?php
class Admin extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('character_model');
		$this->load->model('attributes_model');
		$this->load->model('race_model');
		$this->load->model('skills_model');
		$this->load->model('axioms_model');
	}

	public function index_get() {
		$this->load->view('admin');
	}

	public function test_get() {
		$this->add_skill_post();
	}

	public function add_skill_post() {
		$skills = $this->input->post('skills');
		$status = $this->skills_model->add_skills( $skills );
		if ( !$status ) {
			$this->response($this->skills_model->error, 400);
		} else $this->response( $status );

	}
}
/* End admin.php */