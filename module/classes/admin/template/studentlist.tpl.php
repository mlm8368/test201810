<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);

function gender2($gender) {
	return $gender == 1 ? "男" : "女";
}
?>
<form method="post">
<table cellspacing="0" class="tb ls">
<tr>
<th width="20"><input type="checkbox" onclick="checkall(this.form);"/></th>
<th>姓名</th>
<th>入校时间</th>
<th>毕业时间</th>
<th>性别</th>
<th>生日</th>
</tr>
<?php foreach($lists as $k=>$v) {?>
<tr align="center">
<td><input type="checkbox" name="itemid[]" value="<?php echo $v['itemid'];?>"/></td>
<td><?php echo $v['babyname'];?></td>
<td><?php echo $v['startdate'];?></td>
<td><?php echo $v['enddate'];?></td>
<td><?php echo gender2($v['gender']);?></td>
<td><?php echo $v['birthday'];?></td>
</tr>
<?php }?>
</table>
</form>
<?php echo $pages ? '<div class="pages">'.$pages.'</div>' : '';?>
<script type="text/javascript">Menuon(<?php echo $menuid;?>);</script>
<?php include tpl('footer');?>