/*==========================
 Nebula TinyMCE Toolbar
 ===========================*/
(function(){
	tinyMCE.create('tinymce.plugins.nebulatoolbar', {
		init: function(ed, url){
			ed.addButton('nebulaaccordion', {
				title: 'Insert Accordion',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulaaccordion.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[accordion type="single"]<br />[accordion_item title="TITLE_HERE" default="open"]CONTENT_HERE[/accordion_item]<br />[/accordion]');
				}
			});

			ed.addButton('nebulabutton', {
				title: 'Insert Button',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulabutton.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[button href=https://example.com/ target=_blank]Click Here[/button]');
				}
			});

			ed.addButton('nebulaclear', {
				title: 'Insert Clear',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulaclear.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[clear]');
				}
			});

			ed.addButton('nebulacode', {
				title: 'Insert Code',
				type: 'menubutton',
				icon: 'nebulacode',
				classes: 'widget btn nebula-icon',
				menu: [{
					text: 'Tag',
					onclick: function(){
						ed.focus();
						var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
						ed.selection.setContent('[code]' + shortcodeContent + '[/code]');
					}
				}, {
					text: 'Pre',
					onclick: function(){
						ed.focus();
						var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
						ed.selection.setContent('[pre lang=LANGUAGE]' + shortcodeContent + '[/pre]');
					}
				}, {
					text: 'Gist',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[gist file=FILENAME lang=LANGUAGE]URL[/gist]');
					}
				}]
			});

			ed.addButton('nebuladiv', {
				title: 'Insert Div',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebuladiv.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();

					var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
					ed.selection.setContent('[div class="CLASSES" style=STYLES]' + shortcodeContent + '[/div]');
				}
			});

			ed.addButton('nebulacolgrid', {
				title: 'Insert Grid',
				type: 'menubutton',
				icon: 'nebulacolgrid',
				classes: 'widget btn nebula-icon',
				menu: [{
					text: 'Container',
					onclick: function(){
						ed.focus();
						var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
						ed.selection.setContent('[container class="CLASSES" style="STYLES"]' + shortcodeContent + '[/container]');
					}
				}, {
					text: 'Row',
					onclick: function(){
						ed.focus();
						var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
						ed.selection.setContent('[row class="CLASSES" style="STYLES"]' + shortcodeContent + '[/row]');
					}
				}, {
					text: 'Column',
					onclick: function(){
						ed.focus();
						var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
						ed.selection.setContent('[columns scale=md columns=12 class="CLASSES" style="STYLES"]' + shortcodeContent + '[/columns]');
					}
				}]
			});

			ed.addButton('nebulaicon', {
				title: 'Insert Icon',
				type: 'splitbutton',
				icon: 'nebulaicon',
				classes: 'widget btn colorbutton nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[icon type=fas-home color=COLOR size=SIZE class="CLASSES"]');
				},
				menu: [{
					text: 'View all Font Awesome icons »',
					onclick: function(){
						window.open('https://fontawesome.com/icons?d=gallery', '_blank');
					}
				}]
			});

			ed.addButton('nebulaline', {
				title: 'Insert Line',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulaline.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[line space=5]');
				}
			});

			ed.addButton('nebulamap', {
				title: 'Insert Google Map',
				type: 'splitbutton',
				icon: 'nebulamap',
				classes: 'widget btn colorbutton nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[map q="Syracuse"]');
				},
				menu: [{
					text: 'Place',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[map q="Syracuse"]');
					}
				}, {
					text: 'Directions',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[map mode=directions origin="Syracuse" destination="New York City"]');
					}
				}, {
					text: 'Search',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[map mode=search q="Food in Syracuse, NY"]');
					}
				}, {
					text: 'View',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[map mode=view center="43.0536364,-76.1657063" zoom=19 maptype=satellite]');
					}
				}]
			});

			ed.addButton('nebulaslider', {
				title: 'Insert Slider',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulaslider.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[slider frame status]<br />[slide title="TITLE_HERE" link=https://www.example.com target=_blank]IMAGE_URL_HERE[/slide]<br />[/slider]');
				}
			});

			ed.addButton('nebulaspace', {
				title: 'Insert Vertical Space',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulaspace.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[space height=25]');
				}
			});

			ed.addButton('nebulatooltip', {
				title: 'Insert Tooltip',
				image: nebula.site.directory.template.uri + '/assets/img/admin/nebulatooltip.png',
				classes: 'widget btn nebula-icon',
				onclick: function(){
					ed.focus();
					var shortcodeContent = ( tinyMCE.activeEditor.selection.getContent() !== '' )? tinyMCE.activeEditor.selection.getContent() : 'CONTENT_HERE';
					ed.selection.setContent('[tooltip tip="BUBBLE_TEXT_HERE"]' + shortcodeContent + '[/tooltip]');
				}
			});

			ed.addButton('nebulavideo', {
				title: 'Insert Video',
				type: 'splitbutton',
				icon: 'nebulavideo',
				classes: 'widget btn colorbutton nebula-icon',
				onclick: function(){
					ed.focus();
					ed.selection.setContent('[youtube id=YOUTUBE_VIDEO_ID]');
				},
				menu: [{
					text: 'Youtube',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[youtube id=YOUTUBE_VIDEO_ID]');
					}
				}, {
					text: 'Vimeo',
					onclick: function(){
						ed.focus();
						ed.selection.setContent('[vimeo id=VIMEO_VIDEO_ID]');
					}
				}]
			});
		},
		createControl: function(n, cm){
			return null;
		}
	});
	tinymce.PluginManager.add('nebulatoolbar', tinymce.plugins.nebulatoolbar);
})();