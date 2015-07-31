<?php

$user = 'choretime';
$password = 'chore';
$db = 'chores';

$mysqli = new mysqli("localhost", $user , $password, $db);

function time_left($user = FALSE){
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
