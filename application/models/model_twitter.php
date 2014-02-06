<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_twitter extends CI_Model
{
  public function add_source($data, $key, $secret)
  {
    $username = '@' . $data->Title;
    $count = $this->db->from('sources')
                      ->where('kind', 'twitter')
                      ->where('url', $username)->count_all_results();

    if (!$count)
    {
      $this->db->insert('sources', array(
          'title' => $data->Title,
          'url' => $username,
          'kind' => 'twitter',
          'oauth_key' => $key,
          'oauth_secret' => $secret
      ));
      return true;
    }

    return false;
  }

  public function get_feed($source)
  {
    require_once APPPATH . 'libraries/twitter.php';

    $conf = array(
      'oauth_access_token' => $source['oauth_key'],
      'oauth_access_token_secret' => $source['oauth_secret'],
      'consumer_key' => $this->config->item('twitter_consumer_key'),
      'consumer_secret' => $this->config->item('twitter_consumer_secret')
    );

    $twitter = new TwitterAPIExchange($conf);
    $result = $twitter->setGetfield('?count=70&exclude_replies=true&contributor_details=false&include_entities=true')
      ->buildOauth('https://api.twitter.com/1.1/statuses/home_timeline.json', 'GET')
      ->performRequest();

    $tweets = array();
    foreach (json_decode($result) as $tweet)
    {
      if (isset($tweet->entities) && (count($tweet->entities->urls) || isset($tweet->entities->media)))
      {
        $hash = 'twitter-' . $tweet->id;
        $tweet->hash = $hash;
        $tweets[$hash] = $tweet;
      }
    }

    return $tweets;
  }

  public function parse_entry($tweet, $source)
  {
    $now = time();

    $ts = strtotime($tweet->created_at);

    $data = array(
      'source'       => $source['id'],
      'kind'         => 'twitter',
      'title'        => $tweet->text,
      'hash'         => $tweet->hash,
      'author'       => $tweet->user->name,
      'author_image' => $tweet->user->profile_image_url,
      'image_url'    => NULL,
      'datepublish'  => date('Y-m-d H:i:s', $now < $ts ? $now : $ts),
      'domain'       => '@' . $tweet->user->screen_name
    );

    $data['title'] = preg_replace("/ ?https?:\/\/+[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i", "", $data['title']);

    if (count($tweet->entities->urls))
    {
      $data['url'] = $tweet->entities->urls[0]->expanded_url;
    }

    if (isset($tweet->entities->media))
    {
      foreach ($tweet->entities->media as $media)
      {
        if ($media->media_url)
        {
          $data['image_url'] = $media->media_url;
          if (!isset($data['url']))
          {
            $data['url'] = $media->expanded_url;
          }
          break;
        }
      }
    }

    return $data;
  }
}