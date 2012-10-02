<?php

/**
 * Includes the php files in the pre_load/ directory
 */
function pre_loader( $folder = '' ) {
	$path  = APPPATH . 'pre_load/' . $folder;
	$path .= substr($path, -1) == '/' ? '' : '/';

	if ( is_dir($path) && $dir = opendir($path)) {
		$files = array();

		// Get all php files in the directory
		while ( ($file = readdir($dir)) !== false ) {
			$file = preg_replace('/\s/', '', $file);
			if ( !in_array($file, array('.', '..')) && substr($file, -3) == 'php') {
				$files[] = $path.$file;
			}
		}
		// Sort in natural order
		usort($files, 'strnatcmp');

		// Include all of the files in 'natural' order
		foreach ( $files as $file )
			include_once $file;

		closedir($dir);
	}

	unset( $path, $dir, $file );
}
/* End load_rest_controller.php */