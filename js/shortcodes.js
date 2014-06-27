jQuery.noConflict();

jQuery(window).on('load', function() {	
	
	iframe = document.getElementById("content_ifr");
	win = iframe.contentWindow;
	doc = win.document;

}); //End Window Load


/*==========================
 Button 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulabutton', {  
        init : function(ed, url) {  
            ed.addButton('nebulabutton', {  
                title : 'Add Button',  
                image : url+'/youtube.png', //@TODO: Need to fix image path!
                //image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulabutton.png',
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[button size="medium" type="success" pretty icon="icon-mail" href="http://pinckneyhugo.com/" target="_blank"]Click Here[/button]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulabutton', tinymce.plugins.nebulabutton);  
})();


/*==========================
 Clear 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulaclear', {  
        init : function(ed, url) {  
            ed.addButton('nebulaclear', {  
                title : 'Add Clear',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulaclear.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[clear]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulaclear', tinymce.plugins.nebulaclear);  
})();


/*==========================
 Code 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulacode', {  
        init : function(ed, url) {  
            ed.addButton('nebulacode', {  
                title : 'Add Code',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulacode.png',  
                onclick : function() {  
					if (win.getSelection) {
						var selectedText = win.getSelection().toString();
					} else if (doc.selection && doc.selection.createRange) {
						var selectedText = doc.selection.createRange().text;
					}
                    
                    ed.focus();
                    if ( typeof selectedText != undefined && selectedText != '' ) {
	                    ed.selection.setContent('[code]' + selectedText + '[/code]');
                    } else {
	                    ed.selection.setContent('[code]CONTENT_HERE[/code]');
                    }
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulacode', tinymce.plugins.nebulacode);  
})();


/*==========================
 Div 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebuladiv', {  
        init : function(ed, url) {  
            ed.addButton('nebuladiv', {  
                title : 'Add Div',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebuladiv.png',  
                onclick : function() {  
                	if (win.getSelection) {
						var selectedText = win.getSelection().toString();
					} else if (doc.selection && doc.selection.createRange) {
						var selectedText = doc.selection.createRange().text;
					}
                    ed.focus();
                    if ( typeof selectedText != undefined && selectedText != '' ) {
	                    ed.selection.setContent('[div class="CLASSES" style="STYLES"]' + selectedText + '[/div]<br/>');
                    } else {
	                    ed.selection.setContent('[div class="CLASSES" style="STYLES"]CONTENT_HERE[/div]<br/>');
                    }
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebuladiv', tinymce.plugins.nebuladiv);  
})();


/*==========================
 Gumby Colgrid 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulacolgrid', {  
        init : function(ed, url) {  
            ed.addButton('nebulacolgrid', {  
                title : 'Add Colgrid',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulacolgrid.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[colspan sixteen class="CLASSES" style="STYLES"]CONTENT_HERE[/colspan]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulacolgrid', tinymce.plugins.nebulacolgrid);  
})();


/*==========================
 Gumby Container 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulacontainer', {  
        init : function(ed, url) {  
            ed.addButton('nebulacontainer', {  
                title : 'Add Container',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulacontainer.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[container class="CLASSES" style="STYLES"]CONTENT_HERE[/container]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulacontainer', tinymce.plugins.nebulacontainer);  
})();


/*==========================
 Gumby Row 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebularow', {  
        init : function(ed, url) {  
            ed.addButton('nebularow', {  
                title : 'Add Row',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebularow.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[row class="CLASSES" style="STYLES"]CONTENT_HERE[/row]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebularow', tinymce.plugins.nebularow);  
})();


/*==========================
 Gumby Column 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulacolumn', {  
        init : function(ed, url) {  
            ed.addButton('nebulacolumn', {  
                title : 'Add Column',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulacolumn.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[columns four push="one" class="CLASSES" style="STYLES"]CONTENT_HERE[/columns]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulacolumn', tinymce.plugins.nebulacolumn);  
})();


/*==========================
 Icon 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulaicon', {  
        init : function(ed, url) {  
            ed.addButton('nebulaicon', {  
                title : 'Add Icon',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulaicon.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[icon type="icon-home" color="COLOR" size="SIZE" class="CLASSES"]');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulaicon', tinymce.plugins.nebulaicon);  
})();


/*==========================
 Line 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulaline', {  
        init : function(ed, url) {  
            ed.addButton('nebulaline', {  
                title : 'Add Line',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulaline.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[line space="5"]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulaline', tinymce.plugins.nebulaline);  
})();


/*==========================
 Map 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulamap', {  
        init : function(ed, url) {  
            ed.addButton('nebulamap', {  
                title : 'Add Google Map',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulamap.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[map q="Pinckney Hugo Group"]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulamap', tinymce.plugins.nebulamap);  
})();


/*==========================
 Pre 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulapre', {  
        init : function(ed, url) {  
            ed.addButton('nebulapre', {  
                title : 'Add Pre',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulapre.png',  
                onclick : function() {  
                    if (win.getSelection) {
						var selectedText = win.getSelection().toString();
					} else if (doc.selection && doc.selection.createRange) {
						var selectedText = doc.selection.createRange().text;
					}
                    ed.focus();
                    if ( typeof selectedText != undefined && selectedText != '' ) {
	                    ed.selection.setContent('[pre lang="LANGUAGE"]' + selectedText + '[/pre]<br/>');
                    } else {
	                    ed.selection.setContent('[pre lang="LANGUAGE"]CONTENT_HERE[/pre]<br/>');
                    }
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulapre', tinymce.plugins.nebulapre);  
})();


/*==========================
 Space 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulaspace', {  
        init : function(ed, url) {  
            ed.addButton('nebulaspace', {  
                title : 'Add Vertical Space',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulaspace.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[space height=25]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulaspace', tinymce.plugins.nebulaspace);  
})();


/*==========================
 Youtube 
 ===========================*/
(function() {  
    tinymce.create('tinymce.plugins.nebulayoutube', {  
        init : function(ed, url) {  
            ed.addButton('nebulayoutube', {  
                title : 'Add Youtube Video',  
                image : 'http://gearside.com/nebula/wp-content/themes/WP-Nebula-master/images/admin/nebulayoutube.png',  
                onclick : function() {  
                    ed.focus();
                    ed.selection.setContent('[youtube id="YOUTUBE_VIDEO_ID"]<br/>');  
                }  
            });  
        },  
        createControl : function(n, cm) {  
            return null;  
        }
    });  
    tinymce.PluginManager.add('nebulayoutube', tinymce.plugins.nebulayoutube);  
})();