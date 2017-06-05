<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

function is_debug($strict=false){ return nebula()->is_debug($strict); }

function is_dev($strict=false){ return nebula()->is_dev($strict); }
function is_client($strict=false){ return nebula()->is_client($strict); }
function is_staff($strict=false){ return nebula()->is_staff($strict); }

function is_admin_page(){ return nebula()->is_admin_page(); }