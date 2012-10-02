<?php
class test_test {
	public static function register() {

		// BEFORE
		Event_Handler::register_before('TEST', function( &$event, $params ) {
			echo Event::BEFORE . '<br />';
			var_dump( $event->result );
//			$event->prevent_default();
//			$event->stop_propagation();

			$event->result = 'result_before_1';
		}, true);

		Event_Handler::register_before('TEST', function( &$event ) {
			echo Event::BEFORE.'_2<br />';
			var_dump( $event->result );
//			$event->prevent_default();
//			$event->stop_propagation();

			$event->result = 'result_before_2';
		}, array('number' => 100));

		// AFTER
		Event_Handler::register_after('TEST', function( &$event, $params ) {
			echo Event::AFTER . '<br />';
			var_dump( $event->result );
//			$event->prevent_default();
//			$event->stop_propagation();

			$event->result = 'result_after_1';
		}, false);

		Event_Handler::register_after('TEST', function( &$event, $params ) {
			echo Event::AFTER.'_2 <br />';
			var_dump( $event->result );
//			$event->prevent_default();
//			$event->stop_propagation();

			$event->result = 'result_after_2';
		}, false);
	}
}
/* End test.php */