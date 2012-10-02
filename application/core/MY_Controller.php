<?php

class MY_Controller extends CI_Controller {

	protected $hooks_folder = '';

	public function __construct() {
		parent::__construct();

		if ( !empty($this->hooks_folder) )
			Event_Handler::load_hooks($this->hooks_folder);
	}

	/*
	public function _remap($object_called, $arguments) {
		// Go ahead and URL decode the arguments
		$args = array();
		foreach ( $arguments as $key => $arg ) {
			$args[$key] = urldecode($arg);
		}
		
		parent::_remap($object_called, $args);
	}
	*/
}