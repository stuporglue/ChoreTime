<?php

$user = 'choretime';
$password = 'chores';
$db = 'chores';

global $mysqli;
$mysqli = @new mysqli("localhost", $user , $password, $db);

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

function allowance($user = FALSE){
    global $mysqli;

    $sql = "SELECT * FROM allowance";
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

function toggle_chore($username,$time){
    global $mysqli;

    $q = "SELECT * FROM time_log WHERE username=? AND date=CURDATE()";
    if($time == 'morning'){
        $q .= " AND morning";
    }else if($time == 'night'){
        $q .= " AND night";
    }
    
    $sql = $mysqli->prepare($q);
    $sql->bind_param('s',$username);
    if(!$sql->execute()){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die($mysqli->error);
    }
    $sql->store_result();
    if($sql->num_rows == 1){
        // Delete the row
        $q = "DELETE FROM time_log WHERE username=? AND date=CURDATE()";
        if($time == 'morning'){
            $q .= " AND morning";
        }else if($time == 'night'){
            $q .= " AND night";
        }
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }


        $q = "INSERT INTO ledger(username,date,amount,account,note) VALUES (?,CURDATE(),-0.05,'tithing','$time chores')";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }

        $q = "INSERT INTO ledger(username,date,amount,account,note) VALUES (?,CURDATE(),-0.25,'savings','$time chores')";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }

        $q = "INSERT INTO ledger(username,date,amount,account,note) VALUES (?,CURDATE(),-0.20,'spending','$time chores')";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }






        $done = FALSE;
    }else{
        $q = "INSERT INTO time_log (date,username,";
        if($time == 'morning'){
            $q .= "morning";
        }else if($time == 'night'){
            $q .= "night";
        }
        $q .= ") VALUES (CURDATE(),?,1)";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }

        $q = "INSERT INTO ledger(username,date,amount,account,note) VALUES (?,CURDATE(),0.05,'tithing','$time chores')";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }

        $q = "INSERT INTO ledger(username,date,amount,account,note) VALUES (?,CURDATE(),0.25,'savings','$time chores')";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }

        $q = "INSERT INTO ledger(username,date,amount,account,note) VALUES (?,CURDATE(),0.20,'spending','$time chores')";
        $sql = $mysqli->prepare($q);
        $sql->bind_param('s',$username);
        if(!$sql->execute()){
            print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
            die($mysqli->error);
        }



        $done = TRUE;
    }

    if($done){
        $timeleft = add_time($username,5);
        $balance = add_allowance($username,'0.50');
    }else{
        $timeleft = add_time($username,-5);
        $balance = add_allowance($username,'-0.50');
    }

    $status = Array('done' => $done,'timeleft' => $timeleft,'username' => $username);
    $status = array_merge($status,$balance);

    return $status;
}

function add_allowance($username,$amount){
        $tithing = add_money($username,'tithing',$amount * 0.10);
        $savings = add_money($username,'savings',$amount * 0.50);
        $spending = add_money($username,'spending',$amount * 0.40);
        return Array('tithing' => $tithing,'savings' => $savings, 'spending' => $spending);
}

function add_money($username,$account,$value){
    global $mysqli;

    if(!in_array($account,Array('savings','tithing','spending'))){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die("Bad account");
    }

    $sql = "UPDATE allowance SET $account=($account + ?) WHERE username=?";
    $sql = $mysqli->prepare($sql);
    try {
        $sql->bind_param('ds',$value,$username);
    } catch (Exception $e){
        print_r($e);
    }
    if(!$sql->execute()){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die($mysqli->error);
    }

    $sql = "SELECT $account FROM allowance WHERE username=?";
    $sql = $mysqli->prepare($sql);
    $sql->bind_param('s',$username);
    if(!$sql->execute()){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die($mysqli->error);
    }

    $sql->bind_result($result);
    $sql->fetch();

    return $result;
}

function add_time($username,$amount){
    global $mysqli;

    $sql = $mysqli->prepare("UPDATE screen_time SET minutes=(minutes + ?) WHERE username=?");
    $sql->bind_param('is',$amount,$username);
    if(!$sql->execute()){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die($mysqli->error);
    }

    $sql = $mysqli->prepare("SELECT minutes FROM screen_time WHERE username=?");
    $sql->bind_param('s',$username);
    if(!$sql->execute()){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die($mysqli->error);
    }
    $sql->bind_result($result);
    $sql->fetch();

    return $result;
}

function add_extra_chore($username,$chore,$time){
    global $mysqli;
    $sql = $mysqli->prepare("INSERT INTO time_log(date,username,extra,extra_time) VALUES (CURDATE(),?,?,?)");
    $sql->bind_param('ssi',$username,$chore,$time);
    if(!$sql->execute()){
        print __FILE__ . ':' . __LINE__ . "    (" . time() . ")<br/>\n"; 
        die($mysqli->error);
    }

    $timeleft = add_time($username,$time);

    return Array('done' => TRUE,'timeleft' => $timeleft,'username' => $username);
}
