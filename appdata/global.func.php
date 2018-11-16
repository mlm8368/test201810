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
	global $CFG;
	
	if(is_array($string)) {
    //error_log(print_r($string,true));
    $string = json_encode($string);
  } else {
    //error_log($string);
  }
  
	exit($string);
}

function logs($msg) {
  //error_log($msg);
}

?>