<?php

namespace store\models; 

use Yii; 

/** 
 * This is the model class for table "goods". 
 * 
 * @property int $id
 * @property string $name 商品名称
 * @property string $code 商品编码
 * @property int $logistics_id 物流ID 1-顺丰冷运 2-顺丰快递 3-德邦快递 4-德邦红酒
 */ 
class SmartAreaRecord extends \yii\db\ActiveRecord
{ 
    public static function getDb()
    {
        return \Yii::$app->store_log_db;  
    }

    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'smart_area_record'; 
    } 


} 