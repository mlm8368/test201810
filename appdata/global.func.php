<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2013 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
/*
* 功能：打印$_GET['jsoncallback']函数 + JSON格式数据并结束程序
* 参数：JSON字符串如{'state':'massges'}或PHP数组，如是数据则会转为JSON字符串
* 返回值：无
*/
function jsonexit($string) {
	//global $CFG;
	
	logs($string);
  if(is_array($string)) {
    $string = json_encode($string);
  } 
  
	exit($string);
}

function logs($msg) {
	if(is_array($msg)) $msg = print_r($msg,true);
    error_log($msg);
}

function area_join($areaid) {
	global $AREA;
	$areaid = intval($areaid);
	$AREA or $AREA = cache_read('area.php');
	$arrparentid = $AREA[$areaid]['arrparentid'] ? explode(',', $AREA[$areaid]['arrparentid']) : array();
	$r = array();
	foreach($arrparentid as $one){
		if($one) $r[] = ''.$one;
	}
	$r[] = ''.$areaid;
	return $r;
}
?>