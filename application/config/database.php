<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$active_group = 'default';
$active_record = TRUE;

// Content DB
$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'root';
$db['default']['password'] = 'root';
$db['default']['database'] = 'collector';
$db['default']['dbdriver'] = 'mysql';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = APPPATH . 'cache/queries/';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

// Users DB
$db['users']['hostname'] = 'localhost';
$db['users']['username'] = 'root';
$db['users']['password'] = 'root';
$db['users']['database'] = 'collector';
$db['users']['dbdriver'] = 'mysql';
$db['users']['dbprefix'] = '';
$db['users']['pconnect'] = TRUE;
$db['users']['db_debug'] = TRUE;
$db['users']['cache_on'] = FALSE;
$db['users']['cachedir'] = APPPATH . 'cache/queries/';
$db['users']['char_set'] = 'utf8';
$db['users']['dbcollat'] = 'utf8_general_ci';
$db['users']['swap_pre'] = '';
$db['users']['autoinit'] = TRUE;
$db['users']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */