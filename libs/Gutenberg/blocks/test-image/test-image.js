const {__} = wp.i18n;
const {registerBlockType} = wp.blocks;
const el = wp.element.createElement;

registerBlockType('hiRoy/serverSide', {
	title: __('Nebula Server Side Block', 'text-domain'),
	icon: 'networking',
	category: 'nebula',
	attributes: {
		images: {
			default: [],
			type: 'array',
		}
	},
	edit({attributes, setAttributes, className, focus, id}){
		//Put a user interface here.
	},
	save({attributes, className}){
		//gutenberg will save attributes we can use in server-side callback
		return null;
	},
} );