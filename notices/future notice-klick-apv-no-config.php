<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Klick_Apv_No_Config')) return;

require_once(KLICK_APV_PLUGIN_MAIN_PATH . '/includes/class-klick-apv-abstract-notice.php');

/**
 * Class Klick_Apv_No_Config
 */
class Klick_Apv_No_Config extends Klick_Apv_Abstract_Notice {
	
	/**
	 * Klick_Apv_No_Config constructor
	 */
	public function __construct() {
		$this->notice_id = 'advanced-plugin-view';
		$this->title = __('Advanced Plugin View plugin is installed but not configured', 'klick-apv');
		$this->klick_apv = "";
		$this->notice_text = __('Configure it Now', 'klick-apv');
		$this->image_url = '../images/our-more-plugins/apv.svg';
		$this->dismiss_time = 'dismiss-page-notice-until';
		$this->dismiss_interval = 30;
		$this->display_after_time = 0;
		$this->dismiss_type = 'dismiss';
		$this->dismiss_text = __('Hide Me!', 'klick-apv');
		$this->position = 'dashboard';
		$this->only_on_this_page = 'index.php';
		$this->button_link = KLICK_APV_PLUGIN_SETTING_PAGE;
		$this->button_text = __('Click here', 'klick-apv');
		$this->notice_template_file = 'main-dashboard-notices.php';
		$this->validity_function_param = 'advanced-plugin-view/advanced-plugin-view.php';
		$this->validity_function = 'is_plugin_configured';
	}
}
