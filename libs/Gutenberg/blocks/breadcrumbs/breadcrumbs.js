wp.blocks.registerBlockType('nebula/breadcrumbs', {
	title: 'Nebula Breadcrumbs',
	icon: 'dashicons-admin-links', //https://developer.wordpress.org/resource/dashicons/
	category: 'nebula',
	description: 'Add Breadcrumbs.',
	keywords: ['breadcrumbs', 'nebula'],
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
				'div',
				{
					id: 'nebula-breadcrumbs-block-' + props.instanceId,
					className: 'nebula-breadcrumbs ratio ratio-16x9 ' + props.className,
				},
				wp.element.createElement(
					'iframe',
					{
						id: 'nebula-breadcrumbs-block', //Does this need to be an option? I'd prefer not
						className: 'breadcrumbs',
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