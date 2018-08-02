<?php
//会员等级
namespace store\models\member;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\order\OrderRecord;

//========================================
class MemberLv extends SmartActiveRecord
{
	public function getOrderRecord()
	{
		return $this->hasOne(OrderRecord::className(), ['id'=>'orderId']);
	}
}