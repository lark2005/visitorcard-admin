<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wwl <66192480@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\HomebaseController;
class CityAreaController extends HomebaseController {
	
	protected $areaTypes=array("0"=>"国家地区","1"=>"省份","2"=>"城市","3"=>"区县","4"=>"乡镇");
	
	function _initialize() {
		parent::_initialize();
		$this->area_model = D("Business/DictArea");
		$this->assign("areaTypes",$this->areaTypes);
	}
	function getJson(){
		// 区域级别
		
		
		/**搜索条件**/
		$where = array("status"=>1);
		
		/**搜索条件 type**/
		$query_areaType = I('request.type');
		//$this->assign("query_areaType", $query_areaType);
		if($query_areaType || $query_areaType ==0){
			// $where['type'] = array('eq',"$type");
			$where['type'] = array('eq',"$query_areaType");
		}
		/**搜索条件 name**/
		$query_areaName = I('request.query_areaName');
		// $this->assign("query_areaName", $query_areaName);
		if($query_areaName){
			$where['full_name'] = array('like',"%$query_areaName"."%");
		}
		/**搜索条件 parent**/
		$query_parent = I('request.parent');
		// $this->assign(query_parent);
		if($query_parent){
			$where['parent'] = array('eq',"$query_parent");
		}
		
		// 查询
		$result = $this->area_model->where($where)->order(array("type"=>"asc","listorder"=>"asc","area_id"=>"asc"))->field('area_id,name,path,parent')->select();
		//echo $this->area_model->getlastSql(); 

		echo  json_encode($result,JSON_UNESCAPED_UNICODE);
	}
	
	
	
}