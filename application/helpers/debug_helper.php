<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('print_pre'))
{
	function print_pre( $argument ) {
		echo '<pre>' . print_r( $argument, true ) . '</pre>';
	}
}

// ------------------------------------------------------------------------

/* End of file debug_helper.php */