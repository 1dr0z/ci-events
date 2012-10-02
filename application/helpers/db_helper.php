<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Use filter_var to sanitize a string
 * @param string $string
 * @return string
 */
function sanitize_string( $string ) {
	return filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
}

// ------------------------------------------------------------------------

/**
 * Sanitize an array of strings. Optionally sanitize the array keys.
 * @param array $string_array	Array of strings
 * @param bool $sanitize_keys	If true will sanitize the keys as strings
 * @return array
 */
function sanitize_string_array( $string_array ) {
	$return_array = array();
	// we shift the array so that if it is large we are not building another equally large one.
	foreach ( $string_array as $key => $value ) {
		$key   = sanitize_string( $key );
		$value = sanitize_string( $value );
		// Add to array
		$return_array[$key] = $value;
	}
	return $return_array;
}

// ------------------------------------------------------------------------

function sanitize_int( $int ) {
	return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
}

/* End of file my_db_helper.php */