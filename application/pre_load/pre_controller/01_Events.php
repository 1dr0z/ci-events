<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Register and process event handlers.
 */
class Event_Handler {
	/**
	 * Registered hooks
	 * @var array
	 */
	private static $hooks = array();

	/**
	 * Include all of the files in $folder. Run the register method of the object included.
	 * @param string $folder
	 */
	public static function load_hooks( $folder = '' ) {
		$path  = APPPATH . 'libraries/hooks/' . $folder;
		$path .= substr($path, -1) == '/' ? '' : '/';

		if ( is_dir($path) && $dir = opendir($path)) {
			while ( ($file = readdir($dir)) !== false ) {
				if ( substr($file, -3) == 'php') {
					// Only register the hooks if the file has not been included before.
					if ( !in_array($path.$file, get_included_files()) ) {
						include_once $path.$file;

						// To avoid class name collision, we'll require that classes be named <folder>_<filename>
						$class = $folder.'_'.str_replace('.php', '', $file);
						if (class_exists($class) && method_exists($class, 'register')) {
							$class::register();
						}
					}
				}
			}
			closedir($dir);
		}
	}

	/**
	 * Register a callback for an event. Advise is the state of the event.
	 * Be default this is either BEFORE or AFTER, but custom advisements may be created.
	 * @param string $event			The name of the event we want to listen to
	 * @param string $advise		BEFORE or AFTER (or custom)
	 * @param callable $callback	callable
	 * @param mixed $params			params
	 */
	public static function register( $event, $advise, $callback, $params = null ) {
		self::$hooks["{$event}_{$advise}"][] = array(
			'callback' => $callback,
			'params'   => $params,
		);
	}

	/**
	 * Register a callback for BEFORE the event.
	 * @param string $event			The name of the event we want to listen to.
	 * @param callable $callback	php callable
	 * @param mixed $params			Additional parameters we want our callback to receive
	 */
	public static function register_before( $event, $callback, $params = null ) {
		self::register($event, Event::BEFORE, $callback, $params);
	}

	/**
	 * Register a callback for AFTER the event.
	 * @param string $event			The name of the event we want to listen to.
	 * @param callable $callback	php callable
	 * @param mixed $params			Additional parameters for our callback
	 */
	public static function register_after( $event, $callback, $params = null ) {
		self::register($event, Event::AFTER, $callback, $params);
	}

	/**
	 * Call every hook attached to an advisement. Generally BEFORE or AFTER,
	 * however custom states can be created.
	 * @param string $event		The name of the event to process
	 * @param string $advise	The state of the event. Generally BEFORE or AFTER
	 */
	public static function process_event( &$event, $advise='' ) {
		$event->_continue = true; // Reset the continue flag

		// Run hooks
		$event_name = $event->name . ($advise ? '_'.$advise : '_'.Event::BEFORE);
		if ( !empty(self::$hooks[$event_name]) ) {
			foreach ( self::$hooks[$event_name] as $hook ) {
				$callback = !empty($hook['callback']) ? $hook['callback'] : null;
				$params   = !empty($hook['params']) ? $hook['params'] : null;

				// Anonymous function
				if ( $callback instanceof Closure ) {
					$event->result = $callback( $event, $params );
				}
				// Object & Static methods
				else if ( is_array($callback) ) {
					list($obj, $method) = $callback;

					if ( is_string($obj) ) {
						$event->result = $obj::$method( $event, $params );
					} else {
						$event->result = $obj->$method( $event, $params );
					}
				}
				// Function
				else if ( is_string($callback) ) {
					$event->result = $callback( $event, $params );
				}

				if ( !$event->_continue ) break;
			}
		}
	}
}

/**
 * Event object that gets passed round robbin to the event listeners.
 */
class Event {
	const BEFORE = 'BEFORE';
	const AFTER  = 'AFTER';

	public $name             = null;
	public $data             = array();
	public $folder           = null;
	public $result           = null;
	public $_default         = true;
	public $_continue        = true;
	public $override_default = true;

	public function __construct($name, &$data, $hooks_folder = null) {
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
	 * Only works if the 'prevent_default' was enabled when the event triggered.
	 */
	public function prevent_default() {
		$this->_default = false;
	}

	/**
	 * Process the hooks of a given state. This can be used to run
	 * custom states, to run BEFORE or AFTER states use the provided methods.
	 * @param string $advise
	 * @param boolean $override_default
	 */
	public function notify( $advise, $override_default = true ) {
		$this->override_default = $override_default;
		Event_Handler::process_event($this, $advise);
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
		$this->notify(self::BEFORE);
		return (!$override_default || $this->_default);
	}

	/**
	 * Notify hooks of event AFTER state.
	 */
	public function notify_after() {
		$this->notify(self::AFTER);
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

		// BEFORE
		if ( $this->notify_before($override_default) && is_callable($callback) ) {
			// DEFAULT ACTION

			// Anonymous function
			if ( $callback instanceof Closure ) {
				$this->result = $callback( $this );
			}
			// Object & Static methods
			else if ( is_array($callback) ) {
				list($obj, $method) = $callback;

				if ( is_string($obj) ) {
					$this->result = $obj::$method( $$this );
				} else {
					$this->result = $obj->$method( $this );
				}
			}
			// Function
			else if ( is_string($callback) ) {
				$this->result = $callback( $event, $params );
			}
		}

		// AFTER
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
function trigger_event($name, $data, $action = null, $override_default = true) {
	$event  = new Event($name, $data);
	$result = $event->trigger($action, $override_default);
	unset( $event );
	return $result;
}

/* End Events.php */