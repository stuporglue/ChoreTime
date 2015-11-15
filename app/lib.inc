<?php

require_once('db.php');

function getActive(){
    $active = Array();
    $user_sessions = getSessions();
    foreach($user_sessions as $user => $sessions){
        foreach($sessions as $session){
            if($session['Active'] == 'yes'){
                $active[$user][] = $session;
            }
        }
    }
    return array_unique($active);
}

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

        $double_check = trim(shell_exec("/usr/local/bin/check_xscreensaver.sh {$sessions[$k]['USER']} {$sessions[$k]['Display']}"));


        if($double_check == "true"){
            $sessions[$k]['Active'] = 'no';
        }

    }

    $sessions_by_user = Array();
    foreach($sessions as $session){
        $sessions_by_user[$session['USER']][] = $session;
    }

    return $sessions_by_user;
}

function makeButtonForm($username,$time,$done){
    $html = "<form action='chores.php' method='post'>";
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
    $html = "<form action='chores.php method='post'>";
    $html .= "<input type='hidden' name='username' value='$username'/>";
    $html .= "<input type='hidden' name='time' value='extra'/>";
    $html .= "<input type='text' name='extra' value='' placeholder='What did they do'/><br>";
    $html .= "<input type='text' name='extra_time' value='' placeholder='minutes'/>";
    $html .= "<input type='submit' value='Add'/>";
    $html .= "</form>";
    return $html;
}

function makeMoneyChangeForm($username){
    $html = "<form action='chores.php method='post'>";
    $html .= "<input type='hidden' name='username' value='$username'/>";
    $html .= "<input type='hidden' name='money' value='extra'/>";
    $html .= "<select name='account'>";
        $html .= "<option value='all'>Adjust all 3</option>";
        $html .= "<option value='spending'>Adjust Spending</option>";
        $html .= "<option value='tithing'>Adjust Tithing</option>";
        $html .= "<option value='savings'>Adjust Savings</option>";
    $html .= "</select>";
    $html .= "<input type='text' name='extra' value='' placeholder='What did they do?'/><br>";
    $html .= "<input type='text' name='extra_money' value='' placeholder='Amount. No $ sign.'/>";
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
    $sunday_chores = Array( 'PhoneCall','Letter','Scriptures','Song' );

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

function fheAssignments(){
    $week = date('W',strtotime('yesterday')); // I want these to start showing up on Sundays, but W starts on Monday
    $people = Array( 'ryan','calvin','sophie','hannah','stuporglue','mamallama');
    $jobs = Array('Run The Show','Hymns','Lesson or Activity','Prayers','Treat','Scripture');

    $assignments = Array();

    foreach($people as $i => $name){
        $assignments[$name] = $jobs[($i + $week) % 6];
    }

    return $assignments;
}
