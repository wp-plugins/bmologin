<?php
/*
Plugin Name: BMO Login
Plugin URI: http://www.saas7.com/
Description: Login for BMO
Version: 1.0.1
Author: H.P.Ang
Author URI: http://www.saas7.com/
License: GPL2
*/
/**
 * Copyright 2015  Mobiweb  (email : support@mobiweb.com.my)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

 require_once(plugin_dir_path(__FILE__) . 'mobiweb-bmologin-settings.php');
 
 class BMO_Login {
	protected $settings;
	
	/**
	 * Construct the plugin object
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
		$this->define_admin_hooks();
		$this->define_shortcodes();
	}
	
	private function load_dependencies() {
		$this->settings = new Mobiweb_BMO_Login_Settings();
	}
	
	private function define_hooks() {
		add_action('init', array($this, 'init'));
	}
	
	private function define_admin_hooks() {
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'add_menu'));
	}
	
	private function define_shortcodes() {
		add_shortcode('bmologin', array($this, 'bmologin_func'));
	}
	
	/**
	 * Activate the plugin
	 */
	public static function activate() {
	      // Creates new database field
	      add_option("bmologin_data", 'Default', '', 'yes');
	}
	
	/**
	 * Deactivate the plugin
	 */
	public static function deactivate() {
	      // Deletes the database field
	      delete_option('bmologin_data');
	}
	
	/**
	 * Hook into WP's init action hook
	 */
	public function init() {
		$this->handle_bmologin_post();
	}
	
	private function handle_bmologin_post() {
	      if (isset($_POST['bmousername']) && wp_verify_nonce($_POST['bmo_form_nonce'], 'bmo')) {
		     $bmo_username	= $_POST['bmousername'];
		     $bmo_password	= $_POST['bmopassword'];
		     $captcha		= $_POST['captcha'];
		     
		     if (empty($bmo_username)) {
			    // Empty username
			    $this->bmologin_errors()->add('username_empty', __('Please enter a username', 'mobiweb-bmologin'));
		     }
		     
		     if (empty($bmo_password)) {
			    // Empty password
			    $this->bmologin_errors()->add('password_empty', __('Please enter a password', 'mobiweb-bmologin'));
		     }
		     
		     if ($captcha != $_SESSION['captcha']) {
			    // Invalid captcha
			    $this->bmologin_errors()->add('captcha_invalid', __('Invalid captcha entered', 'mobiweb-bmologin'));
		     }
		     
		     $errors = $this->bmologin_errors()->get_error_messages();

		     // Only initiate login if there are no errors
		     if (empty($errors)) {
			    $tuCurl = curl_init();
			    curl_setopt($tuCurl, CURLOPT_URL, "http://login.saas7.com/verify_login_wp.php");
			    curl_setopt($tuCurl, CURLOPT_VERBOSE, 1);
			    curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
			    curl_setopt($tuCurl, CURLOPT_POST, 1);
			    curl_setopt($tuCurl, CURLOPT_POSTFIELDS, array("rtUsername"=>$bmo_username, "rpPassword"=>$bmo_password, "compcode"=>get_option('setting_login_form_company_code')));

			    $tuData = curl_exec($tuCurl);
			    
			    curl_close($tuCurl);
			    
			    if($tuData == 1){
				   $referer = $_SERVER['HTTP_REFERER'];
				   header("Location: $referer&bmo_success=1");
				   $_SESSION['bmousername'] = $bmo_username;
				   $_SESSION['bmopassword'] = $bmo_password;
			    }
			    exit();
		     }
	      }
	}
	
	// Used for tracking error messages
	function bmologin_errors() {
	      static $wp_error; // Will hold global variable safely
	      return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
	}
	
	// Displays error messages from form submissions
	function bmologin_show_error_messages() {
	      if ($codes = $this->bmologin_errors()->get_error_codes()) {
		     ?>
		     <div>
			    <?php
			    // Loop error codes and display errors
			    foreach ($codes as $code) {
				   $message = $this->bmologin_errors()->get_error_message($code);
				   ?>
				   <span>
					  <strong><?php _e('Error', 'mobiweb-bmologin') ?></strong>: <?php echo $message ?>
				   </span><br />
				   <?php
			    }
			    ?>
		     </div>
		     <?php
	      }
	}
	
	/**
	 * hook into WP's admin_init action hook
	 */
	public function admin_init() {
		// Set up the settings for this plugin
		$this->settings->init_settings();
		// Possibly do additional admin_init tasks
	}
	
	function phpbase64encodeFix($str){
		return base64_encode(base64_encode(base64_encode($str."mobiweb.com.my")));
	}
	
	function bmologin_func($atts, $content){
	      if ($_GET['bmo_success']) {
		     echo '<script language="javascript">
				      window.location.href="http://login.saas7.com/verify_login_wp.php?rtUsername='.("w".$this->phpbase64encodeFix("bmowebpage".$_SESSION['bmousername'])).'&rpPassword='.("w".$this->phpbase64encodeFix("bmowebpage".$_SESSION['bmopassword'])).'&compcode='.("w".$this->phpbase64encodeFix("bmowebpage".get_option('setting_login_form_company_code'))).'&wplogin=1";
			      </script>';
	      } else {// show the form
		     $this->bmologin_form_fields();
	      }
	}
	
	/**
	 * add a menu
	 */
	public function add_menu() {
	      $this->settings->add_menu_settings();
	}
	
       function bmologin_form_fields() {
	      $permalink = get_permalink($post->ID);
	      ?>
	      <h3><?php _e('BMO Login', 'mobiweb-bmologin'); ?></h3>
	      <?php $this->bmologin_show_error_messages(); ?>
	      <form id="form1" name="form1" method="post" action="<?php echo $permalink; ?>">
		     <fieldset>
			    <p>
				   <label for="bmousername"><?php _e('Username', 'mobiweb-bmologin'); ?></label>
				   <input name="bmousername" id="bmousername" type="text" class="required"/>
			    </p>
			    <p>
				   <label for="bmopassword"><?php _e('Password', 'mobiweb-bmologin'); ?></label>
				   <input name="bmopassword" id="bmopassword" type="password" class="required"/>
			    </p>
			    <p>
				   <div style="padding-left: 10px;"><img src="<?php echo WP_PLUGIN_URL; ?>/bmologin/include/captcha.php" id="captcha" /></div>
			    </p>
			    <p>
				   <label for="captcha"><?php _e('Captcha', 'mobiweb-bmologin'); ?></label>
				   <input name="captcha" id="captcha" type="text" class="required" />
			    </p>
			    <p>
				   <?php wp_nonce_field('bmo', 'bmo_form_nonce'); ?>
				   <input name="button" id="button" value="<?php _e('Submit', 'mobiweb-bmologin') ?>" type="submit"/>
			    </p>
		     </fieldset>
	      </form>
	      <?php
       }
 }
 
 // Start session
if (!session_id()) {
       session_start();
}
 
 if (class_exists('BMO_Login')) {
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('BMO_Login', 'activate'));
	register_deactivation_hook(__FILE__, array('BMO_Login', 'deactivate'));
	
	
	
	// Instantiate the plugin class
	$bmo_login = new BMO_Login();
	
	// Add a link to the settings page onto the plugin page
	if (isset($bmo_login)) {
		// Add the settings link to the plugin page
		function mobiweb_bmologin_settings_link($links) {
			$settings_link = '<a href="options-general.php?page=bmologin_setting">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		
		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", "mobiweb_bmologin_settings_link");
	}
 }
?>