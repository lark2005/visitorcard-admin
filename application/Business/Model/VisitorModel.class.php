<?php
namespace Business\Model;
use Common\Model\CommonModel;
class VisitorModel extends CommonModel
{
		
	// 数据表前缀
	protected $tablePrefix = 'biz_'; 
	
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('mobile', 'require', '手机号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
	);
	
	protected $_auto = array(
	    array('create_time','mGetDate',self::MODEL_INSERT,'callback'),
	    array ('last_login_time', 'mGetDate',self::MODEL_BOTH, 'callback' ) 
	);
	
	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function mGetDate() {
		return date('Y-m-d H:i:s');
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
	
}

