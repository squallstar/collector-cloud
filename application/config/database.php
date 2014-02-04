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
$db['squallstar']['hostname'] = 'localhost';
$db['squallstar']['username'] = 'root';
$db['squallstar']['password'] = 'root';
$db['squallstar']['database'] = 'collector';
$db['squallstar']['dbdriver'] = 'mysql';
$db['squallstar']['dbprefix'] = '';
$db['squallstar']['pconnect'] = TRUE;
$db['squallstar']['db_debug'] = TRUE;
$db['squallstar']['cache_on'] = FALSE;
$db['squallstar']['cachedir'] = APPPATH . 'cache/queries/';
$db['squallstar']['char_set'] = 'utf8';
$db['squallstar']['dbcollat'] = 'utf8_general_ci';
$db['squallstar']['swap_pre'] = '';
$db['squallstar']['autoinit'] = TRUE;
$db['squallstar']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */