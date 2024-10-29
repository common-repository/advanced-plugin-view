<!-- First Tab content -->
<div id="klick_apv_tab_first">
	<div class="klick-notice-message"></div>
	<div class="klick-ajax-message"></div>

	<div class="klick-apv-data-box">
		<div class="klick-apv-overlay">
			<img class="loading-image" src="<?php echo KLICK_APV_PLUGIN_URL . 'images/ajax-loader.gif' ?> " alt="Loading.." />'
		</div>
		<div class="apv-list-container">
			<article class="klick-apv-data-table">
				<h1>Advanced Plugin View</h1> <!-- Header tab-->
				<div id="klick_apv_plugin_list" class="klick-apv-add-btn">
					<div class="klick-apv-info">
					 	<button id = "klick_apv_plugin_add" class = "klick_btn button button-primary">Add New Plugin</button> 
					 	<input type="hidden" id="apv_command" value="">
					</div>
				</div>
				<div class="apv-list-table">
			 		<?php echo klick_apv()->get_plugins()->plugin_list(); ?>
				</div>
			</article>
		</div>
		<div id="klick_apv_add_plugin">
			<ul>
				<li>
					<label>Enter Plugin Slug :</label>
					<div class="apv-list-label-content">
						<input type="text" name="klick_apv_plugin_name" id="klick_apv_plugin_name" placeholder="e.g. contact-form-7">
					</div>
				</li>
				<li>
					<label>&nbsp;</label>
					<div class="apv-list-label-content">
						<button id = "klick_apv_plugin_save" class = "klick_btn button button-primary">Add</button>
						<button id = "klick_apv_plugin_cancel" class = "klick_btn button button-primary">Cancel</button>
					</div>
				</li>
			</ul>
		</div>
	</div>	
</div>

<script type="text/javascript">
	var klick_apv_ajax_nonce ='<?php echo wp_create_nonce('klick_apv_ajax_nonce'); ?>';
</script>
