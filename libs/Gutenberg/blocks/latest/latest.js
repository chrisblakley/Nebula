//console.log('inside latest.js');

var el = wp.element.createElement;
var registerBlockType = wp.blocks.registerBlockType;
var withAPIData = wp.components.withAPIData;

//@todo: how to add attributes/parameters to the block? (like Video ID when developing the Youtube block)

registerBlockType('nebula/latest-post', {
	title: 'Nebula Latest Post',
	icon: 'megaphone',
	category: 'nebula',

	edit: withAPIData(function(){
		//console.log('using api data');

		return {
			posts: '/wp/v2/posts?per_page=3'
		};
	})(function(props){
		//console.log('inside props function');

		if ( !props.posts.data ){
			return 'loading!';
		}

		if ( props.posts.data.length === 0 ){
			return 'No posts';
		}

		//console.log('made it past the initial returns');
		var className = props.className;
		var post = props.posts.data[0];

		return el(
			'a', {
				className: className,
				href: post.link
			},
			post.title.rendered
		);
	}),

	save: function({attributes, className}){
		// Rendering in PHP
		//console.log('saving, but returning null (since it is server side)');
		return null;
	},
});