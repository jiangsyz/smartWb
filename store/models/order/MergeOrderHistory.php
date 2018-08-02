<?php

namespace store\models\order; 

use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;

/** 
 * This is the model class for table "merge_order_history". 
 * 
 * @property int $id
 * @property int $orderId 主订单ID
 * @property int $buyingRecordId 合单时对应的buyingRecordId
 * @property int $logisticsId 物流渠道ID
 * @property int $createTime 创建时间
 * @property int $is_completed 是否完成回单
 */ 
class MergeOrderHistory extends \yii\db\ActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'merge_order_history'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['orderId', 'buyingRecordId', 'logisticsId'], 'required'],
            [['orderId', 'buyingRecordId', 'logisticsId', 'createTime', 'is_completed'], 'integer'],
            [['orderId', 'buyingRecordId', 'logisticsId'], 'unique', 'targetAttribute' => ['orderId', 'buyingRecordId', 'logisticsId']],
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
            'buyingRecordId' => 'Buying Record ID',
            'logisticsId' => 'Logistics ID',
            'createTime' => 'Create Time',
            'is_completed' => 'Is Completed',
        ]; 
    } 
} 