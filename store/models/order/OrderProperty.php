<?php

namespace store\models\order; 

use Yii; 
use yii\behaviors\TimestampBehavior;

/** 
 * This is the model class for table "order_property". 
 * 
 * @property int $id 主键
 * @property int $orderId 订单id
 * @property string $propertyKey 属性key
 * @property string $propertyVal 属性val
 * @property int $createTime 添加时间
 */ 
class OrderProperty extends \yii\db\ActiveRecord
{ 
    /*public function behaviors()  
    {  
        return [  
            [  
                'class' => TimestampBehavior::className(),  
                'createdAtAttribute' => 'createTime',
            ],  
        ];  
    }   */
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'order_property'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['orderId', 'propertyKey', 'propertyVal', 'createTime'], 'required'],
            [['orderId', 'createTime'], 'integer'],
            [['propertyVal'], 'string'],
            [['propertyKey'], 'string', 'max' => 200],
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
            'propertyKey' => 'Property Key',
            'propertyVal' => 'Property Val',
            'createTime' => 'Create Time',
        ]; 
    }
} 