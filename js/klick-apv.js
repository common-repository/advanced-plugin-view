/**
 * Send an action via admin-ajax.php
 * 
 * @param {string} action - the action to send
 * @param * data - data to send
 * @param Callback [callback] - will be called with the results
 * @param {boolean} [json_parse=true] - JSON parse the results
 **/

var klick_apv_send_command = function (action, data, callback, json_parse) {
	json_parse = ('undefined' === typeof json_parse) ? true : json_parse;
	var ajax_data = {
		action: 'klick_apv_ajax',
		subaction: action,
		nonce: klick_apv_ajax_nonce,
		data: data
	};
	jQuery.post(ajaxurl, ajax_data, function (response) {
		
		if (json_parse) {
			try {
				var resp = JSON.parse(response);
			} catch (e) {
				console.log(e);
				console.log(response);
				return;
			}
		} else {
			var resp = response;
		}
		
		if ('undefined' !== typeof callback) callback(resp);
	});
}

/**
 * When DOM ready
 * 
 */
jQuery(document).ready(function ($) {
	klick_apv = klick_apv(klick_apv_send_command);
});

/**
 * Function for sending communications
 * 
 * @callable klick_apv_send_command Callable
 * @param {string} action - the action to send
 * @param * data - data to send
 * @param Callback [callback] - will be called with the results
 * @param {boolean} [json_parse=true] - JSON parse the results
 */
/**
 * Main klick_apv
 * 
 * @param {sendcommandCallable} send_command
 */
var klick_apv = function (klick_apv_send_command) {
	var $ = jQuery;
	$(".klick-apv-overlay").hide();
	$('#klick_apv_add_plugin').hide();
	
	/**
	 * Proceses the tab click handler
	 *
	 * @return void
	 */
	$('#klick_apv_nav_tab_wrapper .nav-tab').click(function (e) {
		e.preventDefault();
		
		var clicked_tab_id = $(this).attr('id');
	
		if (!clicked_tab_id) { return; }
		if ('klick_apv_nav_tab_' != clicked_tab_id.substring(0, 18)) { return; }
		
		var clicked_tab_id = clicked_tab_id.substring(18);

		$('#klick_apv_nav_tab_wrapper .nav-tab:not(#klick_apv_nav_tab_' + clicked_tab_id + ')').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');

		$('.klick-apv-nav-tab-contents:not(#klick_apv_nav_tab_contents_' + clicked_tab_id + ')').hide();
		$('#klick_apv_nav_tab_contents_' + clicked_tab_id).show();
	});
	
	/**
	 * Add plugins
	 *
	 * @return void
	 */
	$('#klick_apv_plugin_add').click(function (e) {
		e.preventDefault();
		show_apv_form();
		apv_add_form_reset();
		
	});
	
	/**
	 * Cancel plugins
	 *
	 * @return void
	 */
	$('#klick_apv_plugin_cancel').click(function (e) {
		e.preventDefault();
		$(".klick-notice-message").html("");
		$(".klick-notice-message").css('display','none');
		$(".klick-ajax-notice").html("");
		$(".klick-ajax-notice").css('display','none');
		show_apv_list();
	});
	
	/**
	 * Gathers the details from form
	 *
	 * @returns (string) - serialized row data
	 */
	function gather_row(command){
		var name = $("#klick_apv_plugin_name").val();
		if(is_name_valid() === false) {
			set_notice_message_generate('.klick-notice-message',klick_apv_admin.notice_for_slug_name);
			return false;	
		} else {
			return 'name=' + name;
		}	
	}
	
	/**
	 * Save plugins
	 *
	 * @return void
	 */
	$('#klick_apv_plugin_save').on('click',function (e) {
		e.preventDefault();
		var command = $("#apv_command").val();
		var data = gather_row(command);
		var name = $("#klick_apv_plugin_name").val();
		if(data === false) return false;
		$.ajax({
			url: 'https://wordpress.org/plugins/wp-json/plugins/v1/plugin/' + name,
			type: 'GET',
			cache: false,
			dataType: 'json',
			success : function(response) {
				var data = 'name='+response.name+'&slug='+response.slug;
				klick_apv_send_command('klick_apv_save_settings', data, function (resp) {
					if(resp.status['status'] == 1) { // Save
						apv_list_reload();
						show_apv_list();
						$('.klick-ajax-message').html(resp.status['messages']);
						$('.klick-ajax-message').slideDown();
						$('.fade').delay(2000).slideUp(200, function(){
						});
					}
					if(resp.status['status'] == 2) { // Alreday name exist
						$('.klick-ajax-message').html(resp.status['messages']);
						$('.klick-ajax-message').slideDown();
						$('.fade').delay(10000).slideUp(200, function(){
						});
					}
				});
			},
			error : function(error) {
					$('.klick-ajax-message').html('<div class="klick-ajax-notice updated fade">' + "<p>"+klick_apv_admin.incorrect_slug_notice+"</p></div>");
					$('.fade').delay(5000).slideUp(200, function(){
					});
					return false;
			}
		});
	});

	/**
	 * Delete plugin
	 *
	 * @return void
	 */
	$(document).on('click', '.klick-apv-delete-row', function(e) {	
		e.preventDefault();
		$(".klick-apv-overlay").show();
		var name = $(this).attr('data-id');
		var data = 'name='+name;
		klick_apv_send_command('klick_apv_delete_row', data, function (resp) {
			if(resp.status === true) {
				apv_list_reload();
			}
		});
	});

	/**
	 * To reload apv list
	 *
	 * @return void
	 */
	function apv_list_reload(){
		$(".klick-apv-overlay").show();
		var data = "reload=1";
		klick_apv_send_command('klick_apv_reload', data, function (resp) {
			$(".klick-apv-overlay").hide();
			$(".apv-list-table").html(resp.data);
		});
	}

	/**
	 * To reset whole add plugins form
	 *
	 * @return void
	 */
	function apv_add_form_reset(){
		$('#klick_apv_plugin_name').val("");
	}

	/**
	 * show plugins list
	 *
	 * @return void
	 */
	function show_apv_list(){
		$('#klick_apv_add_plugin').hide();
		$(".apv-list-container").show();
		$('#klick_apv_plugin_list').show();
	}

	/**
	 * Show add plugins form
	 *
	 * @return void
	 */
	function show_apv_form(){
		$('#klick_apv_plugin_list').hide();
		$(".apv-list-container").hide();
		$('#klick_apv_add_plugin').show();
	}

	/**
	 * Validtor for plugin name
	 *
	 * @return void
	 */
	$("#klick_apv_plugin_name").keyup(function(){
		$("#klick_apv_plugin_save").attr('disabled',false);
		var klick_apv_plugin_name = $.trim($("#klick_apv_plugin_name").val());
		if(check_for_alphanumeric_without_space(klick_apv_plugin_name) === true) {
			set_notice_message_generate('.klick-notice-message',klick_apv_admin.notice_for_slug_name);
		} else {
			$('.klick-notice-message').slideUp();
		}
	});	

	/**
	 * Test expression if any non numeric or alpha is entered
	 *
	 * @return boolean
	 */
	function check_for_alphanumeric_with_space( str ) {
	 	return !/^[a-zA-Z0-9-_]+$/.test(str);
	}

	/**
	 * Test expression if any non numeric or alpha is entered
	 *
	 * @return boolean
	 */
	function check_for_alphanumeric_without_space( str ) {
	 	var regexp = /^[a-zA-Z0-9-_]+$/;
	 	if (str.search(regexp) == -1) {
	 		return true; 
	 	} else {
	 	  	return false;
	 	}
	}

	/**
	 * Check whether passed param is empty or not
	 *
	 * @return boolean
	 */
	function is_not_empty(name){
		var result = (name.length == "") ? true : false;
		return result;
	}

	/**
	 * Create and render notice admin side
	 *
	 * @string string selecoter, e.g. #msg_area
	 * @msg string msg
	 * @return void
	 */
	function set_notice_message_generate(selector, msg){
		$(""+selector+"").addClass('klick-notice-message notice notice-error is-dismissible');
		$(""+selector+"").html("<p>" + msg + "</p>");
		$(""+selector+"").slideDown();
		$("#klick_apv_plugin_save").attr('disabled','disabled');
		return false;
	}

	/**
	 * Check valid name by all defined rules
	 *
	 * @return boolean
	 */
	function is_name_valid(){
		var klick_apv_plugin_name = $.trim($("#klick_apv_plugin_name").val());
		if(is_not_empty(klick_apv_plugin_name) === true) {
			set_notice_message_generate('.klick-notice-message',klick_apv_admin.empty_status_name);
			return false;
		} else if(check_for_alphanumeric_without_space(klick_apv_plugin_name) === true) {
			set_notice_message_generate('.klick-notice-message',klick_apv_admin.notice_for_slug_name);
			return false;
		} else {
			$('.klick-notice-message').slideUp();
			return true;
		}
	}

	/**
	 * Close the detail panel
	 *
	 * @return void
	 */
	$(document).on('click', '.details-link-close', function(){
		$(this).parent().parent().css('display','none');
	});

	/**
	 * Open and sync API details in panel view
	 *
	 * @return void
	 */
	$(document).on('click', '.details-link', function(){
		$('.overlay').css('display','none');
		$(this).parent().next().css('display','block');
		Chart.defaults.global.maintainAspectRatio = false;
		Chart.defaults.global.scaleLabel = "<%=parseInt(value).toLocaleString()%>";
		var data_id;
		data_id = $(this).attr("data-id");
		
		var settings = {};
		var settings = {slug:data_id,l10n : {all_time : "All Time",date: "Date", downloads: "Downloads", noData: "No data yet", today: "Today", yesterday: "Yesterday",last_week: "Last Week"}};

		$("#"+data_id+"").find("#download_summary").html("");
		$("#"+data_id+"").find("#details_summary").html("");
		$(".download-loader").show();

		// Download summary
		$.ajax({
			url: 'https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug='+data_id +'&historical_summary=1&callback=?',
			type: 'GET',
			cache: false,
			dataType: 'jsonp',
			success : function(response) {
				$(".download-loader").hide();
				$("#"+data_id+"").find("#download_summary").append("<span>Today : " + response.today+"</span>");
				$("#"+data_id+"").find("#download_summary").append("<span>Yesterday : " + response.yesterday+"</span>");
				$("#"+data_id+"").find("#download_summary").append("<span>Last_week : " + response.last_week+"</span>");
				$("#"+data_id+"").find("#download_summary").append("<span>All_time : " + response.all_time+"</span>");
			}
		});

		// Details summary
		$.ajax({
			url: 'https://wordpress.org/plugins/wp-json/plugins/v1/plugin/' + data_id,
			type: 'GET',
			cache: false,
			dataType: 'json',
			success : function(response) {
				$(".download-loader").hide();
				var rating_row = "";		
				$("#"+data_id+"").find("#details_summary").append("<span>Active Installs : " + response.active_installs+"</span>");
				$("#"+data_id+"").find("#details_summary").append("<span>Version : " + response.version+"</span>");
				$("#"+data_id+"").find("#details_summary").append("<span>Last Updated : " + response.last_updated+"</span>");
				$("#"+data_id+"").find("#details_summary").append("<span>Author : " + response.author+"</span>");
				$("#"+data_id+"").find("#details_summary").append("<span>Requires WP version : " + response.requires+"</span>");
				$("#"+data_id+"").find("#details_summary").append("<span>Tested up to : " + response.tested+"</span>");

				var tags = "";
				var rating = "";
				var star_html = "";
				var ratings = "";

				$.each(response.tags, function(key,value){
					tags += "<a href='https://wordpress.org/plugins/tags/"+value+"' target='_blank'>"+value+"</a>";
				});

				$("#"+data_id+"").find("#details_summary").append("<span class='tags'>Tags : "+tags+"</span>");

				var rating = response.rating/20;
				var star_array = ['empty','empty','empty','empty','empty'];

				if(rating > 0 && rating <= 0.5 ) {
					var star_array= ['half','empty','empty','empty','empty'];
				}
				if(rating > 0.5 && rating <= 1 ) {
					var star_array= ['filled','empty','empty','empty','empty'];
				}

				if(rating > 1 && rating <= 1.5 ) {
					var star_array= ['filled','half','empty','empty','empty'];
				}
				if(rating > 1.5 && rating <= 2 ) {
					var star_array= ['filled','filled','empty','empty','empty'];
				}
				
				if(rating > 2 && rating <= 2.5 ) {
					var star_array= ['filled','filled','half','empty','empty'];
				}
				if(rating > 2.5 && rating <= 3 ) {
					var star_array= ['filled','filled','filled','empty','empty'];
				}

				if(rating > 3 && rating <= 3.5 ) {
					var star_array= ['filled','filled','filled','half','empty'];
				}
				if(rating > 3.5 && rating <= 3 ) {
					var star_array= ['filled','filled','filled','filled','empty'];
				}

				if(rating > 4 && rating <= 4.5 ) {
					var star_array= ['filled','filled','filled','filled','half'];
				}
				if(rating > 4.5 && rating <= 5 ) {
					var star_array= ['filled','filled','filled','filled','filled'];
				}

				// check if all empty
				var no_of_empty = 0;
				if (star_array[0] === 'empty') {
					no_of_empty = 1;
				}

				star_html += '<div class="rating">';
				star_html += '<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="'+rating+' out of 5 stars" data-rating="4">';
			
				if(no_of_empty === 1) {
					star_html += '<span>' + klick_apv_admin.rating_notice + '</span>';
				} else {
					star_html += '<span class="dashicons dashicons-star-'+star_array[0]+'"></span>';
					star_html += '<span class="dashicons dashicons-star-'+star_array[1]+'"></span>';
					star_html += '<span class="dashicons dashicons-star-'+star_array[2]+'"></span>';
					star_html += '<span class="dashicons dashicons-star-'+star_array[3]+'"></span>';
					star_html += '<span class="dashicons dashicons-star-'+star_array[4]+'"></span>';
				}

				star_html += '</div>';
				star_html += '</div>';

				$("#"+data_id+"").find("#rating").html("");
				$("#"+data_id+"").find("#rating").append(""+star_html+"");

				var ratings = [];
				jQuery.each(response.ratings,function(key,value){
				  ratings.push(value);
				});

				ratings = ratings.reverse();
				rating_row  = "<ul class='ratings-list'>";

				$.each(ratings, function(key,value){
					var bar_backcolor = (value*100/1000);
					 rating_row  += '<li class="counter-container">';
					 rating_row += '<a href="https://wordpress.org/support/plugin/'+data_id+'/reviews/?filter='+ (5 - key) +'" target="_blank">';
					 rating_row += '<span class="counter-label">'+ (5 - key) +' stars</span>';
					 rating_row += '<span class="counter-back">';
					 rating_row += '<span class="counter-bar" style="width: '+bar_backcolor+'%;"></span>';
					 rating_row += '</span>';
					 rating_row += '<span class="counter-count">'+value+'</span>'
					 rating_row += '</a>';
					 rating_row += '</li>';
				});

				rating_row  += "</ul>";
				$("#"+data_id+"").find("#ratings").html("");
				$("#"+data_id+"").find("#ratings").append(""+rating_row+"");

				var contributors = "";
				contributors  += "<ul class='contributors-list' id='contributors_list'>";

				$.each(response.contributors, function(key,value){
					contributors += '<li>';
					contributors += '<img alt="" src="'+value.avatar+'" srcset="'+value.avatar+'" class="avatar avatar-32 photo" height="32" width="32">';				
					contributors += '<a href="'+value.profile+'" target="_blank">'+value.display_name+'</a>';
					contributors += '</li>';
				});

				contributors  += "</ul>";
				$("#"+data_id+"").find("#contributors").html("");
				$("#"+data_id+"").find("#contributors").append(""+contributors+"");
			}
		});

		// download chart
		$.ajax({
			url: 'https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug='+data_id +'&limit=267&callback=?',
			type: "GET",
			cache: false,
			dataType: "jsonp",
			success : function(response) {
				var keys = [];
				var filtered_months = [];
				var values = [];
				
				var monthtotal = 0;
				var d = new Date();
				var month = d.getMonth();
				month = month - 6;
				
				$.each(response, function(key, val ){
					formattedKey = new Date( key );
					var loopmonth = formattedKey.getMonth();
					if (loopmonth >= month) {
						if (loopmonth == month) {
							monthtotal = monthtotal + parseInt(val);
						} else {
							keys.push(month);
							values.push(monthtotal);
							monthtotal = 0;
							month = month + 1;
						}
					}
				});
				
				var months = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", 
				               "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
				$.each(keys,function(key, value){
				 	filtered_months.push(months[value+1]);
				});

				var ctx = document.getElementById(""+data_id+"_myChart").getContext('2d');
				var myChart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: filtered_months,
					datasets: [{
						label: '# of Downloads',
						data: values,
						borderWidth: 2,
						borderColor: '#357edd',
						backgroundColor: '#357edd',
						fill:false,
						pointRadius : 1,
					}]
				},
				options: {
					scales: {
						yAxes: [{
							ticks: {
								callback: function (value, index, values) {
									return value.toLocaleString();
								},
								beginAtZero:false
							}
						}]
					},
					tooltips :{
						enabled:true,
					}
				}
			});
			}	
		});

		/* globals google:object */
		google.charts.load( 'current', {
			packages: [ 'corechart' ]
		});

		// Version stats
		$.getJSON( 'https://api.wordpress.org/stats/plugin/1.0/?slug=' + settings.slug + '&callback=?', function ( versions ) {
			var obj = document.getElementById( ''+settings.slug+'_plugin-version-stats' );
			if ( 0 === versions.length ) {
				$( '#'+settings.slug+'_plugin-version-stats' ).text( settings.l10n.noData );
				return;
			}
			google.charts.setOnLoadCallback( function() {
				var barHeaders  = [ '' ],
					barValues   = [ '' ],
					versionList = [],
					index       = 0,
					data, formatter;

				// Gather and sort the list of versions.
				$.each( versions, function( version ) {
					versionList.push( version );
				});

				// Sort the version list by version.
				versionList.sort( function( a, b ) {
					a = a.split( '.' );
					b = b.split( '.' );
					return ( a[0] !== b[0] ) ? a[0]-b[0] : a[1]-b[1];
				});

				// Move 'other' versions to the beginning.
				if ( 'other' === versionList[ versionList.length - 1 ] ) {
					versionList.unshift( versionList.pop() );
				}

				// Add all the versions
				versionList.forEach( function( version ) {
					barHeaders.push( version );
					barValues.push( versions[ version ] );
				});

				data = google.visualization.arrayToDataTable([
					barHeaders,
					barValues
				]);

				// Format it as percentages
				formatter = new google.visualization.NumberFormat( {
					fractionDigits: 1,
					suffix: '%'
				});

				$.each( barValues, function( value ) {
					if ( barValues[ value ] ) {
						formatter.format( data, ++index );
					}
				});

				new google.visualization.BarChart( obj ).draw( data, {
					legend: {
						position: 'bottom'
					},
					chartArea: {
						left: '0',
						width: '100%',
						height: '80%',
						top: '10%'
					},
					hAxis: {
						gridlines: {
							color: 'transparent'
						}
					},
					isStacked: true
				});
			});
		});
 });		
}
