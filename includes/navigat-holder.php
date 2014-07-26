<h4>AJAX successful</h4>
<p>
	In this PHP file, we will be checking your term against page titles, post titles, categories, and tags (in that order). If nothing is found, we will return search results.<br/>
	This will all happen behind the scenes, but while it's in progress you can watch it think!
</p>

<?php
	$requested_page = $_POST['data'];
	$resultCounter = 0;
?>

<h5>The current navigation request is: <strong><?php echo $requested_page; ?></strong></h5>


<h5>Checking against page titles...</h5>
<?php query_posts( array('post_type' => 'page', 'pagename' => $requested_page, 'showposts' => 15) ); ?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<?php $resultCounter++; ?>
	<p><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></p>
	<?php echo 'navigating to here: ' . get_permalink(); ?>
<?php endwhile; ?>
<?php wp_reset_query(); ?>


<?php if ( $resultCounter == 0 ) : ?>
	<h5>No pages, so checking against post titles...</h5>
	<?php query_posts( array('name' => $requested_page, 'showposts' => 1) ); ?>
	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		<?php $resultCounter++; ?>
		<p><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></p>
	<?php endwhile; ?>
	<?php wp_reset_query(); ?>
<?php endif; ?>


<?php if ( $resultCounter == 0 ) : ?>
	<h5>No posts either, so checking against category names...</h5>
	<?php query_posts( array('category_name' => $requested_page, 'showposts' => 1) ); ?>
	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		<?php $resultCounter++; ?>
		<p><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></p>
	<?php endwhile; ?>
	<?php wp_reset_query(); ?>
<?php endif; ?>


<?php if ( $resultCounter == 0 ) : ?>
	<h5>No categories either. Checking against tags...</h5>
	<?php query_posts( array('tag' => $requested_page, 'showposts' => 1) ); ?>
	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
		<?php $resultCounter++; ?>
		<p><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></p>
	<?php endwhile; ?>
	<?php wp_reset_query(); ?>
<?php endif; ?>

<?php if ( $resultCounter == 0 ) : ?>
	<h5>No tags either. Sending to a search results page!</h5>
	<?php
		//@TODO: strip spaces before and after, convert spaces to "+", get rid of that "0" that appears every time
	?>
	<?php echo home_url() . '?s=' . $requested_page; ?>
<?php endif; ?>