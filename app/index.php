<?php

require_once('db.php');

function getSessions(){
    $res = shell_exec('loginctl list-sessions');
    $res = split("\n",$res);
    $sessions = Array();
    foreach($res as $session){
        $session = preg_split("|\s+|",trim($session));
        $first = (isset($first) ? $first : $session);
        if(count($session) == 4 && $session[0] !== 'SESSION'){
            $wordsession = Array();
            foreach($first as $k => $v){
                $wordsession[$v] = $session[$k];
            }
            $sessions[] = $wordsession;
        }
    }

    foreach($sessions as $k => $session){
        $res = shell_exec("loginctl show-session -p Display -p Active  {$session['SESSION']}");
        $res = split("\n",trim($res));
        foreach($res as $line){
            $vars = split("=",trim($line));
            if(count($vars) == 2){
                $sessions[$k][$vars[0]] = $vars[1];
            }
        }
    }

    $sessions_by_user = Array();
    foreach($sessions as $session){
        $sessions_by_user[$session['USER']][] = $session;
    }

    return $sessions_by_user;
}

function makeButtonForm($username,$time,$done){
    $html = "<form action='do_chores.php' method='post'>";
    $html .= "<input type='hidden' name='username' value='$username'/>";
    $html .= "<input type='hidden' name='time' value='$time'/>";
    if($done){
        $html .= "<input class='done doitnow' type='submit' value='Done!'/>";
    }else{
        $html .= "<input class='notdone doitnow' type='submit' value='Do It!'/>";
    }
    $html .= "</form>";
    return $html;
}

function makeExtraChoreForm($username){
    $html = "<form action='do_chores.hpp method='post'>";
    $html .= "<input type='hidden' name='username' value='$username'/>";
    $html .= "<input type='hidden' name='time' value='extra'/>";
    $html .= "<input type='text' name='extra' value=''/><br>";
    $html .= "<input type='text' name='extra_time' value='5'/>";
    $html .= "<input type='submit' value='Add'/>";
    $html .= "</form>";
    return $html;
}

function getChores($date = FALSE){
    if($date === FALSE){
        $date = time();
    }

    $week = date('W',$date);
    $day_of_week = date('w',$date);
    $day_of_year = date('z',$date);
    $chore_days_of_year = $day_of_year - $week;

    $assignments = Array();

    $kids = Array( 'ryan','calvin','sophie','hannah' );
    $chores = Array( 'Garbage','Toys','Laundry','Dishes' );
    $sunday_chores = Array( 'Phone Call','Letter','Scriptures','Song' );

    if($day_of_week == 0){ // Sunday
        foreach($kids as $ki => $kid){
            $assignments[$kid] = $sunday_chores[($ki + $week) % 4];
        }
    }else{
        foreach($kids as $ki => $kid){
            $assignments[$kid] = $chores[($ki + $chore_days_of_year) %4];
        }
    }

    return $assignments;
}

$user_sessions = getSessions();

$usernames = shell_exec('getent passwd | tr ":" " " | awk "\$3 >= $(grep UID_MIN /etc/login.defs | cut -d " " -f 2) { print \$1 }" | sort| uniq|sed -e \'s/nobody//g\' | sort -u');
$usernames = split("\n",trim($usernames));
$usernames = array_map('trim',$usernames);
$usernames = array_filter($usernames);

$user_table_info = Array();

$time_left = time_left();
$todays_chore_status = todays_chore_status();
$todays_assignments = getChores();

foreach($usernames as $username){

    // Logged in state
    $user_table_info[$username]['loggedin'] = isset($user_sessions[$username]);

    // Active state
    $active = FALSE;
    if(isset($user_sessions[$username])){
        foreach($user_sessions[$username] as $session){
            if($session['Active'] == 'yes'){
                $active = TRUE;
                $cmd = "/usr/local/bin/check_xscreesaver.sh $username {$session['Display']}";
                $screensaver_on = trim(shell_exec($cmd));
                if($screensaver_on == 'true'){
                    $active = FALSE;
                }
            }
        }
    }
    $user_table_info[$username]['active'] = $active;


    // Face picture
    $homedir = trim(shell_exec("getent passwd '$username' | cut -d: -f6"));
    $face_file = "$homedir/.face";
    if(file_exists($face_file)){
        $face_data = file_get_contents($face_file);
        $user_table_info[$username]['face'] = "<img class='face' src='data:image/png;base64," . base64_encode($face_data) . "' title='$user'/>";
    }else{
        $user_table_info[$username]['face'] = "No picture found";
    }

    foreach($user_table_info as $username => $details){
        if(array_key_exists($username,$time_left)){
            $user_table_info[$username]['time_left'] = $time_left[$username]['minutes'];
        }else{
            $user_table_info[$username]['time_left'] = 0;
        }
    }

    foreach($user_table_info as $username => $details){
        if(!isset($todays_chore_status[$username])){
            if(isset($detail['morning']) && $detail['morning']){
                $user_table_info[$username]['morning'] = makeButtonForm($username,'morning',TRUE);
            }else{
                $user_table_info[$username]['morning'] = makeButtonForm($username,'morning',FALSE);
            }

            if(isset($detail['night']) && $detail['night']){
                $user_table_info[$username]['night'] = makeButtonForm($username,'night',TRUE);
            }else{
                $user_table_info[$username]['night'] = makeButtonForm($username,'night',FALSE);
            }

            if(isset($detail['extra']) && count($detail['extra'])){
                $html = "<table>";
                foreach($detail['extra'] as $extra){
                    $html .= "<tr><td>{$extra['extra']}</td><td>{$extra['extra_time']}</td></tr>";
                } 
                $html .= "</table>";
                $html .= makeExtraChoreForm($username);
                $user_table_info[$username]['extra'] = $html;
            }else{
                $user_table_info[$username]['extra'] = makeExtraChoreForm($username);
            }
        }else{
            $user_table_info[$username]['morning'] = makeButtonForm($username,'morning',FALSE);
            $user_table_info[$username]['night'] = makeButtonForm($username,'morning',FALSE);
            $user_table_info[$username]['extra'] = makeExtraChoreForm($username);
        }
    }

    if(isset($todays_assignments[$username])){
        $user_table_info[$username]['chore'] = $todays_assignments[$username];
    }else{
        $user_table_info[$username]['chore'] = '';
    }
}


$keys = Array(
    'face' => '',
    'chore' => 'Todays Chore',
    'loggedin' => 'Logged In?',
    'active' => 'Active?',
    'time_left' => 'Time Left',

    'morning'   => 'Morning Chores',
    'night'   => 'Night Chores',
    'extra'   => 'Extra Chores',

    'timetoday' => 'Time Used Today',
    'lock'      => 'Lock Session',
    'vnc'      => 'Start VNC Session',
);




?><!DOCTYPE HTML>
<html>
<head>
    <title>Chore Time!</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>Chore Time!</h1>
<table>
<?php

foreach($keys as $key => $label){
    print "<tr>";
    print "<th>$label</th>";
    foreach($user_table_info as $username => $userinfo){

        $class = ($userinfo['active'] ? 'active' : 'inactive');
        $class .= ' ' . ($userinfo['loggedin'] ? 'loggedin' : 'loggedout');

        if(isset($userinfo[$key])){
            $printme = $userinfo[$key];
            if(is_bool($printme)){
                $printme = ($printme ? 'Yes' : 'No');
            }
        }else{
            $printme = 'Coming Soon!';
        }
        print "<td class='$class'>$printme</td>";
    }
    print "</tr>";
}

?>
</table>
<script src='jquery.js'></script>
<script src='js.js'></script>
</body>
</html>
