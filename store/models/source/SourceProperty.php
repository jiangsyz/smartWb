<?php
//资源属性
namespace store\models\source;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\source\SourcePropertyConf;
//========================================
class SourceProperty extends SmartActiveRecord
{
	/** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['sourceType', 'sourceId', 'propertyKey', 'propertyVal'], 'required'],
            [['sourceType', 'sourceId'], 'integer'],
            [['propertyKey', 'propertyVal'], 'string', 'max' => 200],
            [['sourceType', 'sourceId', 'propertyKey'], 'unique', 'targetAttribute' => ['sourceType', 'sourceId', 'propertyKey']],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'sourceType' => 'Source Type',
            'sourceId' => 'Source ID',
            'propertyKey' => '属性名称',
            'propertyVal' => '属性值',
        ]; 
    }

    public function getSourcePropertyConf()
    {
        return $this->hasOne(SourcePropertyConf::className(), ['eName'=>'propertyKey']);
    }
}