wp.blocks.registerBlockType('nebula/vimeo', {
	title: 'Nebula Vimeo',
	icon: 'video-alt3', //https://developer.wordpress.org/resource/dashicons/
	category: 'nebula',
	description: 'Add a Vimeo video with Nebula styles and built-in tracking with the Vimeo API.',
	keywords: ['vimeo', 'video', 'nebula'],
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
			wp.element.createElement(
				wp.editor.InspectorControls,
				null,
				wp.element.createElement(
					wp.components.PanelBody,
					{
						title: 'Nebula Vimeo Settings'
					},
					wp.element.createElement(wp.components.BaseControl,
					{
						label: 'Video ID',
						id: 'nebula-vimeo-id-' + props.instanceId,
					},
					wp.element.createElement(
						'input',
						{
							type: 'text',
							id: 'nebula-vimeo-id-' + props.instanceId,
							value: props.attributes.videoID,
							onChange: function(e){
								props.setAttributes({
									videoID: e.target.value
								});
							}
						})
					)
				)
			),
			wp.element.createElement(
				'div',
				{
					id: 'nebula-vimeo-block-' + props.instanceId,
					className: 'nebula-vimeo ratio ratio-16x9 ' + props.className,
				},
				wp.element.createElement(
					'iframe',
					{
						id: 'nebula-video', //Does this need to be an option? I'd prefer not
						className: 'vimeo',
						width: 400,
						height: 300,
						src: String('https://player.vimeo.com/video/' + props.attributes.videoID),
						frameBorder: 0,
						allowfullscreen: ''
					}
				)
			)
		];
	},
	save: function(props){
		return null; //Rendering in PHP (by calling whatever function is set to render_callback key of the register_block_type() function)
	},
});