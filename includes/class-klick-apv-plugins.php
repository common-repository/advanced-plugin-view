<?php
if (!defined('KLICK_APV_VERSION')) die('No direct access allowed');

/**
 * Access via klick_apv()->get_plugin_list().
 */
class Klick_Apv_Plugins {
	
	protected $_plugin_list = array();

	private $options;

	public function __construct() {
		$this->options = klick_apv()->get_options();
		$this->_plugin_list = $this->options->get_option('plugin-name');
	}

	/**
	 * Get full list of plugins
	 * 
	 * @return array
	 */
	public function get_all(){
		return $this->_plugin_list;
	}

	/**
	 * Save plugin
	 * 
	 * @param  array $data
	 * @return array
	 */
	public function save($data) {
		$existence = $this->options->get_option('plugin-name');
		$is_name_exist = array_key_exists($data['slug'], $existence);
		if ($is_name_exist == true) {
			$return_array['messages'] = $this->show_admin_warning(__("Plugin is already exist..Try another.", "klick-mos"),'updated fade');
			$return_array['status'] = 2;
			return $return_array;
		}

		$current = array($data['slug'] => array('name' => $data['name'], 'slug' => $data['slug']));

		if ($existence != "") {
			$existence[$data['slug']] = array('name' => $data['name'], 'slug' => $data['slug']);
			$this->options->update_option('plugin-name', $existence);
		} else {
			$this->options->update_option('plugin-name', $current);	
		}
		
		$return_array['messages'] = $this->show_admin_warning(__("Plugin Saved.", "klick-apv"),'updated fade');
		$return_array['status'] = 1;
		return $return_array;
	}

	/**
	 * Delete plugin
	 * 
	 * @param  array $data
	 * @return boolean
	 */
	public function delete($data) {
		$existence = $this->options->get_option('plugin-name');
		foreach ($existence as $key => $value) {
			if ($key == $data['name'] ) {
				unset($existence[$data['name']]);
			}
		}
		$this->options->update_option('plugin-name', $existence);
		return true;
	}

	/**
	 * Render plugin list in row format
	 * 
	 * @param  array $data
	 * @return array
	 */
	public function plugin_list(){
		if (is_array($this->_plugin_list) && count($this->_plugin_list) > 0) {
			$plugin_details = "";
			foreach ($this->get_all() as  $value) {
			$name = $value['name'];	
			$value = $value['slug'];	
				$plugin_details .= "<div id='$value' class='klick-apv-row-container'>";
					$plugin_details .= "<span data-label='Delete' style='width: 30px; text-align: center;' data-id='$value' class='klick-apv-delete-row'><span class='dashicons dashicons-trash'></span></span>";
					$plugin_details .= "<span><a class='details-link theme-install' data-id='$value' href='#$value'>$name</a></span>";

					$plugin_details .= '<div id=' . $value . ' class="apv-overlay" >';
						$plugin_details .= '<div class="content">';
							$plugin_details .= '<h2>' . $value . '</h2><span class="details-link-close dashicons dashicons-no"></span>';

							$plugin_details .= '<div class="klick-apv-row-right">';
								$plugin_details .= '<div class="klick-apv-row-widget"><div class="download-loader"></div>';
								$plugin_details .= '<div id="rating"></div></div>';

								$plugin_details .= '<div class="klick-apv-row-widget"><h4>Ratings</h4><div class="download-loader"></div>';
								$plugin_details .= '<div id="ratings"></div></div>';
							
								$plugin_details .= '<div class="klick-apv-row-widget"><h4>Contributors</h4><div class="download-loader"></div>';
								$plugin_details .= '<div id="contributors"></div></div>';							
							$plugin_details .= '</div>';

							$plugin_details .= '<div class="klick-apv-row-left">';
								$plugin_details .= '<div class="klick-apv-row-widget"><h4>Version Stats</h4><div id="' . $value . '_plugin-version-stats" class="chart version-stats"></div></div>';
								$plugin_details .= '<div class="klick-apv-row-widget"><h4>Download Stats</h4>';
								$plugin_details .= '<div class="chart-container w-80 center mb4"><canvas id="' . $value . '_myChart" class="" width="400" height="100"></canvas></div></div>';

								$plugin_details .= '<div class="klick-apv-row"><div class="klick-apv-row-widget klick-apv-row-widget-50"><h4>Download Summary</h4>';
								$plugin_details .= '<div id="download_summary"></span></div></div>';

								$plugin_details .= '<div class="klick-apv-row-widget klick-apv-row-widget-50"><h4>Details Summary</h4>';
								$plugin_details .= '<div id="details_summary"></div></div></div>';
							$plugin_details .= '</div>';

						$plugin_details .= '</div>';
					$plugin_details .= '</div>';
				$plugin_details .= '</div>';
			}
			return $plugin_details;
		}
	}

	/**
	 * Create ajax notice
	 * @param  String 	$message as a notice
	 * @param  String 	$class an string if many then separated by space defalt is 'updated'
	 *
	 * @return String 	returns message
	 */
	public function show_admin_warning($message, $class = "updated") {
		return  '<div class="klick-ajax-notice ' . $class . '">' . "<p>$message</p></div>";
	}
}
