function tinyplugin() {
	
	// Retrun plugin value to tinyMCE
    return "[eddenvato-plugin]";
	
}

// Start Tiny MCE plugin
(function() {

    tinymce.create('tinymce.plugins.eddenvatoplugin', {

		// When initiated:
        init : function(ed, url){
			
			// Create dialog command
			ed.addCommand('eddenvato-shortcode', function(){
				
				// Send our modified shortcode to the editor with selected content				
				tinymce.execInstanceCommand('content', 'mceInsertContent', false, '[edd-envato]');
	
				// Repaints the editor
				tinymce.execCommand('mceRepaint');
				
			});
			
			// Add the like locker button to the toolbar
            ed.addButton('eddenvatoplugin', {
                title : 'EDD Envato Form',
				cmd : 'eddenvato-shortcode',				
                image: url + "/mce.png"
            }); // end addbutton
			
        },

		// Set MCE plugin info
        getInfo : function() {
			
            return {
				
                longname : 'EDD Envato',
                author : 'Tyler Colwell',
                authorurl : 'http://tyler.tc',
                infourl : 'http://tyler.tc',
                version : "1.0"
				
            };
			
        } // end set info
		
    }); // End main plugin init

	// Finally add functionality to toolbar
    tinymce.PluginManager.add('eddenvatoplugin', tinymce.plugins.eddenvatoplugin);
    
})(); // end plugin