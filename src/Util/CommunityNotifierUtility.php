<?php

namespace Drupal\community_notifier\Util;

class CommunityNotifierUtility {
  /**
   * @param $timestamp Unix timestamp
   * @return bool
   */
  public static function isCurrentDayOfWeek($timestamp){
    $currentDay = date("N");

    if ($currentDay == date('N', $timestamp)){
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $frequency (daily, weekly)
   * @return array of date time range
   */
  public static function frequencyDateRange($frequency){
    $end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 days'))); //change to -1..-8 added for testing purposes only
    if ($frequency == 'daily'){
      $start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))); //change to -1

      return [$start, $end];
    }
    else{
      $start = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));

      return [$start, $end];
    }
  }
  public static function mergeBodies(array $comments){
    $bodyTemp = [];
    $messageSettings = \Drupal::service('config.factory')->getEditable('community_notifier.settings')->get('settings.messages');
    $length = $messageSettings['length'];
    $seperator = $messageSettings['separator'];
    $seperator_string = '';

    for ($i = 0; $i <= 80; $i++){
      $seperator_string .= $seperator;
    }

    foreach ($comments as $comment){
      if ($length != 0){
        array_push($bodyTemp, mb_strimwidth($comment['body'], 0, $length, '...'));
      }
      else{
        array_push($bodyTemp, $comment['body']);
      }
    }

    return implode('<br/>'.$seperator_string.'<br/>', $bodyTemp);
  }
}