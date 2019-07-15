<?php

/*
Plugin Name: Envato Integration - Easy Digital Downloads
Plugin Script: edd-envato.php
Plugin URI: http://tyler.tc
Description: A simple way to let your WordPress / Easy Digital Downloads users to register on your site using their Envato purchase codes, and get a discount in the proccess.
Version: 1.0.1
Author: Tyler Colwell
Author URI: http://tyler.tc

--- THIS PLUGIN AND ALL FILES INCLUDED ARE COPYRIGHT © TYLER COLWELL 2011-2013.
YOU MAY NOT MODIFY, RESELL, DISTRIBUTE, OR COPY THIS CODE IN ANY WAY. ---

*/

/*-----------------------------------------------------------------------------------*/
/*	Define Anything Needed
/*-----------------------------------------------------------------------------------*/

define('EDDENVATO_LOCATION', WP_PLUGIN_URL . '/'.basename(dirname(__FILE__)));
define('EDDENVATO_PATH', plugin_dir_path(__FILE__));
define('EDDENVATO_RELPATH', dirname( plugin_basename( __FILE__ ) ) );
define('EDDENVATO_VERSION', '1.0.0');
require_once('inc/tcf_settings_page.php');
require_once('inc/tcf_manage_page.php');
require_once('inc/tcf_bootstrap.php');

?>