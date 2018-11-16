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

$condition = "1";

switch($action) {
  case 'teacherlist':
    $condition = "a.classesid = '{$cid}'"; 
    $lists = $do->get_teacherlist($condition);
    $menus = array (
      array('教师列表', '?moduleid='.$moduleid.'&file='.$file.'&action=teacherlist&cid='.$cid),
    );
    $menuid = 0;
    include tpl('teacherlist', $module);
  break;
  case 'studentlist':
    $condition = "a.classesid = '{$cid}'";
    $lists = $do->get_studentlist($condition);
    $menus = array (
      array('学生列表', '?moduleid='.$moduleid.'&file='.$file.'&action=studentlist&cid='.$cid),
    );
    $menuid = 0;
    include tpl('studentlist', $module);
  break;
  default:
    if($keyword) $condition .= " AND a.classesname LIKE '%$keyword%'";
    $lists = $do->get_list($condition);
    $menus = array (
      array($MOD['name'].'列表', '?moduleid='.$moduleid),
    );
		$menuid = 0;
		include tpl('index', $module);
	break;
}
?>