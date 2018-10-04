<?php
namespace Business\Controller;

use Common\Controller\AdminbaseController;


class AdminVisitorCardController extends AdminbaseController{

	protected $setting_model;
	
	public function _initialize() {
		parent::_initialize();
		$this->setting_model = D("Business/VisitorCardSetting");
	}

	public function index(){
		$where = array();
		/**搜索条件**/
		$query_card_title = I('request.query_card_title');
		if($query_card_title){
			$where['card_title'] = array('like',"%$query_card_title%");
		}
		//$where['status'] = 1;
		
		$count=$this->setting_model->where($where)->count();
		//echo $this->setting_model->getlastSql(); 
		$page = $this->page($count, 20);
        $items = $this->setting_model
            ->where($where)
            ->order("create_time DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

		//echo $this->setting_model->getlastSql(); 
			
		$this->assign("page", $page->show('Admin'));
		$this->assign("items",$items);
		$this->display();
	}
	
		
	function add(){
	 	$this->display();
	}
	
	function add_post(){
		if (IS_POST) {
			if ($this->setting_model->create()!==false) {
				if ($this->setting_model->add()!==false) {
				    F('all_vistor_card_setting',null);
					$this->success("添加成功！",U("AdminVisitorCard/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->setting_model->getError());
			}
		}
	}
	
	function edit(){
		$id = intval(I("get.id"));
		$data=$this->setting_model->where(array("id" => $id))->find();

		$this->assign("data",$data);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			if ($this->setting_model->create()!==false) {
				// 更新时间
				$this->setting_model->update_time = date("y-m-d h:i:s",time());
				if ($this->setting_model->save()!==false) {
				    F('all_vistor_card_setting',null);
					$this->success("修改成功！");
				} else {
					$this->error("修改失败了！");
				}
			} else {
				$this->error($this->setting_model->getError());
			}
		}
	}
	
	public function delete(){
		if(isset($_GET['id'])){
			$id = I("get.id",0,'intval');
			
			if ($this->setting_model->where(array('id'=>$id))->save(array('status'=>0)) !==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		
		if(isset($_POST['ids'])){
			$ids = I('post.ids/a');
			
			if ($this->setting_model->where(array('id'=>array('in',$ids)))->save(array('status'=>0))!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}

}