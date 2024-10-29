<?php 

if (!defined('KLICK_APV_PLUGIN_MAIN_PATH')) die('No direct access allowed');

/**
 * Commands available from control interface (e.g. wp-admin) are here
 * All public methods should either return the data, or a WP_Error with associated error code, message and error data
 */
/**
 * Sub commands for Ajax
 *
 */
class Klick_Apv_Commands {
	private $options;
	private $plugins;
	
	/**
	 * Constructor for Commands class
	 *
	 */
	public function __construct() {
		$this->options = Klick_Apv()->get_options();
		$this->plugins = Klick_Apv()->get_plugins();
	} 

	/**
	 * dis-miss button
	 *
	 * @param  Array 	$data an array of data UI form
	 *
	 * @return Array 	$status
	 */
	public function dismiss_page_notice_until($data) {

		return array(
			'status' => $this->options->dismiss_page_notice_until($data),
			);
	}

	/**
	 * dis-miss button
	 *
	 * @param  Array 	$data an array of data UI form
	 *
	 * @return Array 	$status
	 */
	public function dismiss_page_notice_until_forever($data) {
		
		return array(
			'status' => $this->options->dismiss_page_notice_until_forever($data),
			);
	}
	
	/**
	 * This sends the passed data value over to the save function
	 *
	 * @param  Array    $data an array of data UI form
	 *
	 * @return Array    $status
	 */
	public function klick_apv_save_settings($data) {
		
		return array(
			'status' => $this->plugins->save($data),
		);
	}
	
	/**
	 * This sends the passed data value over to the delete function
	 *
	 * @param  Array    $data an array of data UI form
	 *
	 * @return Array    $status
	 */
	public function klick_apv_delete_row($data) {
		return array(
			'status' => $this->plugins->delete($data),
		);
	}

	/**
	 * This sends the passed data value over to the reload function
	 *
	 * @param  Array    $data an array of data UI form
	 *
	 * @return Array    $status
	 */
	public function klick_apv_reload($data) {
		return array(
			'data' => $this->plugins->plugin_list(),
		);
	}
}
