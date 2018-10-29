<?php
/* 
	[Destoon B2B System] Copyright (c) 2008-2013 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/include/post.func.php';
require DT_ROOT.'/module/'.$module.'/classes.class.php';
$do = new classes;

if(!$_userid) {
    jsonexit(array('status'=>-1, 'msg'=>'您未登录'));
}

switch($action) {
  case 'list':
    $jsonarr = array();
    $jsonarr['status']=1;
    $jsonarr['lists'] = array();

    $jsonarr['lists'] = getClassesLists();

    jsonexit($jsonarr);
    break;
  case 'edit':
    $jsonarr = array();
    $jsonarr['status']=1;
    $jsonarr['lists'] = array();

    if($_POST['id'] > 0) {
      $do->edit($_POST);
    } else {
      // $_userid = 2; // test
      $_POST['schoolid'] = $_userid;
      $do->add($_POST);
    }
    $jsonarr['lists'] = getClassesLists();

    jsonexit($jsonarr);
    break;
  case 'del':
    $jsonarr = array();
    $jsonarr['status']=1;
    $jsonarr['lists'] = array();

    $do->delete($id);
    $jsonarr['lists'] = getClassesLists();

    jsonexit($jsonarr);
    break;
  case 'teacher':
    if($op == 'list') {
      $jsonarr = array();
      $jsonarr['status']=1;
      $jsonarr['lists'] = array();
     
      $condition = " a.classesid = '{$classesid}' and a.isdelete = 0";
      $r = $do->get_teacherlist($condition);
      if(!empty($r)) {
        foreach($r as $one) {
          $jsonarr['lists'][] = outTeacher($one);
        }
      }
  
      jsonexit($jsonarr);
    } else if($op == 'search'){
      $jsonarr = array();
      $jsonarr['status']=1;
      $jsonarr['lists'] = array();
     
      $condition = " a.schoolid = '{$_userid}' and b.truename like '%{$keywords}%'";
      $r = $do->search_teacher($condition);
      if(!empty($r)) {
        foreach($r as $one) {
          $one['itemid'] = 0;
          $one['teacherpost'] = '';
          $jsonarr['lists'][] = outTeacher($one);
        }
      } else {
        $jsonarr['status']=0;
        $jsonarr['msg']="未在本校找到 {$keywords} 老师";
      }
  
      jsonexit($jsonarr);
    } else if($op == 'add'){
      $jsonarr = array();
      $jsonarr['status']=1;

      $id = $do->add_teacher($_POST);

      $jsonarr['id'] = intval($id);
      jsonexit($jsonarr);
    } else if($op == 'edit'){
      $jsonarr = array();
      $jsonarr['status']=1;

      $do->edit_teacher($_POST);

      jsonexit($jsonarr);
    } else if($op == 'del'){
      $jsonarr = array();
      $jsonarr['status']=1;

      $do->delete_teacher($id);

      jsonexit($jsonarr);
    }
    break;
  case 'student':
    if($op == 'list') {
      $jsonarr = array();
      $jsonarr['status']=1;
      $jsonarr['lists'] = array();
     
      $condition = " a.classesid = '{$classesid}' and a.isdelete = 0";
      $result = $do->get_studentlist($condition);
      while($r = DB::fetch_array($result)) {
        if($r[id]) $jsonarr['lists'][] = outStudent($r);
      }
  
      jsonexit($jsonarr);
    } else if($op == 'search'){
      $jsonarr = array();
      $jsonarr['status']=1;
      $jsonarr['studentInfo'] = array();
     
//    $keywords = '20181';
      $sid = substr($keywords, 4);
      $condition = " a.itemid = '{$sid}'";
      $result = $do->search_studentlist($condition);
      $r = DB::fetch_array($result);
      if($r['studentid']) {
        $r['id'] = 0;
        $r['startdate'] = '';
        $r['enddate'] = '';
        $jsonarr['studentInfo'] = outStudent($r);
      } else {
        $jsonarr['status']=0;
        $jsonarr['msg']='未找到该学生';
      }
  
      jsonexit($jsonarr);
    } else if($op == 'add'){
      $jsonarr = array();
      $jsonarr['status']=1;

      $id = $do->add_student($_POST);

      $jsonarr['id'] = intval($id);
      jsonexit($jsonarr);
    } else if($op == 'edit'){
      $jsonarr = array();
      $jsonarr['status']=1;

      $do->edit_student($_POST);

      jsonexit($jsonarr);
    } else if($op == 'del'){
      $jsonarr = array();
      $jsonarr['status']=1;

      $do->delete_student($id);

      jsonexit($jsonarr);
    }
    break;
}

function getClassesLists() {
  global $do, $_userid;
  $_lists = array();

  // $_userid = 2; // test
  $condition = " schoolid='{$_userid}' and isdelete=0";
  $lists = $do->get_list($condition, 'listorder asc');
  foreach($lists as $one){
    $tmp = array();
    $tmp['id'] = intval($one['itemid']);
    $tmp['classesname'] = $one['classesname'];
    $tmp['listorder'] = intval($one['listorder']);
    $tmp['startdate'] = $one['startdate'];
    $tmp['enddate'] = $one['enddate'];
    $_lists[]=$tmp;
  }

  return $_lists;
}

function outTeacher($one) {
    $tmp = array();
    $tmp['id'] = intval($one['itemid']);
    $tmp['teacherpost'] = $one['teacherpost'];
    $tmp['userid'] = intval($one['teacheruserid']);
    $tmp['username'] = $one['username'];
    $tmp['truename'] = $one['truename'];
    $tmp['avatar'] = $one['avatarpic'];
    $tmp['gender'] = intval($one['gender']);
    $tmp['mobile'] = $one['mobile'];
    $tmp['qq'] = $one['qq'];
    $tmp['wx'] = $one['wx'];

    return $tmp;
}

function outStudent($one) {
    $tmp = array();
    $tmp['id'] = intval($one['id']);
    $tmp['startdate'] = $one['startdate'];
    $tmp['enddate'] = $one['enddate'];
    $tmp['studentid'] = intval($one['studentid']);
    $tmp['babyname'] = $one['babyname'];
    $tmp['avatar'] = $one['avatar'];
    $tmp['gender'] = intval($one['gender']);
    $tmp['birthday'] = $one['birthday'];
    $tmp['parentuserids'] = $one['parentuserids'];
    $tmp['parentcalls'] = $one['parentcalls'];
    $tmp['parenttruenames'] = $one['parenttruenames'];
    $tmp['parentmobiles'] = $one['parentmobiles'];
    $tmp['parentqqs'] = $one['parentqqs'];
    $tmp['parentwxs'] = $one['parentwxs'];

    return $tmp;
}