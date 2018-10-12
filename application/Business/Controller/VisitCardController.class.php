<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: larker <66192480@qq.com>
namespace Business\Controller;

use Common\Controller\HomebaseController;

class VisitCardController extends HomebaseController {
	protected $setting_model;
	protected $dictionary_model;
	protected $visitor_model;
	protected $wx_appid = "wxe873053fa2eb062c";
	protected $wx_secret = "";
	protected $wx_redirect_uri="";
	protected $card_setting_default = "11";
	
	function _initialize() {
		parent::_initialize();
		$this->setting_model = D("Business/VisitorCardSetting");
		$this->dictionary_model = D("Business/DictCard");
		$this->visitor_model = D("Business/Visitor");
		$this->visitor_card_data_model = D("Business/VisitorCardData");
	}
	
    // 第0页 微信页
	public function index_wx() {
		$this->display();
	}
	// 第0页 微盟页
	public function index_wm() {
		$this->display();
	}
    public function wx_index() {
		$oauth2CodeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$wx_appid."&redirect_uri=".$wx_redirect_uri."&response_type=code&scope=snsapi_base&state=1#wechat_redirect";
		
		$oauth2AccessTokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$wx_appid."&secret=".$wx_appid."&code=$code&grant_type=authorization_code";
		
		echo "oauth2AccessTokenUrl=".$oauth2AccessTokenUrl;
		$oauth2AccessToken = getJson($oauth2AccessTokenUrl);
		$openid = $oauth2AccessToken['openid'];
		
		echo "openid=".$openid;
		
		
		// 参观证信息id
    	$card_setting_id=I('get.id',0,'intval');
		// echo 'card_setting_id='.$card_setting_id; 
		if(isset($_SESSION['card_setting_id'])){
			$session_card_setting_id = $_SESSION['card_setting_id'];
		}
		if($card_setting_id == 0 && !empty($session_card_setting_id)){
			$card_setting_id = $session_card_setting_id;
		}
		// 新的模版，新的开始
		if($card_setting_id != $session_card_setting_id){
			unset($_SESSION['visitorId']);
		}
		
		// echo 'card_setting_id='.$card_setting_id;
		if($card_setting_id == 0 ){
			// 默认
			$card_setting_id=1;
		}
		$_SESSION['card_setting_id'] = $card_setting_id;
		
		
		// $this->redirect('VisitCard/visitcard');
	}
	
    // 第一页  用户信息
    public function index() {

		// unset($_SESSION['visitor_finish']);
		// 参观证信息id
    $card_setting_id=I('get.id',0,'intval');
    if($card_setting_id == 0){
    	$card_setting_id=I('get.card_id',0,'intval');
		}
    
		//echo 'card_setting_id='.$card_setting_id; 
		if(isset($_SESSION['card_setting_id'])){
			$session_card_setting_id = $_SESSION['card_setting_id'];
		}
		
		if($card_setting_id == 0 && !empty($session_card_setting_id)){
			//$card_setting_id = $session_card_setting_id;
		}
		// echo 'card_setting_id33332323232='.$session_card_setting_id; 
		// echo 'card_setting_id='.$card_setting_id; 
		
		// echo "$card_setting_id=".$card_setting_id;
		// 新的模版，新的开始
		if($card_setting_id != $session_card_setting_id){
			unset($_SESSION['visitorId']);
		}
		
		// echo 'card_setting_id='.$card_setting_id;
		if($card_setting_id == 0 ){
			// 默认
			$card_setting_id=$this->card_setting_default;
			unset($_SESSION['visitorId']);
		}
		//echo "card_setting_id123=".$card_setting_id;

		// 判断是否有visitor信息
		// session获取visitorId
		if(isset($_SESSION['visitorId'])){
			// 判断完成
			if(isset($_SESSION['visitor_finish']) && $_SESSION['visitor_finish'] == 'finish'){
				$this->redirect('VisitCard/visitcard',array('card_id'=>$card_setting_id));
				// $this->redirect('VisitCard/visitcard');
				return ;
			}
		}
		// 参数获取openId
		$wx_openid=I('request.openid');
		$wx_neckname=I('request.neckname');
		if(empty($wx_openid)){
				$wx_openid=$_SESSION['wx_openid'];
		}
		$_SESSION['wx_openid'] = $wx_openid;
		$_SESSION['wx_neckname'] = $wx_neckname;
		//echo 'openid='.$wx_openid;
		
		// 查询数据库
		if(!empty($wx_openid)){
			// 查询获得用户信息设置信息
	    $visitor=$this->visitor_model
			->where(array('wx_openid'=>$wx_openid,'card_id'=>$card_setting_id))
			->find();
			// echo 'visitor1='.$visitor["id"];
			if(!empty($visitor)){
				//echo 'visitor2='.$visitor["id"];
				$_SESSION['visitorId'] = $visitor["id"];
				$this->redirect('VisitCard/visitcard',array('card_id'=>$card_setting_id));
			}
		}
		
		
		// echo $_SESSION['visitorId'] .'==='.$_SESSION['visitor_finish'];
		unset($_SESSION['visitor_finish']);
		

		// 查询获得设置信息
    $cardSetting=$this->setting_model
		->where(array('id'=>$card_setting_id))
		->find();
		
		if(empty($cardSetting)){
					unset($_SESSION['card_setting_id']);
    	    // 跳回到参观证第1页注册页面
    	    $this->redirect('VisitCard/index');
    	    return;
    }
		
		$_SESSION['card_setting_id'] = $card_setting_id;
		// echo 'card_setting_id333='.$card_setting_id; 
		
		
		$this->assign("cardSetting",$cardSetting);
    	
		$tplname = "visitor_tpls/".$cardSetting['visitor_tpl'];
    	$this->display("VisitCard:$tplname");

    }
	
	// 发送短信验证码(test)
	function sendMsgCode(){
		$resultArr = array ('code'=>"1",'message'=>"");
		if (IS_POST) {
			$mobile= I('request.mobile');
			if ($this->visitor_model->create()!==false) {
				// HTTP_HOST地址获取
				$HTTP_HOST = $_SERVER['HTTP_HOST'];
				//echo "HTTP_HOST = ".$HTTP_HOST;
				
				$msg_model = D("Business/Msg");
				// 需要更新的数据
				$data = $msg_model->create();
				$data['mobile'] = $mobile;
				// 随机6位数
				$msgCode = generate_code(); 
				$data['code'] = $msgCode;
				
				$content = "你的6位验证码是：".$msgCode .",有效期为30分钟,请注意保密。";
				$data['content'] = $content;
				$data['type'] = "注册验证码";
				$data['create_time']=date('Y-m-d H:i:s');
				$data['status']= 0;// 测试不真发
				
				$msg_model->add($data);
				$resultArr['code'] = "0";
				$resultArr['message'] = " [  ".$msgCode."  ] ";
				
				// post发送短信
				// echo "content=".$content;
				// $resultArr['message'] = " ";
				// $sendMsgData = sendMsg_post($mobile,$content);
				// $resultArr['data'] = "".$sendMsgData;
				
			} else {
				$resultArr['code'] = "0";
				$resultArr['message'] = "";
			}
		}
		echo json_encode($resultArr);
	}
	// 发送短信验证码(true)
	function send2MsgCode(){
		$resultArr = array ('code'=>"1",'message'=>"");
		if (IS_POST) {
			$mobile= I('request.mobile');
			if ($this->visitor_model->create()!==false) {
				// HTTP_HOST地址获取
				$HTTP_HOST = $_SERVER['HTTP_HOST'];
				//echo "HTTP_HOST = ".$HTTP_HOST;
				
				$msg_model = D("Business/Msg");
				// 需要更新的数据
				$data = $msg_model->create();
				$data['mobile'] = $mobile;
				// 随机6位数
				$msgCode = generate_code(); 
				$data['code'] = $msgCode;
				
				$content = "你的6位验证码是：".$msgCode .",有效期为30分钟,请注意保密。";
				$data['content'] = $content;
				$data['type'] = "注册验证码";
				$data['create_time']=date('Y-m-d H:i:s');
				$data['status']= 1;//真发
				
				$msg_model->add($data);
				$resultArr['code'] = "0";
				$resultArr['message'] = " ".$msgCode." ";
				
				// post发送短信
				$resultArr['message'] = " ";
				// echo "content=".$content;
				$sendMsgData = sendMsg_post($mobile,$content);
				$resultArr['data'] = "".$sendMsgData;
				
			} else {
				$resultArr['code'] = "0";
				$resultArr['message'] = "";
			}
		}
		echo json_encode($resultArr);
	}
	// 校验验证码
	function checkMsgCode(){
		$resultArr = array ('code'=>"1",'message'=>"");
			// 校验验证码
			$vcode= I('request.vcode');
			$mobile= I('request.mobile');
			if($vcode && $mobile){
				$msg_model = D("Business/Msg");
				// 需要更新的数据
				$condition['mobile'] = $mobile;
				// 随机6位数
				$condition['code'] = $vcode;
				$condition['type'] = "注册验证码";
				
				$data = $msg_model->where($condition)->order(array("create_time"=>"desc"))->find();
				// echo $msg_model->getlastSql(); 
				if(!$data){
					$resultArr['code'] = "1";
					$resultArr['message'] = "此验证码不正确";
				}else if($data && $data["code"] == $vcode){
					$resultArr['code'] = "0";
				}else{
					$resultArr['code'] = "1";
					$resultArr['message'] = "验证码不正确";
				}
			}else {			
				$resultArr['code'] = "1";
				$resultArr['message'] = "验证码不正确";
			}
		echo json_encode($resultArr);
	}
	
	// 保存用户基本信息
	function visitorSave(){
		if (IS_POST) {
			$visitorId= I('request.visitorId');
			$data = $this->visitor_model->create();
			if ($data !==false) {
				// ip地址获取
				$data['last_login_ip'] = GetIP();
				// wx_openid
				$data['wx_openid'] =$_SESSION['wx_openid'];
				$data['wx_neckname'] =$_SESSION['wx_neckname'];
				
				
				// echo "ip = ".$ip;
				if(isset($_SESSION['visitorId'])){
					// 更新
					$visitorId = $_SESSION['visitorId'];
					$this->visitor_model->where(array('id'=>$visitorId))->save($data);
				}else{
					// 新增
					$visitorId = $this->visitor_model->add($data);
				}
				
				// echo "result==".$visitorId;
				if ($visitorId !==false) {
					// 保存session
					// echo "visitorId=".$visitorId;
					$_SESSION['visitorId'] = $visitorId;
					$this->redirect('VisitCard/cardItems');
				} else {
					$this->error("保存失败！！");
				}

			} else {
				$this->error($this->visitor_model->getError());
			}
		}
	}
	
     // 第2页  参观登记信息
    public function cardItems(){
		
		// ip地址获取
		$ip = GetIP();
		// echo "ip = ".$ip;
		
		// 参观证id
		$card_setting_id= I('request.card_setting_id',0,'intval');;
		if(empty($card_setting_id) && isset($_SESSION['card_setting_id'])){
			$card_setting_id = $_SESSION['card_setting_id'];
		}
		// 判断是否有visitor信息
    	$visitorId=I('request.visitorId');
		if(isset($_SESSION['visitorId'])){
			$visitorId = $_SESSION['visitorId'];
		}
		if(empty($visitorId)){
			// 跳回到参观证第1页注册页面
    	    $this->redirect('VisitCard/visitor',array('id'=>$card_setting_id));
    	    return;
    	}
		$this->assign("visitorId",$visitorId);
		
		
		// 判断完成
		if(isset($_SESSION['visitor_finish']) && $_SESSION['visitor_finish'] == 'finish'){
			$this->redirect('VisitCard/visitcard',array('visitorId'=>$visitorId));
		}
			
		
		// 查询获得设置信息
    	$cardSetting=$this->setting_model
		->where(array('id'=>$card_setting_id))
		->find();
		
		//echo $setting_model->getlastSql(); 
		
		if(empty($cardSetting)){
    	    header('HTTP/1.1 404 Not Found');
    	    header('Status:404 Not Found');
    	    if(sp_template_file_exists(MODULE_NAME."/404")){
    	        $this->display(":404");
    	    }
    	    
    	    return;
    	}
		
		$this->assign("cardSetting",$cardSetting);
    	
		$tplname = "card_tpls/".$cardSetting['card_tpl'];
		// echo $tplname;
		
		// 查询问卷调查数据项
		//$where = array("status"=>1);
		//$where['path'] = array('like',$cardSetting['dic_card_id']."%");
		$result = $this->dictionary_model->where(array("status"=>1,"path"=>array('like',"0-".$cardSetting['dic_card_id']."-%")))
		//$dictCards = $this->dictionary_model->where(array("status"=>1))
		->order(array("listorder"=>"asc"))->select();
		
		//echo $this->dictionary_model->getlastSql(); 
		
		// 分组处理
		$dictCardItems =  array();
		$dictCards  =  array();
		foreach($result as $k=>$v){
			// echo "k=".$v['dic_id'];
			if($v['parent'] != $cardSetting['dic_card_id']){
				$dictCardItems[$v['parent']][]  =   $v;
			}
			$dictCards[$v['dic_id']] =  $v;
		}
		$this->assign("dictCards",$dictCards);
		$this->assign("dictCardItems",$dictCardItems);

    	$this->display("VisitCard:$tplname");

    }
	
	// 第2页  参观登记信息 保存
    public function cardItemsSave(){

		
/**
				if ($this->dictionary_model->add()!==false) {
				    F('all_dictionary',null);
					$this->success("添加成功！",U("AdminDictCard/index"));
				} else {
					$this->error("添加失败！");
				}
					//获取请求参数  value与key
		$request_params=I('post.');
		foreach($request_params as $key=>$value){
			// session('$key','$value');  //设置session
			$_SESSION['visitor'][''.$key] = ''.$value;
			// echo "$key=".$value;
		}
		
		// 获取session
		$visitor_name= $_SESSION['visitor']['name'];
		// echo "visitor_name=".$visitor_name ;
			
				
		
		//获取请求参数  value与key
		$request_params=I('post.');
		foreach($request_params as $key=>$value)
		// session('$key','$value');  //设置session
		$_SESSION['card']['$key'] = '$value';
		
		 // 获取session
		$visitor_name= $_SESSION['visitor']['name'];
		$this->assign("visitor_name",$visitor_name);
		//echo "visitor_name=".$visitor_name ;
		
		// 获取company
		$visitor_company= $_SESSION['visitor']['company'];
		$this->assign("visitor_company",$visitor_company);
		//echo "visitor_company=".$visitor_company ;
**/		
				
		$card_id=I('request.card_id');
		$visitorId=I('request.visitorId');
		
		// 用户选择处理
		$visitor_card_data_model = D("Business/VisitorCardData");
		 // 删除原来的数据
		$visitor_card_data_model->where(array('card_id'=>$card_id,'visitor_id'=>$visitorId))->delete();
		
		//获取请求参数  value与key
		
		$request_params=I('post.');
		foreach($request_params as $key=>$value){
			$card_item_value = $value;
			if( is_array($value) ) 
				$card_item_value = implode(',',$value);
			
			// echo 'strpos = '.startwith($key, 'name').'<br>';
			if($key != 'card_id' && $key != 'visitorId'){
				
				// item
				// $data['card_item_id'] = $key;
				if(!startwith($key, "name")){
					//echo $key.' = '.$card_item_value.' </br>'; 
					// 需要更新的数据
					$data = $visitor_card_data_model->create();
					$data['card_id'] = $card_id;
					$data['visitor_id'] = $visitorId;
					$data['card_item_id'] = $key;
					$input_name = 'request.name'.$key;
					//echo I($input_name);
					$data['card_item_name'] = I($input_name);
					$data['card_item_value'] = $card_item_value;
					$visitor_card_data_model->add($data);
				}
			}
		}
		//echo $visitor_card_data_model->getlastSql(); 
		// session('$key','$value');  //设置session
		// $_SESSION['card']['$key'] = '$value';
		
		// 保存成功跳转到二维码页面
		// $this->success("添加成功！",U("AdminDictCard/index"));
		$this->redirect('VisitCard/visitcard',array('card_id'=>$card_id));
		//$this->redirect('VisitCard/visitcard');
		
	}
	
	// 第3页  参观登记卡二维码信息
	public function visitcard(){
		// unset($_SESSION['visitorId']);
		// 获取参数
		$visitorId=I('request.visitorId');
		$card_id=I('request.card_id');
		// $_SESSION['visitorId'] = $visitorId;
		if(isset($_SESSION['visitorId'])){
			$visitorId = $_SESSION['visitorId'];
		}
		// echo "visitorId===================".$visitorId."==card_id==".$card_id;
		if(empty($visitorId)){
			$this->redirect('VisitCard/index',array("card_id"=>$card_id));
		}
	
		// 查询获得用户信息设置信息
    $visitor=$this->visitor_model
		->where(array('id'=>$visitorId))
		->find();
		$this->assign("visitor",$visitor);
		// echo "visitor=".$visitor['card_id'];

		// 判断空
		if(empty($visitor)){
			//echo "visitor333=".$visitor['card_id'];
    	    // 跳回到参观证第1页注册页面
    	  //  $this->redirect('VisitCard/index',array('id'=>$card_id));
    	    return;
    }
  	// echo "visitor22=".$visitor['card_id'];
    $card_id = $visitor['card_id'];
		
		// 查询获得设置信息
		$setting_model = D("Business/VisitorCardSetting");
    $cardSetting=$setting_model
		->where(array('id'=>$card_id))->find();
		
		// echo "visitor=".$card_id;echo "visitor=".$card_id;
    	
		$this->assign("cardSetting",$cardSetting);
		$tplname = "visitcard_tpls/".$cardSetting['visitcard_tpl'];
		
		// 判断完成
		$_SESSION['visitor_finish'] = "finish";
		
    	$this->display("VisitCard:$tplname");

    }
}
