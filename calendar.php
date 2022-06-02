<?php
require('lib/xpath.query.php');
require('lib/site_data.php');
require('lib/countdown.php');
$countdown = new Countdown();
$base_path = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'calendar.php'));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
    <title>Countdown to <?php echo get_race_name(); ?></title>
    <?php if($countdown->timeout == 'null'){ ?>
        <meta http-equiv="refresh" content="3600">
    <?php } ?>
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?php echo $countdown->left_plaintext ?>" />
    <meta property="og:url" content="<?php get_site_url() ?>"/>
    <meta property="og:site_name" content="Countdown to <?php get_race_name() ?>"/>
    <meta property="og:image" content="<?php get_site_url() ?>/images/logo_<?php get_race_name_safe() ?>_fb.png"/>
    <meta property="og:description" content="My countdown and training program in preparation for <?php get_race_name() ?>, <?php get_race_date() ?>"/>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/js/global.js"></script>

    <link href='http://fonts.googleapis.com/css?family=Bowlby+One+SC|Oxygen:400,300|Courgette' rel='stylesheet' type='text/css'>
    <link type="text/css" rel="stylesheet" href="/css/global.css?<?php echo hash_file('md5', $base_path.'/css/global.css') ?>" />
    <link type="text/css" rel="stylesheet" media="print" href="/css/print.css" />

    <style type="text/css" media="print">
        body.calendar #calendar-container table td {
            font-size: <?php echo 130/$countdown->weeknodes->length ?>pt;
            line-height: <?php echo 130/$countdown->weeknodes->length + 0.8?>pt;
        }
    </style>
</head>
<body class="calendar">
<div id="maincontainer">
    <div id="training-chooser">
        <select id="trainingplan-select" name="trainingplan">
            <?php foreach($countdown->training_plans as $plan){
                $selected = $plan == $countdown->training_type;
                ?>
                <option value="<?php echo $plan?>"<?php echo ($selected?' selected':'') ?>><?php echo ucfirst($plan) ?></option>
            <?php } ?>
        </select>
    </div>
    <div id="logo"><a href="/"><img src="/images/logo_<?php get_race_name_safe() ?>.png" /></a></div>
    <div id="calendar-container">
        <table class="training-calendar" id="training-calendar-<?php echo $countdown->training_type ?>">
            <tr>
                <th class="td-week">Week</th>
                <th class="td-dates">Dates</th>
<?php
            for($x=1;$x<=7;$x++) {
                $dayname = date('D', strtotime($countdown->weekstarts . " +" . ($x - 1) . " days"));
?>
                <th class="trainingday <?php echo ($dayname=='Fri'?'td-restday':'td-trainingday') ?>"><?php echo $dayname ?></th>
<?php
            }
?>
                <th>Total KMs</th>
            </tr>
<?php
                $weeknumber = 0;
                $weekbegin = $countdown->trainingstart;
                $minheight = 59 / $countdown->weeknodes->length;
                foreach($countdown->weeknodes as $week) {

                    $weeknumber++;
                    $weekend = clone $weekbegin;
                    $weekend->modify('+6 days');
                    $daynumber = 0;
                    $daynodes = $countdown->xpath_query->get_nodelist('day', $week);
                    $weekly_distance = 0;

                    if ($daynodes && $daynodes->length) {
?>
            <tr>
                <td class="td-week"><?php echo $weeknumber ?></td>
                <td class="td-dates"><?php echo $weekbegin->format('d/m') ?><br>-<br><?php echo $weekend->format('d/m') ?></td>
<?php
                        foreach ($daynodes as $day) {
                            $daynumber++;
                            $dayname = date('D', strtotime($countdown->weekstarts . " +" . ($daynumber - 1) . " days"));
                            $activity = $day->nodeValue;
                            if(preg_match('/([0-9]+)K.+/', $activity, $distance_matches)) {
                                $weekly_distance += (int)$distance_matches[1];
                            }elseif(preg_match('/.*half marathon.*/i', $activity, $distance_matches)) {
                                $weekly_distance += 21;
                            }elseif(preg_match('/.*marathon.*/i', $activity, $distance_matches)){
                                $weekly_distance += 42;
                            }elseif(preg_match('/.*race day.*/i', $activity, $distance_matches)){
                                $weekly_distance += 42;
                            }
                            $activitytype = $day->getAttribute('type');
                            $todayclass = '';
                            if ($weeknumber == $countdown->currentweek && strtolower($countdown->now->format('D')) == strtolower($dayname))
                                $todayclass = ' today';
                            ?>
                <td class="trainingday <?php echo ($dayname=='Fri'?'td-restday':'td-trainingday') ?> <?php echo $activitytype ?><?php echo $todayclass ?>" style="height:<?php echo $minheight ?>vw">
                   <?php echo $activity ?>
                </td>
<?php
                        }
                        for($x=$daynumber+1;$x<=7;$x++){
?>
                <td></td>
<?php
                        }
?>
                <td class="td-total"><strong><?php echo $weekly_distance ?></strong></td>
        </tr>

<?php
                    }
                    $weekbegin = $countdown->trainingstart->modify('+1 weeks');
                }
?>
        </table>
    </div>
    <div class="credits">Training program + definitions sourced from <a href="<?php echo $countdown->xpath_query->get_node('//program/about/url')->nodeValue ?>" target="_blank"><?php echo $countdown->xpath_query->get_node('//program/about/name')->nodeValue ?></a><br> (adjustments made for metric system), &copy; <?php echo date("Y"); ?> Hal Higdon. All rights reserved.</div>
</div>
</body>
</html>