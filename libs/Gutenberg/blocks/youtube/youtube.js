wp.blocks.registerBlockType('nebula/youtube', {
	title: 'Nebula Youtube',
	icon: 'video-alt3', //https://developer.wordpress.org/resource/dashicons/
	category: 'nebula',
	description: 'Add a Youtube video with Nebula styles and built-in tracking with the Youtube API.',
	keywords: ['youtube', 'video', 'nebula'],
	supportHTML: false, //Remove to allow block to be edited in HTML mode
	attributes: {
		videoID: {
			type: 'text',
			default: '',
		},
	},
	edit: function(props){
		var attributes = props.attributes;

		return [
			wp.element.createElement(wp.editor.InspectorControls, null,
				wp.element.createElement(wp.components.PanelBody, {
					title: 'Nebula Youtube Settings'
				},
				wp.element.createElement(wp.components.BaseControl, {
					label: 'Video ID',
					id: 'nebula-youtube-id-' + props.instanceId,
				},
				wp.element.createElement('input', {
					type: 'text',
					id: 'nebula-youtube-id-' + props.instanceId,
					value: props.attributes.videoID,
					onChange: function(e){
						props.setAttributes({
							videoID: e.target.value
						});
					}
				})
				))
			),
			wp.element.createElement('div', {
				id: 'nebula-youtube-block-' + props.instanceId,
				className: 'nebula-youtube embed-responsive embed-responsive-16by9 ' + props.className,
			}, wp.element.createElement('iframe', {
				id: 'phg-overview-video', //Does this need to be an option? I'd prefer not
				className: 'youtube embed-responsive-item',
				width: 400,
				height: 300,
				src: '//www.youtube.com/embed/' + props.attributes.videoID + '?wmode=transparent&enablejsapi=1&rel=0', //WCtWWgtzC-c
				frameBorder: 0,
				allowfullscreen: ''
			}))
		];
	},
	save: function(props){
		return null; //Rendering in PHP (by calling whatever function is set to render_callback key of the register_block_type() function)
	},
});