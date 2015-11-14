<?php

require_once('db.php');

$username = $_REQUEST['username'];
$time = $_REQUEST['time'];
$extra = $_REQUEST['extra'];
$extra_time = $_REQUEST['extra_time'];

if($time == 'extra'){
    $results = add_extra_chore($username,$extra,$extra_time);
} else if($time) {
    $results = toggle_chore($username,$time);
}

header("Content-type: application/json");
print json_encode($results);
