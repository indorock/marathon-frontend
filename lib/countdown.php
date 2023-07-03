<?php
class Countdown {
    public $timeout = null;
    public $currentweek = 1;
    public $total_training_weeks = 16;
    public $training_plans = [];
    public $training_type = 'pfitz-55-18';
    public $trainingstart = null;
    public $raceday = null;
    public $daysleft = 0;
    public $left = '';
    public $left_plaintext = '';
    public $weeknodes = [];
    public $infonodes = [];
    public $alldaynodes = [];
    public $schedule_rootnode = null;
    public $weekstarts = 'mon';
    public $xpath_program = null;
    public $xpath_sitedata = null;
    public $now = null;

    public function __construct(){
        $training_filepath = realpath('./xml/programs');
        foreach(glob($training_filepath."/*.xml") as $xmlpath) {
            $filename = substr($xmlpath, strlen($training_filepath));
            $filename = substr($filename, 0, strpos($filename,'.xml'));
            $filename = ltrim($filename, '/');
            $this->training_plans[] = $filename;
        }

        $req_trainingplan = @strtolower($_GET['trainingplan']);
        if($req_trainingplan&& in_array($req_trainingplan, $this->training_plans)){
            setcookie('training_plan', $req_trainingplan, time() + (86400 * 30), "/");
            $this->training_type = $req_trainingplan;
        }elseif(isset($_COOKIE['training_plan']) && in_array($_COOKIE['training_plan'], $this->training_plans)){
            $this->training_type = $_COOKIE['training_plan'];
        }


        $this->xpath_program = new XPath_Query('xml/programs/'.$this->training_type.'.xml');
        $this->xpath_sitedata = new XPath_Query('xml/site_data.xml');
        $this->infonodes = $this->xpath_program->get_nodelist('//program/info/item');
        $this->schedule_rootnode = $this->xpath_program->get_node('//program/schedule');
        $this->weeknodes = $this->xpath_program->get_nodelist('//program/schedule/week');
        $this->alldaynodes = $this->xpath_program->get_nodelist('//program/schedule/week/day');
        $this->infonodes = $this->xpath_program->get_nodelist('//program/info/item');
        $this->total_training_weeks = $this->weeknodes->length;


        $formatter = new IntlDateFormatter("it_IT", IntlDateFormatter::SHORT, IntlDateFormatter::NONE);
        $unixtime=$formatter->parse(get_race_datetime(false));
        $this->raceday = new DateTime();
        $this->raceday->setTimestamp($unixtime);
        $this->now = new DateTime();
        $this->weeks = 0;
        $tminus = 'T-Minus ';
        $this->daynodes = null;


        $weekstarts = $this->xpath_program->get_node('//program/about')->getAttribute('weekstarts');
        if($weekstarts)
            $this->weekstarts = $weekstarts;

        $secondsleft = $this->raceday->getTimestamp() - $this->now->getTimestamp();

        $this->trainingstart = new DateTime();
        $this->trainingstart->setTimestamp($this->raceday->getTimestamp() - (($this->alldaynodes->length-1) * 24 * 3600));


        $trainingday = (int)($this->trainingstart->modify('00:00:00')->diff($this->now->modify('00:00:00'))->format('%r%a') + 1);
        if($trainingday < 0) $trainingday = 0;

        if($secondsleft > 0){
            $this->daysleft = ceil(($secondsleft/(60*60*24)));
            if($this->daysleft > 1){
                $this->daysleft = $this->now->modify('00:00:00')->diff($this->raceday->modify('00:00:00'))->format('%a');
                $this->timeout = 'null';
                if($this->daysleft > 7){
                    $weeksleft = floor($this->daysleft/7);
                    $days = $this->daysleft - ($weeksleft*7);
                    $this->left = '<span id="weeks">' . $weeksleft . '</span> Week<span id="weeks-plural">'.($weeksleft>1?'s':'').'</span>';
                    $this->left_plaintext = $tminus . $weeksleft . ' Week'.($weeksleft>1?'s':'');
                    if($days > 0){
                        $this->left .= ' <span class="amp">&amp;</span> <span id="days">' . $days . '</span> Day<span id="days-plural">'.($days>1?'s':'') . '</span>';
                        $this->left_plaintext .= ' & ' . $days . ' Day'.($days>1?'s':'');
                    }
                }else{
                    $this->left = $this->daysleft . ' Day'.($this->daysleft>1?'s':'');
                    $this->left_plaintext = $tminus . $this->daysleft . ' Day'.($this->daysleft>1?'s':'');
                }
            }else{
                $minutesleft = (int)($secondsleft/60);
                if($minutesleft >= 60){
                    $this->timeout = '15000';
                    $hours = (int)($minutesleft/60);
                    $minutes = $minutesleft - ($hours*60);
                    $this->left = '<span id="hours">'.$hours . '</span> Hour<span id="hours-plural">'.($hours>1?'s':'').'</span> <span class="amp">&amp;</span> <span id="minutes">' . $minutes . '</span> Minute<span id="minutes-plural">'.($minutes>1?'s':'').'</span>';
                    $this->left_plaintext = $tminus . $hours . ' Hour'.($hours>1?'s':'').' & ' . $minutes . '</span> Minute'.($minutes>1?'s':'');
                }else{
                    $this->timeout = '1000';
                    $seconds = $secondsleft - ($minutesleft*60);
                    $this->left = '<span id="minutes">'.$minutesleft . '</span> Minute<span id="minutes-plural">'.($minutesleft>1?'s':'').'</span> <span class="amp">&amp;</span> <span id="seconds">' . $seconds . '</span> Second<span id="seconds-plural">'.($seconds>1?'s':'').'<span>';
                    $this->left_plaintext = $tminus . $minutesleft . ' Minute'.($minutesleft>1?'s':'').' & ' . $seconds . ' Second'.($seconds>1?'s':'');
                }
            }
            $this->currentweek = ceil($trainingday/7);
        //echo "<br><br><br><br><br>DAY NODES:".$alldaynodes->length."<br>WEEKS: ".$weeknodes->length."<br>SECONDS TILL START:".($raceday->getTimestamp() - (($alldaynodes->length-1) * 24 * 3600))."<br>SECONDS ".$secondsleft."<br>WEEKSLEFT: ".$weeksleft."<br>DAYSLEFT ".$daysleft."<br>TRAINING START:".$trainingstart->format('Y-m-d')."<br>TRAININGDAY: ".$trainingday;
        }else{
            $this->left = "It's Race Day!!";
            $this->left_plaintext = "It's Race Day!!";
            $this->currentweek = $this->total_training_weeks;
        }
    }
}

