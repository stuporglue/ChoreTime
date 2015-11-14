<!DOCTYPE HTML>
<html>
<head>
    <title>Chore Time!</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="<?=$bodyclass?>">
<h1>Chore Time!</h1>
<table>
<?php

if(!isset($accounts_to_skip)){
    $accounts_to_skip = Array();
}

foreach($keys as $key => $label){
    print "<tr class='$key'>";
    print "<th>$label</th>";
    foreach($user_table_info as $username => $userinfo){

        if(in_array($username,$accounts_to_skip)){
            continue;
        }

        $class = ($userinfo['active'] ? 'active' : 'inactive');
        $class .= ' ' . ($userinfo['loggedin'] ? 'loggedin' : 'loggedout');

        if(isset($userinfo[$key])){
            $printme = $userinfo[$key];
            if(is_bool($printme)){
                $printme = ($printme ? 'Yes' : 'No');
            }
        }else if(is_null($userinfo[$key])){
            $printme = '--';
            $class .= ' empty';
        }else{
            $printme = 'Coming Soon!';
            $class .= ' empty';
        }
        if(preg_match('/^[A-Za-z0-9]*$/',$printme)){
            $extra_class = strtolower($printme);
        }else{
            $extra_class = "";
        }
        print "<td class='$class $username $key $extra_class' data-username='$username'>$printme</td>";
    }
    print "</tr>";
}

?>
</table>
<script src='jquery.js'></script>
<script src='js.js'></script>
</body>
</html>
