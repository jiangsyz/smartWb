<?php
//资源
namespace store\models\model;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use yii\db\ActiveRecord;
use store\models\source\SourceProperty;
use store\models\mark\mark;
use store\models\member\Member;
use store\models\product\Spu;
use store\models\product\Sku;
use store\models\product\virtualItem;
use store\models\shoppingCart\shoppingCartRecord;
//========================================
abstract class Source extends SmartActiveRecord{
	//资源类型
	const TYPE_SPU=1;
	const TYPE_SKU=2;
	const TYPE_MEMBER=3;
	const TYPE_STAFF=4;
	const TYPE_ARTICLE=5;
	const TYPE_ORDER_RECORD=6;
	const TYPE_VIRTUAL_ITEM=7;
	//========================================
	//返回资源类型
	abstract public function getSourceType();
	//========================================
	//返回资源id
	public function getSourceId(){return $this->id;}
	//========================================
	//返回资源全局编号
	public function getSourceNo(){return $this->getSourceType().'_'.$this->getSourceId();}
	//========================================
	//获取资源的属性
	public function getProperty(){
		$sType=$this->getSourceType();
		$sId=$this->getSourceId();
		$where="`sourceType`='{$sType}' AND `sourceId`='{$sId}'";
		$sourceProperty=SourceProperty::find()->where($where)->one();
		return $sourceProperty ? $sourceProperty : NULL;
	}
	//========================================
	//获取该资源在购物车中的记录
	public function getShoppingCartRecord(member $member){
		$mId=$member->getSourceId();
		$sType=$this->getSourceType();
		$sId=$this->getSourceId();
		$where="`memberId`={$mId} AND `sourceType`='{$sType}' AND `sourceId`='{$sId}'";
		return shoppingCartRecord::find()->where($where)->one();
	}
	//========================================
	//判断资源是否被锁定
	public function isLocked(){if($this->locked==0) return false; else return true;}
	//========================================
	//不同资源对应的类
	static private function getClass($sourceType){
		if($sourceType==self::TYPE_SPU) return Spu::className();
		if($sourceType==self::TYPE_SKU) return Sku::className();
		if($sourceType==self::TYPE_MEMBER) return Member::className();
		if($sourceType==self::TYPE_VIRTUAL_ITEM) return virtualItem::className();
		throw new SmartException("error sourceType");
	}
	//========================================
	//以sourceType和sourceId字段作为外键来获取资源
	static public function getRelationShip(ActiveRecord $ar){
		$class=self::getClass($ar->sourceType);
		return $ar->hasOne($class,array('id'=>'sourceId'));
	}
	//========================================
	//通过类型和id获取资源
	static public function getSource($sourceType,$sourceId,$lockFlag=false){
		$class=self::getClass($sourceType);
		//不需要加锁
		if(!$lockFlag) return $class::find()->where("`id`='{$sourceId}'")->one();
		//需要加锁
		$table=$class::tableName();
		$sql="SELECT * FROM {$table} WHERE `id`='{$sourceId}' FOR UPDATE";
		return $class::findBySql($sql)->one();
	}
	//========================================
	//获取某个SPU下所有SKU的属性和属性值
	public function getSourceProperyArray()
	{
		
	}
}