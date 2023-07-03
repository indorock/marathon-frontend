<?php

$xpath_query = new XPath_Query('xml/site_data.xml');
$data_groups = $xpath_query->get_nodelist('//data/group[@type!="content"]');

global $sitedata;
$sitedata = array();

foreach($data_groups as $group):

    $type = $group->getAttribute('type');
    $data_items = $xpath_query->get_nodelist('./item', $group);

    foreach($data_items as $item):
        $sitedata[$type][$item->getAttribute('name')] = $item->nodeValue;
    endforeach;

endforeach;

$sections = $xpath_query->get_nodelist('//data/group[@type="content"]/section');
$sitedata['content'] = array();

foreach($sections as $section):

    $name = $section->getAttribute('name');
    $data_items = $xpath_query->get_nodelist('./item', $section);

    foreach($data_items as $item):
        $sitedata['content'][$name][$item->getAttribute('name')] = $item->nodeValue;
    endforeach;

endforeach;


// HELPER FUNCTIONS
function get_metadata($name, $echo = true){
    return get_data_item($name, null, 'metadata', $echo);
}

function get_race_name($echo = true){
    return get_data_item('race_name', null, 'global', $echo);
}

function get_race_name_safe($echo = true){
    $ret = get_data_item('race_name', null, 'global', false);
    $ret = preg_replace('/[ ,\':]/', '_', strtolower($ret));
    if($echo) echo $ret; else return $ret;
}

function get_race_datetime($echo = true){
    $date_regex  = '/^(0?[1-9]|[12][0-9]|3[01])[\-\/](0[1-9]|1[012])[\-\/](20)\d\d$/';
    if(array_key_exists('raceday', $_GET)) {
        $req_raceday = @strtolower(urldecode($_GET['raceday']));
        if($req_raceday && preg_match($date_regex, $req_raceday)) {
            setcookie('raceday', $req_raceday, time()+(86400*30), "/");
            return $req_raceday;
        }
    }elseif(isset($_COOKIE['raceday']) && preg_match($date_regex, $_COOKIE['raceday'])){
        return urldecode($_COOKIE['raceday']);
    }

    return get_data_item('race_datetime', null, 'global', $echo);
}

function get_race_date($echo = true){
    $datetime = new DateTime(get_race_datetime(false));
    $ret = $datetime->format('d M, Y');
    if($echo) echo $ret; else return $ret;
}

function get_site_url($echo = true){
    return get_data_item('url', null, 'global', $echo);
}

function get_title($echo = true, $section = null){
    return get_content('title', $echo, $section);
}

function get_body($echo = true, $section = null){
    return get_content('body', $echo, $section);
}

function get_season_name($season = null, $echo = true){
    if(!$season)
        $season = $_SESSION['season'];
    return get_data_item($season, null,'seasons', $echo, 'ucwords');
}

function get_image($echo = true, $section = null){
    $link = get_image_link(false, $section);
    $out = '<img src="'.$link.'" alt="">';
    if($echo) echo $out;
    else return $out;
}

function get_image_link($echo = true, $section = null){
    return get_content('image', $echo, $section);
}

function get_content($name, $echo = true, $s = null){
    global $section;
    if($s==null) $s = $section;
    return get_data_item($name, $s, 'content', $echo);
}

function get_data_item($name, $section, $type, $echo = true, $trans_func=null){
    global $sitedata;
    $out = '';
    if($sitedata[$type]){
        if(array_key_exists($section, $sitedata[$type])) {
            $out = trim(@$sitedata[$type][$section][$name]);
        }elseif(array_key_exists($name, $sitedata[$type])) {
            $out = trim($sitedata[$type][$name]);
        }
    }
    if($trans_func)
        $out = call_user_func($trans_func, $out);
    if($echo) echo ($out);
    else return ($out);
}

function is_logged_in(){
    $logged_in = false;

    if(isset($_SESSION['user']) && isset($_SESSION['key'])){
        if($_SESSION['key'] == get_key())
            $logged_in = true;
    }
    return $logged_in;
}

function get_key(){
    return hash('sha256', ip2long($_SERVER['REMOTE_ADDR']));
}

function hash_password($plaintext){
    return hash('sha256',trim($plaintext) . '5tand4rdD3sign!');
}
