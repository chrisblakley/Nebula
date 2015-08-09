<?php
	//This code does not work in this template- it would need to be moved to functions.php

	/*
		add_action('wp_ajax_nebula_whois_tester', 'nebula_whois_tester');
		add_action('wp_ajax_nopriv_nebula_whois_tester', 'nebula_whois_tester');
		function nebula_whois_tester() {

			if ( contains($_POST['data'][0]['domain'], array('http://', 'https://', '//')) ) {
				$domain_to_test = $_POST['data'][0]['domain'];
			} else {
				$domain_to_test = 'http://' . $_POST['data'][0]['domain'];
			}

			$whois = getwhois(nebula_url_components('sld', $domain_to_test), ltrim(nebula_url_components('tld', $domain_to_test), '.'));




			//Get Expiration Date
			if ( contains($whois, array('Registrar Registration Expiration Date: ')) ) {
				$domain_exp_detected = substr($whois, strpos($whois, "Registrar Registration Expiration Date: ")+40, 10);
			} elseif ( contains($whois, array('Registry Expiry Date: ')) ) {
				$domain_exp_detected = substr($whois, strpos($whois, "Registry Expiry Date: ")+22, 10);
			} else {
				$domain_exp_detected = '';
			}

			$domain_exp_unix = strtotime($domain_exp_detected);
			$domain_exp = date("F j, Y", $domain_exp_unix);
			$domain_exp_style = ( $domain_exp_unix < strtotime('+1 month') )? 'color: red; font-weight: bold;' : 'color: inherit;' ;
			$domain_exp_html = ( $domain_exp_unix > strtotime('March 27, 1986') )? ' <p style="' . $domain_exp_style . '">' . $domain_exp . '</p>' : '<p>Expiration Not Detected</p>';
			echo '<h3>Detected Domain Expiration</h3>' . $domain_exp_html;



			//Get Registrar URL
			if ( contains($whois, array('Registrar URL: ')) && contains($whois, array('Updated Date: ')) ) {
				$domain_registrar_url_start = strpos($whois, "Registrar URL: ")+15;
				$domain_registrar_url_stop = strpos($whois, "Updated Date: ")-$domain_registrar_url_start;
				$domain_registrar_url = substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop);
			} elseif ( contains($whois, array('Registrar URL: ')) && contains($whois, array('Update Date: ')) ) {
				$domain_registrar_url_start = strpos($whois, "Registrar URL: ")+15;
				$domain_registrar_url_stop = strpos($whois, "Update Date: ")-$domain_registrar_url_start;
				$domain_registrar_url = substr($whois, $domain_registrar_url_start, $domain_registrar_url_stop);
			}



			//Get Registrar Name
			$domain_registrar_start = '';
			$domain_registrar_stop = '';
			if ( contains($whois, array('Registrar: ')) && contains($whois, array('Sponsoring Registrar IANA ID:')) ) {
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Sponsoring Registrar IANA ID:")-$domain_registrar_start;
				$domain_registrar = substr($whois, $domain_registrar_start, $domain_registrar_stop);
			} elseif ( contains($whois, array('Registrar: ')) && contains($whois, array('Registrar IANA ID: ')) ) {
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Registrar IANA ID: ")-$domain_registrar_start;
				$domain_registrar = substr($whois, $domain_registrar_start, $domain_registrar_stop);
			} elseif ( contains($whois, array('Registrar: ')) && contains($whois, array('Registrar IANA ID: ')) ) {
				$domain_registrar_start = strpos($whois, "Registrar: ")+11;
				$domain_registrar_stop = strpos($whois, "Registrar IANA ID: ")-$domain_registrar_start;
				$domain_registrar = substr($whois, $domain_registrar_start, $domain_registrar_stop);
			} elseif ( contains($whois, array('Sponsoring Registrar:')) && contains($whois, array('Sponsoring Registrar IANA ID:')) ) {
				$domain_registrar_start = strpos($whois, "Sponsoring Registrar:")+21;
				$domain_registrar_stop = strpos($whois, "Sponsoring Registrar IANA ID:")-$domain_registrar_start;
				$domain_registrar = substr($whois, $domain_registrar_start, $domain_registrar_stop);
			}



			if ( $domain_registrar_url && strlen($domain_registrar_url) < 50 ) {
				$domain_registrar_html = ( $domain_registrar && strlen($domain_registrar) < 50 )? '<p><a href="//' . $domain_registrar_url . '" target="_blank">' . $domain_registrar . '</a></p>': '<p>Registrar Not Detected</p>';
			} else {
				$domain_registrar_html = ( $domain_registrar && strlen($domain_registrar) < 50 )? '<p>' . $domain_registrar . '</p>': '<p>Registrar Not Detected</p>';
			}

			echo '<br/><h3>Detected Domain Registrar</h3>' . $domain_registrar_html;


			//Get Reseller Name
			$domain_reseller = '';
			if ( contains($whois, array('Reseller: ')) && contains($whois, array('Domain Status: ')) ) { //@TODO "Nebula" 0: Need to detect if there is another "Reseller: " after the first one.
				$domain_reseller_start = strpos($whois, "Reseller: ")+10;
				$domain_reseller_stop = strpos($whois, "Domain Status: ")-$domain_reseller_start; //@TODO "Nebula" 0: Need to detect if there is another "Reseller: " after the first one.
				$domain_reseller = substr($whois, $domain_reseller_start, $domain_reseller_stop);
			} elseif ( contains($whois, array('Reseller: ')) && contains($whois, array('Domain Status: ')) ) {
				$domain_reseller_start = strpos($whois, "Reseller: ")+10;
				$domain_reseller_stop = strpos($whois, "Domain Status: ")-$domain_reseller_start;
				$domain_reseller = substr($whois, $domain_reseller_start, $domain_reseller_stop);
			}

			if ( $domain_reseller && $domain_reseller != '' ) {
				$domain_reseller_html = ( $domain_reseller && strlen($domain_reseller) < 30 )? '<p>' . $domain_reseller . '</p>': '<p>Reseller Not Detected</p>';
				echo '<br/><h3>Detected Domain Reseller</h3>' . $domain_reseller_html;
			}



			echo '<br/><h3>Full WHOIS Detection</h3><p>' . str_replace("\n", '<br/>', $whois) . '</p>';

			exit();
		}
	*/

?>

<style>
    .whoisiframe a {position: relative; display: block; width: 100%; height: 340px; padding: 0; overflow: hidden;}
    	.whoisiframe a:after {content: 'This website may be blocking iframes from an external origin.'; display: block; position: absolute; width: 100%; height: auto; top: 150px; color: #aaa; text-align: center; z-index: 1;}
    .whoisiframe iframe {position: relative; width: 1200px; height: 600px; z-index: 10; border: 1px solid #aaa;}
    .whoisiframe iframe {
        -ms-zoom: 0.75;
        -moz-transform: scale(0.53);
        -moz-transform-origin: 0 0;
        -o-transform: scale(0.53);
        -o-transform-origin: 0 0;
        -webkit-transform: scale(0.53);
        -webkit-transform-origin: 0 0;
    }
</style>

<script>
	jQuery(document).on('submit', '#whoistester', function(e){
		if ( jQuery("#domain").val().trim() == '' ) {
			jQuery("#domain").val('gearside.com');
			var domainLookup = 'https://gearside.com?default';
		} else {
			var domainLookup = jQuery("#domain").val().trim();
		}

		ga('send', 'event', 'WHOIS Tester', domainLookup);

		if ( domainLookup.indexOf('//') < 1 ) {
			domainLookup = 'https://' + domainLookup;
		}

		jQuery('#theactualiframe').remove();
		jQuery('i.fa-spinner').removeClass('hidden');

		if ( jQuery('#lookuppreview').is(':checked') ) {
			jQuery('.whoisiframe a').attr('href', domainLookup);
			jQuery('.whoisiframe').removeClass('hidden');
			jQuery('<iframe />', {
			    name: 'theactualiframe',
			    id: 'theactualiframe',
			    src: domainLookup
			}).appendTo('.whoisiframe a');
		} else {
			jQuery('.whoisiframe a').attr('href', '');
			jQuery('.whoisiframe').addClass('hidden').find('iframe').attr('src', '');
		}

		var domainData = [{
			'domain': domainLookup
		}];
		jQuery.ajax({
			type: "POST",
			url: bloginfo["admin_ajax"],
			data: {
				action: 'nebula_whois_tester',
				data: domainData,
			},
			success: function(response){
				jQuery('#testerresults').html(response);
				jQuery('i.fa-spinner').addClass('hidden');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				jQuery('#testerresults').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
				ga('send', 'event', 'Error', 'WHOIS Tester', 'AJAX Error');
				jQuery('i.fa-spinner').addClass('hidden');
			},
			timeout: 60000
		});

		e.preventDefault();
		return false;
	});
</script>

<div class="row">
	<div class="sixteen columns">
		<div class="whoisiframe hidden" style="margin-top: 30px;">
			<a href="#" target="_blank" style="display: block;"><!-- Iframe goes here dynamically. --></a>
			<div class="nebulashadow bulging" style="margin-top: -22px;"></div>
		</div>
	</div><!--/columns-->
</div><!--/row-->

<div class="row">
	<div class="sixteen columns">

		<br/>
		<h2>WHOIS Tester</h2>
		<p>Enter a domain to see what WHOIS data is detected.</p>

		<form id="whoistester">
			<div class="field">
				<input id="domain" class="input" type="text" placeholder="<?php echo nebula_url_components('domain'); ?>" />
			</div>
			<div>
				<label style="font-size: 12px;">
					<input id="lookuppreview" type="checkbox" checked="checked"> Preview on lookup?
				</label>
			</div>
			<div class="field btn primary medium">
				<input class="submit" type="submit" value="Test" style="padding-left: 15px; padding-right: 15px;"/>
			</div><i class="fa fa-spinner fa-spin hidden" style="font-size: 18px; margin-left: 10px; display: inline-block;"></i>
		</form>

		<p id="testerresults"></p>

	</div><!--/columns-->
</div><!--/row-->