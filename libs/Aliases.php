<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

function is_dev($strict=false){ return nebula()->is_dev($strict); }
function is_client($strict=false){ return nebula()->is_client($strict); }
function is_staff($strict=false){ return nebula()->is_staff($strict); }