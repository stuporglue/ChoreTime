<?php

require_once(__DIR__ . '/lib.inc');

$user_sessions = getSessions();

if(isset($user_sessions[$_GET['user']])){
    foreach($user_sessions[$_GET['user']] as $session){
        if($session['Active'] == 'yes'){
            shell_exec("/usr/local/bin/lock_screen.sh {$session['USER']} {$session['Display']}");
        }
    }
}
