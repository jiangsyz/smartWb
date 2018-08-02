<?php
//推荐记录
namespace store\models\recommend;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
//========================================
class RecommendRecord extends SmartActiveRecord{
	const RECOMMEND_TYPE_RECOMMEND=1;//推荐
	const RECOMMEND_TYPE_HOT=2;//热门

	//设置2个虚拟字段  用于渲染
	public $isHot;
	public $isRecommend;

	public function attributeLabels(){ 
        return [ 
            'isHot' => '是否热门商品',
            'isRecommend' => '是否推荐商品',
        ]; 
    } 
	//========================================
	//获取推荐的资源
	public function getSource(){return Source::getRelationShip($this);}

	//判断SPU是否为热门或推荐商品
	public function isHotOrRecommend($id, $type)
	{
		$record = self::findOne(['recommendType'=>$type, 'sourceType'=>source::TYPE_SPU, 'sourceId'=>$id]);
		return !empty($record) ? 1 : 0;
	}

	public function saveRecommendRecord($params)
	{
		//添加或删除热门商品
		//echo "<pre>";print_r($params);exit;
		$hot = self::findOne(['recommendType'=>self::RECOMMEND_TYPE_HOT, 'sourceType'=>source::TYPE_SPU, 'sourceId'=>$params['spuId']]);
		$recommend = self::findOne(['recommendType'=>self::RECOMMEND_TYPE_RECOMMEND, 'sourceType'=>source::TYPE_SPU, 'sourceId'=>$params['spuId']]);
		//上架且设成热门
		if ($params['RecommendRecord']['isHot'] && $params['Spu']['closed']==0) {
			if (empty($hot)) {
				$hot['recommendType'] = self::RECOMMEND_TYPE_HOT;
				$hot['sourceType'] = source::TYPE_SPU;
				$hot['sourceId'] = $params['spuId'];
				self::addObj($hot);
			}
		} else {
			if (!empty($hot)) {
				$hot->delete();
			}
		}
		//添加推荐商品或删除推荐商品
		if ($params['RecommendRecord']['isRecommend'] && $params['Spu']['closed']==0) {
			if (empty($recommend)) {
				$recommend['recommendType'] = self::RECOMMEND_TYPE_RECOMMEND;
				$recommend['sourceType'] = source::TYPE_SPU;
				$recommend['sourceId'] = $params['spuId'];
				self::addObj($recommend);
			}
		} else {
			if (!empty($recommend)) {
				$recommend->delete();
			}
		}

	}
}