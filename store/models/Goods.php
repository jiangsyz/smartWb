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
class Goods extends \yii\db\ActiveRecord
{ 
    public static function getDb()
    {
        // 使用 "wbdb" 组件
        return \Yii::$app->wbdb;  
    }

    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'goods'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [[/*'name', */'code', 'logistics_id'], 'required'],
            [['logistics_id'], 'integer'],
            [['name'], 'string', 'max' => 200],
            [['code','unit'], 'string', 'max' => 100],
            [['cost'], 'number'],
            ['cost', 'default', 'value' => 0],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'name' => '商品名称',
            'code' => '商品编码',
            'logistics_id' => '物流渠道',
            'unit' => '规格',
            'cost' => '成本',
        ]; 
    }


    /**
     * 二维数组排序
     *
     * @param $arrays
     * @param $sort_key
     * @param int $sort_order
     * @param int $sort_type
     * @return array|bool
     */
    static public function array_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }
} 