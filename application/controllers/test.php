<?php
class test extends MY_Controller {

	protected $hooks_folder = 'test';

	public function index() {
		$data = array(
			'data' => true,
			'test' => true
		);

		try {
			trigger_event('TEST', $data, function( &$event ){
				echo 'DEFAULT ACTION' . '<br />';
				var_dump( $event->result );
				$event->result = 'result_default_1';
			}, true);
		} catch ( Exception $e ) {
			var_dump( $e );
		}
	}
}