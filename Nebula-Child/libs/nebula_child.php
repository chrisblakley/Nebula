<?php

/*==========================
 Custom child theme functions
 Use this file for project-specific functions.

 See instructions for template directories and function overrides in ../functions.php
 ===========================*/

//Add new image sizes
//Certain sizes (like FB Open Graph sizes) are already added, so only add extra sizes that are needed.
//add_action('after_setup_theme', 'custom_image_sizes');
//function ssk_image_sizes(){
	//add_image_size('example', 32, 32, 1);
//}


//Add/remove post formats as needed - http://codex.wordpress.org/Post_Formats
//add_theme_support('post-formats', array('aside', 'chat', 'status', 'gallery', 'link', 'image', 'quote', 'video', 'audio'));

//Google Analytics Experiments (Split Tests)
//Documentation: http://gearside.com/nebula/documentation/custom-functionality/split-tests-using-google-analytics-experiments-with-nebula/
//Add a new condition for each experiment group. There can be as many concurrent experiments as needed (just make sure there is no overlap!)
add_action('nebula_head_open', 'nebula_ga_experiment_detection');
function nebula_ga_experiment_detection(){
	//Example Experiment
	if ( is_page(9999) ){ //Use is_post(9999) for single posts. Change the ID to match the desired page/post! ?>
		<!-- Paste Google Analytics Experiment generated script here -->
	<?php }
}

?>