<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (class_exists('Klick_Apv_Dashboard')) return;

/**
 * Class Klick_Apv_Dashboard
 */
class Klick_Apv_Dashboard {

	/**
	 * Klick_Apv_Dashboard constructor
	 */
	public function __construct() {
	}

	/**
	 * Initalize menu and submenu
	 */
	public function init_menu(){

		$capability_required = klick_apv()->capability_required();

		if (!current_user_can($capability_required)) return;

		$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? KLICK_APV_VERSION . '.' . time() : KLICK_APV_VERSION;
		$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

		// Register and enqueue script
		wp_enqueue_script( 'jquery' );
		wp_register_script( "klick_apv_script", KLICK_APV_PLUGIN_URL . 'js/klick-apv' . $min_or_not . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_script( 'klick_apv_script' );

		// Register and enqueue style
		wp_enqueue_style('klick_apv_css', KLICK_APV_PLUGIN_URL . 'css/klick-apv' . $min_or_not . '.css', array(), $enqueue_version);
		wp_enqueue_style('klick_apv_notices_css', KLICK_APV_PLUGIN_URL . 'css/klick-apv-notices' . $min_or_not . '.css', array(), $enqueue_version);

		wp_register_script( 'klick-apv-chart', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js', array('jquery'), 3.3, true); 
		wp_enqueue_script('klick-apv-chart'); 

		wp_register_script( 'klick-apv-google-chart', 'https://www.gstatic.com/charts/loader.js', array('jquery'), 3.3, true); 
		wp_enqueue_script('klick-apv-google-chart'); 

		$icon = KLICK_APV_PLUGIN_URL . "/images/small_icon.png";
		add_options_page('Advanced Plugin View', 'Advanced Plugin View', $capability_required, 'klick_apv', array($this, 'klick_apv_tab_view'),$icon);

		// Define hook and function to render admin notice
		add_action('all_admin_notices', array($this, 'show_admin_dashboard_notice'));

		// Define localize script to get localize string
		wp_localize_script('klick_apv_script', 'klick_apv_admin', array(
			'notice_for_slug_name' => __('Plugin slug is required, e.g. user-activity-logger ','klick-apv'),
			'empty_status_name' => __('In Name, Should not be empty ','klick-apv'),
			'rating_notice' => __('Insufficient ratings... Try back at a later date ','klick-apv'),
			'incorrect_slug_notice' => __('We cannot locate that slug... Please check and try again.','klick-apv'),
			'page' => 'klick_cvm',
		));
	}
	
	/**
	 * Initlize script and localize script
	 *
	 * @return void
	 */
	public function init_user_end(){

		$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? KLICK_APV_VERSION . '.' . time() : KLICK_APV_VERSION;
		$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

		// Register and enqueue script
		wp_enqueue_script( 'jquery' );
		wp_register_script("klick_apv_ui_script", KLICK_APV_PLUGIN_URL . 'js/klick-apv-ui' . $min_or_not . '.js', array('jquery'), $enqueue_version);
		wp_enqueue_script( 'klick_apv_ui_script' );

		// Define localize script to get localize string
		wp_localize_script('klick_apv_ui_script', 'klick_apv', array(
			'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
			'klick_apv_ajax_nonce' => wp_create_nonce('klick_apv_ajax_nonce'),
			'KLICK_APV_PLUGIN_URL' => KLICK_APV_PLUGIN_URL,
		));
	}

	/**
	 * Renders css at user side
	 *
	 * @return void
	 */
	public function init_user_css(){

		$enqueue_version = (defined('WP_DEBUG') && WP_DEBUG) ? KLICK_APV_VERSION . '.' . time() : KLICK_APV_VERSION;
		$min_or_not = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

		// Register and enqueue style
		wp_enqueue_style('klick_apv_ui_css', KLICK_APV_PLUGIN_URL . 'css/klick-apv-ui' . $min_or_not . '.css', array(), $enqueue_version);
	}

	/**
	 * Renders Notice at main WP dashboard
	 *
	 * @return void
	 */
	public function show_admin_dashboard_notice(){
		klick_apv()->get_notifier()->do_notice('dashboard');
	}
	
	/**
	 * Renders tabs page with template
	 *
	 * @return void
	 */
	public function klick_apv_tab_view() {

		$capability_required = klick_apv()->capability_required();

		if (!current_user_can($capability_required)) {
			echo "Permission denied.";
			return;
		}

		?>
		<br>
		<?php
		
		// Define tabs, set default/active tab
		$tabs = $this->get_tabs();
		
		$active_tab = apply_filters('klick_apv_admin_default_tab', 'plugin-stats');
		
		echo '<div class="wrap"><div id="klick_apv_tab_wrap" class="klick-apv-tab-wrap">';

		$this->include_template('klick-apv-tabs-header.php', false, array('active_tab' => $active_tab, 'tabs' => $tabs));

		$tab_data = array();
			
		foreach ($tabs as $tab_id => $tab_description) {

			echo '<div class="klick-apv-nav-tab-contents" id="klick_apv_nav_tab_contents_' . $tab_id . '" ' . (($tab_id == $active_tab) ? '' : 'style="display:none;"') . '>';
			
			do_action('klick_apv_admin_tab_render_begin', $active_tab);
			
			$tab_data[$tab_id] = isset($tab_data[$tab_description])? $tab_data[$tab_description]:array();
			
			$this->include_template('klick-apv-tab-' . $tab_id . '.php',false, array('data' => $tab_data[$tab_id]));

			echo '</div>';
		}
		
		do_action('klick_apv_admin_tab_render_end', $active_tab);
		
		echo '</div></div>';
	}
	
	/**
	 * Set tab names
	 *
	 * @return array
	 */
	public function get_tabs() {
		return apply_filters('klick_apv_admin_page_tabs', array('plugin-stats' => '<span class="dashicons dashicons-plus-alt"></span>' . __('Plugin Stats', 'klick-apv'), 'our-other-plugins' => __('Our other Plugins', 'klick-apv'), 'change-log' => __('Change Log', 'klick-apv')));
	}
	
	/**
	 * Brings in templates
	 *
	 * @return void
	 */
	public function include_template($path, $return_instead_of_echo, $extract_these = array()) {
		if ($return_instead_of_echo) ob_start();

		if (preg_match('#^([^/]+)/(.*)$#', $path, $matches)) {
			$prefix = $matches[1];
			$suffix = $matches[2];
			if (isset(klick_apv()->template_directories[$prefix])) {
				$template_file = klick_apv()->template_directories[$prefix] . '/' . $suffix;
			}
		}
		
		if (!isset($template_file)) {
			$template_file = KLICK_APV_PLUGIN_MAIN_PATH . '/templates/' . $path;
		}

		$template_file = apply_filters('klick_apv_template', $template_file, $path);

		do_action('klick_apv_before_template', $path, $template_file, $return_instead_of_echo, $extract_these);

		if (!file_exists($template_file)) {
			error_log("Klick: template not found: " . $template_file);
		} else {
			extract($extract_these);

			// Defines the vars used in included template file
			$klick_apv = klick_apv();
			$options = klick_apv()->get_options();
			$dashboard = $this;
			include $template_file;
		}

		do_action('klick_apv_after_template', $path, $template_file, $return_instead_of_echo, $extract_these);

		if ($return_instead_of_echo) return ob_get_clean();
	}

	/**
	 * 
	 * This function can be update to suit any URL as longs as the URL is passed
	 *
	 * @param string $url   URL to be check to see if it an klickonit match.
	 * @param string $text  Text to be entered within the href a tags.
	 * @param string $html  Any specific HTMl to be added.
	 * @param string $class Specify a class for the href.
	 */
	public function klick_apv_url($url, $text, $html = null, $class = null) {
		// Check if the URL is klickonit.
		if (false !== strpos($url, '//klick-on-it.com')) {
			// Apply filters.
			$url = apply_filters('klick_apv_klick_on_it_com', $url);
		}
		// Return URL - check if there is HTMl such as Images.
		if (!empty($html)) {
			echo '<a ' . $class . ' href="' . esc_attr($url) . '">' . $html . '</a>';
		} else {
			echo '<a ' . $class . ' href="' . esc_attr($url) . '">' . htmlspecialchars($text) . '</a>';
		}
	}
}
