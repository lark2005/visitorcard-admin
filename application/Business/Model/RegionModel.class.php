<?php
namespace Business\Model;
use Common\Model\CommonModel;
class RegionModel extends CommonModel {
	
	// 数据表前缀
	protected $tablePrefix = 'biz_'; 

	/*
	 * area_id category name description pid path status
	 */
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('name', 'require', '名称不能为空！', 1, 'regex', 3),
	);
	
	protected function _after_insert($data,$options){
		parent::_after_insert($data,$options);
		$area_id=$data['area_id'];
		$parent_id=$data['parent'];
		if($parent_id==0){
			$d['path']="0-$area_id";
			$d['full_name']=$data['name'];
			$d['type']= 0;
		}else{
			$parent=$this->where("area_id=$parent_id")->find();
			$d['path']=$parent['path'].'-'.$area_id;
			$d['full_name']=$parent['full_name'].' '.$data['name'];
			$d['type']= intval($parent['type'])+1 .'';
		}
		$this->where("area_id=$area_id")->save($d);
	}
	
	protected function _after_update($data,$options){
		parent::_after_update($data,$options);
		if(isset($data['parent'])){
			$area_id=$data['area_id'];
			$parent_id=$data['parent'];
			if($parent_id==0){
				$d['path']="0-$area_id";
				$d['full_name']=$data['name'];
			}else{
				$parent=$this->where("area_id=$parent_id")->find();
				$d['path']=$parent['path'].'-'.$area_id;
				$d['full_name']=$parent['full_name'].' '.$data['name'];
			}
			$result=$this->where("area_id=$area_id")->save($d);
			/* 不能修改上级
			if($result){
				$children=$this->where(array("parent"=>$area_id))->select();
				foreach ($children as $child){
					$this->where(array("area_id"=>$child['area_id']))
					->save(array("parent"=>$area_id,"area_id"=>$child['area_id']));
				}
			}
			*/
		}
		
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	

}