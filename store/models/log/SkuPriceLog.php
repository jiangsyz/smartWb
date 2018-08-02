<?php

namespace store\models\log; 

use Yii; 

/** 
 * This is the model class for table "sku_price_log". 
 * 
 * @property int $id 主键
 * @property int $handlerType 操作者资源类型
 * @property int $handlerId 操作者资源类型
 * @property int $skuId skuId
 * @property int $lv 会员等级
 * @property double $originaPrice 原价
 * @property double $price 修改后的数据
 * @property string $memo 备注
 * @property int $createTime 日志创建时间
 */ 
class SkuPriceLog extends \yii\db\ActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'sku_price_log'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['handlerType', 'handlerId', 'skuId', 'lv', 'price', 'createTime'], 'required'],
            [['handlerType', 'handlerId', 'skuId', 'lv', 'createTime'], 'integer'],
            [['originaPrice', 'price'], 'number'],
            [['memo'], 'string', 'max' => 300],
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
            'skuId' => 'Sku ID',
            'lv' => 'Lv',
            'originaPrice' => 'Origina Price',
            'price' => 'Price',
            'memo' => 'Memo',
            'createTime' => 'Create Time',
        ]; 
    } 

    public function init(){
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT,array($this,"initCreateTime"));
    }

    public function initCreateTime()
    {
        $this->createTime = time();
    }
} 