<?PHP

/*-----------------------------------------------------------------------------------*/
/*	Menu Creation
/*-----------------------------------------------------------------------------------*/

function eddenvato_create_menu() {
	
	// Adds the tab into the options panel in WordPress Admin area
	$page = add_options_page( __("EDD Envato Settings", "eddenvato"), __("EDD Envato", "eddenvato"), 'administrator', __FILE__, 'eddenvato_settings_page');

	// Add Manage Page
	add_users_page( __("Envato Purchase Codes", "eddenvato"), __("Purchase Codes ", "eddenvato"), 'administrator', 'tcenvato-keys', 'eddenvato_manage_page');

	//call register settings function
	add_action( 'admin_init', 'eddenvato_register_settings' );
	
	// Hook style sheet loading
	add_action( 'admin_print_styles-' . $page, 'eddenvato_admin_cssloader' );

}
		
/*-----------------------------------------------------------------------------------*/
/*	Add Admin CSS
/*-----------------------------------------------------------------------------------*/

// Add style sheet for plugin settings
function eddenvato_settings_admin_css(){
				
	/* Register our stylesheet. */
	wp_register_style( 'eddenvatoSettings', EDDENVATO_LOCATION.'/css/tc_framework.css' );
							
} function eddenvato_admin_cssloader(){
	
	// It will be called only on your plugin admin page, enqueue our stylesheet here
	wp_enqueue_style( 'eddenvatoSettings' );
	   
} // End admin style CSS

/*-----------------------------------------------------------------------------------*/
/*	Define Settings
/*-----------------------------------------------------------------------------------*/

global $eddenvato_settings;

$eddenvato_settings = array(
	'enabled' 			=> "false",
	'api-key'			=> "",
	'user-name'			=> "",
	'verify-page'		=> "",
	'verify-message' 	=> "<p>To register simply enter your Envato Purchase Code and your email address and an account will be created for you. Bonus! All our Envato customers get a 10% off coupon!</p>",
	'discount'			=> 'true',
	'discount-amount'	=> '10',
	'discount-type'		=> 'percent',
	'discount-title'	=> 'Your EDD Coupon Code',
	'discount-msg'		=> 'For being an Envato customer of ours you have earned yourself a free discount coupon. To use your coupon simply enter the code at checkout.'
);

/*-----------------------------------------------------------------------------------*/
/*	Register Settings
/*-----------------------------------------------------------------------------------*/

function eddenvato_register_settings(){
	global $eddenvato_settings;
	$prefix = 'eddenvato';
	foreach($eddenvato_settings as $setting => $value){
		// Define
		$thisSetting = $prefix.'-'.$setting;
		// Register setting
		register_setting( $prefix.'-settings-group', $thisSetting );
		// Apply default
		add_option( $thisSetting, $value );
	}
}

/*-----------------------------------------------------------------------------------*/
/*	Ajax save callback
/*-----------------------------------------------------------------------------------*/

add_action('wp_ajax_eddenvato_tc_settings_save', 'eddenvato_tc_settings_save');

function eddenvato_tc_settings_save(){

		// Setup
		global $eddenvato_settings;
		$prefix = 'eddenvato';
		check_ajax_referer( $prefix.'_settings_secure', 'security' );

		// Loop through settings
		foreach($eddenvato_settings as $setting => $value){
			
			// Define
			$thisSetting = $prefix.'-'.$setting;
			
			// Register setting
			update_option( $thisSetting, $_POST[$thisSetting] );
			
		}	
		
}

/*-----------------------------------------------------------------------------------*/
/*	Page Drop Down
/*-----------------------------------------------------------------------------------*/

function eddenvato_dropdown_menu($selected = NULL){
	
	$args = array(
		'post_type' => 'page',
		'post_status' => 'publish'
	); 
	
	$pages = get_pages($args);
	
	foreach($pages as $page){ ?>
    
        <option value="<?PHP echo $page->ID; ?>"<?PHP if( $page->ID == $selected ){?> selected="selected" <?PHP } ?>><?PHP echo $page->post_title; ?></option>
				
	<?PHP } // end foreach
	
	
}

/*-----------------------------------------------------------------------------------*/
/*	New framework settings page
/*-----------------------------------------------------------------------------------*/

function eddenvato_settings_page() {
	
?>

<script>
	
jQuery(document).ready(function(){

/*-----------------------------------------------------------------------------------*/
/*	Options Pages and Tabs
/*-----------------------------------------------------------------------------------*/
	  
	jQuery('.options_pages li').click(function(){
		
		var tab_page = 'div#' + jQuery(this).attr('id');
		var old_page = 'div#' + jQuery('.options_pages li.active').attr('id');
		
		// Change button class
		jQuery('.options_pages li.active').removeClass('active');
		jQuery(this).addClass('active');
				
		// Set active tab page
		jQuery(old_page).fadeOut('slow', function(){
			
			jQuery(tab_page).fadeIn('slow');
			
		});
		
	});
	
/*-----------------------------------------------------------------------------------*/
/*	Form Submit
/*-----------------------------------------------------------------------------------*/
	
	jQuery('form#plugin-options').submit(function(){
		
		// Update MCE
		tinyMCE.triggerSave();
		
		// Post Save Data	
		var data = jQuery(this).serialize();
		jQuery.post(ajaxurl, data, function(response){
			
			if(response == 0){
				
				// Flash success message and shadow
				var success = jQuery('#success-save');
				var bg = jQuery("#message-bg");
				success.css("position","fixed");
				success.css("top", ((jQuery(window).height() - 65) / 2) + jQuery(window).scrollTop() + "px");
				success.css("left", ((jQuery(window).width() - 257) / 2) + jQuery(window).scrollLeft() + "px");
				bg.css({"height": jQuery(window).height()});
				bg.css({"opacity": .45});
				bg.fadeIn("slow", function(){
					success.fadeIn('slow', function(){
						success.delay(1500).fadeOut('fast', function(){
							bg.fadeOut('fast');
						});
					});
				});
								
			} else {
				
				//error out
				
			}
		
		});
				  
		return false;
	
	});	
	
/*-----------------------------------------------------------------------------------*/
/*	Finished
/*-----------------------------------------------------------------------------------*/
	
});

</script>

<div class="wrap">

    <div id="icon-options-general" class="icon32"><br/></div>
    <h2 class="tc-heading"><?PHP _e('EDD Envato Integration', 'tcsocialpanel') ?> <span id="version">V<?PHP echo EDDENVATO_VERSION; ?></span> <a href="<?PHP echo EDDENVATO_LOCATION; ?>/documentation" target="_blank">&raquo; <?PHP _e('View Plugin Documentation'); ?></a></h2>

</div>

<div id="message-bg"></div>
<div id="success-save"></div>

<div id="tc_framework_wrap">
    
    <div id="content_wrap">
    
    	<form id="plugin-options" name="plugin-options" action="/">
        <?php settings_fields( 'eddenvato-settings-group' ); ?>
        <input type="hidden" name="action" value="eddenvato_tc_settings_save" />
        <input type="hidden" name="security" value="<?php echo wp_create_nonce('eddenvato_settings_secure'); ?>" />
        
        	<div id="sub_header" class="info">
            
                <input type="submit" name="settingsBtn" id="settingsBtn" class="button-framework save-options" value="<?php _e('Save All Changes', 'eddenvato') ?>" />
                <span><?PHP _e('Options Page', 'eddenvato') ?></span>
                
            </div>
            
            <div id="content">
            
            	<div id="options_content">
                
                	<ul class="options_pages">
                    	<li id="general_options" class="active"><a href="#"><?php _e('Settings', 'eddenvato') ?></a><span></span></li>
                    	<li id="discount_options"><a href="#"><?php _e('Discount Settings', 'eddenvato') ?></a><span></span></li>
                    </ul>
                    
                    <div id="general_options" class="options_page">
                    
                    	<div class="option">
                        	<h3><?PHP _e('Enable EDD Envato', 'eddenvato'); ?></h3>
                            <div class="section">
                            	<div class="element"><select name="eddenvato-enabled" id="eddenvato-enabled" class="textfield">
                <option value="true" <?PHP if(get_option('eddenvato-enabled') == 'true'){echo 'selected="selected"';} ?>><?php _e('Enabled', 'eddenvato') ?></option>
                <option value="false" <?PHP if(get_option('eddenvato-enabled') == 'false'){echo 'selected="selected"';} ?>><?php _e('Disabled', 'eddenvato') ?></option>
                				</select></div>
                                <div class="description"><?php _e('Enable / disable the Envato purchase code verifier', 'eddenvato') ?>.</div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?PHP _e('Envato Username', 'eddenvato'); ?></h3>
                            <div class="section">
                            	<div class="element"><input class="textfield" name="eddenvato-user-name" type="text" id="eddenvato-user-name" value="<?php echo get_option('eddenvato-user-name'); ?>" /></div>
                                <div class="description"><?PHP _e('Enter your Envato username, the plugin will not work unless you enter a valid username / envato api pair.', 'eddenvato'); ?></div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?PHP _e('Envato API Key'); ?></h3>
                            <div class="section">
                            	<div class="element"><input class="textfield" name="eddenvato-api-key" type="text" id="eddenvato-api-key" value="<?php echo get_option('eddenvato-api-key'); ?>" /></div>
                                <div class="description"><?PHP _e('Enter your Envato API key tied to the username you entered. The plugin will not work if these options are valid.', 'eddenvato'); ?></div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?php _e('Redirect Page', 'eddenvato') ?></h3>
                            <div class="section">
                            	<div class="element">
                                    <select name="eddenvato-verify-page" id="eddenvato-verify-page" class="textfield"><?PHP eddenvato_dropdown_menu( get_option('eddenvato-verify-page') ); ?></select>
                                </div>
                                <div class="description"><?php _e('Select the page you want to redirect your users to after they create an account.', 'eddenvato') ?>.</div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?php _e('Verify Code Message', 'eddenvato') ?></h3>
                            <div class="editor-description"><?php _e('This is the message that will appear over the Purchase Code verification form.', 'eddenvato') ?>.</div><br />
                            <div class="section">
                            	<div class="editor-element">
                                    <?PHP wp_editor( stripslashes(get_option('eddenvato-verify-message')), 'eddenvato-verify-message-pro', array( 'textarea_name' => 'eddenvato-verify-message', 'media_buttons' => true, 'tinymce' => array( 'theme_advanced_buttons1' => 'formatselect,forecolor,|,bold,italic,underline,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,spellchecker,wp_fullscreen,wp_adv' ) ) ); ?>
                                </div>
                            </div>
                        </div>
                        
                    </div>


                    <div id="discount_options" class="options_page hide">
                    
                    	<div class="option">
                        	<h3><?PHP _e('Enable Envato Discount', 'eddenvato'); ?></h3>
                            <div class="section">
                            	<div class="element"><select name="eddenvato-discount" id="eddenvato-discount" class="textfield">
                <option value="true" <?PHP if(get_option('eddenvato-discount') == 'true'){echo 'selected="selected"';} ?>><?php _e('Enabled', 'eddenvato') ?></option>
                <option value="false" <?PHP if(get_option('eddenvato-discount') == 'false'){echo 'selected="selected"';} ?>><?php _e('Disabled', 'eddenvato') ?></option>
                				</select></div>
                                <div class="description"><?php _e('Enable an optional discount to new users who register using valid Envato Purchase Codes.', 'eddenvato') ?></div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?PHP _e('Discount Type', 'eddenvato'); ?></h3>
                            <div class="section">
                            	<div class="element"><select name="eddenvato-discount-type" id="eddenvato-discount-type" class="textfield">
                <option value="percent" <?PHP if(get_option('eddenvato-discount-type') == 'percent'){echo 'selected="selected"';} ?>><?php _e('Percentage', 'eddenvato') ?></option>
                <option value="flat" <?PHP if(get_option('eddenvato-discount-type') == 'flat'){echo 'selected="selected"';} ?>><?php _e('Flat Rate', 'eddenvato') ?></option>
                				</select></div>
                                <div class="description"><?php _e('Choose the type of discount you want to use.', 'eddenvato') ?></div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?PHP _e('Discount Amount', 'eddenvato'); ?></h3>
                            <div class="section">
                            	<div class="element"><input class="textfield" name="eddenvato-discount-amount" type="text" id="eddenvato-discount-amount" value="<?php echo get_option('eddenvato-discount-amount'); ?>" /></div>
                                <div class="description"><?PHP _e('Enter the amount of the discount.', 'eddenvato'); ?></div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?PHP _e('Discount Email Subject', 'eddenvato'); ?></h3>
                            <div class="section">
                            	<div class="element"><input class="textfield" name="eddenvato-discount-title" type="text" id="eddenvato-discount-title" value="<?php echo get_option('eddenvato-discount-title'); ?>" /></div>
                                <div class="description"><?PHP _e('Enter the subject of the email that will be sent notifiying the user of their discount code.', 'eddenvato'); ?></div>
                            </div>
                        </div>
                        
                    	<div class="option">
                        	<h3><?php _e('Discount Email Message', 'eddenvato') ?></h3>
                            <div class="editor-description"><?php _e('This is the message that will be sent in the email to the user notifying them of their discount code. The code will be inserted under this message.', 'eddenvato') ?>.</div><br />
                            <div class="section">
                            	<div class="editor-element">
                                    <?PHP wp_editor( stripslashes(get_option('eddenvato-discount-msg')), 'eddenvato-discount-msg-pro', array( 'textarea_name' => 'eddenvato-discount-msg', 'media_buttons' => true, 'tinymce' => array( 'theme_advanced_buttons1' => 'formatselect,forecolor,|,bold,italic,underline,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,spellchecker,wp_fullscreen,wp_adv' ) ) ); ?>
                                </div>
                            </div>
                        </div>
                                                
                    </div>
                    
                                                            
            		<br class="clear" />
                    
            </div>
            
            <div class="info bottom">
            
                <input type="submit" name="settingsBtn" id="settingsBtn" class="button-framework save-options" value="<?php _e('Save All Changes', 'eddenvato') ?>" />
            
            </div>
            
        </form>
        
    </div>

</div>

<?php } ?>