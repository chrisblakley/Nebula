<style>
	#example-list p {margin: 0; padding: 0;}
	.filtereditem {display: none;}
</style>


<script>
	jQuery(document).ready(function() {

		jQuery(document).on('keyup', '#example-input', function(){
			filter = jQuery('#example-input').val().trim();

			console.log('filtering for: ' + filter);

			jQuery('#example-list').find("*:not(:Contains(" + filter + "))").parents('li').addClass('filtereditem');
			jQuery('#example-list').find("*:Contains(" + filter + ")").parents('li').removeClass('filtereditem');
		});

	});
</script>


<div class="row">
	<div class="sixteen columns">

		<strong>Example Filter</strong>
		<div class="field">
			<input id="example-input" class="input" type="text" placeholder="Type here..." />
		</div>

		<ul id="example-list">
			<li>
				<p>Test</p>
				<span class="hidden">Lorem ipsum</span>
			</li>
			<li>
				<p>Example</p>
				<span class="hidden">Dolor sit</span>
			</li>
			<li>
				<p>Something</p>
				<span class="hidden">Amet Lorem</span>
			</li>
			<li>
				<p>Another Thing</p>
				<span class="hidden">None</span>
			</li>
			<li>
				<p>Whatever</p>
				<span class="hidden">None</span>
			</li>
			<li>
				<p>Hello</p>
				<span class="hidden">Hi hey sup aloha</span>
			</li>
			<li>
				<p>Goodbye</p>
				<span class="hidden">bye cya c-ya adios aloha</span>
			</li>
		</ul>

	</div><!--/columns-->
</div><!--/row-->