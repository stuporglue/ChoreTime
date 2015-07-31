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

$user_sessions = getSessions();

$usernames = shell_exec('getent passwd | tr ":" " " | awk "\$3 >= $(grep UID_MIN /etc/login.defs | cut -d " " -f 2) { print \$1 }" | sort| uniq|sed -e \'s/nobody//g\' | sort -u');
$usernames = split("\n",trim($usernames));
$usernames = array_map('trim',$usernames);
$usernames = array_filter($usernames);

$user_table_info = Array();

foreach($usernames as $username){

    // Logged in state
    $user_table_info[$username]['loggedin'] = isset($user_sessions[$username]);


    // Active state
    $active = FALSE;
    if(isset($user_sessions[$username])){
        foreach($user_sessions[$username] as $session){
            if($session['Active'] == 'yes'){
                $active = TRUE;
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

    $time_left = time_left();
    foreach($user_table_info as $username => $details){
        if(array_key_exists($username,$time_left)){
            $user_table_info[$username]['time_left'] = $time_left[$username]['minutes'];
        }else{
            $user_table_info[$username]['time_left'] = 0;
        }
    }
}

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

$keys = Array(
    'face' => '',
    'loggedin' => 'Logged In?',
    'active' => 'Active?',
    'time_left' => 'Time Left',
    'timetoday' => 'Time Used Today',
    'lock'      => 'Lock Session',
    'vnc'      => 'Start VNC Session',
);

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
</body>
</html>
