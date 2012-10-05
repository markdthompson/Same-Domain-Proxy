<?php

/**
* proxy.php 
* @description - an api proxy script to overcome cross domain issues with api's 
* that don't support json with callbacks for json-p
*
* @author - mark@smithandthompson.net
*
**/

$proxy = '';
if (isset($_REQUEST['proxy'])){
	$proxy = filter_var($_REQUEST['proxy'], FILTER_SANITIZE_STRING);
}

switch($proxy){
	case 'geocode':
		/* Yahoo Geocode service */
		$address = '';
		$city = '';
		$state = '';
		$country = '';
		$flags = '';
		$appid = '';
		
		if (isset($_GET['address'])) $address = filter_var($_GET['address'], FILTER_SANITIZE_STRING);
		if (isset($_GET['city'])) $city = filter_var($_GET['city'], FILTER_SANITIZE_STRING);
		if (isset($_GET['state'])) $state = filter_var($_GET['state'], FILTER_SANITIZE_STRING);
		if (isset($_GET['country'])) $country = filter_var($_GET['country'], FILTER_SANITIZE_STRING);
		if (isset($_GET['flags'])) $flags = filter_var($_GET['flags'], FILTER_SANITIZE_STRING);
		if (isset($_GET['appid'])) $appid = filter_var($_GET['appid'], FILTER_SANITIZE_STRING);
		
		echo geocode_yahoo($address,$city,$state,$country,$flags,$appid);
	break;
	
	case 'iplatlon':
		/* api.hostip.info */
		//get the clients ip
		$ip = getRealIpAddr();
		
		// 
		$url = 'http://api.hostip.info/get_html.php?ip='.$ip.'&position=true';
		$result = file_get_contents($url);

		$result_arr = preg_split( '/\r\n|\r|\n/', $result );
		$lat = explode(':', $result_arr[3]);
		$lon = explode(':', $result_arr[4]);
		$data = array($lat[1],$lon[1]);
		if ($data != '') {
			echo json_encode($data);
		}
		echo false;
	break;
	
	case 'daylength':
		/* Farmsense Daylength service */
		$lat = '';
		$lon = '';
		$tz = '';
		$d = array();
		
		if (isset($_POST['lat'])) $lat = filter_var($_POST['lat'], FILTER_SANITIZE_STRING);
		if (isset($_POST['lon'])) $lon = filter_var($_POST['lon'], FILTER_SANITIZE_STRING);
		if (isset($_POST['tz'])) $tz = filter_var($_POST['tz'], FILTER_SANITIZE_STRING);
		if (isset($_POST['d'])) $d = $_POST['d'];
		

		$postdata = http_build_query(
				array(
					'lat' => $lat,
					'lon' => $lon,
					'tz' => $tz,
					'd' => $d
				)
		);

		$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
		);

		$context  = stream_context_create($opts);
		$result = file_get_contents('http://farmsense-prod.apigee.net/v1/daylengths/', true, $context);

		echo $result;

	break;
}

function geocode_yahoo($address,$city,$state,$country,$flags,$appid) {
	$address = array($address, $city, $state, $country);
	$address = array_filter($address);
	$address = urlencode(implode(', ', $address));
 
	$appid = 'appid';
 
	$url = 'http://where.yahooapis.com/geocode?location='.$address.'&flags='.$flags.'&appid='.$appid;
	$data = file_get_contents($url);
	if ($data != '') {
		return $data;
	}
	return false;
}

function getRealIpAddr(){ 
	$ip = '';
	//check ip from share internet 
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) { 
		$ip=$_SERVER['HTTP_CLIENT_IP']; 
	//to check ip is pass from proxy
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR']; 
	} else { 
		$ip=$_SERVER['REMOTE_ADDR']; 
	} return $ip; 
} 