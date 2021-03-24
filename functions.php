<?php

do_action('qm/start', 'Non-WP Core (Total)'); //This is as close to WP Core finishing as we can measure. This QM measurement includes Nebula, all plugins, and child theme functionality.
require_once 'nebula.php';
nebula();

//Close functions.php. DO NOT add anything after this closing tag!! ?>