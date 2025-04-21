const { registerBlockType } = wp.blocks;
const { PlainText, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

wp.blocks.registerBlockType('nebula/code', {
    title: 'Nebula Code',
    icon: 'editor-code',
    category: 'nebula',
    description: 'Add a code block with Nebula styles and a custom language attribute.',
    keywords: ['code', 'programming', 'nebula'],
    supports: {
        html: false
    },
    attributes: {
        content: {
            type: 'string',
            default: '',
        },
        language: {
            type: 'string',
            default: '',
        },
    },
    edit: function( { attributes, setAttributes } ) {
        let { content, language } = attributes;

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(InspectorControls, null,
                wp.element.createElement( PanelBody, { title: 'Nebula Code Settings' },
                    wp.element.createElement( TextControl, {
                        label: 'Language',
                        value: language,
                        onChange: function( val ) { setAttributes({ language: val }); },
                        placeholder: ''
                    })
                )
            ),
            wp.element.createElement(PlainText, {
                tagName: 'pre',
                className: 'wp-block-code nebula-code ' + language, //Keep this class for convenient admin content editor formatting (at least for now)
                value: content,
                onChange: function( val ) { setAttributes({ content: val }); },
                placeholder: 'Write code…'
            })
        );
    },
    save: function( { attributes } ) {
		return null;
	}
});
