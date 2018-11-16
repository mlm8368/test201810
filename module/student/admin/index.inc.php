<?php
defined('DT_ADMIN') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/'.$module.'.class.php';
$do = new $module();
$this_forward = '?moduleid='.$moduleid.'&file='.$file;

if($_catids || $_areaids) {
	if(isset($userid)) $itemid = $userid;
	if(isset($member['areaid'])) $post['areaid'] = $member['areaid'];
	require DT_ROOT.'/admin/admin_check.inc.php';
}

$condition = '1';

switch($action) {
  case 'parentlist':
    $menus = array (
      array('家长列表', '?moduleid='.$moduleid.'&file='.$file.'&action=parentlist&sid='.$sid),
    );
    if($sid) $condition .= " AND a.studentid = '{$sid}'";
    $lists = $do->get_parentlist($condition);
    $menuid = 0;
		include tpl('parentlist', $module);
	break;
  default:
    $menus = array (
      array($MOD['name'].'列表', '?moduleid='.$moduleid),
    );
    if($keyword) $condition .= " AND babyname LIKE '%$keyword%'";
		$lists = $do->get_list($condition);
		$menuid = 0;
		include tpl('index', $module);
	break;
}
?>