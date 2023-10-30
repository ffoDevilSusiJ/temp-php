<?php

// INIT
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require('./cfg/general.inc.php');
require('./includes/core/functions.php');

init_classes();
init_controllers_common();

DB::connect();

// SESSION

Session::init();
Route::init();

$g['path'] = Route::$path;
HTML::assign('global', $g);
HTML::display('./partials/index.html');
