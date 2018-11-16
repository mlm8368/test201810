<?php 
defined('IN_DESTOON') or exit('Access Denied');
class student {
	var $table_member;
	var $table_student;
	var $table_student_relation;
	var $errmsg = errmsg;

  function __construct($username = '') {
		$this->table_member = DT_PRE.'member';
		$this->table_company = DT_PRE.'company';
    $this->table_student = DT_PRE.'student';
    $this->table_student_parent = DT_PRE.'student_parent';
    $this->table_classes = DT_PRE.'classes';
    $this->table_classes_teacher = DT_PRE.'classes_teacher';
    $this->table_classes_student = DT_PRE.'classes_student';
  }

  function student($username = '') {
		$this->__construct($username);
  }

	function get_list($condition, $order = 'itemid DESC', $cache = '') {
		global $pages, $page, $pagesize, $offset, $items, $sum;
		if($page > 1 && $sum) {
			$items = $sum;
		} else {
			$r = DB::get_one("SELECT COUNT(*) AS num FROM {$this->table_student} WHERE $condition", $cache);
			$items = $r['num'];
		}
		$pages = defined('CATID') ? listpages(1, CATID, $items, $page, $pagesize, 10, $MOD['linkurl']) : pages($items, $page, $pagesize);
		if($items < 1) return array();
		$lists = array();
		$result = DB::query("SELECT * FROM {$this->table_student} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize", $cache);
		while($r = DB::fetch_array($result)) {
			$lists[] = $r;
		}
		return $lists;
  }
  
  function get_parentlist($condition, $order = 'itemid DESC', $cache = '') {
		$lists = array();
		$result = DB::query("SELECT a.*,b.username,b.truename FROM {$this->table_student_parent} a left join {$this->table_member} b on a.parentuserid = b.userid WHERE $condition ORDER BY $order", $cache);
		while($r = DB::fetch_array($result)) {
			$lists[] = $r;
		}
		return $lists;
  }
  
  function app_get_baobaos($studentids){
    $baobaos = array();
    if($studentids){
      $sql = "SELECT a.itemid,a.babyname,a.avatar,a.gender,a.birthday,a.parentnum, b.studentavatar,b.parentuserid,b.parentcall,f.avatarpic,c.classesid,d.classesname,c.startdate,c.enddate,"
        ."g.itemid AS classesteacherid, g.teacheruserid, g.teacherpost, h.truename,h.avatarpic AS teachavatarpic,d.schoolid,e.company,e.thumb,e.hits,e.comments"
        ." FROM {$this->table_student} a LEFT JOIN {$this->table_student_parent} b ON a.itemid = b.studentid LEFT JOIN {$this->table_classes_student} c ON a.itemid = c.studentid"
        ." LEFT JOIN {$this->table_classes} d ON c.classesid = d.itemid LEFT JOIN {$this->table_company} e ON d.schoolid = e.userid LEFT JOIN {$this->table_member} f ON b.parentuserid = f.userid"
        ." LEFT JOIN {$this->table_classes_teacher} g ON c.classesid = g.classesid LEFT JOIN {$this->table_member} h ON g.teacheruserid = h.userid"
        ." WHERE a.itemid IN ({$studentids})";
      $result = DB::query($sql);

      while($one = DB::fetch_array($result)) {
        if(empty($baobaos[$one['itemid']]['baobao'])) {
          $tmp = array();
          $tmp['itemid'] = $one['itemid'];
          $tmp['babyname'] = $one['babyname'];
          $tmp['avatar'] = $one['studentavatar'];
          if(empty($tmp['avatar'])) $tmp['avatar'] = $one['avatar'];
          $tmp['gender'] = $one['gender'];
          $tmp['birthday'] = $one['birthday'];
          $tmp['parentnum'] = $one['parentnum'];
          $tmp['classesid'] = 0;

          $baobaos[$one['itemid']]['baobao'] = $tmp;
        }

        if(empty($baobaos[$one['itemid']]['parent'][$one['parentuserid']])){
          $tmp = array();
          $tmp['parentcall'] = $one['parentcall'];
          $tmp['avatarpic'] = $one['avatarpic'];
          $tmp['parentuserid'] = $one['parentuserid'];
          $baobaos[$one['itemid']]['parent'][$one['parentuserid']] = $tmp;
        }

        if(empty($baobaos[$one['itemid']]['school'][$one['schoolid']])){
          $tmp = array();
          $tmp['school'] = $one['company'];
          $tmp['firstchar'] = mb_substr($one['company'], 0, 1, 'UTF-8');
          $tmp['thumb'] = $one['thumb'];
          $tmp['hits'] = $one['hits'];
          $tmp['comments'] = $one['comments'];
          $tmp['status'] = 'done';
          $baobaos[$one['itemid']]['school'][$one['schoolid']] = $tmp;
        }
        
        if(empty($baobaos[$one['itemid']]['classes'][$one['classesid']])){
          $tmp = array();
          $tmp['classesname'] = $one['classesname'];
          $tmp['firstchar'] = mb_substr($one['classesname'], 0, 1, 'UTF-8');
          $tmp['totaltime'] = '0';
          $tmp['startdate'] = $one['startdate'];
          $tmp['starttime'] = strtotime($one['startdate'].' 00:00:00') * 1000;
          $tmp['enddate'] = $one['enddate'];
          $tmp['endtime'] = 0;
          $tmp['teachers'] = array();
          if($tmp['enddate']) {
            $tmp['endtime'] = strtotime($one['enddate'].' 00:00:00') * 1000;
            if(empty($baobaos[$one['itemid']]['baobao']['classesid'])) {
              $baobaos[$one['itemid']]['baobao']['classesid'] = $one['classesid'];
            }
          } else {
            $baobaos[$one['itemid']]['baobao']['classesid'] = $one['classesid'];
            $baobaos[$one['itemid']]['school'][$one['schoolid']]['status'] = 'doing';
          } 
          $tmp['schoolid'] = $one['schoolid'];
          $baobaos[$one['itemid']]['classes'][$one['classesid']] = $tmp;

          $baobaos[$one['itemid']]['school'][$one['schoolid']]['classesids'][] = $one['classesid'];
        }

        if(empty($baobaos[$one['itemid']]['classes'][$one['classesid']]['teachers'][$one['classesteacherid']])){
          $tmp = array();
          $tmp['userid'] = $one['teacheruserid'];
          $tmp['truename'] = $one['truename'];
          $tmp['avatarpic'] = $one['teachavatarpic'];
          $tmp['teacherpost'] = $one['teacherpost'];

          $baobaos[$one['itemid']]['classes'][$one['classesid']]['teachers'][$one['classesteacherid']] = $tmp;
        }
      }
    }
		
		return $baobaos;
  }

	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>