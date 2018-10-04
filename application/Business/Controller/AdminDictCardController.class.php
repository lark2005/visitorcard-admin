<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: larker <66192480@qq.com>
// +----------------------------------------------------------------------
namespace Business\Controller;
use Business\Controller\AdminDictionaryController;
class AdminDictCardController extends AdminDictionaryController {
	
	protected $dictionary_model;
	protected $taxonomys=array("checkbox"=>"多选框","radio"=>"单选","input"=>"输入框","text"=>"文本");
	
	function _initialize() {
		parent::_initialize();
		$this->dictionary_model = D("Business/DictCard");
		$this->assign("taxonomys",$this->taxonomys);
	}

	function index(){
		
		// 查询所有的卡
		$dictCards = $this->dictionary_model->where(array("status"=>1,"parent"=>0))
		->order(array("listorder"=>"asc"))->select();
		$this->assign("dictCards", $dictCards);
		
		
		//根据参数生成查询条件
    	
		/**搜索条件**/
		$query_dic = I('request.query_dic');
		$this->assign("query_dic", $query_dic);
		//echo $query_dic;
		if($query_dic){
			$where = array("status"=>1);
			$where['path'] = array('like',"$query_dic"."%");

			$result = $this->dictionary_model->where($where)
			->order(array("listorder"=>"asc"))->select();
			
			$tree = new \Tree();
			$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			
			$newmenus=array();
			foreach ($result as $m){
				$newmenus[$m['dic_id']]=$m;
			}
			
			foreach ($result as $n=> $r) {
				$result[$n]['level'] = $this->_get_level($r['dic_id'], $newmenus);
				$result[$n]['parentid_node'] = ($r['parent']) ? ' class="child-of-node-' . $r['parent'] . '"' : '';
				
				$result[$n]['str_manage'] = '<a href="' . U("AdminDictCard/add", array("parent" => $r['dic_id'])) . '">'.L('ADD_SUB_CATEGORY').'</a> | <a href="' . U("AdminDictCard/edit", array("id" => $r['dic_id'])) . '">'.L('EDIT').'</a> | <a class="js-ajax-delete" href="' . U("AdminDictCard/delete", array("id" => $r['dic_id'])) . '">'.L('DELETE').'</a> ';
				$result[$n]['taxonomy'] = $this->taxonomys[$r['taxonomy']];
				$result[$n]['id']=$r['dic_id'];
				$result[$n]['parentid']=$r['parent'];

			}
			
			$tree->init($result);
			$str = "<tr id='node-\$id' \$parentid_node>
						<td>\$id</td>
						<td style='padding-left:20px;'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
						
						<td>\$spacer \$name</td>
						<td>\$spacer \$code</td>
						<td>\$taxonomy</td>
						<td>\$value</td>
						<td>\$str_manage</td>
					</tr>";
			$taxonomys = $tree->get_tree(0, $str);
			$this->assign("taxonomys", $taxonomys);
		}else{
			$this->assign("taxonomys", "");
		}
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
	 	$terms = $this->dictionary_model->order(array("path"=>"asc"))->select();
	 	
	 	$new_terms=array();
	 	foreach ($terms as $r) {
	 		$r['id']=$r['dic_id'];
	 		$r['parentid']=$r['parent'];
	 		$r['selected']= (!empty($parentid) && $r['dic_id']==$parentid)? "selected":"";
	 		$new_terms[] = $r;
	 	}
	 	$tree->init($new_terms);
	 	$tree_tpl="<option value='\$id' \$selected>\$spacer\$name</option>";
	 	$tree=$tree->get_tree(0,$tree_tpl);
	 	
	 	$this->assign("terms_tree",$tree);
	 	$this->assign("parent",$parentid);
	 	$this->display();
	}
	
	function add_post(){
		if (IS_POST) {
			if ($this->dictionary_model->create()!==false) {
				if ($this->dictionary_model->add()!==false) {
				    F('all_dictionary',null);
					$this->success("添加成功！",U("AdminDictCard/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->dictionary_model->getError());
			}
		}
	}
	
	function edit(){
		$id = intval(I("get.id"));
		$data=$this->dictionary_model->where(array("dic_id" => $id))->find();
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$terms = $this->dictionary_model->where(array("dic_id" => array("NEQ",$id), "path"=>array("notlike","%-$id-%")))->order(array("path"=>"asc"))->select();
		
		$new_terms=array();
		foreach ($terms as $r) {
			$r['id']=$r['dic_id'];
			$r['parentid']=$r['parent'];
			$r['selected']=$data['parent']==$r['dic_id']?"selected":"";
			$new_terms[] = $r;
		}
		
		$tree->init($new_terms);
		$tree_tpl="<option value='\$id' \$selected>\$spacer\$name</option>";
		$tree=$tree->get_tree(0,$tree_tpl);
		
		$this->assign("terms_tree",$tree);
		$this->assign("data",$data);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if ($this->dictionary_model->create()!==false) {
				if ($this->dictionary_model->save()!==false) {
				    F('all_dictionary',null);
					$this->success("修改成功！");
				} else {
					$this->error("修改失败了！");
				}
			} else {
				$this->error($this->dictionary_model->getError());
			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->dictionary_model);
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
		$count = $this->dictionary_model->where(array("parent" => $id))->count();
		
		if ($count > 0) {
			$this->error("该菜单下还有子类，无法删除！");
		}
		
		if ($this->dictionary_model->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
}