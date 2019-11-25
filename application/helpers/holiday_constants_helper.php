<?php
/**
 * Sätter konstant-booleans vid högtider.
 * Används av både codeigniter och phpbb.
 */

// Första april
define('APRIL_FOOLS', date('n') == 4 && date('j') == 1);

// Jul 🎅🎁🎄
$earliest_first_advent = strtotime('27 november'); //tidigaste möjliga första advent är 27/11 (https://sv.wikipedia.org/wiki/Advent)
$first_advent = strtotime('sunday', $earliest_first_advent); //hitta första söndagen på, eller efter 27/11
$eight_jan = mktime(0, 0, 0, 1, 8, (date('Y')+1)); //åttonde januari, nästa år (en vecka efter nyårsdagen)
define('XMAS', time() >= $first_advent && time() < $eight_jan);