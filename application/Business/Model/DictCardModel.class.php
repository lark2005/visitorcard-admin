<?php
namespace Business\Model;
use Common\Model\CommonModel;
class DictCardModel extends CommonModel {
	
	// 数据表前缀
	protected $tablePrefix = 'biz_'; 

	/*
	 * dic_id category name description pid path status
	 */
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('name', 'require', '名称不能为空！', 1, 'regex', 3),
	);
	
	protected function _after_insert($data,$options){
		parent::_after_insert($data,$options);
		$dic_id=$data['dic_id'];
		$parent_id=$data['parent'];

		if($parent_id==0){
			$d['path']="0-$dic_id";
			//$d['pathorder']="0";
		}else{
			$parent=$this->where("dic_id=$parent_id")->find();
			$d['path']=$parent['path'].'-'.$dic_id;
			//$d['pathorder']=$parent['pathorder'].'-'.$data['listorder'];
		}
		$code=$data['code'];
		if(empty($code)){
			$d['code']=$d['path'];
		}

		$this->where("dic_id=$dic_id")->save($d);
	}
	
	protected function _after_update($data,$options){
		parent::_after_update($data,$options);
		if(isset($data['parent'])){
			$dic_id=$data['dic_id'];
			$parent_id=$data['parent'];
			if($parent_id==0){
				$d['path']="0-$dic_id";
				//$d['pathorder']="0";
			}else{
				$parent=$this->where("dic_id=$parent_id")->find();
				$d['path']=$parent['path'].'-'.$dic_id;
				//$d['pathorder']=$parent['pathorder'].'-'.$data['listorder'];
			}
			$code=$data['code'];
			if(empty($code)){
				$d['code']=$d['path'];
			}
			
			$result=$this->where("dic_id=$dic_id")->save($d);
			
			//  echo "pathorder=".$d['pathorder'];
			if($result){
				$children=$this->where(array("parent"=>$dic_id))->select();
				foreach ($children as $child){
					$this->where(array("dic_id"=>$child['dic_id']))->save(array("parent"=>$dic_id,"dic_id"=>$child['dic_id']));
				}
			}
		}
		
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	

}