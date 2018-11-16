<?php 
defined('IN_DESTOON') or exit('Access Denied');
class classes {
  var $dt_pre;
  var $table_member;
  var $table_company;
	var $table_classes;
	var $table_classes_teacher;
	var $errmsg = errmsg;

  function __construct($username = '') {
    $this->dt_pre = DT_PRE;
    $this->table_member = DT_PRE.'member';
    $this->table_company = DT_PRE.'company';
    $this->table_classes = DT_PRE.'classes';
    $this->table_student = DT_PRE.'student';
    $this->table_classes_teacher = DT_PRE.'classes_teacher';
    $this->table_classes_student = DT_PRE.'classes_student';
    $this->fields = array('schoolid','classesname','startdate','enddate','listorder','addtime','teachernum','studentnum','isdelete');
    $this->fields_teacher = array('classesid', 'teacheruserid', 'teacherpost', 'isdelete');
    $this->fields_student = array('classesid', 'studentid', 'startdate', 'enddate', 'isdelete');
  }

  function classes($username = '') {
		$this->__construct($username);
  }

	function get_list($condition, $order = 'itemid DESC', $cache = '') {
		global $pages, $page, $pagesize, $offset, $items, $sum;
		if($page > 1 && $sum) {
			$items = $sum;
		} else {
			$r = DB::get_one("SELECT COUNT(*) AS num FROM {$this->table_classes} a WHERE $condition", $cache);
			$items = $r['num'];
		}
		// $pages = defined('CATID') ? listpages(1, CATID, $items, $page, $pagesize, 10, $MOD['linkurl']) : pages($items, $page, $pagesize);
		if($items < 1) return array();
		$lists = array();
		$result = DB::query("SELECT a.*,b.company,b.username FROM {$this->table_classes} a left join {$this->table_company} b on a.schoolid = b.userid WHERE $condition ORDER BY $order LIMIT $offset,$pagesize", $cache);
		while($r = DB::fetch_array($result)) {
			$lists[] = $r;
		}
		return $lists;
  }
  
  function get_teacherlist($condition, $order = 'a.itemid DESC', $cache = '') {
		$lists = array();
		$result = DB::query("SELECT a.*,b.username,b.truename,b.gender,b.mobile,b.qq,b.wx,b.avatarpic FROM {$this->table_classes_teacher} a left join {$this->table_member} b on a.teacheruserid = b.userid WHERE $condition ORDER BY $order", $cache);
		while($r = DB::fetch_array($result)) {
			$lists[] = $r;
		}
		return $lists;
  }

  function search_teacher($condition) {
      $lists = array();
      $sql = "SELECT a.itemid,a.teacherid AS teacheruserid,b.username,b.truename,b.gender,b.mobile,b.qq,b.wx,b.avatarpic FROM {$this->dt_pre}company_teacher a left join {$this->dt_pre}member b on a.teacherid = b.userid WHERE $condition";
      $result = DB::query($sql);
      while($r = DB::fetch_array($result)) {
        $lists[] = $r;
      }
      return $lists;
  }
  
  function add_teacher($post) {
    //$post = $this->set($post);
    $sqlk = $sqlv = '';
    foreach($post as $k=>$v) {
        if(in_array($k, $this->fields_teacher)) { $sqlk .= ','.$k; $sqlv .= ",'$v'"; }
    }
    $sqlk = substr($sqlk, 1);
    $sqlv = substr($sqlv, 1);
    $sql = "INSERT INTO {$this->dt_pre}classes_teacher ($sqlk) VALUES ($sqlv)";
	  DB::query($sql);
    $itemid = DB::insert_id();
    return $itemid;
  }

  function edit_teacher($post) {
    //$post = $this->set($post);
    $itemid = $post['id'];
    $sql = '';
    foreach($post as $k=>$v) {
      if(in_array($k, $this->fields_teacher)) $sql .= ",$k='$v'";
    }
    $sql = substr($sql, 1);
    DB::query("UPDATE {$this->dt_pre}classes_teacher SET $sql WHERE itemid='{$itemid}'");
  }

  function delete_teacher($itemid) {      
    DB::query("UPDATE {$this->dt_pre}classes_teacher SET isdelete = 1 WHERE itemid='{$itemid}'");
  }
  
  function get_studentlist($condition, $order = 'a.itemid DESC', $cache = '') {
    $lists = array();
    $sql = <<<EOF
SELECT
  a.itemid as id,
  a.startdate,
  a.enddate,
  a.studentid,
  b.babyname,
  b.avatar,
  b.gender,
  b.birthday,
  GROUP_CONCAT(c.parentuserid) AS parentuserids,
  GROUP_CONCAT(c.parentcall) AS parentcalls,
  GROUP_CONCAT(d.truename) AS parenttruenames,
  GROUP_CONCAT(d.mobile) AS parentmobiles,
  GROUP_CONCAT(d.qq) AS parentqqs,
  GROUP_CONCAT(d.wx) AS parentwxs
FROM {$this->dt_pre}classes_student a
  LEFT JOIN {$this->dt_pre}student b
    ON a.studentid = b.itemid
  LEFT JOIN {$this->dt_pre}student_parent c
    ON b.itemid = c.studentid
  LEFT JOIN {$this->dt_pre}member d
    ON c.parentuserid = d.userid
WHERE {$condition}
ORDER BY {$order}      
EOF;
//  echo $sql;exit;
    return DB::query($sql, $cache);
  }
  
  function search_studentlist($condition, $order = 'a.itemid DESC', $cache = '') {
    $lists = array();
    $sql = <<<EOF
SELECT
  a.itemid AS studentid,
  a.babyname,
  a.avatar,
  a.gender,
  a.birthday,
  GROUP_CONCAT(c.parentuserid) AS parentuserids,
  GROUP_CONCAT(c.parentcall) AS parentcalls,
  GROUP_CONCAT(d.truename) AS parenttruenames,
  GROUP_CONCAT(d.mobile) AS parentmobiles,
  GROUP_CONCAT(d.qq) AS parentqqs,
  GROUP_CONCAT(d.wx) AS parentwxs
FROM edu_student a
  LEFT JOIN edu_student_parent c
    ON a.itemid = c.studentid
  LEFT JOIN edu_member d
    ON c.parentuserid = d.userid
WHERE {$condition}
ORDER BY {$order}      
EOF;
//  echo $sql;exit;
    return DB::query($sql, $cache);
  }
  
  function add_student($post) {
    //$post = $this->set($post);
    $sqlk = $sqlv = '';
    foreach($post as $k=>$v) {
        if(in_array($k, $this->fields_student)) { $sqlk .= ','.$k; $sqlv .= ",'$v'"; }
    }
    $sqlk = substr($sqlk, 1);
    $sqlv = substr($sqlv, 1);
    $sql = "INSERT INTO {$this->dt_pre}classes_student ($sqlk) VALUES ($sqlv)";
    DB::query($sql);
    $itemid = DB::insert_id();
    return $itemid;
  }

  function edit_student($post) {
    //$post = $this->set($post);
    $itemid = $post['id'];
    $sql = '';
    foreach($post as $k=>$v) {
      if(in_array($k, $this->fields_student)) $sql .= ",$k='$v'";
    }
    $sql = substr($sql, 1);
    DB::query("UPDATE {$this->dt_pre}classes_student SET $sql WHERE itemid='{$itemid}'");
  }

  function delete_student($itemid) {      
    DB::query("UPDATE {$this->dt_pre}classes_student SET isdelete = 1 WHERE itemid='{$itemid}'");
  }
  
/*
  function app_get_classes($studentids){
    $cache = 0;
    $schools = array();
    if($studentids){
      $condition = "a.studentid in ({$studentids})";
      $order = "a.itemid DESC";
      $result = DB::query("SELECT a.*,b.company,b.thumb,c.classesname FROM {$this->table_classes_student} a left join {$this->table_company} b on a.schoolid = b.userid left join {$this->table_classes} c on a.classesid = c.itemid WHERE $condition ORDER BY $order", $cache);
    }
		while($one = DB::fetch_array($result)) {
			if(empty($schools[$one['studentid']][$one['schoolid']])){
        $tmp = array();
        $tmp['schoolid'] = $one['schoolid'];
        $tmp['company'] = $one['company'];
        $tmp['thumb'] = $one['thumb'];
        $tmp['status'] = 'done';
        $tmp['classes'] = array();
        $schools[$one['studentid']][$one['schoolid']] = $tmp;
      }
      if(empty($one['enddate'])) $schools[$one['studentid']][$one['schoolid']]['status'] = 'doing';

      $tmp = array();
      $tmp['classesname'] = $one['classesname'];
      $tmp['startdate'] = $one['startdate'];
      $tmp['enddate'] = $one['enddate'];
      $schools[$one['studentid']][$one['schoolid']]['classes'][] = $tmp;
		}
		return $schools;
  }
*/
  function set($post) {

    return $post;
  }

  function add($post) {
    $post = $this->set($post);
    $sqlk = $sqlv = '';
		foreach($post as $k=>$v) {
			if(in_array($k, $this->fields)) { $sqlk .= ','.$k; $sqlv .= ",'$v'"; }
		}
    $sqlk = substr($sqlk, 1);
    $sqlv = substr($sqlv, 1);
		DB::query("INSERT INTO {$this->table_classes} ($sqlk) VALUES ($sqlv)");
    $this->itemid = DB::insert_id();
    return $this->itemid;
  }

  function edit($post) {
    $post = $this->set($post);
    $itemid = $post['id'];
    $sql = '';
    foreach($post as $k=>$v) {
      if(in_array($k, $this->fields)) $sql .= ",$k='$v'";
    }
    $sql = substr($sql, 1);
    DB::query("UPDATE {$this->table_classes} SET $sql WHERE itemid='{$itemid}'");
  }

  function delete($itemid) {
    DB::query("UPDATE {$this->table_classes} SET isdelete = 1 WHERE itemid='{$itemid}'");
  }
  
	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>