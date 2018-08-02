<?php

namespace store\models\logistics; 

use Yii; 

/** 
 * This is the model class for table "logistics". 
 * 
 * @property int $id 主键
 * @property string $name 物流渠道
 */ 
class Logistics extends \yii\db\ActiveRecord
{ 
    const SF_COLD = 1; //顺丰冷运
    const SF_EXPRESS = 2; //顺丰快递
    const DB_EXPRESS = 3; //德邦快递
    const DB_WINE = 4; //德邦红酒
    const ZTCK = 5; //整条出库
    const OFFICE = 6;

    
    static public $not_combine_logistics = [
        Logistics::DB_WINE,
        Logistics::ZTCK
    ];

    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'logistics'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 200],
            [['name'], 'unique'],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'name' => 'Name',
        ]; 
    }

    //物流渠道下拉框参数
    public static function getLogisticsMap()
    {
        $tree = [];
        $logisticsArr = self::find()->asArray()->all();
        foreach ($logisticsArr as $val) {
            $tree[$val['id']] = $val['name'];
        }
        return $tree;
    }
} 