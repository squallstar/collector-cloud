<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_articles_downloader extends CI_Model
{
  const ARTICLE_CONTENT_LENGTH = 300;

	public function __construct()
  {
    parent::__construct();
    if (!isset($this->db))
    {
      $this->load->database();
    }
  }

  public function update_source($source)
  {
    if (isset($source['kind']) && $source['kind'] == 'twitter')
    {
      $this->load->model('model_twitter');
      $entries = $this->model_twitter->get_feed($source);
    }
    else
    {
      $entries = $this->_get_feed_rss($source);
    }

    if ($entries === false)
    {
      //Failed to update
      return;
    }

    if (count($entries))
    {
      //Filter entries that have been already added to the DB
      $this->_filter_entries($entries);

      if (count($entries))
      {
        $this->_insert_articles($entries, $source);
      }
    }

    $this->db->limit(1)->where('id', $source['id'])->update('sources', array(
      'dateupdate' => date('Y-m-d H:i:s'),
      'title' => $source['title']
    ));
  }

  private function _get_feed_rss(&$source)
  {
    //1. get feed
    $gUrl = "https://www.google.com/uds/Gfeeds?hl=en&num=15&v=1.0&output=json&q=" . urlencode($source['url']) . "&nocache=" . (time()-1200);
    $contents = json_decode(file_get_contents($gUrl));

    if (!$contents || $contents->responseStatus != 200)
    {
      $response = isset($contents->responseDetails) ? $contents->responseDetails : $contents;
      $this->_source_failed($source, $response);
      return false;
    }

    //2. normalize data
    $entries = array();

    if (isset($contents->responseData->feed->title))
    {
      $source['title'] = $contents->responseData->feed->title;
    }

    foreach ($contents->responseData->feed->entries as $entry)
    {
      if (!$entry->link)
      {
        continue;
      }
      
      $entry->link = $this->_strip_utmparams($entry->link);
      
      if (strlen($entry->link) >= 255)
      {
        continue;
      }

      $entry->hash = md5($entry->link);
      
      $entries[$entry->hash] = $entry;
    }

    unset($contents);

    return $entries;
  }

  private function _filter_entries(&$entries)
  {
    $dbArticles = $this->db->select('hash')
                           ->from('articles')
                           ->where_in('hash', array_keys($entries))
                           ->get();

    if ($dbArticles->num_rows > 0)
    {
      //At least one article found on DB
      foreach ($dbArticles->result_array() as $dbArticle)
      {
        //Exists? Skip it
        if (isset($entries[$dbArticle['hash']]))
        {
          unset($entries[$dbArticle['hash']]);
        }
      }

      //Free up
      $dbArticles->free_result();
    }
  }

  private function _source_failed($source, $response)
  {
    // Let's update the source dateupdate and failures
    $this->db->where('id', $source['id'])
             ->set('failures', 'failures+1', FALSE)
             ->limit(1)
             ->update('sources', array('dateupdate' => date('Y-m-d H:i:s')));

    log_message('error', 'Model_articles_downloader#update_source ' . $response . ' > ' . json_encode($source));   
  }

  private function _insert_articles($entries, $source)
  {
    $batchArticles = array();

    if (!function_exists('images_from_string'))
    {
      $this->load->helper('images');
    }

    if (!isset($source['kind']))
    {
      $source['kind'] = 'rss';
    }
    
    switch ($source['kind']) {
      case 'twitter':
        $this->load->model('model_twitter');
        foreach ($entries as $hash => $entry)
        {
          $batchArticles[$hash]= $this->model_twitter->parse_entry($entry, $source);
        }
        break;
      
      default:
        $source_pieces = parse_url($source['url']);
        $source['host'] = $source_pieces['scheme'] . '://' . $source_pieces['host'];

        foreach ($entries as $hash => $entry)
        {
          $batchArticles[$hash]= $this->_get_data_for_entry_rss($entry, $source);
        }
    }

    if (count($batchArticles))
    {
      try{
        $this->db->insert_batch('articles', array_reverse(array_values($batchArticles)));
      } catch (Exception $e) {
        log_message('error', $e->getMessage());
      }
    }
  }

  public function retrieve_feedproxies($articles = array())
  {
    foreach ($articles as $article)
    {
      $ch = curl_init();
      $ret = curl_setopt($ch, CURLOPT_URL, $article['url']);
      $ret = curl_setopt($ch, CURLOPT_HEADER, 1);
      $ret = curl_setopt($ch, CURLOPT_NOBODY, 1);
      $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
      $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      $ret = curl_exec($ch);
      if (!empty($ret))
      {
        $info = curl_getinfo($ch);
        curl_close($ch);
        if (isset($info['redirect_url']))
        {
          $url = $this->_strip_utmparams($info['redirect_url']);

          // We can only have one record with this url
          $n = $this->db->limit(1)->where('url', $url)->select('id')->from('articles')->count_all_results();
          if ($n == 0)
          {
            $url_parts = parse_url($url);
            $this->db->limit(1)->where('id', $article['id'])->update('articles', array(
              'url'    => $url,
              'domain' => isset($url_parts['host']) ? $url_parts['host'] : ''
            ));
          }
          else
          {
            $this->db->limit(1)->where('id', $article['id'])->delete('articles');
          }
        }
      }
    }
  }

  private function _strip_utmparams($string = '')
  {
    return preg_replace('/(\?|\&)?utm_[a-z]+=[^\&]+/', '', $string);
  }

  private function _get_data_for_entry_rss($entry, $source)
  {
    $now = time();

    $url_pieces = parse_url($entry->link);
    $domain = isset($url_pieces['host']) ? $url_pieces['host'] : '';
    
    $ts = strtotime($entry->publishedDate);

    $data = array(
      'source'       => $source['id'],
      'kind'         => 'rss',
      'title'        => $entry->title,
      'url'          => $entry->link,
      'hash'         => $entry->hash,
      'author'       => strip_tags($entry->author),
      'content'      => trim(strip_tags($entry->content)),
      'image_url'    => NULL,
      'datepublish'  => date('Y-m-d H:i:s', $now < $ts ? $now : $ts),
      'domain'       => $domain
    );

    if (strlen($data['content']) >= self::ARTICLE_CONTENT_LENGTH)
    {
      $data['content'] = substr($data['content'], 0, self::ARTICLE_CONTENT_LENGTH-1) . '...';
    }
    
    $img = images_from_string($entry->content);

    if (!$img && isset($entry->mediaGroups))
    {
      $media = $entry->mediaGroups;
      if (count($media) && isset($media[0]->contents))
      {
        foreach ($media[0]->contents as $file)
        {
          if (isset($file->type))
          {
            if (strpos($file->type, 'image') !== FALSE)
            {
              $img = $file->url;
              break;
            }
          }
          else if (isset($file->thumbnails))
          {
            if (isset($file->thumbnails[0]))
            {
              $img = $file->thumbnails[0]->url;
              break;
            }
          }
        }
      }
    }

    if ($img)
    {
      if ($img[0] == '/')
      {
        //Image is relative. We must add the domain
        $data['image_url'] = $source['host'] . $img;
      }
      else
      {
        $data['image_url'] = $img;
      }
    }
    return $data;
  }
}