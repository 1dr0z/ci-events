<?php

class MY_Controller extends CI_Controller {

	protected $hooks_folder = null;

	public function __construct() {
		parent::__construct();

		if ( is_string($this->hooks_folder) )
			Event_Handler::load_hooks($this->hooks_folder);
	}
}