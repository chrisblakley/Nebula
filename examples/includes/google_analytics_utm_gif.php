<style>
	.builder-form-heading {display: inline-block; margin: 0; font-size: 12px; font-weight: bold;}
	.inputhelp-description {display: none; margin: 0; padding: 0; font-size: 10px;}

	.required {color: red;}
	.requiredish {color: #666;}

	.generatingspinner {display: none; color: #0098d7; font-size: 12px; text-transform: uppercase; font-weight: bold;}

	.example-report {font-size: 12px;}


	.utm_campaign {background: #dedede; padding: 25px; margin-bottom: 25px; margin-top: 25px;}
</style>


<script>
	jQuery(document).on('ready', function(){

		jQuery('a.inputhelp').on('click', function(){
			jQuery(this).toggleClass('hover');
			jQuery(this).parents('li').find('.inputhelp-description').slideToggle();
			return false;
		});

		//Check required fields
		jQuery('.builderrequired').each(function(){
			if ( jQuery(this).val().trim() == '' ) {
				jQuery(this).parents('li').addClass('warning');
			} else {
				jQuery(this).parents('li').removeClass('warning');
			}
		});





	});
</script>

<div class="row">
	<div class="sixteen columns">
		<strong>Example __utm.gif Path:</strong><br/>
		<pre class="nebula-code HTML"><?php echo ga_UTM_gif(); ?></pre>

		<?php if ( 1==2 ): //For testing ?>
			<img src="<?php echo ga_UTM_gif(); ?>" />
		<?php endif; ?>
	</div>
</div>

<div class="row">
	<div class="sixteen columns">
		<h2>Google Analytics __utm.gif Generator</h2>
		<p>Note: Still in progress!!</p>

		<form>
			<ul style="list-style: none; padding-left: 0;">
				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> Domain<span class="required">*</span></span>
					<span><input type="text" id="utmwv" class="builderinput text input builderrequired" placeholder="<?php echo nebula_url_components('domain'); ?>"></span>
					<p class="inputhelp-description"><strong>(Required)</strong> This will generate a domain hash for this pixel.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmwv - Tracking Code Version<span class="required">*</span></span>
					<span><input type="text" id="utmwv" class="builderinput text input builderrequired" value="5.3.8" placeholder="5.3.8"></span>
					<p class="inputhelp-description"><strong>(Required)</strong> The tracking code version.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmac - Account Code<span class="required">*</span></span>
					<span><input type="text" id="utmac" class="builderinput text input builderrequired" placeholder="UA-XXXXXXX-X"></span>
					<p class="inputhelp-description"><strong>(Required)</strong> This property's tracking code.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmdt - Page Title<span class="required">*</span></span>
					<span><input type="text" id="utmdt" class="builderinput text input builderrequired" placeholder="<?php echo get_the_title(); ?>"></span>
					<p class="inputhelp-description"><strong>(Required)</strong> The title of the page.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmp - Path<span class="required">*</span></span>
					<span><input type="text" id="utmdt" class="builderinput text input builderrequired" placeholder="<?php echo nebula_url_components('filepath'); ?>"></span>
					<p class="inputhelp-description"><strong>(Required)</strong> The filepath of the page.</p>
				</li>

				<li class="field" style="text-align: center;">
					<div class="field btn primary medium">
						<input class="submit" type="submit" value="Generate" style="padding-left: 15px; padding-right: 15px;">
					</div>
				</li>

				<div class="utm_campaign">
					<h3><strong>utmcc - Cookie and Campaign Data</strong><span class="required">*</span></h3>
					<p>This <strong>required</strong> parameter is made up of the following sub-parameters.</p>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utma<span class="required">*</span></span>
						<p class="inputhelp-description"><strong>(Required)</strong> This parameter is automatically generated, but is comprised of integers separated by periods: "Domain Hash" . "Random ID" . "Time of First Visit" . "Time of Last Visit" . "Time of Current Visit" . "Session Counter"</p>
					</li>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmz</span>
						<p class="inputhelp-description"><strong>(Required if passing Campaign Parameters)</strong> Like utma, this automatically generated campaign parameter is composed of integers separated by periods: "Domain Hash" . "Time" . "Counter" . "Counter"</p>
					</li>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmcsr - Campaign Source<span class="requiredish">*</span></span>
						<span><input type="text" id="utmcsr" class="builderinput text input" placeholder="-"></span>
						<p class="inputhelp-description"><strong>(Required if passing Campaign Parameters)</strong> The campaign source.</p>
					</li>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmccn - Campaign Name<span class="requiredish">*</span></span>
						<span><input type="text" id="utmccn" class="builderinput text input" placeholder="-"></span>
						<p class="inputhelp-description"><strong>(Required if passing Campaign Parameters)</strong> The campaign name.</p>
					</li>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmcmd - Campaign Medium<span class="requiredish">*</span></span>
						<span><input type="text" id="utmcmd" class="builderinput text input" placeholder="-"></span>
						<p class="inputhelp-description"><strong>(Required if passing Campaign Parameters)</strong> The campaign medium.</p>
					</li>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmctr - Campaign Terms</span>
						<span><input type="text" id="utmcmd" class="builderinput text input" placeholder="-"></span>
						<p class="inputhelp-description">The campaign terms (for paid search).</p>
					</li>

					<li class="field">
						<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmcct - Campaign Content Description</span>
						<span><input type="text" id="utmcct" class="builderinput text input" placeholder="-"></span>
						<p class="inputhelp-description">The content description for this campaign.</p>
					</li>
				</div>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmhn - Hostname</span>
					<span><input type="text" id="utmhn" class="builderinput text input" placeholder="<?php echo nebula_url_components('hostname'); ?>"></span>
					<p class="inputhelp-description">The hostname for this account.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmn - Unique ID</span>
					<?php $generated_utmn = rand(pow(10, 10-1), pow(10, 10)-1); ?>
					<span><input type="text" id="utmn" class="builderinput text input" value="<?php echo $generated_utmn; ?>" placeholder="<?php echo $generated_utmn; ?>"></span>
					<p class="inputhelp-description">A random ID generated for each gif request to prevent caching of the image.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utms - Session Requests</span>
					<span><input type="text" id="utms" class="builderinput text input" placeholder="1"></span>
					<p class="inputhelp-description">Generally updates every time the gif is requested (max: 500).</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmul - Language</span>
					<span><input type="text" id="utmul" class="builderinput text input" placeholder="<?php echo str_replace('-', '_', get_bloginfo('language')); ?>"></span>
					<p class="inputhelp-description">Language encoding for the browser. Use "-" for none.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmje - Java Enabled</span>
					<span><input type="text" id="utmje" class="builderinput text input" placeholder="1"></span>
					<p class="inputhelp-description">Indicates if the browser supports Java.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmhid - Random Number</span>
					<?php $generated_utmhid = rand(pow(10, 10-1), pow(10, 10)-1); ?>
					<span><input type="text" id="utmhid" class="builderinput text input" value="<?php echo $generated_utmhid; ?>" placeholder="<?php echo $generated_utmhid; ?>"></span>
					<p class="inputhelp-description">A random number used to link the gif request with AdSense.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmr - Referral</span>
					<span><input type="text" id="utmr" class="builderinput text input" placeholder="<?php echo $_SERVER['HTTP_REFERER']; ?>"></span>
					<p class="inputhelp-description">The complete URL of the referrer.</p>
				</li>


				<li class="field">
					<span class="builder-form-heading"><a class="inputhelp" href="#" tabindex="-1"><i class="fa fa-question-circle"></i></a> utmu</span>
					<span><input type="text" id="utmu" class="builderinput text input" placeholder="q~" value="q~"></span>
					<p class="inputhelp-description">This parameter contains some internal state that helps improve ga.js</p>
				</li>

				<li class="field" style="text-align: center;">
					<div class="field btn primary medium">
						<input class="submit" type="submit" value="Generate" style="padding-left: 15px; padding-right: 15px;">
					</div>
				</li>
			</ul>
		</form>
	</div><!--/columns-->
</div><!--/row-->