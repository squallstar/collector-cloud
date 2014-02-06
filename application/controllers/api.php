<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->benchmark->mark('code_start');

    // Check if the referer is the same domain
    if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] != base_url())
    {
      // 1st case: check from a list of tokens
      $token = $this->input->get_post('token');
      if (!in_array($token, $this->config->item('allowed_tokens')))
      {
        header("HTTP/1.0 401 Unauthorized");
        exit;
      }
    }
    else
    {
      // 2nd case: check for a local cookie
      if ($this->session->userdata('api_allow') !== true)
      {
        header("HTTP/1.0 401 Unauthorized");
        exit;
      }
    }

    $this->load->database();
    $this->load->model('model_sources');
  }

  private function _query_for_boolean_search($query, $like = false)
  {
    $terms = explode(' ', $query);
    $cleanTerms = array();

    foreach ($terms as $term)
    {
      $t = mysql_real_escape_string($term);
      if ($like) $t.='*';
      $cleanTerms[]= $t;
    }

    return '+' . implode(' +', $cleanTerms);
  }

  private function _get_collections()
  {
    if ($id = $this->session->userdata('id'))
    {
      $DBSquallstar = $this->load->database('users', TRUE);

      $res = $DBSquallstar->where('id', $id)
                          ->select('data')
                          ->from('users')
                          ->get()->result_array();
      $data = json_decode($res[0]['data']);

      $collections = array();
      foreach ($data->collections as $collection)
      {
        if ($collection->Kind != 'rss') continue;
        $collections[$collection->Title] = $collection->Urls;
      }
      return $collections;
    }
    else
    {
      // Public collections
      $this->config->load('public_collections');
      return $this->config->item('collections');
    }
  }

  private function _display(&$data, $status = 200)
  {
    if ($status != 200)
    {
      $this->output->set_status_header($status);
    }

    $user = $this->session->userdata('id') ? " [User #" . $this->session->userdata('id') . ']' : '';

    $this->benchmark->mark('code_end');
    log_message('log', "API request [IP " . $this->input->ip_address() . "]" . $user ." [Status code " . $status . "]\r\nRequest: " . $this->uri->uri_string() . ' ' . $_SERVER['QUERY_STRING'] . ' in ' . $this->benchmark->elapsed_time('code_start', 'code_end') . "s.\r\nHeaders:" . json_encode($this->input->request_headers()) . "\r\n");
    $this->output->set_content_type('application/json')->set_output(json_encode($data));
  }

  public function outdated_sources()
  {
    $data = array(
      '< 30 minutes' => count($this->model_sources->get_outdated()),
      '< 60 minutes' => count($this->model_sources->get_outdated(9999, 60))
    );
    $this->_display(
      $data
    );
  }

  public function search()
  {
    $query = $this->input->get_post('q');
    $limit = $this->input->get_post('limit');

    foreach ($this->config->item('blackwords') as $word)
    {
      if (strpos($query, $word) !== FALSE)
      {
        $error = array('error' => 'Your query contains one or more banned words.');
        return $this->_display($error, 400);
      }
    }

    if ($query)
    {
      $min_timestamp = $this->input->get_post('ts');

      $str = $this->_query_for_boolean_search($query);
      $articles = $this->model_sources->get_articles_for_query($str, $limit ? $limit : 40, $min_timestamp);
    }
    else
    {
      $source_id = $this->input->get_post('source');
      $articles = $this->model_sources->get_articles_for_source_id($source_id);
    }

    $order = $this->input->get_post('order');
    if ($order == 'random')
    {
      shuffle($articles);
    }

    $this->model_sources->fill_articles_with_sources($articles);

    $this->_display($articles);
  }

  public function suggestions()
  {
    $query = $this->input->get_post('q');
    $str = $this->_query_for_boolean_search($query, true);

    $suggestions = $this->model_sources->get_suggestions_for_query($str);

    $this->_display($suggestions);
  }

  public function user($action = 'login')
  {
    if ($action == 'logout')
    {
      foreach (array('id', 'username', 'email') as $key)
      {
        $this->session->unset_userdata($key);
      }

      $data = array('logout' => true);
      return $this->_display($data);
    }
    else if ($action == 'send-password')
    {
      $data = array();

      $DBSquallstar = $this->load->database('users', TRUE);

      $res = $DBSquallstar->where('email', $this->input->post('email'))
                          ->select('username, email, password')
                          ->from('users')
                          ->get();

      if ($res->num_rows)
      {
        $res = $res->result_array();
        $u = $res[0];

        $this->load->library('email');

        $this->email->from('noreply@collectorwp.com', 'Collector');
        $this->email->to($u['email']);

        $this->email->subject('Your password request');
        $this->email->message('Hi ' . $u['username'] . ",\r\n\r\nHere's your password linked to your Collector account: " . $u['password'] . "\r\n\r\nSee you on http://cloud.collectorwp.com,\r\nCollector Team");

        if ($this->email->send())
        {
          $data['message'] = 'The email has been sent.';
          return $this->_display($data);
        }
        else
        {
          $data['message'] = 'Cannot send the email right now. Please try later.';
          return $this->_display($data, 500);
        }
      }
      return $this->_display($data, 400);
    }

    $user = $this->input->post(NULL, TRUE);

    if (!isset($user['email']) || !isset($user['password']))
    {
      $data = array('error' => 'Email address or password not provided');
      return $this->_display($data, 401);
    }

    $DBSquallstar = $this->load->database('users', TRUE);

    $res = $DBSquallstar->where('email', $user['email'])
                        ->where('password', $user['password'])
                        ->select('id, username')
                        ->from('users')
                        ->get();

    if ($res->num_rows)
    {
      $res = $res->result_array();
      $u = $res[0];
      $u['email'] = $user['email'];
      $this->session->set_userdata($u);

      $this->db->set('dateweblastlogin', date('Y-m-d H:i:s'))->where('id', $u['id'])->update('users');

      $this->_display($u);
    }
    else
    {
      $this->_display(array('error' => 'Email address or password wrong'), 401);
    }
  }

  /*
   * This api is used to get public collections, or user's collections
   * @return json-array result
   */
  public function collections()
  {
    $collections = $this->_get_collections();
    $colors = $this->config->item('colors');
    shuffle($colors);

    // Riunisco tutti gli id delle sources
    $feeds = array();
    $i = 0;
    foreach ($collections as $fs)
    {
      foreach ($fs as $feed)
      {
        array_push($feeds, $feed);
      }
      $i++;
    }
    // Estraggo tutte le sources necessarie
    $sources = $this->model_sources->get_ids($feeds);
    $ids = array();
    foreach ($sources as $source)
    {
      $ids[]= $source['id'];
    }

    $articles = $this->model_sources->last_articles_for_sources($ids);

    $collections_data = array();
    $i = 0;
    foreach ($collections as $title => $feeds)
    {
      $urls = array();
      foreach ($sources as $source)
      {
        if (in_array($source['url'], $feeds))
        {
          $urls[$source['id']] = array(
            'title' => $source['title'],
            'url' => $source['url']
          );
        }
      }
      $collections_data[] = array(
        'name' => $title,
        'sources' => $urls,
        'articles' => array(),
        'color' => isset($colors[$i]) ? $colors[$i] : $colors[0]
      );
      $i++;
    }

    foreach ($articles as $article)
    {
      foreach ($collections_data as &$collection)
      {
        if (in_array($article['source'], array_keys($collection['sources'])))
        {
          $article['source_title'] = $collection['sources'][$article['source']]['title'];
          $collection['articles'][] = $article;
        }
      }
    }

    // Shuffles the order
    shuffle($collections_data);

    $this->_display($collections_data);
  }

  /*
   * This api is used to get public articles, or user's articles (grouped by collection)
   * @param string ts (min timestamp)
   * @return json-array result
   */
  public function articles()
  {
    $collections = $this->_get_collections();

    $feeds = array();

    // Primo giro per riunire tutti i feed
    $i = 0;
    foreach ($collections as $title => $fs)
    {
      foreach ($fs as $feed)
      {
        array_push($feeds, $feed);
      }
      $i++;
    }

    // Estraggo tutte le sources necessarie
    $sources = $this->model_sources->get_ids($feeds);

    //Metto in ogni source object il nome della collezione a cui appartiene
    foreach ($collections as $title => $feeds)
    {
      foreach ($feeds as $feed)
      {
        foreach ($sources as &$source)
        {
          if ($source['url'] == $feed)
          {
            $source['collection'] = $title;
            break;
          }
        }
      }
    }

    // Logged in user, sources outdated after 2 hours. Not logged in, 3 hours
    $outdated = $this->session->userdata('id') ? 7200 : 10800;

    $this->model_sources->update_if_outdated($sources, $outdated);

    // Estraggo gli articoli
    $articles = array();
    $min_timestamp = $this->input->get_post('ts');
    $limit = intval($this->input->get_post('limit'));
    if (!$limit) $limit = 45;

    $articles = $this->model_sources->get_articles($sources, $limit, $min_timestamp)->result_array();

    $this->model_sources->fill_articles_with_sources($articles, $sources);
    $this->_display($articles);
  }
}
