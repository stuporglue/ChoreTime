<?php

require_once('db.php');

$username = $_REQUEST['username'];
$time = (!empty($_REQUEST['time']) ? $_REQUEST['time'] : null);
$extra = (!empty($_REQUEST['extra']) ? $_REQUEST['extra'] : null);
$extra_time = (!empty($_REQUEST['extra_time']) ? $_REQUEST['extra_time'] : null);

$money =   (!empty($_REQUEST['money']) ? $_REQUEST['money'] : null); // == 'extra'
$amount =  (!empty($_REQUEST['extra_money']) ? $_REQUEST['extra_money'] : null);
$account = (!empty($_REQUEST['account']) ? $_REQUEST['account'] : null);

if($time == 'extra'){
    $results = add_extra_chore($username,$extra,$extra_time);
} else if($time) {
    $results = toggle_chore($username,$time);
} else if($money == 'extra'){
    $results = add_extra_money($username,$account,$amount,$extra);
}

header("Content-type: application/json");
print json_encode($results);
