<?php
//订单记录
namespace store\models\order;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
use store\models\member\Address;
use store\models\order\OrderBuyingRecord;
use store\models\order\OrderProperty;
use store\models\product\Sku;
//========================================
class OrderRecord extends Source
{
	//订单状态  虚拟字段
	public $status;

	//是否需要收货地址
	public $isNeedAddress=NULL;
	//创建订单时所需的外部命令
	public $command=NULL;
	//========================================
	//返回资源类型
	public function getSourceType(){return source::TYPE_ORDER_RECORD;}
	//========================================
	//初始化
	public function init(){
		parent::init();
		$this->on(self::EVENT_BEFORE_INSERT,array($this,"initCreateTime"));
		$this->on(self::EVENT_AFTER_INSERT,array($this,"addAddress"));
		$this->on(self::EVENT_AFTER_INSERT,array($this,"addMemo"));
	}
	//========================================
	//初始化创建时间
	public function initCreateTime(){$this->createTime=time();}
	//========================================
	//添加收货地址
	public function addAddress(){
		//不需要收货地址的情况
		if(!$this->isNeedAddress) return;
		//确定命令中表明该订单记录收货地址的索引
		$addressIndex='address_'.$this->index;
		//客户端没有提交地址
		if(!isset($this->command[$addressIndex])) return;
		//获取地址
		$address=address::find()->where("`id`='{$this->command[$addressIndex]}'")->one();
		if(!$address) throw new SmartException("miss address");
		//添加收货地址
		$orderAddress=array('orderId'=>$this->id,'addressInfo'=>json_encode($address->getData()));
		orderAddress::addObj($orderAddress);
	}
	//========================================
	//添加备注
	public function addMemo(){
		//确定命令中表明该订单备注的索引
		$memoIndex='memo_'.$this->index;
		//客服端没有为该订单指定备注
		if(!isset($this->command[$memoIndex])) return;
		//增加订单备注
		orderMemo::addObj(array('orderId'=>$this->id,'memo'=>$this->command[$memoIndex]));
	}

	public function getOrderBuyingRecord()
	{
		return $this->hasMany(OrderBuyingRecord::className(), ['orderId'=>'id']);
	}

	public function getMember()
	{
		return $this->hasOne(Member::className(), ['id'=>'memberId']);
	}

	public function getOrderProperty()
	{
		return $this->hasMany(OrderProperty::className(), ['orderId'=>'id'])->orderBy('createTime asc');
	}


	//获取收件人  手机  地址等信息
    public function getAddress($field)
    {
    	$rtn = '';
    	switch ($field) {
    		case 'name':
			case 'phone':
			case 'address':
				foreach ($this->orderProperty as $v) {
	    			if ($v->propertyKey == 'address') {
		    			$addressArr = json_decode($v->propertyVal, true);
		    			//echo "<pre>";print_r($addressArr);exit;
		    			if ($field == 'address') {
		    				$rtn = str_replace(',', '', $addressArr['fullAreaName']).$addressArr['address'];
		    			} else {
		    				$rtn = @$addressArr[$field];
		    			}
		    		}
		    	}
				break;
			case 'memberMemo':
    			foreach ($this->orderProperty as $v) {
    				if ($v->propertyKey == 'memberMemo') {
	    				$rtn = $v->propertyVal;
	    			}
    			}
    			break;
    		case 'staffMemo':
	    		foreach ($this->orderProperty as $v) {
    				if ($v->propertyKey == 'staffMemo') {
	    				$rtn .= $v->propertyVal.',';
	    			}
    			}
    			$rtn = substr($rtn, 0, strlen($rtn)-1);
    			break;
    	}
    	//echo "<pre>";print_r($field);
    	return $rtn;
    }
}