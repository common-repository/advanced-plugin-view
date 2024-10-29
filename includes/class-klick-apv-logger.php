<?php

if (!defined('ABSPATH')) die('No direct access allowed');

require_once('class-klick-apv-abstract-logger.php');
require_once('class-klick-apv-logger.php');

if (class_exists('Klick_Apv_Logger')) return;

/**
 * Class Klick_Apv_Logger
 */
class Klick_Apv_Logger {
	
	protected $_loggers = array();

	/**
	 * Constructor
	 *
	 * @param $logger
	 *
	 * @return void
	 */
	public function __construct($logger = null) {
		if (!empty($logger)) $this->_loggers = array($logger);
	}

	/**
	 * Add logger to loggers list
	 *
	 * @param $logger
	 *
	 * @return void
	 */
	public function add_logger($logger) {
		$this->_loggers[] = $logger;
	}

	/**
	 * Return list of loggers
	 *
	 * @return array
	 */
	public function get_loggers() {
		return $this->_loggers;
	}
	
	/**
	 * Logs to each logger type
	 *
	 * @param  mixed  $level
	 * @param  string $message
	 *
	 * @return null
	 */
	public function log($level, $message, $logger_ids = array(), $addition_params = array()) {
		
		if (empty($this->_loggers)) return false;

		foreach ($this->_loggers as $logger_type) {

			if ((in_array($logger_type -> id, $logger_ids))) {

				$logger_type -> additiona_params = $addition_params;
			}
			$logger_type->log($level, $message);
		}
	}
	
	/**
	 * Get array of Klick_Apv_Logger instances
	 * Apply filters for logger classes for individual customisation of loggers
	 * Apply filters for logger classes for  customisation of loggers
	 *
	 * @return array mixed
	 */
	public function klick_apv_get_loggers() {

		$loggers = array();

		// Can be enhanced new more logger class here
		$loggers_classes = array(
			'klick_apv_PHP_Logger' => KLICK_APV_PLUGIN_MAIN_PATH . 'includes/class-klick-apv-php-logger.php',
		);

		$loggers_classes = apply_filters('klick_apv_loggers_classes', $loggers_classes);

		if (!empty($loggers_classes)) {
			foreach ($loggers_classes as $logger_class => $logger_file) {
				if (!class_exists($logger_class)) {
					if (is_file($logger_file)) {
						include_once($logger_file);
					}
				}

				if (class_exists($logger_class)) {
					$loggers[] = new $logger_class();
				}
			}
		}

		$loggers = apply_filters('klick_apv_loggers', $loggers);

		$loggers = (!empty($loggers) ? $loggers : array());

		return $loggers;
	}
	
	/**
	 * Activate logs
	 *
	 * @return boolean
	 */
	public  function activate_logs($logger_types) {

		if (empty($logger_types)) return false;
		
		$option = klick_apv()->get_options();
		
		$logging = $option->get_option('logging');
	
		if (!empty($logging)) {
			foreach ($logger_types as $logger_type) {
				$logger_type->enable();
			}
		}
		return true;
	}
	
	/**
	 * Add logger_types to logger object array
	 *
	 * @return void
	 */
	public  function add_loggers($logger_types) {
		if (!empty($logger_types)) {
			$logger = klick_apv()->get_logger();
			foreach ($logger_types as $logger_type) {
				$logger->add_logger($logger_type);
			}
		}
	}
}
