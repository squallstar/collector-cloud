<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends CI_Controller
{
  public function pull()
  {
  	$output = shell_exec('git pull 2>&1');
  	echo $output . '<br />';

  	$output = shell_exec('npm install');
  	echo $output . '<br />';
  	
  	$output = shell_exec('grunt build:production');
  	echo $output;
  }
}