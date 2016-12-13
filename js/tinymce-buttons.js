(function() {
        tinymce.PluginManager.add('wdm_mce_dropbutton', function( editor, url ) {
           editor.addButton( 'wdm_mce_dropbutton', {
                 text: 'WDM Dropdown',
                 icon: false,
                 type: 'menubutton',
                 menu: [
                       {
                        text: 'Sample Item 1',
                        onclick: function() {
                           editor.insertContent('[wdm_shortcode 1]');
                                  }
                        },
                       {
                        text: 'Sample Item 2',
                        onclick: function() {
                           editor.insertContent('[wdm_shortcode 2]');
                                 }
                       }
                       ]
              });
        });
})();