<?php
//库存计量单元的会员价
namespace store\models\product;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\log\SkuPriceLog;
use store\models\model\Source;
//========================================
class SkuMemberPrice extends SmartActiveRecord
{
	 /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['skuId', 'lv', 'price'], 'required'],
            [['skuId', 'lv'], 'integer'],
            [['price'], 'number'],
            [['skuId', 'lv'], 'unique', 'targetAttribute' => ['skuId', 'lv']],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'skuId' => 'Sku ID',
            'lv' => 'VIP等级',
            'price' => '价格',
        ]; 
    }

	public function init(){
		parent::init();
		$this->on(self::EVENT_AFTER_INSERT,array($this,"createSkuPriceLog"));
	}

	public function createSkuPriceLog()
	{
		$skuPriceLog = new SkuPriceLog();
		$skuPriceLog->handlerType = source::TYPE_STAFF;
		$skuPriceLog->handlerId = 1;
		$skuPriceLog->skuId = $this->skuId;
		$skuPriceLog->lv = $this->lv;
		$skuPriceLog->originaPrice = NULL;
		$skuPriceLog->price = $this->price;
		$skuPriceLog->createTime = time();
		if (!$skuPriceLog->validate()) {
			throw new SmartException("skuPriceLog validate failed"); 
		}
		if (!$skuPriceLog->save(false)) {
			throw new SmartException("skuPriceLog create failed"); 
		}
	}
}