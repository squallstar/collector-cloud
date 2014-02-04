<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_sources extends CI_Model
{
  public function get_all()
  {
    return $this->db->select('id, url, title, dateupdate, failures')
                    ->from('sources')
                    ->get()
                    ->result_array();
  }

  /*
   * Gets the sources that have been updated at least 30 minutes ago
   * @param array sources
   * @return array sources
   */
  public function get_outdated($limit = 999, $interval = 30)
  {
    return $this->db->select('id, url, title, dateupdate, failures, kind, oauth_key, oauth_secret')
                    ->from('sources')
                    ->where('(dateupdate <= CURRENT_TIMESTAMP() - INTERVAL ' . intval($interval) . ' MINUTE OR dateupdate IS NULL)', NULL, FALSE)
                    ->where('failures < 5')
                    ->order_by('dateupdate', 'ASC')
                    ->limit($limit)
                    ->get()
                    ->result_array();
  }

  /*
   * Gets the sources for the given source urls
   * @param array sources
   * @return array sources
   */
  public function get_ids($sources = array())
  {
    $sources = array_unique($sources);
    $dbSources = $this->db->select('id, url, title, dateupdate, failures')
                          ->from('sources')
                          ->where_in('url', $sources)
                          ->get();

    $newSources = array();

    if ($dbSources->num_rows != count($sources))
    {
      //Create the missing sources
      foreach ($sources as $source_url)
      {

        $hasSource = false;
        foreach ($dbSources->result_array() as $dbSource)
        {
          if ($dbSource['url'] == $source_url)
          {
            $hasSource = true;
            $newSources[] = $dbSource;
            break;
          }
        }

        if (!$hasSource && strpos($source_url, 'http://') !== FALSE)
        {
          $this->db->insert('sources', array(
            'url' => $source_url
          ));

          $newSources[] = array(
            'id'  => $this->db->insert_id(),
            'url' => $source_url,
            'dateupdate' => NULL,
            'failures' => 0
          );
        }
      }
    }
    else
    {
      // Sources were already present into the database
      // However, Let's filter sources that have many failures.
      $sources = $dbSources->result_array();
      foreach ($sources as $source)
      {
        if (intval($source['failures']) < 5)
        {
          $newSources[] = $source;
        }
      }
    }

    return $newSources;
  }

  public function update_if_outdated($sources = array(), $outdatedDelta = 3600)
  {
    $outdated = array();

    $oldTs = time() - $outdatedDelta;

    foreach ($sources as $source)
    {
      if (!$source['dateupdate'])
      {
        $outdated[] = $source;
      }
      else
      {
        if ( strtotime($source['dateupdate']) < $oldTs )
        {
          $outdated[] = $source;
        }
      }
    }

    $noutdated = count($outdated);
    if ($noutdated)
    {
      if (!isset($this->downloader))
      {
        $this->load->model('model_articles_downloader', 'downloader');
      }
      foreach ($outdated as $source)
      {
        $this->downloader->update_source($source);
      }
    }

    return $noutdated;
  }

  /*
   * Gets the articles for the given sources
   * @param array sources
   * @param int limit (default 25)
   * @return CI_MySQL result
   */
  public function get_articles($sources = array(), $limit = 40, $min_timestamp = FALSE)
  {
    if (!$sources) return;

    $sourceIds = array();
    foreach ($sources as $source)
    {
      $sourceIds[] = $source['id'];
    }

    $this->update_if_outdated($sources);

    $this->db->from('articles')
             ->select('id, source, url, kind, datepublish, title, image_url, author, content, domain')
             ->where_in('source', $sourceIds)
             ->limit($limit)
             ->order_by('datepublish', 'DESC');

    if ($min_timestamp)
    {
      $this->db->where('datepublish < ', $min_timestamp);
    }

    return $this->db->get();
  }

  /*
   * Gets the articles that have a feedproxy domain
   * @param int limit (default 100)
   * @return array result
   */
  public function get_feedproxy_articles($limit = 100)
  {
    return $this->db->from('articles')
                    ->select('id, url')
                    ->where('domain', 'feedproxy.google.com')
                    ->limit($limit)
                    ->order_by('datepublish', 'DESC')
                    ->get()->result_array();
  }

  public function get_articles_for_source_id($source=1)
  {
    return $this->db->query("SELECT id, source, url, kind, datepublish, title, image_url, author, content, domain FROM articles WHERE source = " . intval($source) ." ORDER BY datepublish DESC LIMIT 40;")->result_array();
  }

  public function get_articles_for_query($query='', $limit = 40, $min_timestamp = FALSE)
  {
    $limit = intval($limit);
    if ($limit > 50 || $limit < 1) $limit = 40;

    $this->db->select('id, source, url, kind, datepublish, title, image_url, author, content, domain')
             ->from('articles')
             ->where("MATCH (title, content, url, author, domain) AGAINST ('" . addslashes($query) . "' IN BOOLEAN MODE)", NULL, FALSE)
             ->order_by('datepublish', 'DESC')
             ->limit($limit);

    if ($min_timestamp)
    {
      $this->db->where('datepublish < ', $min_timestamp);
    }

    if (substr($query, 1, 1) == '@')
    {
      $this->db->where('kind', 'twitter');
    }
    else
    {
      $this->db->where('kind', 'rss');
    }

    return $this->db->get()->result_array();
  }

  public function get_suggestions_for_query($query = '')
  {
    return $this->db->select('domain, source_id, source_name, relevance')
             ->from('suggestions')
             ->where("MATCH (source_name, domain) AGAINST ('" . addslashes($query) . "' IN BOOLEAN MODE)", NULL, FALSE)
             ->order_by('relevance', 'DESC')
             ->limit(8)
             ->get()
             ->result_array();
  }

  public function fill_articles_with_sources(&$articles, $sources = array())
  {
    if (count($articles) == 0) return;

    $colors = $this->config->item('colors');

    if (!$sources)
    {
      //Sources not provided. We need to get them first
      foreach ($articles as $article)
      {
        $ids[$article['source']] = true;
      }

      $res = $this->db->select('id, title')
                      ->from('sources')
                      ->where_in('id', array_keys($ids))
                      ->get()
                      ->result_array();

      foreach ($res as $source)
      {
        $sources[] = array(
          'id'    => $source['id'],
          'title' => strlen($source['title'] > 60) ? substr($source['title'], 0, 59) . '...' : $source['title'],
        );
      }
    }

    $i = 0;

    $source_hash = array();

    if (isset($sources[0]['collection']))
    {
      // Colors by collection: Group the collections
      $colors_collections = array();
      foreach ($sources as $source)
      {
        $colors_collections[$source['collection']] = true;
      }

      // Create colors for each collection
      foreach ($colors_collections as &$color)
      {
        $color = isset($colors[$i]) ? $colors[$i] : $colors[0];
        $i++;
      }

      // Apply collection color to every source
      foreach ($sources as &$source)
      {
        $source['color'] = $colors_collections[$source['collection']];
        $source_hash[$source['id']] = $source;
      }

    }
    else
    {
      // Apply colors by source
      foreach ($sources as &$source)
      {
        $source['color'] = isset($colors[$i]) ? $colors[$i] : $colors[0];
        $source_hash[$source['id']] = $source;
        $i++;
      }
    }

    foreach ($articles as &$article)
    {
      if (isset($source_hash[$article['source']]))
      {
        $source = $source_hash[$article['source']];
        $article['source_title'] = $source['title'];
        $article['color'] = $source['color'];
        if (isset($source['collection']))
        {
          $article['collection'] = $source['collection'];
        }
      }
      else
      {
        $article['source_title'] = null;
        $article['color'] = null;
      }
    }
  }

  public function last_articles_for_sources($sources = array())
  {
    return $this->db->select('MAX(datepublish) as datepublish, id, image_url, title, source')
                    ->from('articles')
                    ->where_in('source', $sources)
                    ->where('image_url IS NOT NULL')
                    ->group_by('source')
                    ->get()
                    ->result_array();
  }
}
