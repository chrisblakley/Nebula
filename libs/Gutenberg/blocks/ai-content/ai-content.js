wp.blocks.registerBlockType('nebula/aicontent', {
	title: 'Nebula AI Content Generator',
	icon: 'media-text',
	category: 'nebula',
	description: 'Generate content from AI using a prompt',
	keywords: ['ai', 'openai', 'content'],
	supports: {
		html: false
	},
	attributes: {
		prompt: {type: 'string', default: ''},
		content: {type: 'string', default: ''}
	},
	edit: function(props){
		const [loading, setLoading] = wp.element.useState(false);
		const {attributes, setAttributes} = props;

		const handleGenerate = () => {
			if ( !attributes.prompt ) return;

			const postTitle = wp.data.select('core/editor').getEditedPostAttribute('title') || '';

			const cleanContent = wp.blocks.parse(wp.data.select('core/editor').getEditedPostAttribute('content'))
				//.filter(b => ['core/paragraph', 'core/heading'].includes(b.name)) //This limits it to just these specific blocks
				.map(b => b.attributes?.content)
				.filter(Boolean)
				.join('\n\n');

			setLoading(true); //Show spinner

			wp.apiFetch({
				path: '/nebula/v1/generate-content',
				method: 'POST',
				data: {
					prompt: attributes.prompt,
					post_title: postTitle,
					post_content: cleanContent
				}
			}).then(response => {
				setAttributes({content: response.data});
			}).finally(() => {
				setLoading(false); //Hide spinner
			});
		};

		return [
			wp.element.createElement(
				wp.editor.InspectorControls,
				null,
				wp.element.createElement(
					wp.components.PanelBody,
					{title: 'AI Content Prompt', initialOpen: true},
					wp.element.createElement(
						wp.components.TextareaControl,
						{
							label: 'Prompt',
							value: attributes.prompt,
							onChange: value => setAttributes({prompt: value}),
							rows: 4
						}
					),
					wp.element.createElement(
						wp.components.Button,
						{
							isPrimary: true,
							className: 'nebula-ai-button',
							onClick: handleGenerate,
							disabled: loading,
							style: {marginTop: '10px'}
						},
						loading ? 'Generating...' : 'Generate Content'
					),
					loading && wp.element.createElement(wp.components.Spinner, {style: {marginLeft: '10px'}})
				)
			),
			wp.element.createElement(
				'div',
				{ className: props.className },
				wp.element.createElement(
					wp.blockEditor.RichText,
					{
						tagName: 'div',
						className: 'ai-content-output',
						value: attributes.content,
						onChange: content => setAttributes({content}),
						placeholder: 'AI content will appear here...',
					}
				)
			)
		];
	},
	save: function(props){
		return wp.element.createElement(
			wp.blockEditor.RichText.Content,
			{
				tagName: 'div',
				className: 'ai-content-output',
				value: props.attributes.content
			}
		);
	}
});
