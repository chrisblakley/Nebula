<?php
	//This code does not work in this template- it would need to be moved to functions.php

	/*
		add_action('wp_ajax_nebula_url_components_tester', 'nebula_url_components_tester');
		add_action('wp_ajax_nopriv_nebula_url_components_tester', 'nebula_url_components_tester');
		function nebula_url_components_tester() {
			echo '
				<strong>"all"</strong> <em>(default)</em>: ' . nebula_url_components("all", $_POST['data'][0]['url']) . '<br/>
				<strong>"protocol"</strong>: ' . nebula_url_components("protocol", $_POST['data'][0]['url']) . '<br/>
				<strong>"scheme"</strong>: ' . nebula_url_components("scheme", $_POST['data'][0]['url']) . '<br/>
				<strong>"www"</strong>: ' . nebula_url_components("www", $_POST['data'][0]['url']) . '<br/>
				<strong>"subdomain"</strong>: ' . nebula_url_components("subdomain", $_POST['data'][0]['url']) . '<br/>
				<strong>"domain"</strong>: ' . nebula_url_components("domain", $_POST['data'][0]['url']) . '<br/>
				<strong>"sld"</strong>: ' . nebula_url_components("sld", $_POST['data'][0]['url']) . '<br/>
				<strong>"tld"</strong>: ' . nebula_url_components("tld", $_POST['data'][0]['url']) . '<br/>
				<strong>"host"</strong>: ' . nebula_url_components("host", $_POST['data'][0]['url']) . '<br/>
				<strong>"filepath"</strong>: ' . nebula_url_components("filepath", $_POST['data'][0]['url']) . '<br/>
				<strong>"path"</strong>: ' . nebula_url_components("path", $_POST['data'][0]['url']) . '<br/>
				<strong>"file"</strong>: ' . nebula_url_components("file", $_POST['data'][0]['url']) . '<br/>
				<strong>"query"</strong>: ' . nebula_url_components("query", $_POST['data'][0]['url']) . '<br/>
			';
			exit();
		}
	*/

?>

<script>
	jQuery(document).on('submit', '#urltester', function(e){
		if ( jQuery("#urlstring").val().trim() != '' ) {
			ga('send', 'event', 'Nebula URL Components Test', jQuery("#urlstring").val().trim());
			
			jQuery('i.fa-spinner').removeClass('hidden');
			
			var urlData = [{
				'url': jQuery("#urlstring").val()
			}];
			jQuery.ajax({
				type: "POST",
				url: bloginfo["admin_ajax"],
				data: {
					action: 'nebula_url_components_tester',
					data: urlData,
				},
				success: function(response){
					jQuery('#testerresults').html(response);
					jQuery('i.fa-spinner').addClass('hidden');
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					jQuery('#testerresults').text('Error: ' + MLHttpRequest + ', ' + textStatus + ', ' + errorThrown);
					ga('send', 'event', 'Error', 'Nebula URL Components Tester', 'AJAX Error');
					jQuery('i.fa-spinner').addClass('hidden');
				},
				timeout: 60000
			});
		}

		e.preventDefault();
		return false;
	});
</script>


<div class="row">
	<div class="sixteen columns">

		<br/>
		<h2>Nebula Requested URL</h2>
		<p><?php echo nebula_requested_url(); ?></p>


		<br/>
		<h2>Detected URL</h2>
		<p>
			<strong>"all"</strong> <em>(default)</em>: <?php echo nebula_url_components('all'); ?><br/>
			<strong>"protocol"</strong>: <?php echo nebula_url_components('protocol'); ?><br/>
			<strong>"scheme"</strong>: <?php echo nebula_url_components('scheme'); ?><br/>
			<strong>"host"</strong>: <?php echo nebula_url_components('host'); ?><br/>
			<strong>"www"</strong>: <?php echo nebula_url_components('www'); ?><br/>
			<strong>"subdomain"</strong>: <?php echo nebula_url_components('subdomain'); ?><br/>
			<strong>"domain"</strong>: <?php echo nebula_url_components('domain'); ?><br/>
			<strong>"sld"</strong>: <?php echo nebula_url_components('sld'); ?><br/>
			<strong>"tld"</strong>: <?php echo nebula_url_components('tld'); ?><br/>
			<strong>"filepath"</strong>: <?php echo nebula_url_components('filepath'); ?><br/>
			<strong>"path"</strong>: <?php echo nebula_url_components('path'); ?><br/>
			<strong>"file"</strong>: <?php echo nebula_url_components('file'); ?><br/>
			<strong>"query"</strong>: <?php echo nebula_url_components('query'); ?><br/>
		</p>


		<br/>
		<h2>Passed URL</h2>
		<p>
			<strong>https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something</strong><br/>
			<strong>"all"</strong> <em>(default)</em>: <?php echo nebula_url_components('all', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"protocol"</strong>: <?php echo nebula_url_components('protocol', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"scheme"</strong>: <?php echo nebula_url_components('scheme', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"host"</strong>: <?php echo nebula_url_components('host', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"www"</strong>: <?php echo nebula_url_components('www', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"subdomain"</strong>: <?php echo nebula_url_components('subdomain', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"domain"</strong>: <?php echo nebula_url_components('domain', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"sld"</strong>: <?php echo nebula_url_components('sld', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"tld"</strong>: <?php echo nebula_url_components('tld', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"filepath"</strong>: <?php echo nebula_url_components('filepath', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"path"</strong>: <?php echo nebula_url_components('path', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"file"</strong>: <?php echo nebula_url_components('file', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
			<strong>"query"</strong>: <?php echo nebula_url_components('query', 'https://something.gearside.co.uk/nebula/documentation/custom-functionality/nebula-url-components/filename.php?query=something'); ?><br/>
		</p>


		<br/>
		<h2>URL Tester</h2>
		<p>Enter a URL to see what <code>nebula_url_components()</code> returns.</p>

		<form id="urltester">
			<div class="field">
				<input id="urlstring" class="input" type="text" placeholder="Enter any URL here!" />
			</div>
			<div class="field btn primary medium">
				<input class="submit" type="submit" value="Test" style="padding-left: 15px; padding-right: 15px;"/>
			</div><i class="fa fa-spinner fa-spin hidden" style="font-size: 18px; margin-left: 10px; display: inline-block;"></i>
		</form>

		<p id="testerresults"></p>

	</div><!--/columns-->
</div><!--/row-->