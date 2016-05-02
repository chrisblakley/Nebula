<div class="row">
	<div class="col-md-12">

		<?php
			$testdomain = 'http://www.pinckneyhugo.com';
			if ( nebula_is_available($testdomain) ) {
				echo '<a href="' . $testdomain . '" target="_blank">PinckneyHugo.com</a> is currently <strong style="color: green;">online</strong>.';
			} else {
				echo '<a href="' . $testdomain . '" target="_blank">PinckneyHugo.com</a> is currently <strong style="color: red;">offline</strong>.';
			}
		?>
		<br />

		<?php
			$testdomain = 'http://spacergif.org';
			if ( nebula_is_available($testdomain) ) {
				echo '<a href="' . $testdomain . '" target="_blank">SpacerGIF.org</a> is currently <strong style="color: green;">online</strong>.';
			} else {
				echo '<a href="' . $testdomain . '" target="_blank">SpacerGIF.org</a> is currently <strong style="color: red;">offline</strong>.';
			}
		?>
		<br />

		<?php
			$testdomain = 'reddit.com/r/syracuse';
			if ( nebula_is_available($testdomain) ) {
				echo '<a href="http://' . $testdomain . '" target="_blank">/r/Syracuse</a> is currently <strong style="color: green;">online</strong>.';
			} else {
				echo '<a href="http://' . $testdomain . '" target="_blank">/r/Syracuse</a> is currently <strong style="color: red;">offline</strong>.';
			}
		?>
		<br /><br />

		<?php
			$characters = 'abcdefghijklmnopqrstuvwxyz';
		    $wordLength = rand(3, 8);
		    $randomString = '';
		    for ( $i=0; $i<$wordLength; $i++ ){
		        $randomString .= $characters[rand(0, strlen($characters)-1)];
		    }
		    $testdomain = $randomString . '.com';

			if ( nebula_is_available($testdomain) ) {
				echo '<a href="http://' . $testdomain . '" target="_blank">' . $testdomain . '</a> (a randomly generated domain) <strong style="color: green;">exists</strong>!';
			} else {
				echo '<a href="http://' . $testdomain . '" target="_blank">' . $testdomain . '</a> (a randomly generated domain) <strong style="color: red;">does not exist</strong>.';
			}
		?>
		<br />

	</div><!--/col-->
</div><!--/row-->