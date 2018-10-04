<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wwl <66192480@qq.com>
// +----------------------------------------------------------------------
namespace Business\Controller;
use Common\Controller\AdminbaseController;
class AdminDictAreaController extends AdminbaseController {
	
	protected $areaTypes=array("0"=>"国家地区","1"=>"省份","2"=>"城市","3"=>"区县","4"=>"乡镇");
	
	function _initialize() {
		parent::_initialize();
		$this->area_model = D("Business/DictArea");
		//$this->area_model = D("Business/Region");
		$this->assign("areaTypes",$this->areaTypes);
	}
	function index(){
		// 区域级别		
		$query_hasChildren = I('request.query_hasChildren');
		if(empty($query_hasChildren)){
			$query_hasChildren = "0";
		}
		$this->assign("query_hasChildren", $query_hasChildren);
		
		/**搜索条件**/
		$where = array("status"=>1);
		
		// 查询级别
		$query_areaType = I('request.query_areaType');
		if(empty($query_areaType)){
			$query_areaType = "0";
		}
		$this->assign("query_areaType", $query_areaType);
		if($query_hasChildren == "1"){
			// $where['type'] = array('eq',"$query_areaType");
			$where['type'] = array('elt',$query_areaType);
		}else{
			$where['type'] = array('eq',"$query_areaType");
		}
		
		// 查询编码
		$query_areaCode = I('request.query_areaCode');
		if(!empty($query_areaCode)){
			$where['code'] = array('like',$query_areaCode."%");
		}
		$this->assign("query_areaCode", $query_areaCode);
		
		// 上级
		$query_parent = I('request.query_parent');
		if(!empty($query_parent)){
			$where['parent'] = array('eq',"$query_parent");
		}
		$this->assign("query_parent", $query_parent);
		// 查询
		$query_parentType = $query_areaType-1;
		$result_parentArea = $this->area_model->where(array("type"=>$query_parentType))->order(array("type"=>"asc","listorder"=>"asc"))->select();
		$this->assign("result_parentArea", $result_parentArea);

		
		/**搜索条件**/
		$query_areaName = I('request.query_areaName');
		$this->assign("query_areaName", $query_areaName);
		if($query_areaName){
			$where['name'] = array('like',"%$query_areaName"."%");
		}
		
		// 查询
		$result = $this->area_model->where($where)->order(array("type"=>"asc","listorder"=>"asc","area_id"=>"asc"))->select();
		// echo $this->area_model->getlastSql(); 
		
		// 按名称查询，不显示树结构
		if($query_hasChildren != "1" || empty($query_areaName) ){
			$taxonomys = "";
			$index = 0;
			foreach ($result as $n=> $r) {
				
				$str_manage = '<a href="' . U("AdminDictArea/add", array("parent" => $r['area_id'])) . '">'.L('ADD_SUB_CATEGORY').'</a> | <a href="' . U("AdminDictArea/edit", array("id" => $r['area_id'])) . '">'.L('EDIT').'</a> | <a class="js-ajax-delete" href="' . U("AdminDictArea/delete", array("id" => $r['area_id'])) . '">'.L('DELETE').'</a> ';
				$id=$r['area_id'];
				$index++;
				
				$name=$r['name'];
				$shortName =$r['short_name'];
				$code=$r['code'];
				$listorder = $r['listorder'];
				
				$taxonomy = $this->areaTypes[$r['type']];
				
				$taxonomys = $taxonomys ."<tr id='node-$id' $parentid_node>
						<td style='padding-left:20px;'>
						<input name='listorders[$id]' type='text' size='3' value='$listorder' class='input input-order'></td>
						<td>$index</td>
						<td>$name</td>
						<td>$code</td>
						<td>$shortName</td>
						<td>$taxonomy</td>
						<td>$str_manage</td>
					</tr>";

			}
			//echo 'taxonomys================'.$taxonomys;
			
			
		}else{
			$tree = new \Tree();
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			
			$newmenus=array();
			foreach ($result as $m){
				$newmenus[$m['area_id']]=$m;
			}
			
			foreach ($result as $n=> $r) {
				$result[$n]['level'] = $this->_get_level($r['area_id'], $newmenus);
				$result[$n]['parentid_node'] = ($r['parent']) ? ' class="child-of-node-' . $r['parent'] . '"' : '';
				
				$result[$n]['str_manage'] = '<a href="' . U("AdminDictArea/add", array("parent" => $r['area_id'])) . '">'.L('ADD_SUB_CATEGORY').'</a> | <a href="' . U("AdminDictArea/edit", array("id" => $r['area_id'])) . '">'.L('EDIT').'</a> | <a class="js-ajax-delete" href="' . U("AdminDictArea/delete", array("id" => $r['area_id'])) . '">'.L('DELETE').'</a> ';
				$result[$n]['id']=$r['area_id'];
				$result[$n]['parentid']=$r['parent'];
				$shortName =$r['short_name'];
				
				$result[$n]['taxonomy'] = $this->areaTypes[$r['type']];

			}

		
			$tree->init($result);
			$str = "<tr id='node-\$id' \$parentid_node>
						<td style='padding-left:20px;'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
						<td>\$id</td>
						<td>\$spacer \$name</td>
						<td>\$spacer \$code</td>
						<td>\$spacer \$shortName</td>
						<td>\$taxonomy</td>
						<td>\$str_manage</td>
					</tr>";
			$taxonomys = $tree->get_tree(0, $str);
			
		}
		$this->assign("taxonomys", $taxonomys);
		$this->display();
	}
	
	/**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {
    
    	if ($array[$id]['parent']==0 || empty($array[$array[$id]['parent']]) || $array[$id]['parent']==$id){
    		return  $i;
    	}else{
    		$i++;
    		return $this->_get_level($array[$id]['parent'],$array,$i);
    	}
    
    }
	
	
	function add(){
	 	$parentid = intval(I("get.parent"));
	 	$tree = new \Tree();
	 	$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
	 	$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$where = array("area_id"=>$parentid);
	 	$terms = $this->area_model->where($where)->order(array("path"=>"asc"))->select();
	 	//echo $this->area_model->getlastSql(); 
		
		$option_parent = "";
		foreach ($terms as $n=> $r) {
			$id=$r['area_id'];
			$name=$r['name'];
			$code=$r['code'];
			$listorder = $r['listorder'];
			$selected= (!empty($parentid) && $r['area_id']==$parentid)? "selected":"";
			$option_parent = $option_parent ."<option value='".$id."' \$selected>".$name."</option>";
		}
		
	 	// echo $option_parent; 
	 	$this->assign("option_parent",$option_parent);
	 	$this->assign("parent",$parentid);
	 	$this->display();
	}
	
	function add_post(){
		if (IS_POST) {
			if ($this->area_model->create()!==false) {
				if ($this->area_model->add()!==false) {
				    F('all_area',null);
					$this->success("添加成功！",U("AdminDictArea/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->area_model->getError());
			}
		}
	}
	
	function edit(){
		$id = intval(I("get.id"));
		$data=$this->area_model->where(array("area_id" => $id))->find();
		
		$parent=$this->area_model->where(array("area_id" => $data['parent']))->find();
		//echo $this->area_model->getlastSql(); 

		$this->assign("data",$data);
		$this->assign("parent",$parent);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if ($this->area_model->create()!==false) {
				if ($this->area_model->save()!==false) {
				    F('all_area',null);
					$this->success("修改成功！");
				} else {
					$this->error("修改失败了！");
				}
			} else {
				$this->error($this->area_model->getError());
			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->area_model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	/**
	 *  删除
	 */
	public function delete() {
		$id = intval(I("get.id"));
		$count = $this->area_model->where(array("parent" => $id))->count();
		
		if ($count > 0) {
			$this->error("还有子类，无法删除！");
		}
		
		if ($this->area_model->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}