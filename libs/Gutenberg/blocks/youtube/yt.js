wp.blocks.registerBlockType('nebula/youtube', {
	title: 'Nebula Youtube',
	icon: 'video-alt3', //https://developer.wordpress.org/resource/dashicons/
	category: 'nebula',
	description: 'Add a Youtube video with Nebula styles and built-in tracking with the Youtube API.',
	keywords: ['youtube', 'video', 'nebula'],
	supports: {
		html: false //Remove to allow block to be edited in HTML mode
	},
	attributes: {
		videoID: {
			type: 'text',
			default: '',
		},
	},
	edit: function(props){
		var attributes = props.attributes;

		return [
			wp.element.createElement(
				wp.editor.InspectorControls,
				null,
				wp.element.createElement(
					wp.components.PanelBody,
					{
						title: 'Nebula Youtube Settings'
					},
					wp.element.createElement(
						wp.components.BaseControl,
						{
							label: 'Video ID',
							id: 'nebula-youtube-id-' + props.instanceId
						},
						wp.element.createElement(
							'input', {
								type: 'text',
								id: 'nebula-youtube-id-' + props.instanceId,
								value: props.attributes.videoID,
								onChange: function(e){
									props.setAttributes({
										videoID: e.target.value
									});
								}
							}
						)
					),
					wp.element.createElement(
						wp.components.BaseControl,
						{
							label: 'Video Timestamp',
							id: 'nebula-youtube-id-' + props.instanceId
						},
						wp.element.createElement(
							'input', {
								type: 'text',
								id: 'nebula-youtube-id-' + props.instanceId,
								value: props.attributes.videoTimestamp,
								onChange: function(e){
									props.setAttributes({
										videoTimestamp: e.target.value
									});
								}
							}
						)
					)
				)
			),
			wp.element.createElement(
				'div',
				{
					id: 'nebula-youtube-block-' + props.instanceId,
					className: 'nebula-youtube ratio ratio-16x9 ' + props.className, //This className var is confirmed working, but this is the Block Editor element– not the front-end!
				},
				wp.element.createElement(
					'iframe',
					{
						id: 'nebula-youtube-block', //Does this need to be an option? I would prefer not
						className: 'youtube',
						width: 400,
						height: 300,
						src: '//www.youtube.com/embed/' + props.attributes.videoID + '?wmode=transparent&enablejsapi=1&rel=0&t=' + props.attributes.videoTimestamp, //WCtWWgtzC-c
						frameBorder: 0,
						allowfullscreen: ''
					}
				)
			)
		];
	},
	save: function(props){

		//console.log('props:', props);

		return null; //Rendering in PHP (by calling whatever function is set to render_callback key of the register_block_type() function)
	},
});