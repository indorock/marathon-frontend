<?php
require('lib/xpath.query.php');
require('lib/site_data.php');
require('lib/countdown.php');
$countdown = new Countdown();
$now = new DateTime();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
    <title>Countdown to <?php echo get_race_name(); ?></title>
<?php if($countdown->timeout == 'null'){ ?>
	<meta http-equiv="refresh" content="3600">
<?php } ?>
    <meta property="og:type" content="website"/>
	<meta property="og:title" content="<?php echo $countdown->left_plaintext ?>"/>
    <meta property="og:url" content="<?php get_site_url() ?>"/>
    <meta property="og:site_name" content="Countdown to <?php get_race_name() ?>"/>
	<meta property="og:image" content="<?php get_site_url() ?>/images/logo_<?php get_race_name_safe() ?>_fb.png"/>
	<meta property="og:description" content="My countdown and training program in preparation for <?php get_race_name() ?>, <?php get_race_date() ?>"/>

	<link href='http://fonts.googleapis.com/css?family=Bowlby+One+SC|Oxygen:400,300|Courgette' rel='stylesheet' type='text/css'>
	<link type="text/css" rel="stylesheet" href="/css/global.css" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script type="text/javascript">var timeout = <?php echo $countdown->timeout ?>;</script>
	<script type="text/javascript" src="/js/global.js"></script>
	<script>
		window.currentweek = <?php echo $countdown->currentweek ?>;
		window.totalweeks = <?php echo $countdown->total_training_weeks ?>;
	</script>
</head>
<body>
<div id="maincontainer">
	<div id="training-chooser">
		<select id="trainingplan-select" name="trainingplan">
			<?php foreach($countdown->training_plans as $plan){
				$selected = $plan == $countdown->training_type;
				?>
				<option value="<?php echo $plan?>"<?php echo ($selected?' selected':'') ?>><?php echo ucfirst($plan) ?></option>
			<?php } ?>
		</select>
		<a href="./calendar.php">Calendar View</a>
	</div>
	<div id="logo">
		<img src="/images/logo_<?php get_race_name_safe() ?>.png" /><br>
		Raceday:<br><strong><?php echo $countdown->raceday->format('d M, Y') ?></strong>
	</div>

	<div id="clock">
		<span class="tminus">T-Minus</span>
		<h1 id="counter"><?php echo $countdown->left ?></h1>
	</div>
<?php
if($countdown->currentweek){
?>
	<div id="training-container">
		<div class="paging paging-back">&lt;</div>
		<div id="training-viewport">
			<div id="training-wrapper">
<?php
$weeknumber = 0;
foreach($countdown->weeknodes as $week) {
		$weeknumber++;
		$daynumber = 0;
		$daynodes = $countdown->xpath_query->get_nodelist('day', $week);
		$weekly_distance = 0;

		if ($daynodes && $daynodes->length) {


?>
				<div class="training clearfix" data-index="<?php echo $weeknumber ?>">
					<div class="training-inner clearfix">
						<div id="schedule" class="clearfix">
							<h2>Training Program - Week <?php echo $weeknumber ?></h2>
<?php
				foreach ($daynodes as $day) {
					$daynumber++;
					$dayname = date('D', strtotime($countdown->weekstarts." +".($daynumber-1)." days"));
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
					if ($weeknumber == $countdown->currentweek && strtolower($now->format('D')) == strtolower($dayname))
						$todayclass = ' today';
?>
								<div class="trainingday clearfix <?php echo $activitytype ?><?php echo $todayclass ?>">
									<span class="dayname"><?php echo $dayname; ?></span>
									<span class="activity" rel="<?php echo $activitytype ?>"><?php echo $activity ?></span>
								</div>
<?php
				}
?>
								<div class="trainingday total"><strong>TOTAL WEEKLY DISTANCE: <?php echo $weekly_distance ?>KM</strong></div>
						</div>

						<div id="infotexts_viewport" class="clearfix">
							<div id="infotexts">
<?php
					foreach ($countdown->infonodes as $item) {
?>
								<div id="<?php echo $item->getAttribute('name') ?>-info" class="info nodisplay"><?php echo $item->nodeValue; ?></div>

<?php
					}
?>
							</div>
						</div>
					</div>
				</div>
<?php
		}
	}
?>
			</div>
		</div>
		<div class="paging paging-next">&gt;</div>
	</div>
	<div class="credits">Training program + definitions sourced from <a href="<?php echo $countdown->xpath_query->get_node('//program/about/url')->nodeValue ?>" target="_blank"><?php echo $countdown->xpath_query->get_node('//program/about/name')->nodeValue ?></a><br> (adjustments made for metric system), &copy; <?php echo date("Y"); ?> Hal Higdon. All rights reserved.</div>
<?php
}else{
?>
<div id="schedule">Training hasn't started yet!<br>Just another <strong><?php echo $countdown->daysleft-$countdown->alldaynodes->length+1 ?> days left</strong> until training starts...</div>
<?php
}
?>
<div class="footer">Website &copy; <?php echo date("Y"); ?> Mark Mulder</div>
</div>
</body>
</html>