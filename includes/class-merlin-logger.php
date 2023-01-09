<?php
/**
 * The logger class, which will abstract the use of the monolog library.
 * More about monolog: https://github.com/Seldaek/monolog
 */

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Merlin_Logger {
	/**
	 * @var object instance of the monolog logger class.
	 */
	private $log;


	/**
	 * @var string The absolute path to the log file.
	 */
	private $log_path;


	/**
	 * @var string The name of the logger instance.
	 */
	private $logger_name;


	/**
	 * The instance *Singleton* of this class
	 *
	 * @var object
	 */
	private static $instance;


	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return object EasyDigitalDownloadsFastspring *Singleton* instance.
	 *
	 * @codeCoverageIgnore Nothing to test, default PHP singleton functionality.
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Logger constructor.
	 *
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct( $log_path = null, $name = 'merlin-logger' ) {
		$this->log_path    = $log_path;
		$this->logger_name = $name;

		if ( empty( $this->log_path ) ) {
			$upload_dir = wp_upload_dir();
			$logger_dir = $upload_dir['basedir'] . '/merlin-wp';

			if ( ! file_exists( $logger_dir ) ) {
				wp_mkdir_p( $logger_dir );
			}

			$this->log_path = $logger_dir . '/main.log';
		}

		$this->initialize_logger();
	}


	/**
	 * Initialize the monolog logger class.
	 */
	private function initialize_logger() {
		if ( empty( $this->log_path ) || empty( $this->logger_name ) ) {
			return false;
		}

		$this->log = new MonologLogger( $this->logger_name );
		$this->log->pushHandler( new StreamHandler( $this->log_path, MonologLogger::DEBUG ) );
	}


	/**
	 * Log message for log level: debug.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function debug( $message, $context = array() ) {
		return $this->log->debug( $message, $context );
	}

	/**
	 * Log message for log level: info.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function info( $message, $context = array() ) {
		return $this->log->info( $message, $context );
	}


	/**
	 * Log message for log level: notice.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function notice( $message, $context = array() ) {
		return $this->log->notice( $message, $context );
	}


	/**
	 * Log message for log level: warning.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function warning( $message, $context = array() ) {
		return $this->log->warning( $message, $context );
	}


	/**
	 * Log message for log level: error.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function error( $message, $context = array() ) {
		return $this->log->error( $message, $context );
	}


	/**
	 * Log message for log level: alert.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function alert( $message, $context = array() ) {
		return $this->log->alert( $message, $context );
	}


	/**
	 * Log message for log level: emergency.
	 *
	 * @param string $message The log message.
	 * @param array  $context The log context.
	 *
	 * @return boolean Whether the record has been processed.
	 */
	public function emergency( $message, $context = array() ) {
		return $this->log->emergency( $message, $context );
	}


	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}


	/**
	 * Private unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	public function __wakeup() {}
}
