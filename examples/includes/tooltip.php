<style>
	.nebula-tooltip {position: relative; top: 0; cursor: default;}
		.nebula-tooltip:before {content: 'The tip.'; position: absolute; display: block; width: auto; min-width: 50px; max-width: 320px; padding: 0 5px; color: #fff; font-size: 12px; text-align: center; background: rgba(0,0,0,0); opacity: 0; top: 0; border-radius: 20px; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
			.nebula-tooltip:hover:before {top: -25px; background: rgba(0,0,0,0.8); opacity: 1;}
		.nebula-tooltip:after {content: 'v'; position: absolute; display: block; color: #000; top: 0; opacity: 0; -moz-transition: all 0.25s ease 0s; -o-transition: all 0.25s ease 0s; transition: all 0.25s ease 0s;}
			.nebula-tooltip:hover:after {top: -15px; opacity: 1;}
</style>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <a class="nebula-tooltip">Vivamus</a> feugiat lorem a enim rhoncus, vitae varius arcu dignissim. Nunc mollis quis orci ut ullamcorper. Praesent rutrum vitae sem eget lacinia. Aenean sem leo, bibendum sit amet velit ut, <a class="nebula-tooltip">condimentum vehicula</a> enim. Duis quis lectus non nibh luctus dignissim vel vitae turpis. Aenean non porttitor leo. Nullam ac diam cursus, pharetra eros ut, iaculis augue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Curabitur rhoncus placerat auctor. Aliquam bibendum hendrerit felis, at pellentesque metus lacinia nec.</p>