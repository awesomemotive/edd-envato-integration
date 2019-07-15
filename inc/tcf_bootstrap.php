<?PHP

/*-----------------------------------------------------------------------------------*/
/*	Install Database
/*-----------------------------------------------------------------------------------*/

function eddenvato_db_install(){

	// define needed globals
	global $wpdb;
	$eddenvato_db = $wpdb->prefix . "tc_edd_envato";

	// create table
	if( $wpdb->get_var("SHOW TABLES LIKE '$eddenvato_db'") != $eddenvato_db ){

		$sql = "CREATE TABLE ".$eddenvato_db." (
			id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			envato_id VARCHAR(250) NOT NULL,
			license_key VARCHAR(250) NOT NULL,
			user_id VARCHAR(250) NOT NULL,
			envato_return TEXT NOT NULL
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		// Create Default Page
		$tc_post = array(
		  'post_title'    => 'EDD Evato License Verification',
		  'post_content'  => '[edd-envato]',
		  'post_status'   => 'publish',
		  'post_author'   => 1,
		  'post_type'	  => 'page'
		);

		// Insert the post into the database
		wp_insert_post( $tc_post );

	} // end table creation

}

/*-----------------------------------------------------------------------------------*/
/*	Bootstrapn' Time!
/*-----------------------------------------------------------------------------------*/

function eddenvato_init(){

	// Load Lang
	load_plugin_textdomain( 'eddenvato', false, EDDENVATO_RELPATH . '/languages/' );

	// Make sure we are not in the admin section
	if (!is_admin()){

		// Flush, register, enque CSS
		wp_deregister_style('eddenvato-css');
		wp_register_style('eddenvato-css', EDDENVATO_LOCATION.'/css/eddenvato.css');
		wp_enqueue_style('eddenvato-css');

	}

	// Strap tinyMCE
	eddenvato_mce();

} // End jsloader function

/*-----------------------------------------------------------------------------------*/
/*	Add tinyMCE Button
/*-----------------------------------------------------------------------------------*/

function eddenvato_mce(){

	// Don't bother doing this stuff if the current user lacks permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
	return;

	// Add only in Rich Editor mode
	if( get_user_option('rich_editing') == 'true'){

		// Add cutom button to TinyMCE
		add_filter('mce_external_plugins', "eddenvato_mce_register");
		add_filter('mce_buttons', 'eddenvato_add_button', 0);

	}

}
function eddenvato_add_button($buttons) {
   array_push($buttons, "separator", "eddenvatoplugin");
   return $buttons;
}
function eddenvato_mce_register($plugin_array) {
   $plugin_array['eddenvatoplugin'] = EDDENVATO_LOCATION."/inc//mce/mce.js";
   return $plugin_array;
} // end tinyMCE

/*-----------------------------------------------------------------------------------*/
/*	Check Current Forum
/*-----------------------------------------------------------------------------------*/

function eddenvato_envato_form(){

	// Globals
	global $wpdb;
	global $eddenvato_msg;
	$eddenvato_db = $wpdb->prefix . "tc_edd_envato";

	// Cache Vars
	$switch = true;

	// Handle Form Submit
	if( isset($_POST['edd-envato-license']) ){

		// Setup Form Vars
		$pcode = $_POST['edd-envato-license'];
		$email = $_POST['edd-envato-email'];
		$email_confirm = $_POST['edd-envato-email-confirm'];

		// Check for email valid
		if( is_email($email) && $email == $email_confirm ){

			// Check if key already in database
			$check = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$eddenvato_db." WHERE license_key = '%s' LIMIT 1", stripslashes($_POST['edd-envato-license'])));

			if($check == 1){

				// Return an error message in the form
				$eddenvato_msg = __('Sorry, this key is already in the database. If you think this is a mistake, please contact us to resolve the issue.', 'eddenvato');

			} else { // New Key

				// Verify Key
				$response_raw = eddenvato_verify_license($_POST['edd-envato-license']);
				$response = json_decode( wp_remote_retrieve_body($response_raw), true);
				// If Verify Purchase is set
				if( isset($response['verify-purchase']['buyer']) ){

					// Setup
					$envato_data = $response['verify-purchase'];
					$buyer = $envato_data['buyer'];

					// Check if user exists
					$user_id = username_exists($buyer);
					if ( !$user_id ){

						// Ready to create
						$random_password = wp_generate_password( 12, false );
						$user_id = wp_create_user( $buyer, $random_password, $email );
						wp_new_user_notification( $user_id, $random_password );
						// Insert Key
						$wpdb->insert(
							$eddenvato_db,
							array(
								'envato_id' => $envato_data['item_id'],
								'license_key' => stripslashes($_POST['edd-envato-license']),
								'user_id' => $user_id,
								'envato_return' => $response_raw
							),
							array(
								'%s',
								'%s',
								'%d',
								'%s'
							)
						);

						// Create Discount Code
						if( get_option('eddenvato-discount') == 'true' ){

							// Create Discount
							$discount_meta = array(
								'name'			=> 'ENVATO-'.strtoupper($buyer),
								'code'			=> 'ENVATO-'.strtoupper($buyer),
								'max_uses'		=> '1',
								'amount'		=> get_option('eddenvato-discount-amount'),
								'type'			=> get_option('eddenvato-discount-type')
							); edd_store_discount( $discount_meta );

						}

						// Send Discount Code to User
						$msg = get_option('eddenvato-discount-msg');
						$msg .= "\n\n".__('Your Discount Code', 'eddenvato').": ".'ENVATO-'.strtoupper($buyer);
						wp_mail( $email, get_option('eddenvato-discount-title'), $msg );

						// Redirect User
						wp_redirect( get_permalink( get_option('eddenvato-verify-page') ) ); exit;

					} else {
						$eddenvato_msg = __('The Envato username on this purchase code / email address has already been used to create an account.', 'eddenvato');
					}

				} else { // key not found

					$eddenvato_msg = __('The license key you have entered is not valid or could not be verified at this time.', 'eddenvato');

				} // end if key valid

			} // end if key found

		} else {

			$eddenvato_msg = __('Check to make sure the email address you entered is valid.', 'eddenvato');

		} // end if email valid

	} // end form submit

}

/*-----------------------------------------------------------------------------------*/
/*	Verify Form ShortCode
/*-----------------------------------------------------------------------------------*/

function eddenvato_verify_form($atts){

	// Start output
	$output = '';

	// Only show if not logged in
	if( is_user_logged_in() ){

		$output = 'You are already logged in to your account.';

	} else {

		// Setup vars
		global $eddenvato_msg;
		if(isset($_POST['edd-envato-license'])) : $license = $_POST['edd-envato-license']; else: $license = ''; endif;
		if(isset($_POST['edd-envato-email'])) : $email = $_POST['edd-envato-email']; else: $email = ''; endif;
		if(isset($_POST['edd-envato-email-confirm'])) : $confirm_email = $_POST['edd-envato-email-confirm']; else: $confirm_email = ''; endif;

	 	// Error Message Display
		if( isset($eddenvato_msg) ) : $output .= '<p class="tc-eddenvato-error"><strong>'.$eddenvato_msg.'</strong></p>'; endif;

		// Output custom message
		$output .= stripslashes(get_option('eddenvato-verify-message'));

		// Build Form
		$output .= '
		<form class="tc-eddenvato-form" action="" method="post" name="tc-envato-verify-form" style="margin:20px 0px;">

			<label>'.__("Envato Purchase Code", "eddenvato").'</label><br />
			<input class="tc-eddenvato-field" type="text" id="edd-envato-license" name="edd-envato-license" style="width:400px;" value="'.$license.'" /><br />

			<label>'.__("Email Address", "eddenvato").'</label><br />
			<input class="tc-eddenvato-field" name="edd-envato-email" type="text" name="edd-envato-email" style="width:400px" value="'.$email.'" /><br />

			<label>'.__("Confirm Email", "eddenvato").'</label><br />
			<input class="tc-eddenvato-field" name="edd-envato-email-confirm" type="text" name="edd-envato-email-confirm" style="width:400px" value="'.$confirm_email.'" /><br />
			<input class="tc-eddenvato-submit" name="submit" type="submit" value="Submit" />

		</form>
		';

	} // end if else

	return $output;

}

/*-----------------------------------------------------------------------------------*/
/*	Make API Call
/*-----------------------------------------------------------------------------------*/

function eddenvato_verify_license($key){

	// Setup Call
	$envato_apikey = get_option('eddenvato-api-key');
	$envato_username = get_option('eddenvato-user-name');
	$license_to_check = $key;
	return wp_remote_get( 'http://marketplace.envato.com/api/edge/'.$envato_username.'/'.$envato_apikey.'/verify-purchase:'.$license_to_check.'.json' );

}

/*-----------------------------------------------------------------------------------*/
/*	Add Menu ID An Purchase Codes to User Table
/*-----------------------------------------------------------------------------------*/

// Add Columns
function eddenvato_add_id($columns) {
    //$columns['user_id'] = 'User ID';
    $columns['purchase_codes'] = 'Purchase Codes';
    return $columns;
}

// Column Callbacks
function eddenvato_id_column_content($value, $column_name, $user_id){

	// Get User Data
	global $wpdb;
	$eddenvato_db = $wpdb->prefix . "tc_envato_verify";
    $user = get_userdata( $user_id );

	// User ID
	if( 'user_id' == $column_name ){

		return $user_id;

	// Purchase Codes
	} else if( 'purchase_codes' == $column_name ){

		// Return Codes
		$count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM ".$eddenvato_db." WHERE user_id = '%s'", $user_id));
		if($count == 0){
			return $count;
		} else {
			return $count.' <small><a href="options-general.php?page=tcenvato-keys&uid='.$user_id.'">(View)</a></small>';
		}

	} // end if else

}

/*-----------------------------------------------------------------------------------*/
/*	Start Running Hooks
/*-----------------------------------------------------------------------------------*/

// Installer
register_activation_hook( EDDENVATO_PATH.'/edd-envato.php', 'eddenvato_db_install' );
// Start the plugin
add_action( 'init', 'eddenvato_init' );
// Add hook to include settings CSS
add_action( 'admin_init', 'eddenvato_settings_admin_css' );
// create custom plugin settings menu
add_action( 'admin_menu', 'eddenvato_create_menu' );

// Check to enable the plugin
if( get_option('eddenvato-enabled') == 'true' ){

	// Check to redirect
	add_action('template_redirect', 'eddenvato_envato_form');
	// Add Shortcode
	add_shortcode('edd-envato', 'eddenvato_verify_form');
	// Add Columns to User List
	add_action('manage_users_custom_column',  'eddenvato_id_column_content', 10, 3);

}

?>