<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Event_Handler {
	private static $hooks = array();

	public static function load_hooks( $folder = '' ) {
		$path  = APPPATH . 'libraries/hooks/' . $folder;
		$path .= substr($path, -1) == '/' ? '' : '/';

		if ( is_dir($path) && $dir = opendir($path)) {
			while ( ($file = readdir($dir)) !== false ) {
				if ( !in_array($file, array('.', '..')) && substr($file, -3) == 'php') {
					include_once $path.$file;

					// To avoid class name collision, we'll require that classes be named folder_filename
					$class = $folder.'_'.str_replace('.php', '', $file);
					if (class_exists($class) && method_exists($class, 'register')) {
						call_user_func(array($class, 'register'));
					}
				}
			}
			closedir($dir);
		}

		unset( $path, $dir, $file );
	}

	/**
	 * Register a callback for an event.
	 * @param string $event			The name of the event we want to listen to
	 * @param string $advise		BEFORE or AFTER
	 * @param callable $callback	callable
	 * @param mixed $params			params
	 */
	public static function register( $event, $advise, $callback, $params = null ) {
		self::$hooks["{$event}_{$advise}"][] = array(
			'callback' => $callback,
			'params'   => $params,
		);
	}

	public static function register_before( $event, $callback, $params = null ) {
		self::register($event, Event::BEFORE, $callback, $params);
	}

	public static function register_after( $event, $callback, $params = null ) {
		self::register($event, Event::AFTER, $callback, $params);
	}

	public static function process_event( &$event, $advise='' ) {
		$event_name = $event->name . ($advise ? '_'.$advise : '_'.Event::BEFORE);

		if ( !empty(self::$hooks[$event_name]) ) {
			foreach ( self::$hooks[$event_name] as $hook ) {
				$callback = $hook['callback'];
				$params   = $hook['params'];

				if ( $callback instanceof Closure ) {
					$callback( $event, $params );
				}

				else if ( is_array($callback) ) {
					list($obj, $method) = $callback;
					$obj->$method( $event, $params );
				}

				else if ( is_string ) {
					$callback( $event, $params );
				}

				if ( !$event->_continue ) break;
			}
		}
	}
}

class Event {
	const BEFORE = 'BEFORE';
	const AFTER  = 'AFTER';

	public $name             = '';
	public $data             = array();
	public $folder           = '';
	public $result           = null;
	public $_default         = true;
	public $_continue        = true;
	public $override_default = true;

	public function __construct($name, &$data, $hooks_folder = '') {
		$this->name   = $name;
		$this->data   = $data;
		$this->folder = $hooks_folder;
	}

	/**
	 * Stop further handling of the event by registered hooks.
	 * Control will be handed to the event source
	 */
	public function stop_propagation() {
		$this->_continue = false;
	}

	/**
	 * Prevent the event source from performing the deafault action.
	 */
	public function prevent_default() {
		$this->_default = false;
	}

    /**
	 * Preferably events will be fired with the trigger method.
	 * However, it may sometimes be desirable to run things manually.
     * if these methods are used by functions outside of this object, they must
     * properly handle correct processing of any default action and issue an
     * advise_after() signal. e.g.
     *    $evt = new Doku_Event(name, data);
     *    if ($evt->advise_before(canPreventDefault) {
     *      // default action code block
     *    }
     *    $evt->advise_after();
     *    unset($evt);
     *
     * @return  results of processing the event, usually $this->_default
     */
	public function notify_before( $override_default = true) {
		$this->override_default = $override_default;
		Event_Handler::process_event($this, self::BEFORE);
		return (!$override_default || $this->_default);
	}

	public function notify_after() {
		$this->_continue = true;
		Event_Handler::process_event($this, self::AFTER);
	}

	/**
	 * Process the <event>_BEFORE handlers.
	 * Perform the default action if $action is callable. This can be prevented if
	 * one of the handlers prevents the default action.
	 * Process the <event>_AFTER handlers.
     *
     * @return  $event->results
	 *			Can be set by any of the <event>_BEFORE handlers if the default action is prevented.
	 *			Will be set by the default action if it is not prevented.
	 *			Can be set by any of the <event>_AFTER handlers
	 *			If no action took place it will be NULL
     */
	public function trigger( $callback = null, $override_default = true ) {
		if ( !is_callable($callback) ) $override_default = false;

		if ( $this->notify_before($override_default) && is_callable($callback) ) {

			if ( $callback instanceof Closure ) {
				$callback( $this );
			}

			else if ( is_array($callback) ) {
				list($obj, $method) = $callback;
				$obj->$method( $this );
			}

			else if ( is_string ) {
				$callback( $this );
			}
		}

		$this->notify_after();
		return $this->result;
	}
}

/**
 *
 * function wrapper to process (create, trigger and destroy) an event
 *
 * @param  $name               (string)   name for the event
 * @param  $data               (mixed)    event data
 * @param  $action             (callback) (optional, default=NULL) default action, a php callback function
 * @param  $canPreventDefault  (bool)     (optional, default=true) can hooks prevent the default action
 *
 * @return (mixed)                        the event results value after all event processing is complete
 *                                         by default this is the return value of the default action however
 *                                         it can be set or modified by event handler hooks
 */
function trigger_event($name, &$data, $action = null, $override_default = true) {
	$event = new Event($name, $data);
	return $event->trigger($action, $override_default);
}

/* End Events.php */