<?php

namespace store\models;

use Yii;
use yii\base\Model;
use store\models\product\Spu;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

class ProductSearch extends Spu
{
    public $minPrice;
    public $maxPrice;
    public $minCount;
    public $maxCount;

    public function scenarios()
    {
        // 旁路在父类中实现的 scenarios() 函数
        return Model::scenarios();
    }

    public function getSqlWhere($params)
    {
        $where = " WHERE sku_member_price.lv=0";
        $having = " HAVING 1=1";
        
        //拼接查询语句
        if (isset($params['closed'])) {
            $where .= " AND spu.closed={$params['closed']}";
        }
        //标题
        if (!empty($params['title'])) {
            $where .= " AND spu.title like '%{$params['title']}%'";
        }
        //编码
        if (!empty($params['uniqueId'])) {
            $where .= " AND spu.uniqueId='{$params['uniqueId']}'";
        }
        //价格区间
        if (isset($params['minPrice']) && $params['minPrice']!='') {
            $having .= " AND price>={$params['minPrice']}";
        }
        if (isset($params['maxPrice']) && $params['maxPrice']!='') {
            $having .= " AND price<={$params['maxPrice']}";
        }
        //库存区间
        if (isset($params['minCount']) && $params['minCount']!='') {
            $having .= " AND count>={$params['minCount']}";
        }
        if (isset($params['maxCount']) && $params['maxCount']!='') {
            $having .= " AND count<={$params['maxCount']}";
        }
        //发布时间
        if (!empty($params['minDate'])) {
            $where .= " AND createTime>=".strtotime($params['minDate']);
        }
        if (!empty($params['maxDate'])) {
            $where .= " AND createTime<=".(strtotime($params['maxDate'])+86400-1);
        }
        return [$where, $having];
    }

    public function search($params)
    {
        list($where, $having) = $this->getSqlWhere($params);
        
        $sql = "SELECT spu.id, spu.uniqueId, spu.desc, spu.cover, spu.title, spu.createTime, sum(sku.count) as count,
                min(sku_member_price.price) as price, spu.closed, spu.locked
                FROM `spu`
                LEFT JOIN `sku` ON spu.id=sku.spuId
                LEFT JOIN `sku_member_price` ON sku_member_price.skuId=sku.id
                {$where}
                GROUP BY spu.id
                {$having}
                ORDER BY createTime DESC";
        //echo $sql;exit;
        $count = Yii::$app->db->createCommand("select count(*) from ({$sql}) a")->queryScalar();
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => [
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
                ],
            ],
        ]);

        return $dataProvider;
    }
}