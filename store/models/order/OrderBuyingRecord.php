<?php

namespace store\models\order; 

use Yii; 
use store\models\product\Sku;
use yii\db\SmartActiveRecord;
use store\models\source\VirtualItem;

/** 
 * This is the model class for table "order_buying_record". 
 * 
 * @property int $id 主键
 * @property int $orderId 订单id
 * @property int $sourceType 资源类型
 * @property int $sourceId 资源id
 * @property int $buyingCount 购买数量
 * @property double $price 单价
 * @property double $finalPrice 成交单价
 * @property string $dataPhoto 数据快照
 */ 
class OrderBuyingRecord extends SmartActiveRecord
{ 
    public $parentId=null;
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'order_buying_record'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['orderId', 'sourceType', 'sourceId', 'buyingCount', 'price', 'finalPrice', 'dataPhoto'], 'required'],
            [['orderId', 'sourceType', 'sourceId', 'buyingCount'], 'integer'],
            [['price', 'finalPrice'], 'number'],
            [['dataPhoto'], 'string', 'max' => 500],
            [['orderId', 'sourceType', 'sourceId'], 'unique', 'targetAttribute' => ['orderId', 'sourceType', 'sourceId']],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'orderId' => 'Order ID',
            'sourceType' => 'Source Type',
            'sourceId' => 'Source ID',
            'buyingCount' => 'Buying Count',
            'price' => 'Price',
            'finalPrice' => 'Final Price',
            'dataPhoto' => 'Data Photo',
        ]; 
    }

    public function getSku()
    {
        return $this->hasOne(Sku::className(), ['id'=>'sourceId']);
    }

    public function getVirtualItem()
    {
        return $this->hasOne(VirtualItem::className(), ['id'=>'sourceId']);
    }
} 