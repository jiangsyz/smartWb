<?php
//分类记录
namespace store\models\Category;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
//========================================
class CategoryRecord extends SmartActiveRecord{
	//========================================
	//规则
    public function rules(){ 
        return [
            [['categoryId', 'sourceType', 'sourceId'], 'required'],
            [['categoryId', 'sourceType', 'sourceId'], 'integer'],
            [['categoryId', 'sourceType', 'sourceId'], 'unique', 'targetAttribute' => ['categoryId', 'sourceType', 'sourceId']],
        ]; 
    } 

    //========================================
	//属性
    public function attributeLabels(){ 
        return [ 
            'id' => 'ID',
            'categoryId' => 'Category ID',
            'sourceType' => 'Source Type',
            'sourceId' => 'Source ID',
        ]; 
    }
	//获取分类
	public function getCategory(){return $this->hasOne(Category::className(),array('id'=>'categoryId'));}
	//========================================
	//获取资源
	public function getSource(){return Source::getRelationShip($this);}
	//========================================
	//保存资源分类记录
	public function saveCategoryRecord($data)
	{
		$this->categoryId = $data['CategoryRecord']['categoryId'];
		$this->sourceType = Source::TYPE_SPU;
		$this->sourceId = $data['spuId'];
		if (!$this->validate()) {
			throw new SmartException("categoryRecord validate failed");
		}
		if (!$this->save(false)){
			throw new SmartException("categoryRecord save failed");
		}
		
	}
}