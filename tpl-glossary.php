<?php
/**
 * Template Name: Glossary
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div id="maincontentareawrap" class="row">
	<div class="sixteen columns">
		
		<section class="sixteen colgrid">
			<div class="container">
				
				<div id="bcrumbscon" class="row">
					<?php the_breadcrumb(); ?>
				</div><!--/row-->
				
				<div class="contentbg">
					<div class="corner-left"></div>
					<div class="corner-right"></div>
					
					<?php heroslidercon('full'); ?>
					
					<div class="row">
						<div class="fourteen columns centered">
							
							<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									<h1 class="entry-title"><?php the_title(); ?></h1>
									
									<div class="entry-meta">
										<hr/>
							        	<?php nebula_meta('on', 0); ?> <?php nebula_meta('cat'); ?> <?php nebula_meta('by'); ?> <?php nebula_meta('tags'); ?>
							        	<hr/>
							        </div>
																		
									<div class="entry-content">
										<?php the_content(); ?>							
									</div><!-- .entry-content -->
									
									<?php if ( current_user_can('manage_options') ) : ?>
										<div class="container entry-manage">
											<div class="row">
												<hr/>
												<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
												<hr/>
											</div>
										</div>
									<?php else : ?>
										<hr class="articleend" />
									<?php endif; ?>
								</article><!-- #post-## -->
							<?php endwhile; ?>
							
						</div><!--/columns-->
					</div><!--/row-->
					
					<div class="row">
						<div class="fourteen columns centered">
							
							<hr/>
									
							<div class="enablejs">
								<p><strong>Enable Javascript for real-time filtering and other great features!</strong></p>
							</div>
							
							<div class="dataTables_wrapper">
								<select name="filter_Category" id="filter_Category">
									<option value=" ">Select a Category (Optional)</option>
									<option value="General">General</option>
									<option value="Fundamental">Fundamentals</option>
									
									<option value="Application">Applications</option>
										<option value="DreamWeaver">--Adobe DreamWeaver</option>
										<option value="Flash">--Adobe Flash</option>
										<option value="Photoshop">--Adobe Photoshop</option>
											<option value="Blending Options">----Blending Options</option> <!-- Blending Options vs. Blending Modes? -->
											<option value="Photoshop Filters">----Filters</option>
										<option value="Illustrator">--Adobe Illustrator</option>
											<option value="Blending Options">----Blending Options</option>
											<option value="Illustrator Filters Effects">----Filters/Effects</option>
											<option value="Illustrator Pathfinder">----Pathfinder</option>
										<option value="InDesign">--Adobe InDesign</option>
										<option value="CorelDraw">--CorelDraw</option>
										<option value="Quark Xpress">--Quark Xpress</option>
										
									<option value="Movement">Art Movements</option>
										<option value="Person">--Famous People</option>
									
									<option value="Programming">Programming</option>
										<option value="Programming Language">--Languages</option>
									
									<option value="Computing">Computing</option>
										<option value="File Extension">--File Extensions</option>
										<option value="Unit Information">--Units of Information</option>
									
									<option value="Photography">Photography</option>
									
									<option value="Print">Print</option>
										<option value="Prepress">--Prepress</option>
										<option value="on-press">--Press</option>
									
									<option value="Concept">Concepts</option>
										<option value="Color Theory">--Color Theory</option>
									
									<option value="Color">Color</option>
										<option value="Color Mode">--Color Modes</option>
										<option value="Color Theory">--Color Theory</option>
									
									<option value="Typography">Typography</option>
										<option value="Typography Anatomy">--Typography: Anatomy</option>
										
									<option value="Units">Units of Measurement</option>
										<option value="Unit Length">--Units of Length</option>
										<option value="Unit Information">--Units of Information Storage</option>
										<option value="Unit Typography">--Units of Typography</option>
									
									<option value="Web">Web</option>
										<option value="Web Analytics">--Analytics</option>
										<option value="Browser">--Browsers</option>
								</select>
								
								
								<a class="filterlinkicon" href="#" title="Link to the currently active search filter"></a>
								<a class="reseticon" href="#" title="Reset the search filter"></a>																				
      
            				<table id="definitions" width="100%" border="1" cellspacing="0" cellpadding="5">
            					<thead>
			                        <tr>
			                        	<th class="Keyword-cell">Keywords</th>
			                        	<th class="Link-cell" width="10">Link</th>
			                        	<th class="Category-cell" width="50">Main Category</th>
			                        	<th class="Term-cell" width="100">Term <br/><span style="font-size: 12px;">Click term for more info.</span></th>
			                        	<th class="Definition-cell">Definition <br/><span style="font-size: 12px;">Click on the term for a more in-depth definition and examples.</span></th>
			                        	<th class="Tutorials-Docs-cell">Documentation <br/><span style="font-size: 12px;">Tutorials, Downloads, etc.</span></th>
			                        	<th class="Credit-cell">Credit</th>
			                        </tr>
            					</thead>
            					<tfoot>
            						<tr>
            							<td align="center" colspan="6">Compiled by Chris Blakley | <a href="http://gearsidecreative.com">Gearside Creative</a></</td>
            						</tr>
            					</tfoot>
            					<tbody>
            						<?php include('includes/terms.php'); ?>
								</tbody>
	                        </table>
	                        
							</div><!-- /dataTables_wrapper -->
	                       
	                       Management here
							
						</div><!--/columns-->
					</div><!--/row-->
										
				</div><!--/contentbg-->
				<div class="nebulashadow floating"></div>
			</div><!--/container-->
		</section><!--/colgrid-->
		
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>