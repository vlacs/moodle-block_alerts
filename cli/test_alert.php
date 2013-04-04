<?php

if (PHP_SAPI !== 'cli') { print "NO!\n"; exit; }
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

$longopts = array('course_id:', 'user_id:');
$opts = (object)getopt(null, $longopts);

if(!isset($opts->course_id) or !isset($opts->user_id)) {
    // WTF?!
    exit();
}

$alerts = Alerts::fetch_alerts($opts->course_id, $opts->user_id);
foreach($alerts as $a) {
    print $a->get_html(true);
}
