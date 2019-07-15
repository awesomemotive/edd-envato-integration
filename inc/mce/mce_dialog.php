<?PHP

// Boostrap WP
$wp_include = "../wp-load.php";
$i = 0;
while (!file_exists($wp_include) && $i++ < 10) {
  $wp_include = "../$wp_include";
}

// let's load WordPress
require($wp_include);

// Get Options from DB
//$tc = get_option('');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Like Locker Pro</title>
<style type="text/css">

#shortcodeform label{
	display:block;
	margin:10px 0px 5px 0px;
	color:#21759B;
	font-family:Arial, Helvetica, sans-serif;
	font-weight:bold;
	font-size:13px;
	background:url(../images/mce-gear.png) left center no-repeat;
	line-height:20px;
	padding:0 0 0 24px;
}

#shortcodeform .input{
	width:395px;
    background: none repeat scroll 0 0 #F3F3F3;
    border: 1px solid #DDDDDD;
    color: #333333;
    padding: 6px;
	margin:5px 0px 10px 0px;
	font-size:11px;
}

#shortcodeform .input.size{
	width:80px !important;
    background: none repeat scroll 0 0 #F3F3F3;
    border: 1px solid #DDDDDD;
    color: #333333;
    padding: 6px;
	margin:5px 0px 10px 0px;
	font-size:11px;
}

#tcsh-insert{
	padding:7px 11px;
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	font-weight:bold;
    background: none repeat scroll 0 0 #F3F3F3;
    border: 1px solid #DDDDDD;
    color: #999999 !important;
	font-size:12px;
	cursor:pointer;
}

#tcsh-insert:hover{
	border:1px solid #bbbbbb;
	color:#666666 !important;
}

</style>
<link href="<?php echo get_option('siteurl') ?>/wp-includes/js/thickbox/thickbox.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/jquery/jquery.js"></script>
<script language="javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/thickbox/thickbox.js"></script>
<script language="javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
<script language="javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
<script language="javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
<script language="javascript" type="text/javascript">

	jQuery(document).ready(function() {
	
		jQuery('#upload_image_button').click(function() {
			 formfield = jQuery('#upload_image').attr('name');
			 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
			 return false;
		});
		
		window.send_to_editor = function(html) {
			 imgurl = jQuery('img',html).attr('src');
			 jQuery('#upload_image').val(imgurl);
			 tb_remove();
		}
	
	});


	// Start TinyMCE
	function init() {
		tinyMCEPopup.resizeToInnerSize();
	}	
	
	// Function to add the like locker shortcode to the editor
	function addLocker(){
		
		// Cache our form vars
		var id 		= "<?PHP echo substr( md5( time() ), 0, -15 ); ?>";
		var url 	= document.getElementById('llpro-url').value;
		var theme 	= document.getElementById('llpro-theme').value;
		var scheme 	= document.getElementById('llpro-scheme').value;
		var message = document.getElementById('llpro-msg').value;
					
		// If TinyMCE runable
		if(window.tinyMCE) {
			
			// Get the selected text in the editor
			selected = tinyMCE.activeEditor.selection.getContent();
			
			// Send our modified shortcode to the editor with selected content				
			window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, '[like-locker-pro id="'+id+'" theme="'+theme+'" scheme="'+scheme+'" url="'+url+'" message="'+message+'"]'+selected+'[/like-locker-pro]');

			// Repaints the editor
			tinyMCEPopup.editor.execCommand('mceRepaint');
			
			// Close the TinyMCE popup
			tinyMCEPopup.close();
			
		} // end if
		
		return; // always R E T U R N

	} // end add like locker function
	
</script>
</head>

<body>

<div class="tabs">
    <ul>
        <li id="main_tab" class="current"><span><a href="javascript:mcTabs.displayTab('main_tab','main_panel');" onmousedown="return false;">Locker Settings</a></span></li>
    </ul>
</div>

<div id="thkBC_options" class="panel_wrapper">

    <form method="post" action="" id="shortcodeform">

    <div id="main_panel" class="panel current" style="height:400px;"><br />
    
        <label for="llpro-theme">Color Theme</label>
        <select name="llpro-theme" class="input" id="llpro-theme">
            <option value="facebook" selected="selected">Facebook Theme</option>
            <option value="blue">Blue Theme</option>
            <option value="grey">Grey Theme</option>
            <option value="red">Red Theme</option>
            <option value="green">Green Green</option>
            <option value="yellow">Yellow Theme</option>
            <option value="orange">Orange Theme</option>
            <option value="black">Black / Dark Theme</option>
        </select>

        <label for="llpro-sceheme">Like Button Colorscheme</label>
        <select name="llpro-scheme" class="input" id="llpro-scheme">
            <option value="default" selected="selected">Default</option>
            <option value="light">light</option>
            <option value="dark">dark</option>
        </select>
        
        <label for="llpro-url">Like Button URL</label>
        <input name="llpro-url" type="text" class="input" id="llpro-url" value="CURRENT" />
        
        <label for="llpro-msg">Message To Show Users</label>
        <textarea name="llpro-msg" id="llpro-msg" cols="" rows="3" class="input">Click Like to unlock this content!</textarea><br />

        <input name="tcsh-insert" type="button" id="tcsh-insert" value="Insert Locker" onclick="addLocker();" />                              
                                  
    </div>
        
    </form>

</div>

</body>
</html>