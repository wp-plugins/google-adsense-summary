<?php
/*
Plugin Name: Google Adsense Summary
Plugin URI: http://learnix.net
Description: Google Adsense Summary will check your adsense page and show you what you've earned.
It will display yesterdays, todays, this month, last month and last payment earnings.
Version: 1.0.0
Author: agentc0re
Author URI: http://learnix.net
*/

/*  Copyright 2010

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
* Guess the wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

/**
* Class google_adsense_summary
* Main Plugin class
* Google Adsense Summary is abreveated "gas" through most of this plugin
*/
if (!class_exists("google_adsense_summary")) {
	class google_adsense_summary {

		/**
		* Assign our class variables here
		*/
		private $username;
		private $password;
		private $cookiefile;
		private $localizationDomain = "adsense_stats";
		private $thispluginurl = '';
		private $thispluginpath = '';
		private $adminOptionsName = 'GoogleAdsenseSummaryAdminOptions';
		private $display_today;
		private $display_yesterday;
		private $display_last7days;
		private $display_thismonth;
		private $display_lastmonth;
		private $display_alltime;
		
		/**
		* Compatible php4 constructor
		*/
		function adsense_summary(){$this->__construct();}

		/**
		* PHP5 constructor
		*/
		function __construct() {

			// Initialize the options **Required**
			$this->getAdminOptions();

			//Language Setup
			$locale = get_locale();
			$mo = dirname(__FILE__) . "/languages/" . $this->localizationDomain . "-".$locale.".mo";
			load_textdomain($this->localizationDomain, $mo);

			//Constants setup
			$this->cookiefile = tempnam("", "adsense_"); //dirname(__FILE__)."/cookiefile";
			//$this->cookiefile = $this->thispluginpath."adsense_"; //dirname(__FILE__)."/cookiefile";
			/*
			$this->username = $gasOptions['username'];
			$this->password = $gasOptions['password'];
			$this->display_today = $gasOptions['today'];
			$this->display_yesterday = $gasOptions['yesterday'];
			$this->display_last7days = $gasOptions['last7days'];
			$this->display_thismonth = $gasOptions['lastmonth'];
			$this->display_lastmonth = $gasOptions['thismonth'];
			$this->display_alltime = $gasOptions['alltime'];
			*/
			$this->thispluginurl = PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
			$this->thispluginpath = PLUGIN_PATH . '/' . dirname(plugin_basename(__FILE__)).'/';

			//Actions
			//register_activation_hook( __FILE__, array(&$this, 'init'));
			/**
			* Need to setup deactivation hook
			*/
			//register_deactivation_hook( __FILE__, array(&$gas_pluginSeries, 'init'));
			add_action('admin_menu', array(&$this, 'GoogleAdsenseSummary_ap'));
			add_action('admin_head-index.php', array(&$this, 'GoogleAdsenseSummaryJS'));
			add_action('wp_ajax_gas_ajax_widget', array(&$this, 'GoogleAdsenseSummaryAjaxWidget'));

			//Widgets
			add_action('wp_dashboard_setup', array(&$this, 'GoogleAdsenseSummaryDashboardWidgetInit'));

			//Filters


		}

		/**
		* This is to initialize our admin options.
		* This will also be used to call your admin options
		*
		* gasAdminOptions: Assigns defaults for our admin options
		*
		* gasOptions: attempts to find previous options
		*
		* If there are options, overwrite the default values
		*
		* update_option: Store the options in the wordpress database
		*
		* return: return the options for our use
		*/
		function getAdminOptions() {
			$gasAdminOptions = array('username' => 'adsenseuser@youremail.domain',
									'password' => 'your-adsense-password',
									'display_today' => 'true',
									'display_yesterday' => 'false',
									'display_last7days' => 'false',
									'display_thismonth' => 'true',
									'display_lastmonth' => 'false',
									'display_alltime' => 'false');
			$gasOptions = get_option($this->adminOptionsName);
			if (!empty($gasOptions)) {
				foreach ($gasOptions as $key => $option)
					$gasAdminOptions[$key] = $option;
			}
			update_option($this->adminOptionsName, $gasAdminOptions);
			return $gasAdminOptions;
		} // End getAdminOptions() Function

		/**
		* Initialize the admin options function above

		function  init() {
			$this->getAdminOptions();
		} // End init() function
		*/

		/**
		* Init Admin options page
		*/
		function GoogleAdsenseSummary_ap() {
			add_options_page('Google Adsense Summary', 'Google Adsense Summary', 9, basename(__FILE__), array(&$this, 'printAdminPage'));
		}


		/**
		* Prints out the admin page
		*/
		function printAdminPage() {
			$gasOptions = $this->getAdminOptions();

			if (isset($_POST['update_gasPluginSettings'])) {
				if (isset($_POST['gas_username']))
					$gasOptions['username'] = $_POST['gas_username'];
				if (!empty($_POST['gas_password']))
					$gasOptions['password'] = $_POST['gas_password'];
				if (isset($_POST['gas_display_today']))
					$gasOptions['display_today'] = $_POST['gas_display_today'];
				if (isset($_POST['gas_display_yesterday']))
					$gasOptions['display_yesterday'] = $_POST['gas_display_yesterday'];
				if (isset($_POST['gas_display_last7days']))
					$gasOptions['display_last7days'] = $_POST['gas_display_last7days'];
				if (isset($_POST['gas_display_thismonth']))
					$gasOptions['display_thismonth'] = $_POST['gas_display_thismonth'];
				if (isset($_POST['gas_display_lastmonth']))
					$gasOptions['display_lastmonth'] = $_POST['gas_display_lastmonth'];
				if (isset($_POST['gas_display_alltime']))
					$gasOptions['display_alltime'] = $_POST['gas_display_alltime'];

				update_option($this->adminOptionsName, $gasOptions);
?>
<div class="updated"><p><strong><?php _e("Settings Updated.", "google_adsense_summary");?></strong></p></div>
<?php
			} // End if options _POST
?>
<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<?php settings_fields('google_adsense_summary_plugin_options'); ?>
	<h3>Google Adsense Summary Admin Options</h3>
	<p>Please your Adsense Login information below</p>
	<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
		<tr valign="top">
			<th><b><?php _e('Adsense Username:', $this->localizationDomain); ?></b></th>
			<td><input type="text" id="gas_password" name="gas_username" value="<?php echo $gasOptions['username']; ?>" /></td>
		</tr>
		<tr valign="top">
			<th><b><?php _e('Adsense Password:', $this->localizationDomain); ?></b></th>
			<td><input type="password" id="gas_password" name="gas_password" />For security reasons the password field will remain blank after you save your password for the first time.</td>
		</tr>
		</table>
		<br /><br /><br />
		<h3>Display the periods you would like shown in the widget</h3>
		<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
		<tr valign="top">
			<th><?php _e('Today:', $this->localizationDomain); ?></th>
			<td><label for="gas_today_yes"><input type="radio" id="display_today_yes" name="gas_display_today" value="true" <?php if ($gasOptions['display_today'] == "true") { _e('checked="checked"', $this->localizationDomain); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="display_today_no"><input type="radio" id="display_today_no" name="gas_display_today" value="false" <?php if ($gasOptions['display_today'] == "false") { _e('checked="checked"', $this->localizationDomain); }?>/> No</label></p></td>
		</tr>
		<tr valign="top">
			<th><?php _e('Yesterday:', $this->localizationDomain); ?></th>
			<td><label for="gas_yesterday_yes"><input type="radio" id="display_yesterday_yes" name="gas_display_yesterday" value="true" <?php if ($gasOptions['display_yesterday'] == "true") { _e('checked="checked"', $this->localizationDomain); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="display_yesterday_no"><input type="radio" id="display_yesterday_no" name="gas_display_yesterday" value="false" <?php if ($gasOptions['display_yesterday'] == "false") { _e('checked="checked"', $this->localizationDomain); }?>/> No</label></p></td>
		</tr>
		<tr valign="top">
			<th><?php _e('Last 7 Days:', $this->localizationDomain); ?></th>
			<td><label for="gas_last7days_yes"><input type="radio" id="display_last7days_yes" name="gas_display_last7days" value="true" <?php if ($gasOptions['display_last7days'] == "true") { _e('checked="checked"', $this->localizationDomain); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="display_last7days_no"><input type="radio" id="display_last7days_no" name="gas_display_last7days" value="false" <?php if ($gasOptions['display_last7days'] == "false") { _e('checked="checked"', $this->localizationDomain); }?>/> No</label></p></td>
		</tr>
		<tr valign="top">
			<th><?php _e('This Month:', $this->localizationDomain); ?></th>
			<td><label for="gas_thismonth_yes"><input type="radio" id="display_thismonth_yes" name="gas_display_thismonth" value="true" <?php if ($gasOptions['display_thismonth'] == "true") { _e('checked="checked"', $this->localizationDomain); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="display_thismonth_no"><input type="radio" id="display_thismonth_no" name="gas_display_thismonth" value="false" <?php if ($gasOptions['display_thismonth'] == "false") { _e('checked="checked"', $this->localizationDomain); }?>/> No</label></p></td>
		</tr>
		<tr valign="top">
			<th><?php _e('Last Month:', $this->localizationDomain); ?></th>
			<td><label for="gas_lastmonth_yes"><input type="radio" id="display_lastmonth_yes" name="gas_display_lastmonth" value="true" <?php if ($gasOptions['display_lastmonth'] == "true") { _e('checked="checked"', $this->localizationDomain); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="display_lastmonth_no"><input type="radio" id="display_lastmonth_no" name="gas_display_lastmonth" value="false" <?php if ($gasOptions['display_lastmonth'] == "false") { _e('checked="checked"', $this->localizationDomain); }?>/> No</label></p></td>
		</tr>
		<tr valign="top">
			<th><?php _e('All Time:', $this->localizationDomain); ?></th>
			<td><label for="gas_alltime_yes"><input type="radio" id="display_alltime_yes" name="gas_display_alltime" value="true" <?php if ($gasOptions['display_alltime'] == "true") { _e('checked="checked"', $this->localizationDomain); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="display_alltime_no"><input type="radio" id="display_alltime_no" name="gas_display_alltime" value="false" <?php if ($gasOptions['display_alltime'] == "false") { _e('checked="checked"', $this->localizationDomain); }?>/> No</label></p></td>
		</tr>
	</table>
	<div class="submit">
		<input type="submit" name="update_gasPluginSettings" value="<?php _e('Update Settings', $this->localizationDomain); ?>" />
	</div>
</form>
<p>I would like to take this space and give a special thanks to the following:</p>
<p>[-jon-] from ##php on freenode.net.  He went to the trouble of coaching me on a few things, 1 on 1.  Thanks man.</p>
<p>##php in general.  Got a lot of help from various individuals about proper syntax and how I was doing things in a php4 way<p>
<p>sivil from #wordpress on freenode.net.  Got me pointed in the ajax direction for loading the dashboard widget as the whole dashboard loads versus the whole dashboard waiting on it to finally load.</p>
<p>fire|bird, dive and mag0o from ##slackware-offtopic.  Thanks for answering all my dumb questions.  :D</p>
<p>Last but not least: script which most of this is based off of: Gary http://www.garyshood.com</p>
</div>
<?php
		}//End function printAdminPage()

		/**
		* Dashboard Widget
		*/
		function GoogleAdsenseSummaryDashboardWidget() {
			/**
			* note
			* <small> is also a referrence to ".inside small" in the ajax function
			*/
			echo '<small>' . __('Loading', $this->localizationDomain) . '...</small>';
			echo '<div class="target" style="display: none;"></div>';

		} //End Dashboard Widget

		/**
		* Javascript - Insert in our ajax widget
		*/
		function GoogleAdsenseSummaryJS() {
?>
		<script type="text/javascript">

			jQuery(document).ready(function(){

				// Add a link to see full stats on the Analytics website
				jQuery('#gas-dashboard-widget h3.hndle span').append('<span class="postbox-title-action"><a href="https://www.google.com/adsense/login2" class="edit-box open-box"><?php _e('Click here for the Google Adsense website', &$this->localizationDomain); ?></a></span>');

				// Grab the widget data
				jQuery.ajax({
					type: 'post',
					url: 'admin-ajax.php',
					data: {
						action: 'gas_ajax_widget',
						_ajax_nonce: '<?php echo wp_create_nonce("gasWidgetget"); ?>'
					},
					success: function(html) {
						// Hide the loading message
						jQuery('#gas-dashboard-widget .inside small').remove();

						// Place the widget data in the area
						jQuery('#gas-dashboard-widget .inside .target').html(html);

						// Display the widget data
						jQuery('#gas-dashboard-widget .inside .target').slideDown();
					}
				});

			});

		</script>
<?php
		}


		/**
		* Ajax Widget - This allows the dashboard to load while the widget still gathers it's information
		*/
		function GoogleAdsenseSummaryAjaxWidget() {
			# Check the ajax widget
			check_ajax_referer('gasWidgetget');

			$gasResults = $this->google_adsense_summary_retrieve_data();
			$gasOptions = $this->getAdminOptions();
			
			$gasOutput = "\n".'<table width="100%" cellpadding="10" cellspacing="10" class="gas_dashboard_widget">';
			if ( $gasOptions['display_today'] == 'true' ) {
				$gasOutput .= "\n\t".'<td>';
				$gasOutput .= "\n\t".'<table>';
				$gasOutput .= "\n\t\t".'<th>Today</th><th></th><th></th><th></th><th></th><th></th>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td></td><td>Impressions</td><td>Clicks</td><td>CTR</td><td>eCPM</td><td>Earnings</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Content</td><td>'.$gasResults['content']['impressions']['today'].'</td><td>'.$gasResults['content']['clicks']['today'].'</td><td>'.$gasResults['content']['ctr']['today'].'</td><td>'.$gasResults['content']['ecpm']['today'].'</td><td>'.$gasResults['content']['earnings']['today'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Search</td><td>'.$gasResults['search']['impressions']['today'].'</td><td>'.$gasResults['search']['clicks']['today'].'</td><td>'.$gasResults['search']['ctr']['today'].'</td><td>'.$gasResults['search']['ecpm']['today'].'</td><td>'.$gasResults['search']['earnings']['today'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Feeds</td><td>'.$gasResults['feeds']['impressions']['today'].'</td><td>'.$gasResults['feeds']['clicks']['today'].'</td><td>'.$gasResults['feeds']['ctr']['today'].'</td><td>'.$gasResults['feeds']['ecpm']['today'].'</td><td>'.$gasResults['feeds']['earnings']['today'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Mobile</td><td>'.$gasResults['mobile']['impressions']['today'].'</td><td>'.$gasResults['mobile']['clicks']['today'].'</td><td>'.$gasResults['mobile']['ctr']['today'].'</td><td>'.$gasResults['mobile']['ecpm']['today'].'</td><td>'.$gasResults['mobile']['earnings']['today'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Domains</td><td>'.$gasResults['domains']['impressions']['today'].'</td><td>'.$gasResults['domains']['clicks']['today'].'</td><td>'.$gasResults['domains']['ctr']['today'].'</td><td>'.$gasResults['domains']['ecpm']['today'].'</td><td>'.$gasResults['domains']['earnings']['today'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t".'</table>';
				$gasOutput .= "\n\t".'</td>';
			}
			if ( $gasOptions['display_yesterday'] == 'true' ) {
				$gasOutput .= "\n\t".'<td>';
				$gasOutput .= "\n\t".'<table>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<th>Yesterday</th><th></th><th></th><th></th><th></th><th></th>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td></td><td>Impressions</td><td>Clicks</td><td>CTR</td><td>eCPM</td><td>Earnings</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Content</td><td>'.$gasResults['content']['impressions']['yesterday'].'</td><td>'.$gasResults['content']['clicks']['yesterday'].'</td><td>'.$gasResults['content']['ctr']['yesterday'].'</td><td>'.$gasResults['content']['ecpm']['yesterday'].'</td><td>'.$gasResults['content']['earnings']['yesterday'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Search</td><td>'.$gasResults['search']['impressions']['yesterday'].'</td><td>'.$gasResults['search']['clicks']['yesterday'].'</td><td>'.$gasResults['search']['ctr']['yesterday'].'</td><td>'.$gasResults['search']['ecpm']['yesterday'].'</td><td>'.$gasResults['search']['earnings']['yesterday'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Feeds</td><td>'.$gasResults['feeds']['impressions']['yesterday'].'</td><td>'.$gasResults['feeds']['clicks']['yesterday'].'</td><td>'.$gasResults['feeds']['ctr']['yesterday'].'</td><td>'.$gasResults['feeds']['ecpm']['yesterday'].'</td><td>'.$gasResults['feeds']['earnings']['yesterday'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Mobile</td><td>'.$gasResults['mobile']['impressions']['yesterday'].'</td><td>'.$gasResults['mobile']['clicks']['yesterday'].'</td><td>'.$gasResults['mobile']['ctr']['yesterday'].'</td><td>'.$gasResults['mobile']['ecpm']['yesterday'].'</td><td>'.$gasResults['mobile']['earnings']['yesterday'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Domains</td><td>'.$gasResults['domains']['impressions']['yesterday'].'</td><td>'.$gasResults['domains']['clicks']['yesterday'].'</td><td>'.$gasResults['domains']['ctr']['yesterday'].'</td><td>'.$gasResults['domains']['ecpm']['yesterday'].'</td><td>'.$gasResults['domains']['earnings']['yesterday'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t".'</table>';
				$gasOutput .= "\n\t".'</td>';
			}
			if ( $gasOptions['display_last7days'] == 'true' ) {
				$gasOutput .= "\n\t".'<td>';
				$gasOutput .= "\n\t".'<table>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<th>Last 7 Days</th><th></th><th></th><th></th><th></th><th></th>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td></td><td>Impressions</td><td>Clicks</td><td>CTR</td><td>eCPM</td><td>Earnings</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Content</td><td>'.$gasResults['content']['impressions']['last7days'].'</td><td>'.$gasResults['content']['clicks']['last7days'].'</td><td>'.$gasResults['content']['ctr']['last7days'].'</td><td>'.$gasResults['content']['ecpm']['last7days'].'</td><td>'.$gasResults['content']['earnings']['last7days'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Search</td><td>'.$gasResults['search']['impressions']['last7days'].'</td><td>'.$gasResults['search']['clicks']['last7days'].'</td><td>'.$gasResults['search']['ctr']['last7days'].'</td><td>'.$gasResults['search']['ecpm']['last7days'].'</td><td>'.$gasResults['search']['earnings']['last7days'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Feeds</td><td>'.$gasResults['feeds']['impressions']['last7days'].'</td><td>'.$gasResults['feeds']['clicks']['last7days'].'</td><td>'.$gasResults['feeds']['ctr']['last7days'].'</td><td>'.$gasResults['feeds']['ecpm']['last7days'].'</td><td>'.$gasResults['feeds']['earnings']['last7days'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Mobile</td><td>'.$gasResults['mobile']['impressions']['last7days'].'</td><td>'.$gasResults['mobile']['clicks']['last7days'].'</td><td>'.$gasResults['mobile']['ctr']['last7days'].'</td><td>'.$gasResults['mobile']['ecpm']['last7days'].'</td><td>'.$gasResults['mobile']['earnings']['last7days'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Domains</td><td>'.$gasResults['domains']['impressions']['last7days'].'</td><td>'.$gasResults['domains']['clicks']['last7days'].'</td><td>'.$gasResults['domains']['ctr']['last7days'].'</td><td>'.$gasResults['domains']['ecpm']['last7days'].'</td><td>'.$gasResults['domains']['earnings']['last7days'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t".'</table>';
				$gasOutput .= "\n\t".'</td>';
			}
			if ( $gasOptions['display_thismonth'] == 'true' ) {
				$gasOutput .= "\n\t".'<td>';
				$gasOutput .= "\n\t".'<table>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<th>This Month</th><th></th><th></th><th></th><th></th><th></th>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td></td><td>Impressions</td><td>Clicks</td><td>CTR</td><td>eCPM</td><td>Earnings</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Content</td><td>'.$gasResults['content']['impressions']['thismonth'].'</td><td>'.$gasResults['content']['clicks']['thismonth'].'</td><td>'.$gasResults['content']['ctr']['thismonth'].'</td><td>'.$gasResults['content']['ecpm']['thismonth'].'</td><td>'.$gasResults['content']['earnings']['thismonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Search</td><td>'.$gasResults['search']['impressions']['thismonth'].'</td><td>'.$gasResults['search']['clicks']['thismonth'].'</td><td>'.$gasResults['search']['ctr']['thismonth'].'</td><td>'.$gasResults['search']['ecpm']['thismonth'].'</td><td>'.$gasResults['search']['earnings']['thismonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Feeds</td><td>'.$gasResults['feeds']['impressions']['thismonth'].'</td><td>'.$gasResults['feeds']['clicks']['thismonth'].'</td><td>'.$gasResults['feeds']['ctr']['thismonth'].'</td><td>'.$gasResults['feeds']['ecpm']['thismonth'].'</td><td>'.$gasResults['feeds']['earnings']['thismonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Mobile</td><td>'.$gasResults['mobile']['impressions']['thismonth'].'</td><td>'.$gasResults['mobile']['clicks']['thismonth'].'</td><td>'.$gasResults['mobile']['ctr']['thismonth'].'</td><td>'.$gasResults['mobile']['ecpm']['thismonth'].'</td><td>'.$gasResults['mobile']['earnings']['thismonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Domains</td><td>'.$gasResults['domains']['impressions']['thismonth'].'</td><td>'.$gasResults['domains']['clicks']['thismonth'].'</td><td>'.$gasResults['domains']['ctr']['thismonth'].'</td><td>'.$gasResults['domains']['ecpm']['thismonth'].'</td><td>'.$gasResults['domains']['earnings']['thismonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t".'</table>';
				$gasOutput .= "\n\t".'</td>';
			}
			if ( $gasOptions['display_lastmonth'] == 'true' ) {
				$gasOutput .= "\n\t".'<td>';
				$gasOutput .= "\n\t".'<table>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<th>Last Month</th><th></th><th></th><th></th><th></th><th></th>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td></td><td>Impressions</td><td>Clicks</td><td>CTR</td><td>eCPM</td><td>Earnings</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Content</td><td>'.$gasResults['content']['impressions']['lastmonth'].'</td><td>'.$gasResults['content']['clicks']['lastmonth'].'</td><td>'.$gasResults['content']['ctr']['lastmonth'].'</td><td>'.$gasResults['content']['ecpm']['lastmonth'].'</td><td>'.$gasResults['content']['earnings']['lastmonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Search</td><td>'.$gasResults['search']['impressions']['lastmonth'].'</td><td>'.$gasResults['search']['clicks']['lastmonth'].'</td><td>'.$gasResults['search']['ctr']['lastmonth'].'</td><td>'.$gasResults['search']['ecpm']['lastmonth'].'</td><td>'.$gasResults['search']['earnings']['lastmonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Feeds</td><td>'.$gasResults['feeds']['impressions']['lastmonth'].'</td><td>'.$gasResults['feeds']['clicks']['lastmonth'].'</td><td>'.$gasResults['feeds']['ctr']['lastmonth'].'</td><td>'.$gasResults['feeds']['ecpm']['lastmonth'].'</td><td>'.$gasResults['feeds']['earnings']['lastmonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Mobile</td><td>'.$gasResults['mobile']['impressions']['lastmonth'].'</td><td>'.$gasResults['mobile']['clicks']['lastmonth'].'</td><td>'.$gasResults['mobile']['ctr']['lastmonth'].'</td><td>'.$gasResults['mobile']['ecpm']['lastmonth'].'</td><td>'.$gasResults['mobile']['earnings']['lastmonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Domains</td><td>'.$gasResults['domains']['impressions']['lastmonth'].'</td><td>'.$gasResults['domains']['clicks']['lastmonth'].'</td><td>'.$gasResults['domains']['ctr']['lastmonth'].'</td><td>'.$gasResults['domains']['ecpm']['lastmonth'].'</td><td>'.$gasResults['domains']['earnings']['lastmonth'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t".'</table>';
				$gasOutput .= "\n\t".'</td>';
			}
			if ( $gasOptions['display_alltime'] == 'true' ) {
				$gasOutput .= "\n\t".'<td>';
				$gasOutput .= "\n\t".'<table>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<th>All Time</th><th></th><th></th><th></th><th></th><th></th>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td></td><td>Impressions</td><td>Clicks</td><td>CTR</td><td>eCPM</td><td>Earnings</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Content</td><td>'.$gasResults['content']['impressions']['alltime'].'</td><td>'.$gasResults['content']['clicks']['alltime'].'</td><td>'.$gasResults['content']['ctr']['alltime'].'</td><td>'.$gasResults['content']['ecpm']['alltime'].'</td><td>'.$gasResults['content']['earnings']['alltime'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Search</td><td>'.$gasResults['search']['impressions']['alltime'].'</td><td>'.$gasResults['search']['clicks']['alltime'].'</td><td>'.$gasResults['search']['ctr']['alltime'].'</td><td>'.$gasResults['search']['ecpm']['alltime'].'</td><td>'.$gasResults['search']['earnings']['alltime'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Feeds</td><td>'.$gasResults['feeds']['impressions']['alltime'].'</td><td>'.$gasResults['feeds']['clicks']['alltime'].'</td><td>'.$gasResults['feeds']['ctr']['alltime'].'</td><td>'.$gasResults['feeds']['ecpm']['alltime'].'</td><td>'.$gasResults['feeds']['earnings']['alltime'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Mobile</td><td>'.$gasResults['mobile']['impressions']['alltime'].'</td><td>'.$gasResults['mobile']['clicks']['alltime'].'</td><td>'.$gasResults['mobile']['ctr']['alltime'].'</td><td>'.$gasResults['mobile']['ecpm']['alltime'].'</td><td>'.$gasResults['mobile']['earnings']['alltime'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t\t".'<tr>';
				$gasOutput .= "\n\t\t".'<td>Domains</td><td>'.$gasResults['domains']['impressions']['alltime'].'</td><td>'.$gasResults['domains']['clicks']['alltime'].'</td><td>'.$gasResults['domains']['ctr']['alltime'].'</td><td>'.$gasResults['domains']['ecpm']['alltime'].'</td><td>'.$gasResults['domains']['earnings']['alltime'].'</td>';
				$gasOutput .= "\n\t\t".'</tr>';
				$gasOutput .= "\n\t".'</table>';
				$gasOutput .= "\n\t".'</td>';
			}
			$gasOutput .= "\n".'</table>';
			echo $gasOutput;

			// This must exist so that a "0" isn't returned within the widget data
			die();
		}

		/**
		* Dashboard Widget initialize
		*/
		function GoogleAdsenseSummaryDashboardWidgetInit() {
			global $wp_meta_boxes;
			
			wp_add_dashboard_widget('gas-dashboard-widget', __( 'Google Adsense Summary', $this->localizationDomain ), array(&$this, 'GoogleAdsenseSummaryDashboardWidget'));
		} // End Initialize dashboard widget

		/**
		* This is the curl get function to fetch all our information
		*/
		///////////////////////////////////////////////
		// Curl Get
		///////////////////////////////////////////////

		function curl_get($url, $cookiefile) {
			$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookiefile);
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookiefile);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			$this->data = curl_exec($this->curl);
			curl_close($this->curl);
			return $this->data;
		}

		///////////////////////////////////////////////
		// Curl Post
		///////////////////////////////////////////////

		function curl_post($url, $cookiefile, $post) {
			$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_POST, 1);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookiefile);
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookiefile);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			$this->data = curl_exec($this->curl);
			curl_close($this->curl);
			return $this->data;
		}

		function google_adsense_summary_retrieve_data() {
			$gasOptions = $this->getAdminOptions();
			$this->username = $gasOptions['username'];
			$this->password = $gasOptions['password'];
			
			/**
			* Get the GA3T value for login
			*/
			$data = $this->curl_get("https://www.google.com/accounts/ServiceLoginBox?service=adsense&ltmpl=login&ifr=true&rm=hide&fpui=3&nui=15&alwf=true&passive=true&continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&hl=en_US", $this->cookiefile);

			/**
			* Testing and debuging purposes
			echo "\n--------------curl_get--------------\n";
			//echo $data;
			echo $this->cookiefile;
			echo "\n------------end-data--------------\n\n\n\n";
			*/
			preg_match("/<input type=\"hidden\" name=\"GA3T\" value=\"(.*?)\"/", $data, $ga3t);
			preg_match("/<input type=\"hidden\" name=\"GALX\" value=\"(.*?)\"/", $data, $galx);

			/**
			* Login to AdSense
			*/
			$data = $this->curl_post("https://www.google.com/accounts/ServiceLoginBoxAuth", $this->cookiefile, "continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&service=adsense&nui=15&fpui=3&ifr=true&rm=hide&ltmpl=login&hl=en_US&alwf=true&ltmpl=login&GA3T=$ga3t[1]&GALX=$galx[1]&Email=$this->username&Passwd=$this->password");

			/**
			* Debugging purposes
			*
			
			echo "\n<br />--------------curl_post--------------<br />\n";
			echo $this->username;
			echo "<br />";
			echo $this->password;
			//echo $data;
			//echo $this->cookiefile;
			echo "\n<br />--------------ga3t--------------\n<br />";
			print_r ( $ga3t );
			echo "<br /> ga3t echo:  ";
			echo $ga3t[1];
			echo "<br />";
			echo "\n<br />--------------galx--------------\n<br />";
			print_r ( $galx );
			echo "<br /> ga3t echo:  ";
			echo $galx[1];
			echo "<br />";
			echo "\n<br />------------end-data--------------\n\n\n\n<br />";
			*/

			/**
			* If we didn't set the right username and password, die
			*/
			if(strpos($data, 'Username and password do not match.')) {
				unlink($this->cookiefile);
				die("Login failed.\n");
			} //end if


			/**
			* Authenticate login
			*/
			$data = $this->curl_get("https://www.google.com/accounts/CheckCookie?continue=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&followup=https%3A%2F%2Fwww.google.com%2Fadsense%2Flogin-box-gaiaauth&hl=en_US&service=adsense&ltmpl=login&chtml=LoginDoneHtml", $this->cookiefile);

			/**
			* debugging purposes
			*
			echo "\n--------------curl_get--------------\n";
			//echo $data;
			echo $this->cookiefile;
			echo "\n------------end-data--------------\n\n\n\n";
			*/

			/**
			* If our attempt to Authenticate our login failed, our cookie functionality
			* might not be working.
			*/
			if(strpos($data, 'cookie functionality is turned off.')) {
				unlink($this->cookiefile);
				echo "\n$this->cookiefile\n";
				die("<br />Cookie functionality is not working.<br />");
			} //end if


			/**
			* Fetch Array is for all the times you can get a report for
			*****
			* Summary Results array is all the types of reports
			*****
			* Ad types is just that, reports for every category ad type
			*****
			*/
			$fetch_times = array(
			'today',
			'yesterday',
			'last7days',
			'thismonth',
			'lastmonth',
			'alltime'
			);


			$google_adsense_summary_results = array(
			'impressions',
			'clicks',
			'ctr',
			'ecpm',
			'earnings',
			);

			$google_adsense_summary_ad_types = array(
			'content',
			'search',
			'feeds',
			'mobile',
			'domains'
			);

			/**
			* This goes through each report time to gather that specific information.  All the information is held
			* in the $match array.  We also check to see if any data is present, and if not to fill that area with
			* the "No Data" string or we will run into offset errors.
			*/
			foreach($fetch_times as $key_times => &$report_times) {
				$data = $this->curl_get("https://www.google.com/adsense/report/overview?timePeriod=$report_times", $this->cookiefile);
				preg_match_all("/<td nowrap valign=\"top\" style=\"text-align\:right\" class=\"\">(.*?)<\/td>/", $data, $match);
				/**
				* Content
				*/
				if(!isset($match[1][0])) {
					$match[1][0] = "No Data";
					$match[1][1] = "No Data";
					$match[1][2] = "No Data";
					$match[1][3] = "No Data";
					$match[1][4] = "No Data";
				}
				$google_adsense_summary_results[$google_adsense_summary_ad_types[0]]['impressions'][$report_times] = $match[1][0];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[0]]['clicks'][$report_times] = $match[1][1];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[0]]['ctr'][$report_times] = $match[1][2];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[0]]['ecpm'][$report_times] = $match[1][3];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[0]]['earnings'][$report_times] = $match[1][4];

				/**
				* Search
				*/
				if(!isset($match[1][15])) {
					$match[1][15] = "No Data";
					$match[1][16] = "No Data";
					$match[1][17] = "No Data";
					$match[1][18] = "No Data";
					$match[1][19] = "No Data";
				}
				$google_adsense_summary_results[$google_adsense_summary_ad_types[1]]['impressions'][$report_times] = $match[1][15];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[1]]['clicks'][$report_times] = $match[1][16];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[1]]['ctr'][$report_times] = $match[1][17];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[1]]['ecpm'][$report_times] = $match[1][18];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[1]]['earnings'][$report_times] = $match[1][19];

				/**
				* Feeds
				*/
				if(!isset($match[1][25])) {
					$match[1][25] = "No Data";
					$match[1][26] = "No Data";
					$match[1][27] = "No Data";
					$match[1][28] = "No Data";
					$match[1][29] = "No Data";
				}
				$google_adsense_summary_results[$google_adsense_summary_ad_types[2]]['impressions'][$report_times] = $match[1][25];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[2]]['clicks'][$report_times] = $match[1][26];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[2]]['ctr'][$report_times] = $match[1][27];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[2]]['ecpm'][$report_times] = $match[1][28];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[2]]['earnings'][$report_times] = $match[1][29];

				/**
				* Mobile
				*/
				if(!isset($match[1][35])) {
					$match[1][35] = "No Data";
					$match[1][36] = "No Data";
					$match[1][37] = "No Data";
					$match[1][38] = "No Data";
					$match[1][39] = "No Data";
				}
				$google_adsense_summary_results[$google_adsense_summary_ad_types[3]]['impressions'][$report_times] = $match[1][35];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[3]]['clicks'][$report_times] = $match[1][36];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[3]]['ctr'][$report_times] = $match[1][37];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[3]]['ecpm'][$report_times] = $match[1][38];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[3]]['earnings'][$report_times] = $match[1][39];

				/**
				* Domains
				*/
				if(!isset($match[1][45])) {
					$match[1][45] = "No Data";
					$match[1][46] = "No Data";
					$match[1][47] = "No Data";
					$match[1][48] = "No Data";
					$match[1][49] = "No Data";
				}
				$google_adsense_summary_results[$google_adsense_summary_ad_types[4]]['impressions'][$report_times] = $match[1][45];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[4]]['clicks'][$report_times] = $match[1][46];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[4]]['ctr'][$report_times] = $match[1][47];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[4]]['ecpm'][$report_times] = $match[1][48];
				$google_adsense_summary_results[$google_adsense_summary_ad_types[4]]['earnings'][$report_times] = $match[1][49];

			} //end foreach
			/**
			* Unset our array so that no data is written over the last value
			* Delete our cookiefile
			*/
			unset($report_times);
			unlink($this->cookiefile);
			/**
			* This was used for debugging purposes
			//var_export($google_adsense_summary_results);
			echo "\n\nmatch\n";
			print_r($match);
			echo "\n\nkey times\n";
			//print_r($key_times); // numerical
			echo "\n\nkey types\n";
			//print_r($key_types); // charactor - earnings
			echo "\n\nreport_types\n";
			//print_r($report_types); // - array
			echo "\n\nreport times\n";
			//print_r($report_times); // - charactor sincelastpayment
			echo "\n\ngoogle adsense results\n";
			print_r($google_adsense_summary_results);
			//var_dump($data);
			//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($google_adsense_summary_results,true))."<br>";
			*/
			return $google_adsense_summary_results;
		}



	} // End Class google_adsense_summary

} // End if class exists

/**
* Class Initialize
*
*/
if (class_exists("google_adsense_summary")) {
	$gas_pluginSeries = new google_adsense_summary();
} //End of Class Initialize
?> <!-- End of Main plugin PHP TAG -->