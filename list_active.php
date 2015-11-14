#!/usr/bin/env php
<?php

require_once(__DIR__ . '/app/lib.inc');
// Cron this once a minute. It will subtract one minute from whoever is Active on the computer

$active = getActive();

foreach($active as $username => $sessions){
    foreach($sessions as $session){
        echo "{$session['USER']} {$session['Display']}\n";
    }
}
