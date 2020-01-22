<?php

$training_filepath = realpath('./xml/programs');
$training_plans = [];
foreach(glob($training_filepath."/*.xml") as $xmlpath) {
    $filename = substr($xmlpath, strlen($training_filepath));
    $filename = substr($filename, 0, strpos($filename,'.xml'));
    $filename = ltrim($filename, '/');
    $training_plans[] = $filename;
}


$training_type = 'runyourbq-16';
$req_trainingplan = @strtolower($_GET['trainingplan']);
if($req_trainingplan&& in_array($req_trainingplan, $training_plans)){
    setcookie('training_plan', $req_trainingplan, time() + (86400 * 30), "/");
    $training_type = $req_trainingplan;
}elseif(isset($_COOKIE['training_plan']) && in_array($_COOKIE['training_plan'], $training_plans)){
    $training_type = $_COOKIE['training_plan'];
}


$xpath_query = new XPath_Query('xml/programs/'.$training_type.'.xml');
$infonodes = $xpath_query->get_nodelist('//program/info/item');
$schedule_rootnode = $xpath_query->get_node('//program/schedule');
$weeknodes = $xpath_query->get_nodelist('//program/schedule/week');
$alldaynodes = $xpath_query->get_nodelist('//program/schedule/week/day');
$infonodes = $xpath_query->get_nodelist('//program/info/item');
$total_training_weeks = $weeknodes->length;

$raceday = new DateTime('2018-09-16 10:00:00');
$now = new DateTime();
$timeout = 'null';
$left = '';
$weeks = 0;
$left_plaintext = '';
$tminus = 'T-Minus ';
$daynodes = null;


$weekstarts = $xpath_query->get_node('//program/about')->getAttribute('weekstarts');
if(!$weekstarts)
    $weekstarts = 'mon';

$secondsleft = $raceday->getTimestamp() - $now->getTimestamp();

$trainingstart = new DateTime();
$trainingstart->setTimestamp($raceday->getTimestamp() - (($alldaynodes->length-1) * 24 * 3600));


$trainingday = $trainingstart->modify('00:00:00')->diff($now->modify('00:00:00'))->format('%a') + 1;

if($secondsleft > 0){
    $daysleft = ceil(($secondsleft/(60*60*24)));
    if($daysleft > 1){
        $daysleft = $now->modify('00:00:00')->diff($raceday->modify('00:00:00'))->format('%a');
        $timeout = 'null';
        if($daysleft > 7){
            $weeksleft = floor($daysleft/7);
            $days = $daysleft - ($weeksleft*7);
            $left = '<span id="weeks">' . $weeksleft . '</span> Week<span id="weeks-plural">'.($weeksleft>1?'s':'').'</span>';
            $left_plaintext = $tminus . $weeksleft . ' Week'.($weeksleft>1?'s':'');
            if($days > 0){
                $left .= ' <span class="amp">&amp;</span> <span id="days">' . $days . '</span> Day<span id="days-plural">'.($days>1?'s':'') . '</span>';
                $left_plaintext .= ' & ' . $days . ' Day'.($days>1?'s':'');
            }
        }else{
            $left = $daysleft . ' Day'.($daysleft>1?'s':'');
            $left_plaintext = $tminus . $daysleft . ' Day'.($daysleft>1?'s':'');
        }
    }else{
        $minutesleft = (int)($secondsleft/60);
        if($minutesleft >= 60){
            $timeout = '15000';
            $hours = (int)($minutesleft/60);
            $minutes = $minutesleft - ($hours*60);
            $left = '<span id="hours">'.$hours . '</span> Hour<span id="hours-plural">'.($hours>1?'s':'').'</span> <span class="amp">&amp;</span> <span id="minutes">' . $minutes . '</span> Minute<span id="minutes-plural">'.($minutes>1?'s':'').'</span>';
            $left_plaintext = $tminus . $hours . ' Hour'.($hours>1?'s':'').' & ' . $minutes . '</span> Minute'.($minutes>1?'s':'');
        }else{
            $timeout = '1000';
            $seconds = $secondsleft - ($minutesleft*60);
            $left = '<span id="minutes">'.$minutesleft . '</span> Minute<span id="minutes-plural">'.($minutesleft>1?'s':'').'</span> <span class="amp">&amp;</span> <span id="seconds">' . $seconds . '</span> Second<span id="seconds-plural">'.($seconds>1?'s':'').'<span>';
            $left_plaintext = $tminus . $minutesleft . ' Minute'.($minutesleft>1?'s':'').' & ' . $seconds . ' Second'.($seconds>1?'s':'');
        }
    }
    $currentweek = ceil($trainingday/7);
}else{
    $left = "It's Race Day!!";
    $left_plaintext = "It's Race Day!!";
    $currentweek = $total_training_weeks;
}
