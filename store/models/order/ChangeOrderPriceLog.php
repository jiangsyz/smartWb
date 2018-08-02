<?php
namespace store\models\order; 

use Yii; 
use yii\base\SmartException;
use yii\db\SmartActiveRecord;


/** 
 * This is the model class for table "change_order_price_log". 
 * 
 * @property int $id 主键
 * @property int $handlerType 操作者资源类型
 * @property int $handlerId 操作者资源id
 * @property int $orderId 订单id
 * @property string $originaData 老数据
 * @property string $data 新数据
 * @property string $memo 备注
 * @property int $createTime 日志创建时间
 */ 
class ChangeOrderPriceLog extends SmartActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'change_order_price_log'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['handlerType', 'handlerId', 'orderId', 'originaData', 'data', 'memo', 'createTime'], 'required'],
            [['handlerType', 'handlerId', 'orderId', 'createTime'], 'integer'],
            [['originaData', 'data', 'memo'], 'string', 'max' => 300],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'handlerType' => 'Handler Type',
            'handlerId' => 'Handler ID',
            'orderId' => 'Order ID',
            'originaData' => 'Origina Data',
            'data' => 'Data',
            'memo' => 'Memo',
            'createTime' => 'Create Time',
        ]; 
    } 
} 