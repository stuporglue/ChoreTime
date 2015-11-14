<?php

require_once('db.php');

$username = $_REQUEST['username'];
$time = $_REQUEST['time'];
$extra = $_REQUEST['extra'];
$extra_time = $_REQUEST['extra_time'];

$money = $_REQUEST['money']; // == 'extra'
$amount = $_REQUEST['extra_money'];
$account = $_REQUEST['account'];

if($time == 'extra'){
    $results = add_extra_chore($username,$extra,$extra_time);
} else if($time) {
    $results = toggle_chore($username,$time);
} else if($money == 'extra'){
    $results = add_extra_money($username,$account,$amount,$extra);
}

header("Content-type: application/json");
print json_encode($results);
