<?php
class test extends MY_Controller {

	protected $hooks_folder = 'test';

	public function index() {
		$data = array(
			'before' => true,
			'test'   => true
		);

		// Notice that it doesn't bind the hooks multiple times.
		Event_Handler::load_hooks($this->hooks_folder);
		Event_Handler::load_hooks($this->hooks_folder);

		$default = function( &$event ) {
			$event->data['before'] = false;

			echo "[{$event->name}] : default action<br />";
			var_dump( $event->result );
			var_dump( $event->data   );

			// Modify result & data
			$event->data['trace'][] = 'default_action';

			return 'default_action';
		};

		try {
			trigger_event('TEST', $data, $default, true);
		} catch ( Exception $e ) {
			echo "Received error '{$e->getMessage()}'. Doing some cleanup.";
		}
	}
}