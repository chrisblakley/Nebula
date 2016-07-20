<script>
	jQuery(document).ready(function() {
		jQuery.getScript('//cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js').done(function(){
			chosenExampleOptions();
		}).fail(function(){
			//do nothing
		});
		nebulaLoadCSS('//cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css');

		jQuery('.submitteams').on('click', function(){
			var favTeam = jQuery('.favteam').val();
			var hateTeams = jQuery('.hateteams').val();

			var i;
			for (i = 0; i < hateTeams.length; ++i) {
				if ( hateTeams[i] == 'Philadelphia Eagles') {
					if ( favTeam.length > 0 && favTeam != 'Philadelphia Eagles' ) {
						hateTeams[i] = favTeam;
						ga('send', 'event', 'NFL Teams', 'Swapped ' + favTeam + ' with the Eagles.');
					} else {
						hateTeams[i] = '';
						ga('send', 'event', 'NFL Teams', 'No favorite team, so added Eagles.');
					}

					//This shows how to re-trigger Chosen elements using .trigger()
					jQuery('.favteam').val('Philadelphia Eagles').trigger('chosen:updated');
					jQuery('.hateteams').val(hateTeams).trigger('chosen:updated');

					lockForm();
					return false;
				}
			}

			ga('send', 'event', 'NFL Teams', 'Favorite: ' + favTeam, 'Hated: ' + hateTeams.join(', '));
			nebulaConversion('form', 'Example Chosen Form (NFL Teams)');
			nv('append', {
				'favorite_nfl_team': favTeam,
				'hated_nfl_teams': hateTeams.join(', '),
			});
			lockForm();

			return false;
		});

		jQuery('.nflteams').submit(function(){
			return false;
		});

	});

	function chosenExampleOptions(){
		jQuery('.chosen-example').chosen({
			'disable_search_threshold': 5,
			'search_contains': true,
			'no_results_text': "No results found.",
			'allow_single_deselect': true,
			'width': "100%"
		});
	}

	function lockForm(){
		jQuery('.favteam').attr('disabled', 'disabled');
		jQuery('.hateteams').attr('disabled', 'disabled');
		jQuery('#nflteams').css({'opacity': '0.4', 'pointer-events': 'none'});
		jQuery('.submitteams').css({'pointer-events': 'none'}).attr('disabled', 'disabled');
		jQuery('.submitsuccess').fadeIn();
	}
</script>

<div id="nflteams">
	<div class="row">
		<div class="col-md-6">
			<h4>Your favorite NFL team</h4>
			<div class="form-group">
				<select class="form-control chosen-example favteam" data-placeholder="Your favorite NFL team">
					<option value=""></option>
					<optgroup label="NFC East">
						<option>Dallas Cowboys</option>
						<option>New York Giants</option>
						<option>Philadelphia Eagles</option>
						<option>Washington Redskins</option>
					</optgroup>
					<optgroup label="NFC North">
						<option>Chicago Bears</option>
						<option>Detroit Lions</option>
						<option>Green Bay Packers</option>
						<option>Minnesota Vikings</option>
					</optgroup>
					<optgroup label="NFC South">
						<option>Atlanta Falcons</option>
						<option>Carolina Panthers</option>
						<option>New Orleans Saints</option>
						<option>Tampa Bay Buccaneers</option>
					</optgroup>
					<optgroup label="NFC West">
						<option>Arizona Cardinals</option>
						<option>St. Louis Rams</option>
						<option>San Francisco 49ers</option>
						<option>Seattle Seahawks</option>
					</optgroup>
					<optgroup label="AFC East">
						<option>Buffalo Bills</option>
						<option>Miami Dolphins</option>
						<option>New England Patriots</option>
						<option>New York Jets</option>
					</optgroup>
					<optgroup label="AFC North">
						<option>Baltimore Ravens</option>
						<option>Cincinnati Bengals</option>
						<option>Cleveland Browns</option>
						<option>Pittsburgh Steelers</option>
					</optgroup>
					<optgroup label="AFC South">
						<option>Houston Texans</option>
						<option>Indianapolis Colts</option>
						<option>Jacksonville Jaguars</option>
						<option>Tennessee Titans</option>
					</optgroup>
					<optgroup label="AFC West">
						<option>Denver Broncos</option>
						<option>Kansas City Chiefs</option>
						<option>Oakland Raiders</option>
						<option>San Diego Chargers</option>
					</optgroup>
				</select>
			</div>
		</div><!--/col-->
		<div class="col-md-6">
			<h4>Your most hated NFL teams</h4>
			<div class="form-group">
				<select class="form-control chosen-example hateteams" data-placeholder="Your most hated NFL teams" multiple>
					<option value=""></option>
					<optgroup label="NFC East">
						<option>Dallas Cowboys</option>
						<option>New York Giants</option>
						<option>Philadelphia Eagles</option>
						<option>Washington Redskins</option>
					</optgroup>
					<optgroup label="NFC North">
						<option>Chicago Bears</option>
						<option>Detroit Lions</option>
						<option>Green Bay Packers</option>
						<option>Minnesota Vikings</option>
					</optgroup>
					<optgroup label="NFC South">
						<option>Atlanta Falcons</option>
						<option>Carolina Panthers</option>
						<option>New Orleans Saints</option>
						<option>Tampa Bay Buccaneers</option>
					</optgroup>
					<optgroup label="NFC West">
						<option>Arizona Cardinals</option>
						<option>St. Louis Rams</option>
						<option>San Francisco 49ers</option>
						<option>Seattle Seahawks</option>
					</optgroup>
					<optgroup label="AFC East">
						<option>Buffalo Bills</option>
						<option>Miami Dolphins</option>
						<option>New England Patriots</option>
						<option>New York Jets</option>
					</optgroup>
					<optgroup label="AFC North">
						<option>Baltimore Ravens</option>
						<option>Cincinnati Bengals</option>
						<option>Cleveland Browns</option>
						<option>Pittsburgh Steelers</option>
					</optgroup>
					<optgroup label="AFC South">
						<option>Houston Texans</option>
						<option>Indianapolis Colts</option>
						<option>Jacksonville Jaguars</option>
						<option>Tennessee Titans</option>
					</optgroup>
					<optgroup label="AFC West">
						<option>Denver Broncos</option>
						<option>Kansas City Chiefs</option>
						<option>Oakland Raiders</option>
						<option>San Diego Chargers</option>
					</optgroup>
				</select>
			</div>
		</div><!--/col-->
	</div><!--/row-->
	<div class="row">
		<div class="col-md-12">
			<input class="btn btn-primary submitteams" type="submit" value="Submit" style="float: right;" />
		</div><!--/col-->
	</div><!--/row-->
</div>

<div class="row submitsuccess" style="display: none;">
	<div class="col-md-12">
		<p style="color: green; font-size: 21px; text-align: center;">Thank you! Your selection has been logged!</p>
	</div><!--/col-->
</div><!--/row-->

<br /><br />