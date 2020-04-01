<?php
/**
 * Sätter konstant-booleans vid högtider.
 * Används av både codeigniter och phpbb.
 */

// Första april
define('APRIL_FOOLS', date('n') == 4 && date('j') == 1);

// Jul 🎅🎁🎄
$earliest_first_advent = strtotime('27 november'); // tidigaste möjliga första advent är 27/11 (https://sv.wikipedia.org/wiki/Advent)
$first_advent = strtotime('sunday', $earliest_first_advent); // hitta första söndagen på, eller efter 27/11
$sixth_jan = strtotime('6 january'); // åttonde januari (en vecka efter nyårsdagen)
define('XMAS', time() >= $first_advent || time() < $sixth_jan); // efter första advent eller före 6:e jan
///////////////lista ut när treddondagen är

// Första torsdagen i mars
// Hitta första mars, sedan hitta tidigaste torsdagen
$first_march = strtotime('1 march');
$first_thursday = date('Y-m-d', strtotime('thursday', $first_march));
define('CAKE', date('Y-m-d') == $first_thursday); // är årets första torsdag i mars, idag?
