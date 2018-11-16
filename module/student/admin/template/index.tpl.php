<?php
defined('DT_ADMIN') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<form action="?">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<table cellspacing="0" class="tb">
<tr>
<td>&nbsp;
<input type="text" size="25" name="kw" value="<?php echo $kw;?>" placeholder="请输入关键词" title="请输入关键词"/>&nbsp;
<input type="text" name="psize" value="<?php echo $pagesize;?>" size="2" class="t_c" title="条/页"/>&nbsp;
<input type="submit" value="搜 索" class="btn"/>&nbsp;
<input type="button" value="重 置" class="btn" onclick="Go('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>');"/>
</td>
</tr>
</table>
</form>
<form method="post">
<table cellspacing="0" class="tb ls">
<tr>
<th width="20"><input type="checkbox" onclick="checkall(this.form);"/></th>
<th width="14"> </th>
<th><?php echo $MOD['name'];?>名称</th>
<th>性别</th>
<th>生日</th>
<th>家长</th>
<th>添加日期</th>
<th width="60">操作</th>
</tr>
<?php foreach($lists as $k=>$v) {?>
<tr align="center">
<td><input type="checkbox" name="userid[]" value="<?php echo $v['userid'];?>"/></td>
<td><?php if($v['avatar']) {?><a href="javascript:_preview('<?php echo $v['avatar'];?>');"><img src="admin/image/img.gif" width="10" height="10" title="宝宝头像,点击预览" alt=""/></a><?php } ?></td>
<td><?php echo $v['babyname'];?></td>
<td><?php echo $v['gender'];?></td>
<td><?php echo $v['birthday'];?></td>
<td class="px12"><a href="javascript:Dwidget('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=parentlist&sid=<?php echo $v['itemid'];?>', '查看家长');"><?php echo $v['parentnum'];?></a></td>
<td><?php echo $v['addtime'];?></td>
<td><a href="?moduleid=2&action=delete&userid=<?php echo $v['userid'];?>" onclick="return _delete();"><img src="admin/image/delete.png" width="16" height="16" title="删除" alt=""/></a></td>
</tr>
<?php }?>
</table>
<div class="btns">
<input type="submit" value="删除宝宝" class="btn-r" onclick="if(confirm('确定要删除选中宝宝吗？')){this.form.action='?moduleid=2&action=delete'}else{return false;}"/>&nbsp;
</div>
</form>
<?php echo $pages ? '<div class="pages">'.$pages.'</div>' : '';?>
<script type="text/javascript">Menuon(<?php echo $menuid;?>);</script>
<?php include tpl('footer');?>