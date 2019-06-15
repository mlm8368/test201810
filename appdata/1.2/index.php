<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2013 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
header("Access-Control-Allow-Credentials: true");
if(!empty($_SERVER['HTTP_ORIGIN'])) header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
header("Access-Control-Allow-Headers: APPACCTOKEN,x-requested-with,content-type");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 86400");
if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit();

require '../common.inc.php';
require '../global.func.php';
header("Content-type:application/json; charset=utf-8");
require DT_ROOT.'/include/module.func.php';
// include load('wap.lang');
$wap_modules = array('member', 'company', 'extend', 'student', 'classes');
//
logs($_POST);
//
if(in_array($module, $wap_modules)) {
	if(in_array($action, array('category', 'area', 'ad'))) {
		include $action.'.inc.php';
	} else {
		include $module.'.inc.php';
	}
}else {
  if($action == 'checklogin'){
    $jsonarr = array();
    $jsonarr['status']=0;
    $jsonarr['mobile']=(string) $_mobile;
    if(!empty($_userid)) {
		$jsonarr['status']=1;
		$jsonarr['userid']=(int) $_userid;
		$db->query("UPDATE LOW_PRIORITY {$db->pre}member SET logintime='{$DT_TIME}' WHERE userid='$_userid'", 'UNBUFFERED');
	}
    jsonexit($jsonarr);
  }else if($action == 'errorlog'){
    $db->query("INSERT INTO {$DT_PRE}404 (url,refer,robot,username,ip,addtime) VALUES ('$url','$refer','','$_username','$DT_IP','$DT_TIME')");
    jsonexit(array('status'=>1));
  }else if($action=='bcebosFile'){
    $sql=array();
    $urls = explode('|',$_POST['urls']);
    foreach($urls as $url){
        $sql[] = "('{$url}','{$DT_TIME}','{$_username}')";
    }
    $db->query("INSERT INTO {$DT_PRE}delbosfile (url,addtime,username) VALUES ".implode(',',$sql));
    $jsonarr = array();
    $jsonarr['status']=1;
    jsonexit($jsonarr);
  }else if($action=='setbosacests'){
    $item='bos';
    $item_key='sts';
    $item_value=json_encode($_POST);
    //print_r($_POST,true);
    
    $db->query("delete from {$DT_PRE}setting where item='{$item}' and item_key='{$item_key}'");
    $db->query("INSERT INTO {$DT_PRE}setting (item,item_key,item_value) VALUES ('{$item}','{$item_key}','{$item_value}')");
    jsonexit(array('status'=>1));
  }
}
jsonexit("{\"state\":\"index-err\"}");
?>