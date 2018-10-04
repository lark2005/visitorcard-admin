<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: larker <66192480@qq.com>
// +----------------------------------------------------------------------

// 指定长度验证码
function generate_code($length = 6) {
    return rand(pow(10,($length-1)), pow(10,$length)-1);
	/***
	// 随机6位数
				$numbers = range (1,50);
				//shuffle 将数组顺序随即打乱
				shuffle ($numbers);
				//array_slice 取该数组中的某一段
				$msgCode = implode('',array_slice($numbers,0,4)); 
	***/
	
}
	/**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    function request_post($url = '', $param) {
        if (empty($url) || empty($param)) {
            return false;
        }
        
		// 请求地址
        $postUrl = $url;
        
		// 参数处理
		if( is_array( $param )){
		  // echo '是数组';
		  	$curlPost = "" ;
			$i=0;
			$arr_n=count($param)-1;
			foreach ( $param as $k => $v )
			{
				 $curlPost .= "$k=" .  ($v );
				 if($arr_n!=$i){
					 $curlPost .= "&" ;
				 }
				 $i++;
			}
		}else{
			$curlPost = $param;
		}
		// echo $curlPost;
		
		// $curlPost = $param;
		// echo $curlPost;
		
        $ch = curl_init();//初始化curl
		$this_header = array(
			"content-type: application/x-www-form-urlencoded; charset=UTF-8"
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
		
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        // curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		// curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		// 如果为1，后端echo 不会直接输出到浏览器，如果为0后端echo 则会显示在浏览器上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }

	/**
     * 短信发送
     * @param string $mobile
     * @param string $content
     */
    function sendMsg_post($mobile,$content) {
		// 短信通道地址
		$url = "http://101.200.29.88:8082/SendMT/SendMessage";
		// 发送参数
		$param = array('CorpID'=>"username",'Pwd'=>"password");
		// 通道号码末尾添加的扩展号码
		$param['Cell'] = "110";
		// 多个手机号码之间用英文“,”分开，最大支持1000个手机号码，同一请求中，最好不要出现相同的手机号码
		$param['Mobile'] = "".$mobile;
		// 短信内容，内容需要URL(UTF-8)编码
		$urlencodeContent = $content;
		// $urlencodeContent = utf8_encode($content);
		// echo "urlencodeContent=".$urlencodeContent;
		$param['Content'] = "".$urlencodeContent;
		
		// 发送短信
        $data = request_post($url,$param);
        
        return $data;
    }
	
	/**
	function asyn_sendmail() {
		$fp=fsockopen('localhost',80,&$errno,&$errstr,5);
		if(!$fp){
			echo "$errstr ($errno)<br />\n";
		}
		sleep(1);
		fputs($fp,"GET /sendmail.php?param=1\r\n"); #请求的资源 URL 一定要写对
		fclose($fp);
	} 
	**/

	
// 获得Ip地址
function GetIP(){
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
	  $cip = $_SERVER["HTTP_CLIENT_IP"];
	}
	elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
	  $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif(!empty($_SERVER["REMOTE_ADDR"])){
	  $cip = $_SERVER["REMOTE_ADDR"];
	}
	else{
	  $cip = "无法获取！";
	}
	return $cip;
}

/**
 * 获取应用当前模板下的模板列表
 * @return array
 */
function sp_admin_get_visitcard_tpl_file_list($type='tpls'){
	$template_path=C("SP_TMPL_PATH").C("SP_DEFAULT_THEME")."/".MODULE_NAME."/VisitCard/".$type.'/';
	$files=sp_scan_dir($template_path."*");
	$tpl_files=array();
	foreach ($files as $f){
		if($f!="." || $f!=".."){
			if(is_file($template_path.$f)){
				$suffix=C("TMPL_TEMPLATE_SUFFIX");
				$result=preg_match("/$suffix$/", $f);
				if($result){
					$tpl=str_replace($suffix, "", $f);
					$tpl_files[$tpl]=$tpl;
				}else if(preg_match("/\.php$/", $f)){
				    $tpl=str_replace($suffix, "", $f);
				    $tpl_files[$tpl]=$tpl;
				}
			}
		}
	}
	return $tpl_files;
}

	
/**
 * 获取Path深度
 */
function sp_admin_get_dic_path_deepth($path) {
	$pathArr = explode('-', $path);
	return count($pathArr);
}



/**
 * 获取字典卡数据项
 * @return array
 */
function sp_admin_get_dict_cards($where=array()){
    	$where=is_array($where)?$where:array();
    
    	//根据参数生成查询条件
    	$where['parent'] = 0;
    	$where['status'] = array('eq',1);
    
		$dictionary_model = D("Business/DictCard");

	/*	
		// 根据code查当前字典
		$dic = $dictionary_model
            ->where($where)
            ->order("listorder ASC")
            ->find();
		// 查询当前code下的所有数据
		$path = $dic['path'];
		if($deepth==1){
			$where2['parent'] = $dic['dic_id'];
		}else{
			$where2['path'] = array('like',"$path-%");
		}
	*/
		
//echo $dictionary_model->getlastSql(); 
		$items = $dictionary_model
            ->where($where)
            ->order(array("listorder"=>"asc"))->select();
//echo $dictionary_model->getlastSql(); 
			
    	return $items;
    }
	

/**
 * 获取字典表数据项
 * @return array
 */
function sp_admin_get_dic($dicCode,$deepth=1,$where=array()){
	$where=is_array($where)?$where:array();

	//根据参数生成查询条件
	$where['code'] = array('eq',$dicCode);
	$where['status'] = array('eq',1);

	if (isset($tag['where'])) {
		$where['_string'] = $tag['where'];
	}

	$dictionary_model = D("Business/Dictionary");
	
	// 根据code查当前字典
	$dic = $dictionary_model
		->where($where)
		->order("listorder ASC")
		->find();
		
	return $dic;
}

/** 
 * google api 二维码生成【QRcode可以存储最多4296个字母数字类型的任意文本，具体可以查看二维码数据格式】 
 * @param string $chl 二维码包含的信息，可以是数字、字符、二进制信息、汉字。 
 不能混合数据类型，数据必须经过UTF-8 URL-encoded 
 * @param int $widhtHeight 生成二维码的尺寸设置 
 * @param string $EC_level 可选纠错级别，QR码支持四个等级纠错，用来恢复丢失的、读错的、模糊的、数据。 
 * L-默认：可以识别已损失的7%的数据 
 * M-可以识别已损失15%的数据 
 * Q-可以识别已损失25%的数据 
 * H-可以识别已损失30%的数据 
 * @param int $margin 生成的二维码离图片边框的距离 
 */
function generateQRfromGoogle($chl,$widhtHeight ='150',$EC_level='L',$margin='0') 
{ 
 $chl = urlencode($chl); 
 echo '<img src="http://chart.apis.google.com/chart?chs='.$widhtHeight.'x'.$widhtHeight.' 
 &cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$chl.'" alt="QR code" widhtHeight="'.$widhtHeight.'
 " widhtHeight="'.$widhtHeight.'"/>'; 
} 

function qrcode_show($url="larkersos.com",$fileName="myRQcode",$level=3,$size=4)
{
	vendor('phpqrcode.phpqrcode');//导入类库
	$value = $url; //二维码内容 
	$errorCorrectionLevel = 'L';//容错级别 
	$matrixPointSize = 6;//生成图片大小 
	//生成二维码图片 
	$filePathSrc = 'data/rqcode/'.$fileName.'-src.png';
	QRcode::png($value, $filePathSrc, $errorCorrectionLevel, $matrixPointSize, 2); 
	$logo = 'logo.png';//准备好的logo图片 
	$QR = $filePathSrc;//已经生成的原始二维码图 
	  
	if ($logo !== FALSE) { 
	 $QR = imagecreatefromstring(file_get_contents($QR)); 
	 $logo = imagecreatefromstring(file_get_contents($logo)); 
	 $QR_width = imagesx($QR);//二维码图片宽度 
	 $QR_height = imagesy($QR);//二维码图片高度 
	 $logo_width = imagesx($logo);//logo图片宽度 
	 $logo_height = imagesy($logo);//logo图片高度 
	 $logo_qr_width = $QR_width / 5; 
	 $scale = $logo_width/$logo_qr_width; 
	 $logo_qr_height = $logo_height/$scale; 
	 $from_width = ($QR_width - $logo_qr_width) / 2; 
	 //重新组合图片并调整大小 
	 imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, 
	 $logo_qr_height, $logo_width, $logo_height); 
	} 
	//输出图片 
	$filePath = 'data/rqcode/'.generate_code(11).'.png';
	imagepng($QR,$filePath ); 
	echo '<img src="'.$filePath.'">'; 
}

function startwith($str,$pattern) {
    if(strpos($str,$pattern) === 0)
          return true;
    else
          return false;
}