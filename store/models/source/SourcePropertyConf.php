<?php

namespace store\models\source; 

use Yii; 

/** 
 * This is the model class for table "source_property_conf". 
 * 
 * @property int $id 主键
 * @property string $cName 中文名称
 * @property string $eName 英文名称
 */ 
class SourcePropertyConf extends \yii\db\ActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'source_property_conf'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['cName', 'eName'], 'required'],
            [['cName', 'eName'], 'string', 'max' => 200],
            [['cName'], 'unique'],
            [['eName'], 'unique'],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'cName' => 'C Name',
            'eName' => 'E Name',
        ]; 
    } 

    //属性名称下拉框
    public static function getSourcePropertyConfMap()
    {
        $logisticsArr = self::find()->asArray()->all();
        foreach ($logisticsArr as $val) {
            $tree[$val['eName']] = $val['cName'];
        }
        return $tree;
    }
} 