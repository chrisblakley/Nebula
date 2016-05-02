<style>
	#infinite-posts-list article {margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px dotted #ccc;}
</style>

<br/><br/>
<div class="row">
	<div class="col-md-12">

		<h2>Infinite Load Example</h2><br/><br/>

		<?php
			nebula_infinite_load_query(array('category_name' => 'bacon', 'showposts' => 4, 'paged' => get_query_var('paged')), 'nebula_custom_loop_example'); //Callback function custom_loop_example() must be defined in a functions file. Leaving empty will use loop.php
			wp_reset_query(); //Always reset the query!
		?>

	</div><!--/col-->
</div><!--/row-->