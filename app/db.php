<?php

$user = 'choretime';
$password = 'chores';
$db = 'chores';

global $mysqli;
$mysqli = new mysqli("localhost", $user , $password, $db);

function time_left($user = FALSE){
    global $mysqli;

    $sql = "SELECT * FROM screen_time";
    if($user){
        $sql .= " where username='$user'";
    }

    $res = $mysqli->query($sql);
    $ret = Array();

    while($row = $res->fetch_assoc()){
        $ret[$row['username']] = $row;
    }

    return $ret;
}

function todays_chore_status(){
    global $mysqli;

    $sql = "SELECT * FROM time_log WHERE date=CURDATE()";
    $res = $mysqli->query($sql);
    $e = $mysqli->error;

    $ret = Array();
    while($row = $res->fetch_assoc()){
        if($row['morning']){
            $ret[$row['username']]['morning'] = TRUE;
        }else if($row['night']){
            $ret[$row['username']]['night'] = TRUE;
        }else if($row['extra']){
            $ret[$row['username']]['extra'][] = Array('extra' => $row['extra'], 'extra_time' => $row['extra_time']);
        }
    }

    return $ret;
}
