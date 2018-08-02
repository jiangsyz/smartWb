<?php

namespace store\models\source; 

use Yii;
use yii\base\SmartException;
use yii\base\Component;
use store\models\member\Member;
use store\models\member\Address;
use store\models\model\Source;
use yii\data\SqlDataProvider;
use yii\db\SmartActiveRecord;

/** 
 * This is the model class for table "virtual_item". 
 * 
 * @property int $id 主键
 * @property string $title 标题
 * @property string $desc 描述
 * @property string $cover 封面
 * @property string $price
 * @property int $memberId 推荐人id
 * @property int $closed 是否下架(0=上架/1=下架)
 * @property int $locked 锁定(0=正常/1=锁定)
 */ 
class VirtualItem extends SmartActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'virtual_item'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['title', 'memberId'], 'required'],
            [['price'], 'number'],
            [['memberId', 'closed', 'locked'], 'integer'],
            [['title', 'desc', 'cover'], 'string', 'max' => 200],
            [['title'], 'unique'],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'title' => 'Title',
            'desc' => 'Desc',
            'cover' => 'Cover',
            'price' => 'Price',
            'memberId' => 'Member ID',
            'closed' => 'Closed',
            'locked' => 'Locked',
        ]; 
    }


    public static function search($params)
    {
        //$where = self::getSqlWhere($params);

        //整单退实体商品查询
        $sql = "SELECT *
            FROM `virtual_item` AS a";

        //echo $sql;exit;
        $count = Yii::$app->db->createCommand("select count(*) from ({$sql}) a")->queryScalar();
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 30,
            ]
        ]);

        return $dataProvider;
    }
} 