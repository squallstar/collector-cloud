<?php

function images_from_string($string = '')
{
  if ($string == '') return NULL;
  
  preg_match_all("/<(img)?+[^>]* src=['\"]([^\"']+\.(jpe?g|png)[#?]?[^\"']*?)[\"']/i", $string, $images);

  if (is_array($images[2]) && count($images[2]))
  {
    $needles = array('twitter', 'facebook', 'linkedin', 'googleplus', 'mail', '/fb');

    foreach ($images[2] as $image)
    {
      $t = explode('.', $image);
      $ext = $t[count($t)-1];
      $is_valid = true;
      foreach ($needles as $needle)
      {
        if (strpos($image, $needle . '.' . $ext) !== false)
        {
          $is_valid = false;
          break;
        }
      }

      if ($is_valid)
      {
        return $image;
      }
    }
  }

  return NULL;
}