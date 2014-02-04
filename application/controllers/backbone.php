<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Backbone extends CI_Controller
{
  public function index()
  {
    $this->session->set_userdata('api_allow', true);

    $id = $this->session->userdata('id');
    if ($id)
    {
      $user = json_encode(array(
        'id'       => $id,
        'username' => $this->session->userdata('username'),
        'email'    => $this->session->userdata('email')
      ));
    }
    else
    {
      $user = 'false';
    }

    $this->load->view('backbone/index', array(
      'user' => $user
    ));
  }
}