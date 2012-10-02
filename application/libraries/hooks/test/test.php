<?php

function test_function( &$event, $params ) {
	echo "[{$event->name}] : regular function<br />";
	var_dump( $event->result );
	var_dump( $event->data   );

	// Modify data
	$event->data['trace'][] = 'result_after_1';

	// Modify event execution
	//$event->stop_propagation();

	return 'result_after_1';
}

class test_test {
	public static function test_static( &$event, $params ) {
		echo "[{$event->name}] : static method<br />";
		var_dump( $event->result );
		var_dump( $event->data   );

		// Modify data
		$event->data['trace'][] = 'result_before_1';

		// Modify event execution
		//$event->prevent_default();
		//$event->stop_propagation();

		//throw new Exception('Epic fail!');
		return 'result_before_1';
	}

	public function test_method( &$event, $params ) {
		echo "[{$event->name}] : object method<br />";
		var_dump( $event->result );
		var_dump( $event->data   );

		// Modify data
		$event->data['trace'][] = 'result_before_2';

		return 'result_before_2';
	}

	public static function register() {

		// Bind static function
		Event_Handler::register_before('TEST', array('test_test', 'test_static') , true);

		// Bind object instance
		$obj = new test_test();
		Event_Handler::register_before('TEST', array($obj, 'test_method'), array('number' => 100));

		// Bind function
		Event_Handler::register_after('TEST', 'test_function', false);

		// Bind closure
		Event_Handler::register_after('TEST', function( &$event, $params ) {
			echo "[{$event->name}] : anonymous function<br />";
			var_dump( $event->result );
			var_dump( $event->data   );

			// Modify data
			$event->data['trace'][] = 'result_after_2';

			return 'result_after_2';
		}, new stdClass());


		/// CUSTOM STATE ///
		Event_Handler::register('TEST', 'CUSTOM', function( &$event ) {
			echo "[{$event->name}] : CUSTOM<br />";
			var_dump( $event->result );
			var_dump( $event->data   );

			$event->data['trace'][] = 'custom';

			return 'custom';
		});
	}
}
/* End test.php */