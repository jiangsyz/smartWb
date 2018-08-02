<?php

namespace store\models\setting;

use Yii; 
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use yii\helpers\ArrayHelper;
/** 
 * This is the model class for table "wechat_conf". 
 * 
 * @property int $id 主键
 * @property string $confKey 配置key
 * @property string $confVal 配置val
 */ 
class WechatConf extends SmartActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'wechat_conf'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['confKey', 'confVal'], 'required'],
            [['confKey', 'confVal'], 'string', 'max' => 200],
            [['confKey'], 'unique'],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'confKey' => 'Conf Key',
            'confVal' => 'Conf Val',
        ]; 
    } 
} 