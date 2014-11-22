<?php
/**
 * Template Name: NFL
 */

if ( !defined('ABSPATH') ) {  //Log and redirect if accessed directly
	header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], "wp-content/")) . '?directaccess=' . basename($_SERVER['PHP_SELF']));
	exit;
}

get_header(); ?>

<div id="maincontentareawrap" class="row">
	<div class="thirteen columns">
		
		<section class="sixteen colgrid">
			<div class="container">
				
				<div id="bcrumbscon" class="row">
					<?php the_breadcrumb(); ?>
				</div><!--/row-->
				
				<div class="contentbg">
					<div class="corner-left"></div>
					<div class="corner-right"></div>
					
					<?php heroslidercon(); ?>
					
					<div class="row">
						<div class="fourteen columns centered">
							
							
							
							
							
							
							
							
							
							
							
							
							<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									<h1 class="entry-title"><?php the_title(); ?></h1>
									
									<div class="entry-content">
										<?php the_content(); ?>
										
										<hr/>
										
										<?php
											//Post-Season Date & Time
											$playoffs = array(
											    "minutes" => 30,
											    "hour-12" => 4,
											    "ampm" => 'pm',
											    "dayname" => "Saturday",
											    "day" => 4,
											    "monthname" => "January",
											    "month" => 1,
											    "year" => 2015,
											);
											$playoffs['hour-24'] = ( $playoffs['ampm'] == 'pm' && $playoffs['hour-12'] != 12 ) ? $playoffs['hour-12']+12 : $playoffs['hour-12'];
											$playoffs['hour-24'] = ( $playoffs['ampm'] == 'am' && $playoffs['hour-12'] == 12 ) ? 0 : $playoffs['hour-24'];
											
											//Super Bowl Date & Time
											$superbowl = array(
											    "minutes" => 35,
											    "hour-12" => 6,
											    "ampm" => 'pm',
											    "dayname" => "Sunday",
											    "day" => 2,
											    "monthname" => "February",
											    "month" => 2,
											    "year" => 2014,
											    "broadcast" => "Fox"
											);
											$superbowl['hour-24'] = ( $superbowl['ampm'] == 'pm' && $superbowl['hour-12'] != 12 ) ? $superbowl['hour-12']+12 : $superbowl['hour-12'];
											$superbowl['hour-24'] = ( $superbowl['ampm'] == 'am' && $superbowl['hour-12'] == 12 ) ? 0 : $superbowl['hour-24'];
											
											//Draft Date & Time
											$draft = array(
											    "minutes" => '00',
											    "hour-12" => 8,
											    "ampm" => 'pm',
											    "dayname" => "Thursday",
											    "day" => 8,
											    "monthname" => "May",
											    "month" => 8,
											    "year" => 2014,
											    "broadcast" => "ESPN and NFL Network"
											);
											$draft['hour-24'] = ( $draft['ampm'] == 'pm' && $draft['hour-12'] != 12 ) ? $draft['hour-12']+12 : $draft['hour-12'];
											$draft['hour-24'] = ( $draft['ampm'] == 'am' && $draft['hour-12'] == 12 ) ? 0 : $draft['hour-24'];
											
											//Season Opener Date & Time
											$season = array(
											    "minutes" => 35,
											    "hour-12" => 8,
											    "ampm" => 'pm',
											    "dayname" => "Sunday",
											    "day" => 3,
											    "monthname" => "September",
											    "month" => 7,
											    "year" => 2014,
											    "broadcast" => "NBC"
											);
											$season['hour-24'] = ( $season['ampm'] == 'pm' && $season['hour-12'] != 12 ) ? $season['hour-12']+12 : $season['hour-12'];
											$season['hour-24'] = ( $season['ampm'] == 'am' && $season['hour-12'] == 12 ) ? 0 : $season['hour-24'];
										?>
																											
										<?php if (1==1) : ?>
											<div class="playoffs">
												<p><strong>Countdown to the <?php echo $playoffs['year']-1; ?> NFL Post-Season</strong></p>
												<span class="nfldate"><?php echo $playoffs['dayname']; ?>, <?php echo $playoffs['monthname']; ?> <?php echo $playoffs['day']; ?>, <?php echo $playoffs['year']; ?> @ <?php echo $playoffs['hour-12']; ?>:<?php echo $playoffs['minutes']; ?><?php echo $playoffs['ampm']; ?></span>
												<?php echo do_shortcode('[countdown title="Countdown" event="Until Post-Season" date="' . $playoffs['day'] . ' ' . $playoffs['monthname'] . ' ' . $playoffs['year'] . '" hour="' . $playoffs['hour-24'] . '" minutes="' . $playoffs['minutes'] . '" seconds="00" format="DHMS"]'); ?>
											</div>
										<?php endif; ?>
											
										<?php if (1==2) : ?>
											<div class="superbowl">
												<p><strong>Countdown to Super Bowl <?php echo $superbowl['year']-1966; ?></strong></p>
												<span class="nfldate"><?php echo $superbowl['dayname']; ?>, <?php echo $superbowl['monthname']; ?> <?php echo $superbowl['day']; ?>, <?php echo $superbowl['year']; ?> @ <?php echo $superbowl['hour-12']; ?>:<?php echo $superbowl['minutes']; ?><?php echo $superbowl['ampm']; ?> (<?php echo $superbowl['broadcast']; ?>)</span>
												<?php echo do_shortcode('[countdown title="Countdown" event="Until Super Bowl" date="' . $superbowl['day'] . ' ' . $superbowl['monthname'] . ' ' . $superbowl['year'] . '" hour="' . $superbowl['hour-24'] . '" minutes="' . $superbowl['minutes'] . '" seconds="00" format="DHMS"]'); ?>
											</div>
										<?php endif; ?>
										
										<?php if (1==2) : ?>
											<div class="draft">
												<p><strong>Countdown to the <?php echo $draft['year']; ?> NFL Draft</strong></p>
												<span class="nfldate"><?php echo $draft['dayname']; ?>, <?php echo $draft['monthname']; ?> <?php echo $draft['day']; ?>, <?php echo $draft['year']; ?> @ <?php echo $draft['hour-12']; ?>:<?php echo $draft['minutes']; ?><?php echo $draft['ampm']; ?> (<?php echo $draft['broadcast']; ?>)</span>
												<?php echo do_shortcode('[countdown title="Countdown" event="Until Draft" date="' . $draft['day'] . ' ' . $draft['monthname'] . ' ' . $draft['year'] . '" hour="' . $draft['hour-24'] . '" minutes="' . $draft['minutes'] . '" seconds="00" format="DHMS"]'); ?>
											</div>
										<?php endif; ?>
											
										<?php if (1==2) : ?>
											<div class="season">
												<p><strong>Countdown to the <?php echo $season['year']; ?> NFL Season Kickoff</strong></p>
												<span class="nfldate"><?php echo $season['dayname']; ?>, <?php echo $season['monthname']; ?> <?php echo $season['day']; ?>, <?php echo $season['year']; ?> @ <?php echo $season['hour-12']; ?>:<?php echo $season['minutes']; ?><?php echo $season['ampm']; ?> (<?php echo $season['broadcast']; ?>)</span>
												<?php echo do_shortcode('[countdown title="Countdown" event="Until Season Opener" date="' . $season['day'] . ' ' . $season['monthname'] . ' ' . $season['year'] . '" hour="' . $season['hour-24'] . '" minutes="' . $season['minutes'] . '" seconds="00" format="DHMS"]'); ?>
											</div>
										<?php endif; ?>
										<hr/>
										<br/>
			                        	<div class="container">
			                        		<div class="row">
			                        			<div class="seven columns">
			                        				<?php
							                        	// open this directory 
														$myDirectoryA = opendir($_SERVER['DOCUMENT_ROOT'] . "/nfl-schedules/eagles");
														// get each entry
														while($entryNameA = readdir($myDirectoryA)) {
														    $dirArrayA[] = $entryNameA;
														}
														// close directory
														closedir($myDirectoryA);
														//  count elements in array
														$indexCountA = count($dirArrayA);
														// sort 'em
														sort($dirArrayA);
														// print 'em
														
														
														print("<TABLE class='nfltable' border=0 cellpadding=0 cellspacing=0 class=whitelinks>\n");
														
																												
														
														//print("<TR><TD><img class='nflthumb notbig' src='/nfl-schedules/eagles/" . $dirArrayA[$indexCountA-1] . "'></TD></TR>\n");
														print("<TR><TD><img class='nflthumb notbig' src='http://gearside.com/wp-content/themes/gearside2014/images/eaglessched.png'></TD></TR>\n");
														
														
														
														
														print("<TR><TD><h2 class='nfltitle'>Philadelphia Eagles Regular Season</h2></TD></TR>\n");
														print("<TR><TD><span class='nfldesc'>Check back every Tuesday evening during the regular season for the latest results.</span></TD></TR>\n");
														// loop through the array of files and print them all
														for($indexA=0; $indexA < $indexCountA; $indexA++) {
														        if (substr("$dirArrayA[$indexA]", 0, 1) != "."){ // don't list hidden files
														        print("<TR><TD class='schedcell'><a href=\"/nfl-schedules/eagles/$dirArrayA[$indexA]\" target='_blank'>$dirArrayA[$indexA]</a></td>");
														        print("</TR>\n");
														    }
														}
														print("</TABLE>\n");
						                        	?>
			                        			</div><!--/columns-->
			                        			<div class="seven columns push_two">
						                        	<?php
							                        	// open this directory 
														$myDirectoryB = opendir($_SERVER['DOCUMENT_ROOT'] . "/nfl-schedules/post-season");
														// get each entry
														while($entryNameB = readdir($myDirectoryB)) {
														    $dirArrayB[] = $entryNameB;
														}
														// close directory
														closedir($myDirectoryB);
														//  count elements in array
														$indexCountB = count($dirArrayB);
														// sort 'em
														sort($dirArrayB);
														// print 'em
														print("<TABLE class='nfltable' border=0 cellpadding=0 cellspacing=0 class=whitelinks>\n");
														
																											
														//print("<TR><TD><img class='nflthumb notbig' src='/nfl-schedules/post-season/" . $dirArrayB[$indexCountB-1] . "'></TD></TR>\n");
														print("<TR><TD><img class='nflthumb notbig' src='http://gearside.com/wp-content/themes/gearside2014/images/nflplayoffs.png'	></TD></TR>\n");
														
														
														print("<TR><TD><h2 class='nfltitle'>2014 NFL Playoff Bracket</h2></TD></TR>\n");
														print("<TR><TD><span class='nfldesc'>You won't find a cleaner bracket with this much information <strong>anywhere else</strong> online; incuding ESPN, Fox, and CBS Sports!</span></TD></TR>\n");
														// loop through the array of files and print them all
														for($indexB=0; $indexB < $indexCountB; $indexB++) {
														        if (substr("$dirArrayB[$indexB]", 0, 1) != "."){ // don't list hidden files
														        print("<TR><TD class='schedcell'><a href=\"/nfl-schedules/post-season/$dirArrayB[$indexB]\" target='_blank'>$dirArrayB[$indexB]</a></td>");
														        print("</TR>\n");
														    }
														}
														print("</TABLE>\n");
						                        	?>
			                        			</div><!--/columns-->
			                        		</div><!--/row-->
			                        		<div class="row">
			                        			<div class="sixteen columns">
			                        				<p>For previous seasons, <strong><a class="prevseasons" href="http://gearside.com/nfl-schedules/previous_seasons" target="_blank">click here</a></strong>.</p>
			                        			</div><!--/columns-->
			                        		</div><!--/row-->
			                        	</div><!--/container-->
										
										<?php if ( current_user_can('manage_options') ) : ?>
											<div class="container entry-manage">
												<div class="row">
													<hr/>
													<?php nebula_manage('edit'); ?> <?php nebula_manage('modified'); ?>
													<hr/>
												</div>
											</div>
										<?php else : ?>
											<hr/>
										<?php endif; ?>
										
									</div><!-- .entry-content -->
								</article><!-- #post-## -->
							<?php endwhile; ?>
							
							
							
							
							
							
							
							
							
							
							
							
							
							
							
							
							
							
							
							
						</div><!--/columns-->
					</div><!--/row-->
										
				</div><!--/contentbg-->
				<div class="nebulashadow floating"></div>
			</div><!--/container-->
		</section><!--/colgrid-->
		
	</div><!--/columns-->
	<div class="three columns">
		<?php get_sidebar(); ?>
	</div><!--/columns-->
</div><!--/row-->

<?php get_footer(); ?>