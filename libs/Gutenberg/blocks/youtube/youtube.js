/*
var el = wp.element.createElement;
var registerBlockType = wp.blocks.registerBlockType;
var withAPIData = wp.components.withAPIData;

registerBlockType('nebula/procedural', {
	title: 'Nebula Procedural',
	icon: 'megaphone',
	category: 'widgets',
	edit: withAPIData(function(){
		return {
			posts: '/wp/v2/posts?per_page=1'
		};
	})(function(props){
		if ( !props.posts.data ){
			return "Loading latest posts...";
		}

		if ( props.posts.data.length === 0 ){
			return "No posts";
		}

		var className = props.className;
		var post = props.posts.data[0];

		return el('a', {
			className: className,
			href: post.link
		}, post.title.rendered);
	}),
	save: function(){
		return null; //Rendering in PHP
	},
} );
*/






wp.blocks.registerBlockType('nebula/youtube', {
	title: 'Nebula Youtube',
	icon: 'video-alt3', //https://developer.wordpress.org/resource/dashicons/
	category: 'widgets',
	description: 'Add a Youtube video with Nebula styles and built-in tracking with the Youtube API.',
	keywords: ['youtube', 'video', 'nebula'],
	supportHTML: false, //Remove to allow block to be edited in HTML mode

	attributes: {
		content: {
			type: 'array',
			source: 'children',
			selector: 'p',
		},
		alignment: {
			type: 'string',
		},
	},


	edit: function(props){

		//Trying to create an inspector panel with a simple text input..........

		//Maybe try:
/*
		wp.element.createElement(wp.editor.InspectorControls, null,
			wp.element.createElement(ee.PanelBody, { //ee is sent via the edit function like this: edit: Object(ee.withInstanceId)(function(e){..........
				title: "Spacer Settings"
			},
			wp.element.createElement(ee.BaseControl, { //ee is sent via the edit function like this: edit: Object(ee.withInstanceId)(function(e){..........
				label: "Height in pixels",
				id: a //I don't know what "a" was minified from
			},
			wp.element.createElement("input", {
				type: "number",
				id: a, //I don't know what "a" was minified from
				onChange: function(e){
					n({height: parseInt(e.target.value, 10)}) //I don't know what "n()" was minified from
				},
				value: i, //I don't know what "i" was minified from
				min: "20",
				step: "10"
			})
			))
		);
*/



		return [
			wp.element.createElement(wp.editor.InspectorControls, null)
		];






/*
		return [
			wp.element.createElement(RichText, {
				key: 'editable',
				tagName: 'p',
				className: props.className,
				value: props.attributes.content,
			})
		];
*/




/*
		//This is from this tutorial: https://github.com/pantheon-systems/github-gist-gutenberg-block/blob/master/blocks/github-gist/index.js
		var returnElements = []; //Fill this with elements to return

		//Ugh this tutorial i followed just uses the editor itself not the sidebar panel.... :(
		var url = props.attributes.url || '';
		var focus = props.focus;

		// retval is our return value for the callback.
		var retval = [];

		// When the block is focus or there's no URL value, show the text input control so the user can enter a URL.
		if ( !!focus || !url.length ) {
			console.log('focused on the youtube block -or- no URL value');

			returnElements.push(
				wp.element.createElement(wp.components.TextControl, {
					value: url, // Existing 'url' value for the block.

					// When the text input value is changed, we need to update the 'url' attribute to propagate the change.
					onChange: function(newVal){
						console.log('changed the control value in youtube block inspector panel');
						props.setAttributes({
							url: newVal
						});
					},
					placeholder: 'Enter a Youtube video ID',
				})
			);
		}

		//Just for fun:
		returnElements.push(
			wp.element.createElement('div', {
				className: 'nebula-youtube embed-responsive embed-responsive-16by9 ' + props.className,
			}, wp.element.createElement('iframe', {
				id: 'phg-overview-video',
				className: 'youtube embed-responsive-item',
				width: 400,
				height: 300,
				src: '//www.youtube.com/embed/M77qiSNdnNA?wmode=transparent&enablejsapi=1&rel=0',
				frameBorder: 0,
				allowfullscreen: ""
			}))
		);

		//Interesting note: you can return an array of elements created by wp.element.createElement()
		return returnElements;
*/





		//This stuff below is working. Don't screw with it!

		return wp.element.createElement('div', {
			className: 'nebula-youtube embed-responsive embed-responsive-16by9 ' + props.className,
		}, wp.element.createElement('iframe', {
			id: 'phg-overview-video',
			className: 'youtube embed-responsive-item',
			width: 400,
			height: 300,
			src: '//www.youtube.com/embed/M77qiSNdnNA?wmode=transparent&enablejsapi=1&rel=0',
			frameBorder: 0,
			allowfullscreen: ""
		}));
	},
	save: function(){
		return null; //Rendering in PHP
	},
});



/*
//This uses ESNext and I need raw JS... Can I transpile it somewhere? I just need to see what the output looks like... also where does it go?
renderInspector = ({ isSelected, attributes, setAttributes }) => {
	if (!isSelected) {
		return null;
	}

	const {
		textColor
	} = attributes
	return (
		<InspectorControls>
			<div>
				<h2>{'Text Color'}</h2>
				<BaseControl>
					<ColorPalette
						value={textColor}
						onChange={(textColor) => setAttributes({ textColor })} />
				</BaseControl>
			</div>
		</InspectorControls>
	)
}
*/