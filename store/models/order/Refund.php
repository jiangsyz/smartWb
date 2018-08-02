<?php
namespace store\models\order; 

use Yii; 
use store\models\member\Member;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/** 
 * This is the model class for table "refund". 
 * 
 * @property int $id 主键
 * @property int $oid 订单id
 * @property int $bid 购物行为id
 * @property int $price 退款金额(分为单位)
 * @property int $applyTime 申请时间
 * @property int $status 状态(0=申请中/1=完成/-1=驳回)
 * @property string $applyMemo 申请备注
 * @property string $rejectMemo 驳回备注
 * @property string $refundMemo 退款备注
 */ 
class Refund extends \yii\db\ActiveRecord
{ 
    /** 
     * {@inheritdoc} 
     */ 
    public static function tableName() 
    { 
        return 'refund'; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function rules() 
    { 
        return [
            [['oid', 'price', 'applyTime', 'status'], 'required'],
            [['oid', 'bid', 'applyTime', 'status'], 'integer'],
            [['applyMemo', 'rejectMemo', 'refundMemo'], 'string', 'max' => 300],
        ]; 
    } 

    /** 
     * {@inheritdoc} 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'oid' => 'Oid',
            'bid' => 'Bid',
            'price' => 'Price',
            'applyTime' => 'Apply Time',
            'status' => 'Status',
            'applyMemo' => 'Apply Memo',
            'rejectMemo' => 'Reject Memo',
            'refundMemo' => 'Refund Memo',
        ]; 
    }

    public function getOrderRecord()
    {
        return $this->hasOne(OrderRecord::className(), ['id'=>'oid']);
    }

    public static function getSqlWhere($params)
    {
        $where = " WHERE 1=1";
        if (!empty($params['phone'])) {
            $where .= " AND e.phone='{$params['phone']}'";
        }
        if (!empty($params['code'])) {
            $where .= " AND b.code='{$params['code']}'";
        }
        if (!empty($params['minDate'])) {
            $where .= " AND a.applyTime>=".strtotime($params['minDate']);
        }
        if (!empty($params['maxDate'])) {
            $where .= " AND a.applyTime<=".(strtotime($params['maxDate'])+86400-1);
        }
        if (isset($params['status']) && $params['status']!='' && $params['status']!=99) {
            $where .= " AND a.status={$params['status']}";
        }
        return $where;
    }

    public static function search($params)
    {
        $where = self::getSqlWhere($params);

        //整单退实体商品查询
        $sql = "SELECT a.id, TRUNCATE(a.price/100,2) AS price, a.applyTime, a.status, a.applyMemo, a.rejectMemo, b.code, d.finalPrice as singlePrice, d.buyingCount,
            concat(g.title,'（', f.title, '）') AS title, g.cover, e.phone, e.nickName
            FROM `refund` AS a 
            INNER JOIN `order_record` AS b ON b.id=a.oid
            INNER JOIN `order_record` AS c ON c.parentId=b.id
            INNER JOIN `order_buying_record` AS d ON d.orderId=c.id AND d.sourceType=2
            INNER JOIN `member`AS e ON e.id=b.memberId 
            INNER JOIN `sku`AS f ON f.id=d.sourceId
            INNER JOIN `spu` AS g ON g.id=f.spuId
            {$where} AND a.bid=0";
        //部分退实体商品查询
        $sql .= " UNION (SELECT a.id, TRUNCATE(a.price/100,2) AS price, a.applyTime, a.status, a.applyMemo, a.rejectMemo, b.code, d.finalPrice as singlePrice, d.buyingCount,
            concat(g.title,'（', f.title, '）') AS title, g.cover, e.phone, e.nickName
            FROM `refund` AS a 
            INNER JOIN `order_record` AS b ON b.id=a.oid
            INNER JOIN `order_buying_record` AS d ON d.id=a.bid AND d.sourceType=2
            INNER JOIN `member`AS e ON e.id=b.memberId 
            INNER JOIN `sku`AS f ON f.id=d.sourceId
            INNER JOIN `spu` AS g ON g.id=f.spuId
            {$where} AND a.bid<>0)";
        //echo $sql;exit;
        //整单退虚拟商品查询
        $sql .= " UNION (SELECT a.id, TRUNCATE(a.price/100,2) AS price, a.applyTime, a.status, a.applyMemo, a.rejectMemo, b.code, d.finalPrice as singlePrice, d.buyingCount,
            f.title, f.cover, e.phone, e.nickName
            FROM `refund` AS a 
            INNER JOIN `order_record` AS b ON b.id=a.oid
            INNER JOIN `order_record` AS c ON c.parentId=b.id
            INNER JOIN `order_buying_record` AS d ON d.orderId=c.id AND d.sourceType=7
            INNER JOIN `member`AS e ON e.id=b.memberId 
            INNER JOIN `virtual_item` AS f ON f.id=d.sourceId
            {$where} AND a.bid=0)";
        //部分退虚拟商品查询
        $sql .= " UNION (SELECT a.id, TRUNCATE(a.price/100,2) AS price, a.applyTime, a.status, a.applyMemo, a.rejectMemo, b.code, d.finalPrice as singlePrice, d.buyingCount,
            f.title, f.cover, e.phone, e.nickName
            FROM `refund` AS a 
            INNER JOIN `order_record` AS b ON b.id=a.oid
            INNER JOIN `order_record` AS c ON c.parentId=b.id
            INNER JOIN `order_buying_record` AS d ON d.orderId=c.id AND d.sourceType=7
            INNER JOIN `member`AS e ON e.id=b.memberId 
            INNER JOIN `virtual_item` AS f ON f.id=d.sourceId
            {$where} AND a.bid<>0)";
        //echo $sql;exit;
        $count = Yii::$app->db->createCommand("select count(*) from ({$sql}) a")->queryScalar();
        //union时不能排序  
        $sql = "select * from ({$sql}) t order by t.applyTime desc";
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 30,
            ]
        ]);

        return $dataProvider;
    }

    public static function tidyRefunds($refunds)
    {
        //echo "<pre>";print_r($refunds);exit;
        $rtn = [];
        foreach ($refunds as $key => $refund) {
            $refundId = $refund['id'];
            if (!isset($rtn[$refundId])) {
                $rtn[$refundId] = $refund;
                unset($rtn[$refundId]['singlePrice']);
                unset($rtn[$refundId]['buyingCount']);
                unset($rtn[$refundId]['title']);
                unset($rtn[$refundId]['cover']);
            }
            $rtn[$refundId]['product'][] = [
                'cover' => $refund['cover'],
                'title' => $refund['title'],
                'singlePrice' => $refund['singlePrice'],
                'buyingCount' => $refund['buyingCount']
            ];
        }
        return $rtn;
    }

    
} 