<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 */
?>

<ul class="xoxo" style="position: relative;">
	
	
	<?php if ( !$GLOBALS["mobile_detect"]->isMobile() ) : ?>
		<li class="gacon">
			<div data-0="position: relative; top: !0px; text-align: right;" data-59-top="position: fixed; top: !50px;"> <?php //@TODO: Consider position: sticky if it gains more support. ?>
				<div class="googleresources">
					<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<!-- Gearside 2015 Sidebar -->
					<ins class="adsbygoogle"
					     style="display:inline-block;width:160px;height:600px;"
					     data-ad-client="ca-pub-3057391662144745"
					     data-ad-slot="1103346660"></ins>
					<script>
					(adsbygoogle = window.adsbygoogle || []).push({});
					</script>
				</div><!--/googleresources-->
				
				<a class="blockdetection hidden" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VALMBPW4PWH8S" target="_blank">
					<?php //@TODO: Replace this with a funny (or a series of funny) fake ads that click through to Paypal. ?>
					<p><i class="fa fa-times-circle"></i></p>
					<p>
						It seems your AdBlock software is up-to-date.<br/>
						<span class="blockcopy">
							This software check-up, along with the rest of my content, is provided for free.<br/><br/>
							Consider donating to support original content!
						</span>
					</p>
				</a><!--/blockdetection-->
			</div>
		</li>
	<?php endif; ?>
	
	
</ul>