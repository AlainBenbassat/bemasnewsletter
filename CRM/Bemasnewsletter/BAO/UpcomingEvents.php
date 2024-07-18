<?php

class CRM_Bemasnewsletter_BAO_UpcomingEvents {
  private const monthNames = [
    'nl_NL' => [
      '01' => 'Januari',
      '02' => 'Februari',
      '03' => 'Maart',
      '04' => 'April',
      '05' => 'Mei',
      '06' => 'Juni',
      '07' => 'Juli',
      '08' => 'Augustus',
      '09' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'December',
    ],
    'fr_FR' => [
      '01' => 'Janvier',
      '02' => 'Février',
      '03' => 'Mars',
      '04' => 'Avril',
      '05' => 'Mai',
      '06' => 'Juin',
      '07' => 'Juilliet',
      '08' => 'Août',
      '09' => 'Septembre',
      '10' => 'Octobre',
      '11' => 'Novembre',
      '12' => 'Décembre',
    ]
  ];

  public static function getFormattedList(string $from, string $to, string $newsletterLanguage, array $eventCodeSuffix) {
    $htmlList = '';

    $events = self::getEventsInRange($from, $to, $newsletterLanguage, $eventCodeSuffix);
    foreach ($events as $eventType => $eventMonthAndDetails) {
      $htmlList .= "<h2 style=\"color: #a8c947\">$eventType</h2>\n";

      foreach ($eventMonthAndDetails as $monthName => $eventIdAndDetails) {
        $htmlList .= "<h3>$monthName</h3>\n";
        $htmlList .= "<ul>\n";

        foreach ($eventIdAndDetails as $eventId => $event) {
          $htmlList .= '<li>' . $event['start_date'] . ' - <a href="' . $event['url'] . '">' . $event['title'] . "</a></li>\n";
        }

        $htmlList .= "</ul>\n";
      }
    }

    return $htmlList;
  }

  private static function getEventsInRange(string $from, string $to, string $newsletterLanguage, array $eventCodeSuffix) {
    $events = \Civi\Api4\Event::get(FALSE)
      ->addSelect('id', 'event_type_id:label', 'start_date', 'title')
      ->addWhere('start_date', '>=', $from . ' 00:00:00')
      ->addWhere('start_date', '<=', $to . ' 23:59:59')
      ->addWhere('is_active', '=', TRUE)
      ->addWhere('event_type_id', 'NOT IN', [20, 17]) // inhouse, meeting
      ->addOrderBy('event_type_id:label', 'ASC')
      ->addOrderBy('start_date', 'ASC')
      ->execute();

    $eventArray = [];
    foreach ($events as $event) {
      if (!self::eventMatchesRequestedLanguage($event['title'], $eventCodeSuffix)) {
        continue;
      }

      $eventType = $event['event_type_id:label'];
      $eventMonthName = self::getEventMonthName($event['start_date'], $newsletterLanguage);
      $eventId = $event['id'];

      $eventArray[$eventType][$eventMonthName][$eventId] = [
        'start_date' => self::getFormattedDate($event['start_date']),
        'title' => self::stripEventCode($event['title']),
        'url' => 'https://www.bemas.org/gotoevent?cid=' . $eventId . '&utm_source=nieuwsbrief&utm_medium=email&utm_campaign=' . $eventId,
      ];
    }

    return $eventArray;
  }

  private static function getEventMonthName(string $startDate, string $newsletterLanguage) {
    $month = substr($startDate, 5, 2);
    return self::monthNames[$newsletterLanguage][$month];
  }

  private static function getFormattedDate(string $startDate) {
    return substr($startDate, 8, 2) . '/' . substr($startDate, 5, 2);
  }

  private static function stripEventCode(string $title) { return $title;
    $pieces = explode(' - ', $title);

    // make sure we have at least 2 pieces
    $numPieces = count($pieces);
    if ($numPieces == 1) {
      return $title;
    }

    // if the event code contains spaces it's not an event code
    if (str_contains($pieces[0], ' ')) {
      return $title;
    }

    // if the event code is too short or too long it's not an event code
    if (strlen($pieces[0]) < 7 || strlen($pieces[0]) > 11) {
      return $title;
    }

    $titleWithoutEventCode = '';
    for ($i = 1; $i < $numPieces; $i++) {
      $titleWithoutEventCode .= $pieces[$i];
    }

    return $titleWithoutEventCode;
  }

  private static function eventMatchesRequestedLanguage(string $eventTitle, $requestEventCodeSuffixes) {
    $eventCodeSuffix = self::getEventCodeSuffix($eventTitle);
    if (!in_array($eventCodeSuffix, ['V', 'W', 'N'])) {
      return TRUE; // the suffix is not valid, so we just accept the event
    }

    foreach ($requestEventCodeSuffixes as $requestEventCodeSuffix) {
      if ($eventCodeSuffix == $requestEventCodeSuffix) {
        return TRUE;
      }
    }

    return FALSE;
  }

  private static function getEventCodeSuffix($eventTitle) {
    $n = strpos($eventTitle, ' - ');
    if ($n === FALSE || $n == 0) {
      return '';
    }

    return substr($eventTitle, $n - 1, 1);
  }
}
