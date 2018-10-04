<?php
namespace Business\Controller;

use Common\Controller\AdminbaseController;

class AdminVisitorController extends AdminbaseController{

	protected $users_model;
	protected $dictionary_model;

	public function _initialize() {
		parent::_initialize();
		$this->users_model = D("Business/Visitor");
		$this->dictionary_model = D("Business/DictCard");
	}

	public function index(){
		
		// 查询所有的卡
		$dictCards = $this->dictionary_model->where(array("status"=>1,"parent"=>0))
		->order(array("listorder"=>"asc"))->select();
		$this->assign("dictCards", $dictCards);
		
		
		$where = array("user_status"=>1);
		/**搜索条件**/
		$query_card_title = I('request.query_card_title');
		$this->assign("query_card_title", $query_card_title);
		// echo "query_card_title=".$query_card_title;
		
		$query_name = I('request.query_name');
		$this->assign("query_name", $query_name);
		
		$query_mobile = trim(I('request.query_mobile'));
		$this->assign("query_mobile", $query_mobile);
		
		$query_field = trim(I('request.query_field'));
		$this->assign("query_field", $query_field);
		
		$query_value = trim(I('request.query_value'));
		$this->assign("query_value", $query_value);
		
		if($query_card_title){
			$where['card_title'] = array('like',"%$query_card_title%");
		}
		
		if($query_name){
			$where['name'] = array('like',"%$query_name%");
		}
		
		if($query_mobile){
			$where['mobile'] = array('like',"%$query_mobile%");
		}
		
		if($query_value){
			$where[$query_field] = array('like',"%$query_value%");
		}

		
		$count=$this->users_model->where($where)->count();
		$page = $this->page($count, 20);
        $users = $this->users_model
            ->where($where)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

		// echo $this->$users_model->getlastSql(); 

		$this->assign("page", $page->show('Admin'));
		$this->assign("users",$users);
		$this->display();
	}
	
	public function userinfo(){
		 $id = I('get.id',0,'intval');
		$user=$this->users_model->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->display();
	}

	public function exportVisitors(){

		$where = array("user_status"=>1);
		/**搜索条件**/
		$query_card_title = I('request.query_card_title');
		$query_name = I('request.query_name');
		$query_mobile = trim(I('request.query_mobile'));
		$query_field = trim(I('request.query_field'));
		$query_value = trim(I('request.query_value'));
		
		if($query_card_title){
			$where['card_title'] = array('like',"%$query_card_title%");
		}
		
		if($query_name){
			$where['name'] = array('like',"%$query_name%");
		}
		
		if($query_mobile){
			$where['mobile'] = array('like',"%$query_mobile%");
		}
		
		if($query_value){
			$where[$query_field] = array('like',"%$query_value%");
		}

		
		$count=$this->users_model->where($where)->count();
		$page = $this->page($count, 5000);// 最多到处5000条
        $expTableData = $this->users_model
            ->where($where)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
			
		// 导出设置
        $expCellName  = array(
            array('id','序号'),
			array('card_title','参观证标题'),
            array('name','姓名'),
            array('mobile','手机号'),
            array('company','公司'),
            array('position','职位'),
            array('country','国家区域'),
			array('province','省'),
			array('city','市'),
			array('create_time','登记时间'),
        );
		$xlsTitle = iconv('utf-8', 'gb2312', "参观用户数据");//文件名称
        $fileName = $xlsTitle . date('YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();
		// 设置工作簿的名称
        $objPHPExcel->getActiveSheet()->setTitle("参观用户数据". date('YmdHis'));

        $cellName = array('A','B','C','D','E','F','G','H','I','J');
        //$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
//        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
//         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
// 

        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
				
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), ' '.$expTableData[$i][$expCellName[$j][0]]);
            }
        }
		
		// 表头
		for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
			// $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i].'')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i].'')->setWidth(15);;
			//Set alignments 设置对齐  
			//$objPHPExcel->getActiveSheet()->getStyle('D11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  //$objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);  
			$objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); 
			$objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getFont()->setBold(true);  
		}

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');//Excel5为xls格式，excel2007为xlsx格式
        $objWriter->save('php://output');
        exit;

	
	}
	
	
}