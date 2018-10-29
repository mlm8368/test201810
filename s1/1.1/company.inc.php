<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2016 www.destoon.com
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
$userid = isset($userid) ? intval($userid) : 0;
$username = isset($username) ? trim($username) : '';
check_name($username) or $username = '';
if($userid || $username) {
	if($userid) $username = get_user($userid, 'userid', 'username');
	$COM = userinfo($username);
	if(!$COM || ($COM['groupid'] < 5 && $COM['groupid'] > 1)) {
		userclean($username);
		mobile_msg($L['msg_not_corp']);
	}
	if(!$COM['edittime'] && !$MOD['openall']) mobile_msg($L['com_opening']);
	$COM['year'] = vip_year($COM['fromtime']);
	$COMGROUP = cache_read('group-'.$COM['groupid'].'.php');
	if(!isset($COMGROUP['homepage']) || !$COMGROUP['homepage']) mobile_msg($L['com_no_home']);
	require_once DT_ROOT.'/module/member/global.func.php';
	$userid = $COM['userid'];
	$company = $COM['company'];
	$HURL = 'index.php?moduleid=4&username='.$username;
	include load('homepage.lang');
	if($COMGROUP['menu_d']) {
		$_menu_show = array();
		foreach($HMENU as $k=>$v) {
			$_menu_show[$k] = strpos(','.$COMGROUP['menu_d'].',', ','.$k.',') !== false ? 1 : 0;
		}
		$_menu_show = implode(',', $_menu_show);
	} else {
		$_menu_show = '1,1,1,1,1,1,1,1,0,0,0,0,0,0';
	}
	$_menu_order = '0,10,20,30,40,50,60,70,80,90,100,110,120,130';
	$_menu_num = '1,16,30,30,10,30,1,12,12,12,12,30,12,1';
	$_menu_file = implode(',' , $MFILE);
	$_menu_name = implode(',' , $HMENU);

	$HOME = get_company_setting($COM['userid'], '', 'CACHE');
	
	$menu_show = explode(',', isset($HOME['menu_show']) ? $HOME['menu_show'] : $_menu_show);
	$menu_order = explode(',', isset($HOME['menu_order']) ? $HOME['menu_order'] : $_menu_order);
	$menu_num = explode(',', isset($HOME['menu_num']) ? $HOME['menu_num'] : $_menu_num);
	$menu_file = explode(',', isset($HOME['menu_file']) ? $HOME['menu_file'] : $_menu_file);
	$menu_name = explode(',', isset($HOME['menu_name']) ? $HOME['menu_name'] : $_menu_name);
	$_HMENU = array();
	asort($menu_order);
	foreach($menu_order as $k=>$v) {
		$_HMENU[$k] = $HMENU[$k];
	}
	$HMENU = $_HMENU;

	$MENU = $_MENU = array();
	$menuid = 0;
	foreach($HMENU as $k=>$v) {
		if($menu_show[$k] && in_array($menu_file[$k], $MFILE)) {
			$MENU[$k]['name'] = $menu_name[$k];
			$MENU[$k]['file'] = $menu_file[$k];
			$_MENU[$menu_file[$k]] = $menu_name[$k];
		}
	}

	isset($_MENU['introduce']) or $_MENU['introduce'] = $L['com_introduce'];
	isset($_MENU['news']) or $_MENU['news'] = $L['com_news'];
	isset($_MENU['credit']) or $_MENU['credit'] = $L['com_credit'];
	isset($_MENU['contact']) or $_MENU['contact'] = $L['com_contact'];

	$head_title = $company;
	$foot = '';
	switch($action) {
		case 'introduce':
			$table = $DT_PRE.'page';
			$table_data = $DT_PRE.'page_data';
			$head_name = $_MENU[$action];
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;
			if($itemid) {
				$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
				($item && $item['status'] > 2 && $item['username'] == $username) or mobile_msg($L['msg_not_exist']);
				extract($item);
				$t = $db->get_one("SELECT content FROM {$table_data} WHERE itemid=$itemid");
				$content = video5($t['content']);
				if($share_icon) $share_icon = share_icon('', $content);
				$db->query("UPDATE {$table} SET hits=hits+1 WHERE itemid=$itemid");
				$date = timetodate($addtime, 3);
				$back_link = $HURL.'&action='.$action;
				$head_title = $title.$DT['seo_delimiter'].$head_title;
			} else {
				$content_table = content_table(4, $userid, is_file(DT_CACHE.'/4.part'), $DT_PRE.'company_data');
				$t = $db->get_one("SELECT content FROM {$content_table} WHERE userid=$userid");
				$content = video5($t['content']);
				if($share_icon) $share_icon = share_icon($thumb, $content);
				$video = isset($HOME['video']) ? $HOME['video'] : '';
				$thumb = $COM['thumb'];
				$lists = array();
				$result = $db->query("SELECT itemid,title,style FROM {$table} WHERE status=3 AND username='$username' ORDER BY listorder DESC,addtime DESC");
				while($r = $db->fetch_array($result)) {
					$lists[] = $r;
				}
				$back_link = $HURL;
			}
			include template('homepage-'.$action, 'mobile');
		break;
		case 'credit':
			$head_name = $_MENU[$action];
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;
			$back_link = $HURL;
			$typeid = isset($typeid) ? intval($typeid) : 0;
			in_array($typeid, array(0, 1, 2)) or $typeid = 0;
			$tab = isset($MODULE[16]) ? 1 : 0;
			if($typeid && $tab) {
				$table = $DT_PRE.'mall_comment';
				$comment = 1;
				$STARS = $L['star_type'];
				if($typeid == 2) {
					$condition = "buyer='$username' AND buyer_star>0";
				} else {
					$condition = "seller='$username' AND seller_star>0";
				}
				$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition", 'CACHE');
				$items = $r['num'];
				$pages = mobile_pages($items, $page, $pagesize);
				$lists = array();
				if($items) {
					$result = $db->query("SELECT * FROM {$table} WHERE $condition ORDER BY itemid DESC LIMIT $offset,$pagesize");
					while($r = $db->fetch_array($result)) {
						$lists[] = $r;
					}
				}
			}
			include template('homepage-'.$action, 'mobile');
		break;
		case 'contact':
			$could_contact = check_group($_groupid, $MOD['group_contact']);
			if($username == $_username) $could_contact = true; 
			$could_contact or mobile_msg($L['com_no_permission'].$_MENU[$action]);
			$head_name = $_MENU[$action];
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;
			$back_link = $HURL;
			include template('homepage-'.$action, 'mobile');
		break;
		case 'news':
			$table = $DT_PRE.'news';
			$table_data = $DT_PRE.'news_data';
			$head_name = $_MENU[$action];
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;
			if($itemid) {
				$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
				($item && $item['status'] > 2 && $item['username'] == $username) or mobile_msg($L['msg_not_exist']);
				extract($item);
				$t = $db->get_one("SELECT content FROM {$table_data} WHERE itemid=$itemid");
				$content = video5($t['content']);
				if($share_icon) $share_icon = share_icon('', $content);
				$db->query("UPDATE {$table} SET hits=hits+1 WHERE itemid=$itemid");
				$date = timetodate($addtime, 3);
				$back_link = $HURL.'&action='.$action;
				$head_title = $title.$DT['seo_delimiter'].$head_title;
			} else {
				$typeid = isset($typeid) ? intval($typeid) : 0;
				$condition = "username='$username' AND status=3";
				if($kw) $condition .= " AND title LIKE '%$keyword%'";		
				if($typeid) $condition .= " AND typeid='$typeid'";
				$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition", 'CACHE');
				$items = $r['num'];
				$pages = mobile_pages($items, $page, $pagesize);
				$lists = array();
				if($items) {
					$result = $db->query("SELECT * FROM {$table} WHERE $condition ORDER BY addtime DESC LIMIT $offset,$pagesize");
					while($r = $db->fetch_array($result)) {
						$r['date'] = timetodate($r['addtime'], $page < 4 ? 2 : 3);
						$lists[] = $r;
					}
				}
				$back_link = $HURL;
				if($typeid) $back_link .= '&action='.$action;
			}
			include template('homepage-'.$action, 'mobile');
		break;
		case 'honor':
			isset($_MENU[$action]) or dheader($HURL);
			$table = $DT_PRE.'honor';
			$head_name = $_MENU[$action];
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;
			if($itemid) {
				$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid");
				($item && $item['status'] > 2 && $item['username'] == $username) or mobile_msg($L['msg_not_exist']);
				extract($item);
				$content = video5($item['content']);
				if($share_icon) $share_icon = share_icon($thumb, $content);
				$image = str_replace('.thumb.'.file_ext($thumb), '', $thumb);
				$db->query("UPDATE {$table} SET hits=hits+1 WHERE itemid=$itemid", 'UNBUFFERED');
				$date = timetodate($addtime, 3);
				$back_link = $HURL.'&action='.$action;
				$head_title = $title.$DT['seo_delimiter'].$head_title;
			} else {
				$condition = "username='$username' AND status=3";
				$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition", 'CACHE');
				$items = $r['num'];
				$pages = mobile_pages($items, $page, $pagesize);
				$lists = array();
				if($items) {
					$result = $db->query("SELECT * FROM {$table} WHERE $condition ORDER BY addtime DESC LIMIT $offset,$pagesize");
					while($r = $db->fetch_array($result)) {
						$lists[] = $r;
					}
				}
				$back_link = $HURL;
			}
			include template('homepage-'.$action, 'mobile');
		break;
		case 'link':
			isset($_MENU[$action]) or dheader($HURL);
			$table = $DT_PRE.'link';
			$head_name = $_MENU[$action];
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;
			$condition = "username='$username' AND status=3";
			$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition", 'CACHE');
			$pages = mobile_pages($r['num'], $page, $pagesize);
			$lists = array();
			$result = $db->query("SELECT * FROM {$table} WHERE $condition ORDER BY listorder DESC,addtime DESC LIMIT $offset,$pagesize");
			while($r = $db->fetch_array($result)) {
				$lists[] = $r;
			}
			$back_link = $HURL;
			include template('homepage-'.$action, 'mobile');
		break;
		case 'type':
			isset($item) or $item = '';
			if($item == 'sell') {
				$_TYPE = get_type('product-'.$userid);
				$head_name = $L['com_type_sell'];
			} else if($item == 'mall') {
				$_TYPE = get_type('mall-'.$userid);
				$head_name = $L['com_type_mall'];
			} else if($item == 'news') {
				$_TYPE = get_type('news-'.$userid);
				$head_name = $L['com_type_news'];
			} else {
				dheader($HURL);
			}
			$_TP = $_TYPE ? sort_type($_TYPE) : array();
			$head_title = $head_name.$DT['seo_delimiter'].$head_title;		
			$back_link = $HURL.'&action='.$item;
			include template('homepage-'.$action, 'mobile');
		break;
		case 'mall':
			$moduleid = 16;
		break;
		case 'sell':
			$moduleid = 5;
		break;
		case 'buy':
			isset($_MENU[$action]) or dheader($HURL);
			$could_buy = check_group($_groupid, $MOD['group_buy']);
			if($username == $_username) $could_buy = true;
			$could_buy or mobile_msg($L['com_no_permission'].$_MENU[$action]);
			$moduleid = 6;
		break;
		case 'job':
			$moduleid = 9;
		break;
		case 'photo':
			$moduleid = 12;
		break;
		case 'info':
			$moduleid = 22;
		break;
		case 'brand':
			$moduleid = 13;
		break;
		case 'video':
			$moduleid = 14;
		break;
		default:
			$background = (isset($HOME['background']) && $HOME['background']) ? $HOME['background'] : '';
			$logo = (isset($HOME['logo']) && $HOME['logo']) ? $HOME['logo'] : ($COM['thumb'] ? $COM['thumb'] : 'static/img/home-logo.png');
			$M = array();
			foreach($MENU as $v) {
				if(in_array($v['file'], array('introduce', 'news', 'credit', 'contact'))) continue;
				$M[] = $v;
			}
			include template('homepage', 'mobile');
		break;
	}
	if(in_array($action, array('mall', 'sell', 'buy', 'job', 'photo', 'info', 'brand', 'video'))) {
		isset($_MENU[$action]) or dheader($HURL);
		$table = get_table($moduleid);
		$head_name = $_MENU[$action];
		$head_title = $head_name.$DT['seo_delimiter'].$head_title;
		$back_link = $HURL;
		$condition = "username='$username' AND status=3";
		if(in_array($action, array('mall', 'sell'))) {
			$typeid = isset($typeid) ? intval($typeid) : 0;
			if($typeid) {
				$MTYPE = get_type(($action == 'sell' ? 'product' : 'mall').'-'.$userid);
				if($MTYPE[$typeid]['parentid']) {
					$condition .= " AND mycatid='$typeid'";
				} else {
					$cids = '';
					foreach($MTYPE as $k=>$v) {
						if($v['parentid'] == $typeid) $cids .= $k.',';
					}
					if($cids) {
						$cids = substr($cids, 0, -1);
						$condition .= " AND mycatid IN ($cids)";
					} else {
						$condition .= " AND mycatid='$typeid'";
					}
				}
				$back_link .= '&action='.$action;
			}
		}
		$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition", 'CACHE');
		$pages = mobile_pages($r['num'], $page, $pagesize);
		$lists = array();
		$result = $db->query("SELECT * FROM {$table} WHERE $condition ORDER BY edittime DESC LIMIT $offset,$pagesize");
		while($r = $db->fetch_array($result)) {
			$r['linkurl'] = mobileurl($moduleid, 0, $r['itemid']);
			$r['date'] = timetodate($r['edittime'], 5);
			$lists[] = $r;
		}
		include template('homepage-channel', 'mobile');
	}
	if($DT['cache_hits']) {
         cache_hits(4, $userid);
    } else {
        $db->query("UPDATE LOW_PRIORITY {$DT_PRE}company SET hits=hits+1 WHERE userid=$userid", 'UNBUFFERED');
    }
} else {
  require DT_ROOT.'/module/'.$module.'/company.class.php';
  $do = new company;
  
	switch($action) {
    case 'staff':
      if($op == "list") {
        $jsonarr = array();
        $jsonarr['status']=1;
        $jsonarr['lists'] = array();
      
        $result = $db->query("SELECT a.itemid as id,a.agree,a.jointime,b.userid,b.truename,b.mobile FROM {$DT_PRE}company_teacher a inner join {$DT_PRE}member b on a.teacherid = b.userid where a.schoolid = {$_userid} and a.isdelete = 0 order by a.agree asc, a.itemid asc");
    		while($r = $db->fetch_array($result)) {
    		  $r['agree'] = intval($r['agree']);
    			$jsonarr['lists'][] = $r;
    		}
    		
    		jsonexit($jsonarr);
      } else if($op == "search") {
        $jsonarr = array();
        $jsonarr['status']=1;
        $jsonarr['userinfo'] = array();
      
        $r = $db->get_one("SELECT userid,truename,mobile FROM {$DT_PRE}member where mobile = '{$mobile}'");
        if($r) {
          $r2 = $db->get_one("SELECT itemid FROM {$DT_PRE}company_teacher where schoolid = {$_userid} and teacherid = {$r['userid']} and isdelete = 0");
          if($r2) {
            $jsonarr['status']=0;
            $jsonarr['msg'] = '该老师已添加';
          } else {
            $jsonarr['userinfo'] = $r;
          }
        } else {
          $jsonarr['status']=0;
          $jsonarr['msg'] = '未找到该老师';
        }
        
        jsonexit($jsonarr);
      } else if ($op == "add") {
        $jsonarr = array();
        $jsonarr['status']=1;
        
        $db->query("insert into {$DT_PRE}company_teacher (schoolid,teacherid) value ('{$_userid}','{$teacherid}')");
        
        jsonexit($jsonarr);
      } else if ($op == "edit") {
        $jsonarr = array();
        $jsonarr['status']=1;
        
        $db->query("update {$DT_PRE}company_teacher set jointime ='{$jointime}' where itemid='{$id}'");
        
        jsonexit($jsonarr);
      } else if ($op == "del") {
        $jsonarr = array();
        $jsonarr['status']=1;
        
        $db->query("update {$DT_PRE}company_teacher set isdelete ='1' where itemid='{$id}'");
        
        jsonexit($jsonarr);
      } else if ($op == "agree") {
        $jsonarr = array();
        $jsonarr['status']=1;
        
        $db->query("update {$DT_PRE}company_teacher set agree ='{$agree}' where itemid='{$id}'");
        
        jsonexit($jsonarr);
      }
    break;
  }
}
?>