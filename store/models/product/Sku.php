<?php
//库存计量单元
namespace store\models\product;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
use store\models\member\Member;
use store\models\source\SourceProperty;
use store\models\product\SkuMemberPrice;
use store\models\log\SkuPriceLog;
//========================================
class Sku extends Source{
    public function rules(){ 
        return [
            [['uniqueId', 'spuId', 'title'], 'required'],
            [['spuId', 'count', 'closed', 'locked'], 'integer'],
            [['uniqueId', 'title'], 'string', 'max' => 200],
            [['spuId', 'title'], 'unique', 'targetAttribute' => ['spuId', 'title']],
            [['uniqueId'], 'unique'],
        ]; 
    } 
    //========================================
    public function attributeLabels(){ 
        return [ 
            'id' => 'ID',
            'uniqueId' => 'SKU编码',
            'spuId' => 'SPUID',
            'title' => '标题',
            'price' => '价格',
            'count' => '库存数量',
            'closed' => 'Closed',
            'locked' => 'Locked',
        ]; 
    }
    //========================================
	//返回资源类型
	public function getSourceType(){return Source::TYPE_SKU;}
	//========================================
	//判断是否业务锁定
	public function isLocked(){
		if($this->locked) return true;
		if($this->spu->isLocked()) return true;
		return false;
	}
	//========================================
	//判断是否下架(true=下架了)
	public function isClosed(){
		if($this->closed) return true;
		if($this->spu->isClosed()) return true;
		return false;
	}
	//========================================
	//获取商家
	public function getShop(){return $this->spu->shop;}
	//========================================
	//获取产品类型
	public function getProductType(){return $this->spu->getSourceType();}
	//========================================
	//获取产品id
	public function getProductId(){return $this->spu->getSourceId();}
	//========================================
	//获取产品名称
	public function getProductName(){return $this->spu->title;}
	//========================================
	//获取封面
	public function getCover(){return $this->spu->cover;}
	//========================================
	//获取针对某个会员等级的售卖价格
	public function getLevelPrice($level)
	{
		//获取会员专享价
		$price = SkuMemberPrice::find()->where("`skuId`='{$this->id}' AND `lv`='{$level}'")->one();
		//有专享价返回专享价,没有返回原价
		return $price ? $price->price : NULL;
	}
	//========================================
	//获取最终成交价格
	public function getFinalPrice(Member $member){return $this->getLevelPrice($member->getLevel());}
	//========================================
	//获取库存(无库存限制返回NULL)
	public function getKeepCount(){return $this->count;}
	//========================================
	//获取物流配送方式
	public function getDistributeType(){return $this->spu->getDistributeType();}
	//========================================
	//获取spu
	public function getSpu(){return $this->hasOne(Spu::className(),array('id'=>'spuId'));}
	//========================================

	//插入sku
	public function saveSku($data){
		//echo "<pre>";print_r($data);exit;
		foreach ($data['Sku'] as $key => $val){
			if ($val['title']=='' || $val['uniqueId']=='' || $val['price']=='' || $val['vprice']=='' || $val['propertyKey']=='' || $val['propertyVal']=='') { 
				continue;
			}
			$sku = new Sku();
			$sku->spuId = $data['spuId'];
			$sku->title = $val['title'];
			$sku->count = 0;//$val['count'];
			$sku->uniqueId = $val['uniqueId'];
			if (!$sku->validate()) { throw new SmartException("sku validate failed"); }
			if (!$sku->save(false)) { throw new SmartException("sku add failed"); }
			
			/*******************************保存属性表***************************************/
			$skuId = $sku->attributes['id'];
			$sourceProperty = new SourceProperty();
			$sourceProperty->sourceType = Source::TYPE_SKU;
	        $sourceProperty->sourceId = $skuId;
	        $sourceProperty->propertyKey = $val['propertyKey'];
	        $sourceProperty->propertyVal = $val['propertyVal'];
	        $sourceProperty->createTime = time();
	        if (!$sourceProperty->validate()) {throw new SmartException("sourceProperty validate failed"); }
	        if (!$sourceProperty->save(false)) { throw new SmartException("sourceProperty save failed"); }
			
			/*******************************保存VIP价格表***************************************/
			//V0价 即原价
			$skuMemberPrice = new SkuMemberPrice();
			$skuMemberPrice->skuId = $skuId;
			$skuMemberPrice->lv = 0;
			$skuMemberPrice->price = $val['price'];
			if (!$skuMemberPrice->validate()) { throw new SmartException("V0-Price validate failed"); }
			if (!$skuMemberPrice->save(false)) { throw new SmartException("V0-Price save failed"); }
			//V1价
			$skuMemberPrice = new SkuMemberPrice();
			$skuMemberPrice->skuId=$skuId;
			$skuMemberPrice->lv = 1;
			$skuMemberPrice->price = $val['vprice'];
			if (!$skuMemberPrice->validate()) { throw new SmartException("V1-Price validate failed"); }
			if (!$skuMemberPrice->save(false)) { throw new SmartException("V1-Price add failed"); }
		}
	}
}