<?php
/*
Plugin Name: BMO Login
Plugin URI: http://www.saas7.com/
Description: Login for BMO
Version: 1.0.1
Author: H.P.Ang
Author URI: http://www.saas7.com/
License: GPL
*/

session_start();

function phpbase64encodeFix($str){
	return base64_encode(base64_encode(base64_encode($str."mobiweb.com.my")));
}

function bmologin_func($atts, $content){
  global $post;
  $permalink = get_permalink($post->ID);
  if($error = $_GET["demoform_error"]){
    echo "Error processing submission<br>";
    echo $_SESSION['error_text'];
  }
  elseif($success = $_GET["bmo_success"])
    //echo '<iframe src="http://login.saas7.com/verify_login_wp.php?rtUsername='.("w".phpbase64encodeFix("bmowebpage".$_SESSION['bmousername'])).'&rpPassword='.("w".phpbase64encodeFix("bmowebpage".$_SESSION['bmopassword'])).'&compcode='.("w".phpbase64encodeFix("bmowebpage".get_option('bmologin_company_code'))).'&wplogin=1" width="'.get_option('bmologin_width').'px" height="'.get_option('bmologin_height').'px"></iframe>';
    
	echo '<script language="javascript">
			window.location.href="http://login.saas7.com/verify_login_wp.php?rtUsername='.("w".phpbase64encodeFix("bmowebpage".$_SESSION['bmousername'])).'&rpPassword='.("w".phpbase64encodeFix("bmowebpage".$_SESSION['bmopassword'])).'&compcode='.("w".phpbase64encodeFix("bmowebpage".get_option('bmologin_company_code'))).'&wplogin=1";
		</script>';
  else{// show the form
  //wpcf7();
  ?>
  <!--<h2>BMO Login</h2>-->
  <form id="form1" name="form1" method="post" action="<?php echo $permalink; ?>">
		<p>Username: <input type="text" name="bmousername" id="bmousername" /></p>
		<p>Password: <input type="password" name="bmopassword" id="bmopassword" /></p>
		<!--<p>Company Code: <?php //echo get_option('bmologin_company_code'); ?></p>-->
		<p><div style="padding-left: 10px;"><img src="<?php echo WP_PLUGIN_URL; ?>/bmologin/include/captcha.php" id="captcha" /></div></p>
		<p>Captcha: <input type="text" name="captcha" id="captcha" /></p>
  <p><input type="submit" name="button" id="button" value="Submit" /></p>

<?php
  }
  wp_nonce_field("bmo","bmo_form_nonce");
  }
  
  
//  function my_scripts_method() {
//    wp_deregister_script( 'jquery' );
//    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
//    wp_enqueue_script( 'jquery' );
//}    
// 
//add_action('wp_enqueue_scripts', 'my_scripts_method');
  
function handle_bmologin_post() {
	global $post;
	//$r = $_SERVER['HTTP_REFERER'];
	$r = $_SERVER['HTTP_REFERER'];
	if($nonce = $_POST["bmo_form_nonce"]){
		if(wp_verify_nonce($nonce, "bmo") ){
			
			$captcha_valid = 1;
			$no_error = 1;
			$_SESSION['error_text'] = "";
			
			if($_POST['captcha'] != $_SESSION['captcha']){
				
				$captcha_valid = 0;
				$_SESSION['error_text'] .= "Invalid captcha entered.<br>";
			}
			
			if($captcha_valid && $no_error){
				
				$username = $_POST['bmousername'];
				$password = $_POST['bmopassword'];
				
				$tuCurl = curl_init();
				curl_setopt($tuCurl, CURLOPT_URL, "http://login.saas7.com/verify_login_wp.php");
				//curl_setopt($tuCurl, CURLOPT_URL, "http://www.isms.com.my/isms_send.php");
				curl_setopt($tuCurl, CURLOPT_VERBOSE, 1);
				curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($tuCurl, CURLOPT_POST, 1);
				curl_setopt($tuCurl, CURLOPT_POSTFIELDS, array("rtUsername"=>$username, "rpPassword"=>$password, "compcode"=>get_option('bmologin_company_code')));

				$tuData = curl_exec($tuCurl);
				
				curl_close($tuCurl);
				
				if($tuData == 1){
					
					header("Location: $r&bmo_success=1");
					$_SESSION['bmousername'] = $username;
					$_SESSION['bmopassword'] = $password;
				}
				else{
					
					header("Location: $r&demoform_error=1&$tuData");
					$_SESSION['error_text'] .= "Invalid username/password/company code entered.<br>";
				}
				//header("Location: $r&demoform_success=1&$tuData");
				exit();
			}
			else{
				header("Location: $r&demoform_error=1");
				exit();
			}
		}
		else{
			header("Location: $r&demoform_error=1");
			exit();
		}
	}
}


add_action('init','handle_bmologin_post');

add_shortcode('bmologin', 'bmologin_func');
  /* Runs when plugin is activated */
register_activation_hook(__FILE__,'bmologin_form_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'bmologin_form_remove' );

function bmologin_form_install() {
/* Creates new database field */
add_option("bmologin_data", 'Default', '', 'yes');
}

function bmologin_form_remove() {
/* Deletes the database field */
delete_option('bmologin_data');
}

if ( is_admin() ){
  /* Call the html code */
  add_action('admin_menu', 'bmologin_admin_menu');
}

function bmologin_admin_menu() {
  add_options_page('BMO Login Setting', 'BMO Login Form', 'administrator', 'bmologin_setting', 'bmologin_html_page');
  //add_menu_page('BAW Plugin Settings', 'BAW Settings', 'administrator', __FILE__, 'email_sms_html_page',plugins_url('/images/icon.png', __FILE__));
  
  add_action( 'admin_init', 'register_bmo_settings' );

}


function register_bmo_settings() {
	//register our settings
  register_setting( 'bmologin-settings-group', 'bmologin_company_code' );
  register_setting( 'bmologin-settings-group', 'bmologin_width' );
  register_setting( 'bmologin-settings-group', 'bmologin_height' );
}

function bmologin_html_page() {
?>
<div>
<h2>SMS Email Contact form</h2>
  <form method="post" action="options.php">
    <?php settings_fields('bmologin-settings-group'); ?>
    <?php do_settings_fields('bmologin-settings-group','');?>
<table width="600">
<!--<tr valign="top">
  <td width="260" style="text-align:right" scope="row">Notify owner with iSMS</td>
  <td width="300" style="text-align:left;padding-left:10px"><input name="isms_notification" type="checkbox" id="isms_notification" value="1" <?php //echo get_option('isms_notification')=="1"?"checked":""; ?> /></td>
</tr>-->
<tr>
  <td width="260" style="text-align:right" scope="row">Company Code</td>
  <td width="300" style="text-align:left;padding-left:10px"><input name="bmologin_company_code" type="text" id="bmologin_company_code" value="<?php echo get_option('bmologin_company_code'); ?>" /></td>
</tr>
<tr>
  <td width="260" style="text-align:right" scope="row">Width(px)</td>
  <td width="300" style="text-align:left;padding-left:10px"><input name="bmologin_width" type="text" id="bmologin_width" value="<?php echo get_option('bmologin_width'); ?>" /></td>
</tr>
<tr>
  <td width="260" style="text-align:right" scope="row">Height(px)</td>
  <td width="300" style="text-align:left;padding-left:10px"><input name="bmologin_height" type="text" id="bmologin_height" value="<?php echo get_option('bmologin_height'); ?>" /></td>
</tr>
</table> 
<p>
<input type="submit" value="<?php _e('Save Changes'); ?>" />
</p>

</form>
</div>
<?php
}
?>