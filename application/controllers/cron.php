<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();

    if (ENVIRONMENT == 'production')
    {
      if (!$this->input->is_cli_request() && $this->router->fetch_method() != 'logs')
      {
        show_404();
      }
    }

    $this->load->database();
    $this->load->model('model_sources');
  }

  public function logs($when = 'today')
  {
    if ($when == 'today')
    {
      $path = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
    }
    else if ($when == 'yesterday')
    {
      $path = APPPATH . 'logs/log-' . date('Y-m-d',strtotime("-1 days")) . '.php';
    }
    else
    {
      die;
    }

    if (file_exists($path))
    {
      echo '<pre>';
      include($path);
      echo '</pre>';
    }
  }

  public function delete_old_articles()
  {
    $this->db->where('dateinsert < CURRENT_TIMESTAMP() - INTERVAL 10 DAY')
             ->delete('articles');

    if ($this->input->is_cli_request())
    {
      log_message('log', 'CRON deleted ' . $this->db->affected_rows() . ' old articles.');
    }
    else
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
        'status' => 200,
        'articles_deleted' => $this->db->affected_rows()
      )));
    }
  }

  public function update_all_sources()
  {
    set_time_limit(480); # 8 minutes

    $this->benchmark->mark('code_start');

    $how_many = 250;

    $sources = $this->model_sources->get_outdated($how_many);
    $noutdated = $this->model_sources->update_if_outdated($sources, 1800);

    $this->benchmark->mark('code_end');

    if ($this->input->is_cli_request())
    {
      log_message('log', 'CRON updated ' . $noutdated . ' of ' . count($sources) . ' sources in ' . round($this->benchmark->elapsed_time('code_start', 'code_end')) . 's.');
    }
    else
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
        'status' => 200,
        'time' => $this->benchmark->elapsed_time('code_start', 'code_end'),
        'sources_count' => count($sources),
        'sources_updated' => $noutdated
      )));
    }
  }

  public function retrieve_feedproxy_urls()
  {
    set_time_limit(300); # 5 minutes

    $this->benchmark->mark('code_start');

    $articles = $this->model_sources->get_feedproxy_articles(1000);
    $narticles = count($articles);
    if ($narticles)
    {
      $this->load->model('model_articles_downloader', 'downloader');
      $this->downloader->retrieve_feedproxies($articles);
    }

    $this->benchmark->mark('code_end');

    if ($this->input->is_cli_request())
    {
      if ($narticles)
      {
        log_message('log', 'CRON feedproxy pulled ' . $narticles . ' articles in ' . round($this->benchmark->elapsed_time('code_start', 'code_end')) . 's.');
      }
    }
    else
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
          'status' => 200,
          'time' => $this->benchmark->elapsed_time('code_start', 'code_end'),
          'articles_updated' => $narticles
      )));
    }
  }

  public function optimize_tables()
  {
    $this->benchmark->mark('code_start');

    $this->load->dbutil();
    $this->dbutil->optimize_table('articles');
    $this->dbutil->optimize_table('sources');
    $this->dbutil->optimize_table('suggestions');

    $this->benchmark->mark('code_end');

    if ($this->input->is_cli_request())
    {
      log_message('log', 'CRON optimized 3 tables in ' . round($this->benchmark->elapsed_time('code_start', 'code_end')) . 's.');
    }
    else
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
          'status' => 200,
          'time' => $this->benchmark->elapsed_time('code_start', 'code_end')
      )));
    }
  }

  public function update_suggestions()
  {
    $this->benchmark->mark('code_start');
    $count = 0;

    $this->db->query("TRUNCATE suggestions");

    //1. rss
    $this->db->query("
      INSERT INTO suggestions (domain, relevance, kind, source_id, source_name)
      SELECT articles.domain, COUNT( articles.domain ) AS narticles, articles.kind, sources.id AS source_id, sources.title AS source_name
      FROM articles
      LEFT JOIN sources ON sources.id = articles.source
      WHERE sources.title IS NOT NULL
      AND articles.kind = 'rss'
      GROUP BY articles.source
      ORDER BY narticles DESC");
    $count += $this->db->affected_rows();

    //2. twitter
    $this->db->query("
      INSERT INTO suggestions (domain, relevance, kind, source_id, source_name)
      SELECT articles.domain, COUNT( articles.domain ) AS narticles, articles.kind, sources.id AS source_id, author
      FROM articles
      LEFT JOIN sources ON sources.id = articles.source
      WHERE articles.kind = 'twitter'
      GROUP BY articles.domain
      ORDER BY narticles DESC");
    $count += $this->db->affected_rows();

    $this->db->query("DELETE from suggestions WHERE relevance <= 1");
    $count -= $this->db->affected_rows();

    $this->benchmark->mark('code_end');

    if ($this->input->is_cli_request())
    {
      log_message('log', 'CRON updated suggestions (' . $count . ') in' . $this->benchmark->elapsed_time('code_start', 'code_end') . '.');
    }
    else
    {
      $this->output->set_content_type('application/json')->set_output(json_encode(array(
          'status' => 200,
          'time' => $this->benchmark->elapsed_time('code_start', 'code_end'),
          'suggestions' => $count
      )));
    }
  }
}
