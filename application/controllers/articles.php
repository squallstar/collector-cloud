<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Articles extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->database();

    $this->load->model('model_sources');
    $this->load->model('model_twitter');
  }

  public function load_sources_from_users()
  {
    $this->benchmark->mark('code_start');

    $users = $this->db->select('data')->from('users')->limit(100)->get()->result_array();

    $s = 0;
    foreach ($users as $user)
    {
      $data = json_decode($user['data']);
      if (isset($data) && isset($data->collections))
      {
        foreach ($data->collections as $collection)
        {
          switch ($collection->Kind) {
            case 'rss':
              if (!$collection->Urls) continue;
              $s+=count($collection->Urls);
              $this->model_sources->get_ids($collection->Urls);
              break;
            case 'twitter':
              if (isset($data->preferences->{"Twitter.Token.Key"}) && isset($data->preferences->{"Twitter.Token.Secret"}))
              {
                $this->model_twitter->add_source(
                  $collection,
                  $data->preferences->{"Twitter.Token.Key"},
                  $data->preferences->{"Twitter.Token.Secret"}
                );
              }
          }          
        }
      }
    }

    $this->benchmark->mark('code_end');

    $this->output->set_content_type('application/json')->set_output(json_encode(array(
      'status' => 200,
      'time' => $this->benchmark->elapsed_time('code_start', 'code_end'),
      'sources_count' => $s
    )));
  }

	public function index()
	{
    $headers = $this->input->request_headers();
    if (!isset($headers['Method']) || $headers['Method'] == 'GET') {
      $this->get();
    }
	}

  public function random($howMany = 100)
  {
    $id = $this->db->select('id')->from('users')->order_by('RAND()')->limit(1)->get()->result_array()[0]['id'];
    $this->user($id, $howMany, 'show'); 
  }

  public function user($id = 330, $limitPerCollection = 25, $action = 'json')
  {
    $this->benchmark->mark('code_start');

    $users = $this->db->select('data')->from('users')->where('id', $id)->limit(1)->get()->result_array();
    if (count($users))
    {
      $user = $users[0];
    }
    else
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
        'status' => 400,
        'error' => 'Not found'
      )));
    }

    $data = json_decode($user['data']);

    $cs = array();
    foreach ($data->collections as $collection)
    {
      if ($collection->Kind != 'rss') continue;
      $c = array(
        'sources' => $this->model_sources->get_ids($collection->Urls)
      );
      $articles = $this->model_sources->get_articles($c['sources'], $limitPerCollection);
      $c['articles'] =& $articles->result_array();

      //Free up memory
      $articles->free_result();

      $cs[$collection->Title] = $c;
    }

    $this->benchmark->mark('code_end');

    if ($action == 'json')
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
        'status' => 200,
        'info' => array(
          'time' => $this->benchmark->elapsed_time('code_start', 'code_end'),
          'queries' => array(
            'count' => $this->db->total_queries(),
            'queries' => $this->db->queries
          )
        ),
        'data' => $cs
      )));
    }
    else if ($action == 'show')
    {
      $this->load->view('user_collections', array(
        'collections' => $cs
      ));
    }
  }

  public function get($action = 'json')
  {
    $sources = $this->input->get_post('sources');

    if (is_array($sources) && count($sources))
    {
      $sources = $this->model_sources->get_ids($sources);
    } else {
      $sources = $this->model_sources->get_outdated(10);
    }
    $articles = $this->model_sources->get_articles($sources);

    if ($action == 'show') return $this->load->view('user_collections', array(
      'collections' => array(
        'Feed' => array('articles' => $articles->result_array())
      )
    ));

    $this->output->set_content_type('application/json')
         ->set_output(json_encode(array(
      'status' => 200,
      'sources' => $sources,
      'articles' => $articles->result_array()
    )));
  }
}
