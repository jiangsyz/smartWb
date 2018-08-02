<?php
//会员
namespace store\models\member;

use Yii;
use yii\base\SmartException;
use yii\data\ActiveDataProvider;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
use yii\data\SqlDataProvider;
//========================================
class Member extends \yii\db\ActiveRecord
{
	//用户列表筛选
	public function search($params)
	{
        $query = Member::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 15,
            ],
            'sort' => [
                'defaultOrder' => [
                    'createTime' => SORT_DESC,
                ]
            ],
        ]);

        if (isset($params['id'])) {
            $query->andFilterWhere([
                'id' => $params['id'],
            ]);
        }
        if (isset($params['phone'])) {
            $query->andFilterWhere([
                'phone' => $params['phone'],
            ]);
        }

        $minDate = isset($params['minDate']) ? $params['minDate'] : null;
        $maxDate = isset($params['maxDate']) ? $params['maxDate'] : null;

        $this->addWhereDateStartAndEnd($query, 'createTime', $minDate, $maxDate, false);

        return $dataProvider;
    
	}

    public function addWhereDateStartAndEnd($query, $item, $start, $end , $isDateTime = true)
    {
        if (!empty($start) && !empty($end)) {
            $starTime = strtotime($start);
            $endTime = $isDateTime ? strtotime($end) : strtotime($end.' 23:59:59');
            $query->andFilterWhere(['>=', $item, $starTime]);
            $query->andFilterWhere(['<=', $item, $endTime]);

        } elseif (!empty($start)) {
            $starTime = strtotime($start);
            $query->andFilterWhere(['>=', $item, $starTime]);
        } elseif (!empty($end)) {
            $endTime = $isDateTime ? strtotime($end) : strtotime($end.' 23:59:59');
            $query->andFilterWhere(['<=', $item, $endTime]);
        }
    }
}