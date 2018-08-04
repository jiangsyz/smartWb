<?php
//推荐记录
namespace store\models\recommend;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
use yii\data\SqlDataProvider;
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

	public function getSqlWhere($params)
	{
		$where = " where recommend_record.sourceType=1";
		if ($params['recommendType']) {
			$where .= " and recommendType={$params['recommendType']}";
		}
		return $where;
	}

	public function search($params)
    {
        $where = $this->getSqlWhere($params);
        
        $sql = "SELECT recommend_record.id, spu.uniqueId, spu.cover, spu.title, spu.createTime, spu.closed, spu.locked, recommend_record.sort
                FROM `recommend_record`
                LEFT JOIN `spu` ON spu.id=recommend_record.sourceId
                {$where}
                ORDER BY recommend_record.sort ASC";
        //echo $sql;exit;
        $count = Yii::$app->db->createCommand("select count(*) from ({$sql}) a")->queryScalar();
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                /*'attributes' => [
                    'price' => [
                        'desc' => ['price' => SORT_DESC],
                        'asc' => ['price' => SORT_ASC],
                        'default' => SORT_DESC,
                    ],
                    'count' => [
                        'desc' => ['count' => SORT_DESC],
                        'asc' => ['count' => SORT_ASC],
                        'default' => SORT_DESC,  
                    ]
                ],*/
            ],
        ]);

        return $dataProvider;
    }
}