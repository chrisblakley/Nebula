<style>
div.cssbs {position: relative; display: table; height: 150px; min-width: 300px; border: 2px solid #222;}
	div.cssbs:after {width: 100%; height: 100%; line-height: 28px; color: #fff; text-align: center; font-family: 'FontAwesome', 'Open Sans', sans-serif; font-size: 16px; white-space: pre; display: table-cell; vertical-align: middle;}
	div.cssbs:before {content: ''; position: absolute; width: 100%; height: 100%; background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0.2));}

	.windows.chrome div.cssbs {background: #4884b8;} .windows.chrome div.cssbs:after {content: '\f17a \00A0 Windows \A \f268 \00A0 Chrome';}
	.mac.chrome div.cssbs {background: #4884b8;} .mac.chrome div.cssbs:after {content: '\f179 \00A0 Mac \A \f179 \f268 Chrome';}
	.linux.chrome div.cssbs {background: #4884b8;} .linux.chrome div.cssbs:after {content: '\f17c \00A0 Linux \A \f268 \00A0 Chrome';}

	.windows.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .windows.firefox div.cssbs:after {content: '\f17a \00A0 Windows \A \f269 \00A0 Firefox';}
	.mac.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .mac.firefox div.cssbs:after {content: '\f179 \00A0 Mac \A \f269 \00A0 Firefox';}
	.linux.firefox div.cssbs {background: #dc5d27; border: 2px solid #b31b27;} .linux.firefox div.cssbs:after {content: '\f17c \00A0 Linux \A \f269 \00A0 Firefox';}

	.mac.safari div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .mac.safari div.cssbs:after {content: '\f179 \00A0 Mac \A \f267 \00A0 Safari';}
	.windows.safari div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .windows.safari div.cssbs:after {content: '\f17a \00A0 Windows \A \f267 \00A0 Safari';}

	.opera div.cssbs {background: #e53141; border: 2px solid #9b1624;} .opera div.cssbs:after {content: '\f26a \00A0 Opera';}

	.ie div.cssbs {background: #2ebaee;} .ie div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer';}
	.ie5 div.cssbs {background: #3ea3e2;} .ie5 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 5';}
	.ie6 div.cssbs {background: #3696e9; border: 2px solid #72f0fc;} .ie6 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 6';}
	.ie7 div.cssbs {background: #1374ae; border: 2px solid #f4b619;} .ie7 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 7';}
	.ie8 div.cssbs {background: #1374ae; border: 2px solid #f4b619;} .ie8 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 8';}
	.ie9 div.cssbs {background: #3aa8de; border: 2px solid #fbd21e;} .ie9 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 9';}
	.ie10 div.cssbs {background: #2b6bec;} .ie10 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 10';}
	.ie11 div.cssbs {background: #2ebaee;} .ie11 div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Internet Explorer 11';}

	.edge div.cssbs {background: #2ebaee;} .edge div.cssbs:after {content: '\f17a \00A0 Windows \A \f26b \00A0 Microsoft Edge';}

	.android div.cssbs {background: #a5c93a; border: 2px solid #a5c93a;} .android div.cssbs:after {content: '\f17b \00A0 Android';}
	.android.chrome div.cssbs {background: #4884b8;} .android.chrome div.cssbs:after {content: '\f17b \00A0 Android \A \f268 \00A0 Chrome';}

	.ios div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .ios div.cssbs:after {content: '\f179 \00A0 iOS';}
	.iphone div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .iphone div.cssbs:after {content: '\f179 \00A0 iPhone';}
	.iphone.chrome div.cssbs {background: #4884b8;} .iphone.chrome div.cssbs:after {content: '\f179 \00A0 iPhone \A \f179 \f268 Chrome';}
	.ipad div.cssbs {background: #42aeda; border: 2px solid #a1a1a1;} .ipad div.cssbs:after {content: '\f179 \00A0 iPad';}
	.ipad.chrome div.cssbs {background: #4884b8;} .ipad.chrome div.cssbs:after {content: '\f179 \00A0 iPad \A \f179 \f268 Chrome';}
</style>

<div class="row">
	<div class="col-md-5">
		<div class="cssbs"></div>
	</div><!--/col-->
</div><!--/row-->

<br/><br/><hr/><br/><br/>

<div id="container" style="min-width: 310px; height: 400px; margin: 25px auto;"></div>

<?php
	WP_Filesystem();
	global $wp_filesystem;

	$browsers = json_decode(gzdecode($wp_filesystem->get_contents('https://analytics.usa.gov/data/live/browsers.json')));
	$ie = json_decode(gzdecode($wp_filesystem->get_contents('https://analytics.usa.gov/data/live/ie.json')));
	$devices = json_decode(gzdecode($wp_filesystem->get_contents('https://analytics.usa.gov/data/live/devices.json')));
	$operating_systems = json_decode(gzdecode($wp_filesystem->get_contents('https://analytics.usa.gov/data/live/os.json')));
	$windows = json_decode(gzdecode($wp_filesystem->get_contents('https://analytics.usa.gov/data/live/windows.json')));

	//Create market_share array structure
	$market_share = array(
		'totals' => array(
			'browsers' => array(
				'all' => $browsers->totals->visits,
				'ie' => $ie->totals->visits,
			),
			'devices' => $devices->totals->visits,
			'os' => array(
				'all' => $operating_systems->totals->visits,
				'windows' => $operating_systems->totals->visits,
			),
		),
		'browsers' => array(),
		'devices' => array(),
		'os' => array(),
	);

	//Add browsers to $market_share array
	if ( !empty($market_share['totals']['browsers']['all']) ){
		foreach ( $browsers->totals->browser as $browser => $visits ){
			$market_share['browsers'][$browser] = array(
				'total' => $visits,
				'percent' => round(($visits/$market_share['totals']['browsers']['all'])*100, 2)
			);
		}
	}

	//Add IE to browser $market_share array
	if ( !empty($market_share['totals']['browsers']['ie']) ){
		foreach ( $ie->totals->ie_version as $ie_version => $visits ){
			$market_share['browsers']['Internet Explorer']['IE ' . str_replace('.0', '', $ie_version)] = array(
				'total' => $visits,
				'ie percent' => round(($visits/$market_share['totals']['browsers']['ie'])*100, 2),
				'percent' => round(($visits/$market_share['totals']['browsers']['all'])*100, 2),
			);
		}
	}

	//Add devices to $market_share array
	if ( !empty($market_share['totals']['devices']) ){
		foreach ( $devices->totals->devices as $device => $visits ){
			$market_share['devices'][$device] = array(
				'total' => $visits,
				'percent' => round(($visits/$market_share['totals']['devices'])*100, 2)
			);
		}
	}

	//Add OS to $market_share array
	if ( !empty($market_share['totals']['os']['all']) ){
		foreach ( $operating_systems->totals->os as $os => $visits ){
			$market_share['os'][$os] = array(
				'total' => $visits,
				'percent' => round(($visits/$market_share['totals']['os']['all'])*100, 2)
			);
		}
	}

	//Add Windows to OS $market_share array
	if ( !empty($market_share['totals']['os']['windows']) ){
		foreach ( $windows->totals->os_version as $windows_version => $visits ){
			$market_share['os']['Windows']['Windows ' . $windows_version] = array(
				'total' => $visits,
				'windows percent' => round(($visits/$market_share['totals']['os']['windows'])*100, 2),
				'percent' => round(($visits/$market_share['totals']['os']['all'])*100, 2),
			);
		}
	}

	echo 'Market Share Data (Provided by analytics.usa.gov)';
	echo do_shortcode('[pre]' . json_encode($market_share, JSON_PRETTY_PRINT) . '[/pre]');
?>
<br/><br/><hr/><br/><br/>

<?php
	//Sort browsers by percent
?>

<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/data.js"></script>
<script src="http://code.highcharts.com/modules/drilldown.js"></script>
<script>
	jQuery('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Browser market shares'
        },
        subtitle: {
            text: 'Click the IE column to view versions.'
        },
        xAxis: {
            type: 'category'
        },
        yAxis: {
            title: {
                text: 'Market share (%)'
            }
        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f}%'
                }
            }
        },
        series: [{
            name: "Browser",
            colorByPoint: true,
            data: [
	            <?php foreach ( $market_share['browsers'] as $browser => $value ): ?>
					<?php if ( $value['percent'] < 0.8 ){ continue; } ?>
					{
						name: "<?php echo $browser; ?>",
						y: <?php echo $value['percent']; ?>,
						drilldown: "<?php echo $browser; ?>"
					},
		        <?php endforeach; ?>
            ]
        }],
        drilldown: {
            series: [{
                name: "Internet Explorer",
                id: "Internet Explorer",
                data: [
		            <?php foreach ( $market_share['browsers']['Internet Explorer'] as $version => $value ): ?>
						<?php if ( $version == 'total' || $version == 'percent' ){ continue; } ?>
						[
							"<?php echo $version; ?>",
							<?php echo $value['ie percent']; ?>
						],
			        <?php endforeach; ?>
	            ]
            }]
        }
    });
</script>

<br/><br/><hr/><br/><br/>