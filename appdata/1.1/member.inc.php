<?php
/* 
	[Destoon B2B System] Copyright (c) 2008-2013 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/include/post.func.php';
require DT_ROOT.'/module/'.$module.'/member.class.php';
$do = new member;

switch($action) {
	case 'reg':
    $jsonarr = array();
    $jsonarr['status']=0;
    
    if($op == 'checkmobile'){
      if($do->mobile_exists($mobile)) {
        $jsonarr['msg']='手机号已注册';
        jsonexit($jsonarr);
      }else{
        $jsonarr['status']=1;
        jsonexit($jsonarr);
      }
    }

    if($submit) {
      $do->logout();

      if(!$mobile) {
        $jsonarr['msg']='请填手机号';
        jsonexit($jsonarr);
      }
      if(!$password) {
        $jsonarr['msg']='请填登录密码';
        jsonexit($jsonarr);
      }
      if($do->mobile_exists($mobile)) {
        $jsonarr['msg']='手机号已注册';
        jsonexit($jsonarr);
      }
      //
      $username = 'nouname'.$mobile;
      $member=array();
      $member['username']=$username;
      $member['password']=$password;
      $member['cpassword']=$cpassword;
      $member['mobile']=$mobile;
      $member['groupid']=5;
      $member['areaid']=$areaid;
      //$member['email']='abc@abc.com';
      $member['passport']=$username;
      //$member['company']=$username;
      if($userid = $do->add($member)){
        $jsonarr['status']=1;
        jsonexit($jsonarr);
      }else{
        $jsonarr['msg']='注册失败,'.$do->errmsg;
        jsonexit($jsonarr);
      }
    }
    
    $jsonarr['msg']='注册失败';
    jsonexit($jsonarr);
	break;
	case 'login':
    $jsonarr = array();
    $jsonarr['status']=0;

    $sql = "SELECT a.userid,a.username,a.passport,a.avatarpic,a.mobile,a.groupid,a.areaid,a.truename,a.gender,GROUP_CONCAT(DISTINCT b.studentid) AS studentids,GROUP_CONCAT(DISTINCT b.relatename) AS relatename, GROUP_CONCAT(DISTINCT c.classesid) AS classesids, GROUP_CONCAT(DISTINCT c.teacherpost) AS teacherpost,"
        ." GROUP_CONCAT(DISTINCT d.classesid) AS studentclassesids, GROUP_CONCAT(DISTINCT e.parentuserid) AS studentparentuserids, GROUP_CONCAT(f.teacheruserid) AS studentteacherids"
        ." FROM {$DT_PRE}member a LEFT JOIN {$DT_PRE}student_parent b ON a.userid = b.parentuserid LEFT JOIN {$DT_PRE}classes_teacher c ON a.userid = c.teacheruserid"
        ." LEFT JOIN {$DT_PRE}classes_student d ON b.studentid = d.studentid LEFT JOIN {$DT_PRE}student_parent e ON b.studentid = e.studentid LEFT JOIN {$DT_PRE}classes_teacher f ON d.classesid = f.classesid";
    
    if($op === 'checkloginuserinfo') {
      $sql .= " WHERE a.`userid`='{$_userid}'";
      $user = $db->get_one($sql);
      if($user['userid']) {
        $jsonarr['status']=1;
        $jsonarr['userInfo']=setLoginUserInfo($user);
      }

      jsonexit($jsonarr);
    }
    
    if($submit) {
      if(!$mobile) {
        $jsonarr['msg']='请填手机号';
        jsonexit($jsonarr);
      }
      if(!$password) {
        $jsonarr['msg']='请填登录密码';
        jsonexit($jsonarr);
      }

      $sql .= " WHERE a.`mobile`='{$mobile}'";
      $r = $db->get_one($sql);
      if($r) {
        $username = $r['username'];
        $passport = $r['passport'];
      } else {
        $jsonarr['msg']='该手机未注册';
        jsonexit($jsonarr);
      }
      $user = $do->login($username, $password);
      if($user) {
        $jsonarr['status']=1;

        $user['studentids'] = $r['studentids'];
        $user['relatename'] = $r['relatename'];
        $user['classesids'] = $r['classesids'];
        $user['teacherpost'] = $r['teacherpost'];
        $user['studentclassesids'] = $r['studentclassesids'];
        $user['studentparentuserids'] = $r['studentparentuserids'];

        $jsonarr['userInfo']=setLoginUserInfo($user);

        jsonexit($jsonarr);
      } else {
        $jsonarr['msg']=$do->errmsg;
        jsonexit($jsonarr);
      }
    }
    
    $jsonarr['msg']='登录失败';
    jsonexit($jsonarr);
	break;
	case 'setnewpass':
		$jsonarr = array();
		$jsonarr['status']=0;
		if(empty($_userid)){
			$jsonarr['msg']='您未登录';
			jsonexit($jsonarr);
		}
		$username=$oldusername;
    $password=$opass;
    $newpassword=$upass;

    require DT_ROOT.'/include/post.func.php';
    require DT_ROOT.'/module/member/member.class.php';
    $do = new member;

    $user = $do->login($username, $password, 86400*365,false,false);
    if($user) {
      $do->userid=$_userid;
      $r=$do->edit(array('password'=>$newpassword),false);
      if($r){
        $user = $do->login($username, $upass, 86400*365,false,false);
        if($user) {
          $jsonarr['status']=1;
          $jsonarr['acctoken'] = $user['auth'];
          jsonexit($jsonarr);
        }
      }else{
        $jsonarr['msg']='修改密码失败了';
        jsonexit($jsonarr);
      }
    }else{
			$jsonarr['msg']='用户名或原密码错误';
			jsonexit($jsonarr);
		}
		
		jsonexit($jsonarr);
	break;
	case 'getuserinfo':
		$jsonarr = array();
		$jsonarr['status']=-1;
		if(empty($_userid)){
			$jsonarr['msg']='您未登录';
			jsonexit($jsonarr);
		}

		$r = $db->get_one("SELECT userid,passport,username,truename,groupid,email,qq,wx,mobile,areaid,gender,avatarpic,message FROM {$DT_PRE}member WHERE userid='$_userid'");
		$r or jsonexit($jsonarr);
		$jsonarr['status']=1;

		$tmpArr=array();
    $tmpArr['id'] = $r['userid'];
    unset($r['userid']);
    $tmpArr['area'] = $r['areaid'];
    if($r['areaid']) $tmpArr['areaname'] = area_pos($r['areaid'],' ');
    else $tmpArr['areaname'] = '';
    $tmpArr = array_merge($tmpArr, $r);

		if($r['groupid'] >= '6'){
			$r2 = $db->get_one("SELECT * FROM {$DT_PRE}company WHERE userid='$_userid'");
			if($r2) {
        unset($r2['hits'],$r2['keyword'],$r2['linkurl']);
				$tmpArr = array_merge($tmpArr, $r2);
			}
      //
      $tmpArr['banners'] = array();
      $result = $db->query("SELECT item_value FROM {$DT_PRE}company_setting WHERE userid='$_userid' and item_key in ('banner1','banner2','banner3','banner4','banner5') order by item_key");
      while($r = $db->fetch_array($result)) {
      	if(!empty($r['item_value'])) $tmpArr['banners'][] = $r['item_value'];
      }
      //
      $tmpArr['vip'] = array('startdate'=>'1232','enddate'=>'1223','leftday'=>'12');
		}

		$jsonarr['userinfo'] = $tmpArr;
		jsonexit($jsonarr);
	break;
	case 'setuserinfo':
    if($key=='address' && $imei && $mac){
      $client = md5($imei.$mac);
      $lasttime=date('Y-m-d H:i:s',$DT_TIME);
      if(empty($_userid)) {
        $_userid=0;
        $_username='';
      }
      $r2 = $db->get_one("SELECT itemid FROM {$DT_PRE}brand_item_13 WHERE client='$client' and userid='{$_userid}'");
      if($r2){
        if($val=='北京') 
          $db->query("UPDATE {$DT_PRE}brand_item_13 SET lasttime='{$lasttime}' WHERE client='$client' and userid='{$_userid}'");
        else 
          $db->query("UPDATE {$DT_PRE}brand_item_13 SET address='{$val}',lasttime='{$lasttime}' WHERE client='$client' and userid='{$_userid}'");
      }else{
        if($val=='北京') 
          $db->query("INSERT INTO {$DT_PRE}brand_item_13 (client,userid,username,lasttime) VALUES ('{$client}','{$_userid}','{$_username}','{$lasttime}')");
        else 
          $db->query("INSERT INTO {$DT_PRE}brand_item_13 (client,address,userid,username,lasttime) VALUES ('{$client}','{$val}','{$_userid}','{$_username}','{$lasttime}')");
      }
      jsonexit(array('status'=>1));
    }
    //
		$jsonarr = array();
		$jsonarr['status']=-1;
		if(empty($_userid)){
			$jsonarr['msg']='您未登录';
			jsonexit($jsonarr);
		}

		require DT_ROOT.'/include/post.func.php';
    //
    if($key=='avatar'){
      $sql="avatarpic='{$val}'";
    }else if($key=='realname'){
      $sql="truename='{$val}'";
    }else if($key=='gender'){
      if($val=='男') $sql="gender='1'";
      else $sql="gender='2'";
    }else if($key=='mobile'){
      $mobile=$val;
      if(!empty($mobile) && !is_telephone($mobile)) {
        $jsonarr['status']=0;
        $jsonarr['msg']='手机号格式不正确';
        jsonexit($jsonarr);
      }
      $sql="mobile='{$val}'";
    }else if($key=='email'){
      $email=$val;
      if(!empty($email) && !is_email($email)) {
        $jsonarr['status']=0;
        $jsonarr['msg']='邮箱格式不正确';
        jsonexit($jsonarr);
      }
      $sql="email='{$val}'";
    }else if($key=='msn'){
      $msn=$val;
      $sql="msn='{$msn}'";
    }else if($key=='qq'){
      $qq=$val;
      $sql="qq='{$qq}'";
    }else if($key=='area' || $key=='city'){
      $area = array("全国","北京","上海","天津","重庆","河北","山西","内蒙古","辽宁","吉林","黑龙江","江苏","浙江","安徽","福建","江西","山东","河南","湖北","湖南","广东","广西","海南","四川","贵州","云南","西藏","陕西","甘肃","青海","宁夏","新疆","台湾","香港","澳门");
      $areaid=array_search ($val, $area );
      $sql="areaid='{$areaid}'";
      $jsonarr['areaId']=$areaid;
    }else if($key=='address'){
      $address=$val;
      
      $areaname='';
      $r = $db->get_one("SELECT areaid FROM {$DT_PRE}member WHERE userid='$_userid'");
      if($r['areaid']) $areaname=area_pos($r['areaid'],'_');

      $areanameArr=explode('_',$areaname);
      foreach($areanameArr as $one){
        $address=str_replace($one,'',$address);
      }
      $areaname=str_replace('_','',$areaname);

      $lng=$lat='';
      $url="http://api.map.baidu.com/geocoder/v2/?ak=9ac4b01814977c8a3241307fcf400166&output=json&address=".urlencode($areaname.$address);
      $content = file_get_contents($url);
      if($content){
        $tmp = json_decode($content,true);
        if($tmp['status']==0){
          $lng = $tmp['result']['location']['lng'];
          $lat = $tmp['result']['location']['lat'];
        }
      }

      $sql="address='{$address}',clng='{$lng}',clat='{$lat}'";
    }
    
    if($sql) {
      if($key=='address') $db->query("UPDATE {$DT_PRE}company SET {$sql} WHERE userid='$_userid'");
      else if($key=='area' || $key=='city'){
        $db->query("UPDATE {$DT_PRE}company SET {$sql} WHERE userid='$_userid'");
        $db->query("UPDATE {$DT_PRE}member SET {$sql},edittime='$DT_TIME' WHERE userid='$_userid'");
      }else if($key=='email'){
        //$db->query("UPDATE {$DT_PRE}company SET mail='{$email}' WHERE userid='$_userid'");
        $db->query("UPDATE {$DT_PRE}member SET email='{$email}',edittime='$DT_TIME' WHERE userid='$_userid'");
      }
      else $db->query("UPDATE {$DT_PRE}member SET {$sql},edittime='$DT_TIME' WHERE userid='$_userid'");
    }
		//$db->query("UPDATE {$DT_PRE}member SET truename='$realname',gender='$sex',areaid='$area',mobile='$mobile',email='$email',edittime='$DT_TIME' WHERE userid='$_userid'");
        
        ////
        if($key=='avatar'){
            include DT_ROOT.'/api/rongcloud/rongcloud.php';
            $appKey = 'x18ywvqfxj5wc';
            $appSecret = 'U2yofOpFB8aY';
            $RongCloud = new RongCloud($appKey,$appSecret);
            $result = $RongCloud->user()->refresh($_userid, $_username, $val);
        }
		
		$jsonarr['status']=1;
		
		jsonexit($jsonarr);
	break;
	case 'setcompanyinfo':
		$jsonarr = array();
		$jsonarr['status']=-1;
		if(empty($_userid)){
			$jsonarr['msg']='您未登录';
			jsonexit($jsonarr);
		}
		
		$lng=$lat='';
		$url="http://api.map.baidu.com/geocoder/v2/?ak=9ac4b01814977c8a3241307fcf400166&output=json&address=".urlencode($address);
		$content = file_get_contents($url);
		if($content){
			$tmp = json_decode($content,true);
			if($tmp['status']==0){
				$lng = $tmp['result']['location']['lng'];
				$lat = $tmp['result']['location']['lat'];
			}
		}

		$db->query("UPDATE {$DT_PRE}company SET address='$address', company='$company', type='$type', business='$business', regyear='$regyear', size='$size', capital='$capital', postcode='$postcode', telephone='$telephone', fax='$fax', homepage='$homepage', regunit='人民币',clng='$lng',clat='$lat' WHERE userid='$_userid'");

		if($groupid==5){
			$db->query("delete from {$DT_PRE}upgrade WHERE status='2' and userid='$_userid'");
			$db->query("INSERT INTO {$DT_PRE}upgrade (userid,username,groupid,company,truename,telephone,mobile,email,addtime,ip,status) VALUES ('$_userid','$_username', '6','$company','$truename','$telephone','$mobile','$email', '$DT_TIME', '$DT_IP','2')");
		}

		if($groupid==5 && $thumb){
			$db->query("delete from {$DT_PRE}validate WHERE type='company' and username='$_username'");
			$db->query("INSERT INTO {$DT_PRE}validate (title,type,thumb,username,addtime,editor,edittime,status) VALUES ('$company','company','$thumb','$_username','$DT_TIME','system','$DT_TIME','2')");
		}
		
		$jsonarr['status']=1;
		
		jsonexit($jsonarr);
	break;
	case 'favorite':
		$jsonarr = array();
		$jsonarr['status']=0;
		if(empty($_userid)){
			$jsonarr['msg']='您未登录';
			jsonexit($jsonarr);
		}
		if($op=='list'){
			$jsonarr['status']=1;
            $jsonarr['page']=$page;
            $jsonarr['maxid']=0;
			$jsonarr['list']=array();
			$jsonarr['hasnext']=1;
			$jsonarr['uptime']=date("Y-m-d H:i:s");

			require DT_ROOT.'/module/member/favorite.class.php';
			$do = new favorite();
			$condition = "userid=$_userid AND item_mid=5";
            if($maxid) $condition .= " AND itemid > {$maxid}";
			$lists = $do->get_list($condition,'itemid DESC','itemid,item_id,title');
			
			foreach($lists as $r){
                if($page == 1 && $r['itemid'] > $jsonarr['maxid']) $jsonarr['maxid'] = $r['itemid'];
				$tmp=array();
				$tmp['id']=$r['itemid'];
                $tmp['sellid']=$r['item_id'];
				$tmp['title'] = html_entity_decode($r['title']);

				$jsonarr['list'][]=$tmp;
			}

			if(count($jsonarr['list'])<$pagesize) $jsonarr['hasnext']=0;
			
			jsonexit($jsonarr);
		}else if($op=='del'){
			require DT_ROOT.'/module/member/favorite.class.php';
			$do = new favorite();
			$do->delete($ids);

			$jsonarr['status']=1;
			jsonexit($jsonarr);
		}
  break;
  /*
  case 'getclasses':
    $jsonarr = array();
    $jsonarr['status']=1;
    $jsonarr['schools']=array();

    require DT_ROOT.'/module/classes/classes.class.php';
    $do = new classes;
    $jsonarr['schools'] = $do->app_get_classes($studentids);

    jsonexit($jsonarr);
  break;
  */
  case 'getbaobao':
    $jsonarr = array();
    $jsonarr['status']=1;
    $jsonarr['baobaos']=array();

    require DT_ROOT.'/module/student/student.class.php';
    $do = new student;
    $jsonarr['baobaos'] = $do->app_get_baobaos($studentids);

    jsonexit($jsonarr);
  break;
}

//
function gender2($gender, $type = 0) {
	if($type) return $gender == 1 ? "男" : "女";
	return $gender == 1 ? "先生" : "女士";
}
function setLoginUserInfo($user){
  $tmpArr=array();
  $tmpArr['userid'] = intval($user['userid']);
  $tmpArr['username'] = $user['username'];
  $tmpArr['accessToken'] = $user['auth'];
  $tmpArr['student'] = null;
  $tmpArr['classes'] = null;
  if($user['studentids']) $tmpArr['student'] = array('studentids'=>$user['studentids'],'relatename'=>$user['relatename'],'classesids'=>$user['studentclassesids'],'parentuserids'=>$user['studentparentuserids'],'teacherids'=>$user['studentteacherids']);
  if($user['classesids']) $tmpArr['classes'] = array('classesids'=>$user['classesids'],'teacherpost'=>$user['teacherpost']);
  $tmpArr['avatar'] = $user['avatarpic']?$user['avatarpic']:'';
  $tmpArr['mobile'] = $user['mobile'];  
  $tmpArr['groupid'] = intval($user['groupid']);
  //$tmpArr['credit'] = intval($user['credit']);
  //$tmpArr['money'] = intval($user['money']);
  $tmpArr['areaid'] = intval($user['areaid']);
  $tmpArr['area'] = '全国';
  if($user['areaid']) $tmpArr['area']=area_pos($user['areaid'],' ');
  $tmpArr['truename'] = $user['truename'];
  $tmpArr['gender'] = intval($user['gender']);
  //$tmpArr['wx'] = $user['wx'];
  //$tmpArr['qq'] = $user['qq'];
  
  return $tmpArr;
}
?>