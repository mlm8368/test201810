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
logs(print_r($_POST, true));
//
if($_userid>0){
  $db->query("UPDATE LOW_PRIORITY {$db->pre}member SET logintime='{$DT_TIME}' WHERE userid='$_userid'", 'UNBUFFERED');
}
//
if(in_array($module, $wap_modules)) {
	if(in_array($action, array('category', 'area', 'ad'))) {
		include $action.'.inc.php';
	} else {
		include $module.'.inc.php';
	}
}else {
  if($action == 'errorlog'){
    $db->query("INSERT INTO {$DT_PRE}404 (url,refer,robot,username,ip,addtime) VALUES ('$url','$refer','','$_username','$DT_IP','$DT_TIME')");
    jsonexit(array('status'=>1));
  }
	if($action=='home'){
		$jsonarr = array();
		$jsonarr['times']=time();
		$jsonarr['slide']=array();
        $jsonarr['sellpic']=array('s'=>'../../static/images/tmp/muwu.jpg','m'=>'../../static/images/tmp/shuijiao.jpg','b'=>'../../static/images/tmp/yuantiao.jpg');
        $jsonarr['news']=array();
        $jsonarr['dogbreed']='';
        $jsonarr['dogvideo']='';

		if(true){
			require DT_ROOT.'/module/extend/ad.class.php';
			$do = new ad();
			$condition = 'pid=27 and status=3';
			$order = 'listorder ASC';
			$list = $do->get_list($condition, $order);

			foreach($list as $a){
				$tmp=array('id'=>$a['aid'],'img'=>$a['image_src'],'title'=>$a['title']);
				if($a['note']=='json' && !empty($a['image_url'])){
					$tmp2=json_decode($a['image_url'],true);
					$tmp = array_merge($tmp,$tmp2);
				}
				$jsonarr['slide'][]=$tmp;
			}
		}
    //sell
    $result = $db->query("(SELECT catid,thumb FROM quan_sell_5 WHERE bigcatid='1' AND `status`='3' ORDER BY itemid DESC LIMIT 1) UNION (SELECT catid,thumb FROM quan_sell_5 WHERE bigcatid='4' AND `status`='3' ORDER BY itemid DESC LIMIT 1) UNION (SELECT catid,thumb FROM quan_sell_5 WHERE bigcatid='5' AND `status`='3' ORDER BY itemid DESC LIMIT 1)");
	while($r = $db->fetch_array($result)) {
        if(!empty($r['thumb'])) {
            if($r['catid'] == '1') $jsonarr['sellpic']['s'] = $r['thumb'];
            else if($r['catid'] == '4') $jsonarr['sellpic']['m'] = $r['thumb'];
            else if($r['catid'] == '5') $jsonarr['sellpic']['b'] = $r['thumb'];
        }
    }
    //news
    $addtime=$DT_TIME-86400;
    $addtime=0;
    $w=date('w');
    if($w==0||$w==1||$w==6) $addtime=$DT_TIME-86400*7;
		$tags=tag("moduleid=21&condition=status=3 and catid != 83&pagesize=10&order=hits desc&template=null",3600);
		if($tags){
            $todayTime = strtotime(date('Y-m-d').' 00:00:00');
			foreach($tags as $t){
				$tmp=array();
				$tmp['id']=$t['itemid'];
				$tmp['title']=$t['title'];
                if($t['addtime'] > $todayTime)
                  $tmp['times'] = date('H:i', $t['addtime']);
                else 
                  $tmp['times'] = date('m-d', $t['addtime']);
				$tmp['imgmode']=0;
				$jsonarr['news'][]=$tmp;
			}
		}

    //dogbreed
		$tags=tag("moduleid=21&condition=status=3 and catid=67 and level=3&pagesize=1&order=hits desc&template=null",3600*20);
		if($tags){
			foreach($tags as $t){
				$tmp=array();
				$tmp['id']=$t['itemid'];
				$tmp['title']=$t['title'];
                $tmp['img']=$t['thumb'];
				$tmp['description']=$t['introduce'];
				$tmp['tag']=$t['tag'];
				$jsonarr['dogbreed']=$tmp;
			}
		}

		//dogvideo
		$tags=tag("moduleid=21&condition=status=3 and catid=83&pagesize=1&order=itemid desc&template=null",3600);
		if($tags){
			foreach($tags as $t){
				$tmp=array();
				$tmp['id']=$t['itemid'];
				$tmp['title']=$t['title'];
                $tmp['img']=$t['thumb'];
				$jsonarr['dogvideo']=$tmp;
			}
		}

		jsonexit($jsonarr);
	}else if($action=='activeclient'){
		if($op=='active'){
			$db->query("INSERT INTO {$DT_PRE}activeclient (username,device,version,devicetype,os,imei,mac,addtime) VALUES ('$_username','$device','$version','$devicetype','$os', '$imei','$mac','$DT_TIME')");
		}else if($op=='appstatistics'){
			$db->query("INSERT INTO {$DT_PRE}appstatistics (type,userid,username,imei,addtime) VALUES ('$type','$_userid','$_username','$imei','$DT_TIME')");
		}
	}else if($action=='upgrade'){
		$jsonarr = array();
		$jsonarr['status']=0;
    
    //$version=str_replace('.','',$version);
    $version=transVersion($version);
		if($device=='android'){
      //$nowVersion=str_replace('.','','1.1.89');///android最新版本
      $nowVersion=transVersion('1.1.28');
			if($version<$nowVersion){
				$jsonarr['status']=1;
				$jsonarr['version']='1.1.28';
				$jsonarr['url']='http://fs.appcan.cn/uploads/2015/09/14//11392971_android_01.01.0028_000_21914_0.apk';
				$jsonarr['title']='1.1.28版发布，建议立即更新。';
				$jsonarr['msg']='1、增加“狗市图库”功能;'."\n\n".'2、优化程序，提高App运行效率;'."\n\n".'3、修复已知BUG';
        //$jsonarr['msg']='1、修复首页无法加载等已知BUG';
			}
		}else if($device=='ios'){
      //$nowVersion=str_replace('.','','1.2.2');///ios最新版本
      $nowVersion=transVersion('1.1.30');
			if($version<$nowVersion){
				$jsonarr['status']=1;
				$jsonarr['version']='1.1.30';
				$jsonarr['url']='https://itunes.apple.com/us/app/gou-shi-chang/id1016149190?l=zh&ls=1&mt=8';
				$jsonarr['title']='1.1.30版发布，建议立即更新。';
				$jsonarr['msg']='1、增加“狗市图库”功能;'."\n\n".'2、优化程序，提高App运行效率;'."\n\n".'3、修复已知BUG';
			}
		}

		jsonexit($jsonarr);
	}else if($action=='location'){
		if($device=='android' || $device=='iso'){
			$jsonarr = array();
			$jsonarr['status']=0;

			$url="http://api.map.baidu.com/geocoder/v2/?ak=9ac4b01814977c8a3241307fcf400166&location={$inlat},{$inlng}&output=json";
			$content = file_get_contents($url);
			if($content){
				$tmp = json_decode($content,true);
				if($tmp['status']==0){
					$jsonarr['status']=1;
					$jsonarr=array_merge($jsonarr,$tmp['result']);

					if($_userid) $db->query("UPDATE {$DT_PRE}member SET lng='$inlng',lat='$inlat' WHERE userid='$_userid'");
				}
			}
			jsonexit($jsonarr);
		}
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
  }
}
jsonexit("{\"state\":\"index-err\"}");
?>