<?php
require_once(__DIR__ . '/lib.inc');

$user_sessions = getSessions();

$usernames = shell_exec('getent passwd | tr ":" " " | awk "\$3 >= $(grep UID_MIN /etc/login.defs | cut -d " " -f 2) { print \$1 }" | sort| uniq|sed -e \'s/nobody//g\' | sort -u');
$usernames = split("\n",trim($usernames));
$usernames = array_map('trim',$usernames);
$usernames = array_filter($usernames);

$user_table_info = Array();

$time_left = time_left();
$allowance = allowance();
$todays_chore_status = todays_chore_status();
$todays_assignments = getChores();
$this_weeks_fhe = fheAssignments();

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

    // computer time left
    foreach($user_table_info as $username => $details){
        if(array_key_exists($username,$time_left)){
            $user_table_info[$username]['time_left'] = $time_left[$username]['minutes'];
        }else{
            $user_table_info[$username]['time_left'] = 0;
        }
    }

    // allowance status
    foreach($user_table_info as $username => $details){
        if(array_key_exists($username,$allowance)){
            $user_table_info[$username]['spending'] = $allowance[$username]['spending'];
            $user_table_info[$username]['tithing'] = $allowance[$username]['tithing'];
            $user_table_info[$username]['savings'] = $allowance[$username]['savings'];
        }else{
            $user_table_info[$username]['spending'] = '0.00';
            $user_table_info[$username]['tithing'] = '0.00';
            $user_table_info[$username]['savings'] = '0.00';
        }
    }

    // which chores are done / chore forms
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

            $user_table_info[$username]['moneyform'] = makeMoneyChangeForm($username);
        }else{
            $user_table_info[$username]['morning'] = makeButtonForm($username,'morning',$todays_chore_status[$username]['morning']);
            $user_table_info[$username]['night'] = makeButtonForm($username,'night',$todays_chore_status[$username]['night']);
            $user_table_info[$username]['extra'] = makeExtraChoreForm($username);
            $user_table_info[$username]['moneyform'] = makeMoneyChangeForm($username);
        }
    }

    // Todays chores
    if(isset($todays_assignments[$username])){
        $user_table_info[$username]['chore'] = $todays_assignments[$username];
    }else{
        $user_table_info[$username]['chore'] = '';
    }

    // Lock button
    $user_table_info[$username]['lock'] = "<button class='lockbutton'>Lock</button>";

    // FHE Assignments
    $user_table_info[$username]['fhe'] = $this_weeks_fhe[$username];
}


