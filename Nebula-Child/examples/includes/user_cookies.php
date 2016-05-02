<?php
	/*
		*** This example is currently just in the brainstorming stage. Even after it is completed, this likely will not make it into Nebula core- just live as an example. ***

		Consider using a $_SESSION instead of cookies.
		Encode or hash the data.

		Consider using WordPress sessions instead of cookies too.

		Overall user cookie ideas:
			- Make it an object with all known information
				- Last browser used (or an array of browsers used)
				- Last device (or an array of devices)
				- IP address(es)
				- Geolocation(s)
				- Referrer(s)
				- Email address
				- Name
				- User ID (either passed or generated)
				- Amount of times seen

			- Need a way to combine users (maybe just manual editing the DB?) with discovering duplicates (without breaking the cookie functionality)
	*/


	/* @TODO "Nebula" 0:
		- Check previous cookie for User Object
		- Check query string(s) for User ID
		- Check/Update cookie object with known server-side info

		- Store in DB example
	*/

?>

<!-- PHP Method Output -->
<div class="row">
	<div class="col-md-12">

	</div><!--/col-->
</div><!--/row-->







<script>
	jQuery(document).on('ready', function(){

		//This example should be JS only (no PHP) and not use any Nebula functions (so it is modular)
		/* @TODO "Nebula" 0:
			- Check previous cookie for User Object
			- Check query string(s) for User ID
			- If both query string user ID -and- cookie user ID
				- If they match: Returning user
				- If they do not match: Note as updating user, update user ID cookie

			- Else if only query string (no previous cookie)
				- Create cookie with User ID

			- Else if only cookie (no query string)
				- Returning user

			- Send GA event example
			- Send Custom dimension of VIP (or whatever) example
			- Store in DB example (AJAX?)
		*/

	});
</script>

<!-- JS Method Output -->
<div class="row">
	<div class="col-md-12">

	</div><!--/col-->
</div><!--/row-->