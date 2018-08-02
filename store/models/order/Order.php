<?php
namespace store\models\order;

use store\models\product\Spu;
use Yii;
use yii\base\SmartException;
use yii\base\Component;
use store\models\member\Member;
use store\models\member\Address;
use store\models\model\Source;
use yii\data\SqlDataProvider;
use yii\db\SmartActiveRecord;
use common\models\Excel;
use store\models\product\Sku;
use store\models\logistics\Logistics;
use store\models\order\OrderBuyingRecord;
use store\models\order\MergeOrderHistory;
use store\models\Goods;
use yii\helpers\ArrayHelper;

//========================================
class Order extends SmartActiveRecord
{
    const STATUS_UNPAID = 1;//待支付
    const STATUS_REFUNDING = 2;//退款中
    const STATUS_CLOSED = 3 ;//交易关闭
    const STATUS_UNDELIVERED = 4;//待发货
    const STATUS_UNRECEIPTED = 5;//待收货
    const STATUS_FINISHED= 6;//已完成

    //虚拟字段  1-子订单  2-主订单  用于导表工具
    public $otype;
	
	public static function getSqlWhere($params)
    {
        $where = " WHERE 1=1";
        
        //订单ID
        if (!empty($params['id'])) {
            $where .= " AND f.id={$params['id']}";
        }
        //订单编号
        if (!empty($params['code'])) {
            //$where .= " AND a.parentId={$params['id']}";
            $where .= " AND f.code={$params['code']}";
        }
        //是否锁定
        if (isset($params['locked']) && $params['locked']!=='') {
            $where .= " AND f.locked={$params['locked']}";
        }
        //手机
        if (!empty($params['phone'])) {
            $where .= " AND c.phone={$params['phone']}";
        }
        //下单时间
        if (!empty($params['minDate'])) {
            $where .= " AND a.createTime>=".strtotime($params['minDate']);
        }
        if (!empty($params['maxDate'])) {
            $where .= " AND a.createTime<=".(strtotime($params['maxDate'])+86400-1);
        }
        if (!empty($params['status'])) {
            switch ($params['status']) {
                case '3': //交易关闭
                    $where .= " AND (f.cancelStatus=1 OR f.closeStatus=1 OR f.payStatus=-1)";
                    break;
                case '6': //已完成
                    $where .= " AND f.finishStatus=1 AND f.payStatus=1 AND f.deliverStatus=3";
                    break;
                case '2': //退款中
                    $where .= " AND f.refundingStatus=1";
                    break;
                case '5': //待收货
                    $where .= " AND f.isNeedAddress=1 AND f.payStatus=1 AND (f.deliverStatus=1 OR f.deliverStatus=2) AND f.cancelStatus=0 AND f.closeStatus=0 AND f.finishStatus=0 AND f.refundingStatus=0";
                    break;
                case '4': //待发货
                    $where .= " AND f.isNeedAddress=1 AND f.payStatus=1 AND f.deliverStatus=0 AND f.cancelStatus=0 AND f.closeStatus=0 AND f.finishStatus=0";
                    break;
                case '1': //待支付
                    $where .= " AND f.payStatus=0 AND f.deliverStatus=0 AND f.cancelStatus=0 AND f.closeStatus=0";
                    break;
            }
        }
        
        return $where;
    }

	//找出所有符合筛选条件的子订单
	public static function search($params)
	{
		$where = self::getSqlWhere($params);
		$skuWhere = $virtualWhere = "";
		if (!empty($params['title'])) {
			$skuWhere = " AND e.title like '%{$params['title']}%'";
            $virtualWhere = " AND d.title like '%{$params['title']}%'";
		}
        if (!empty($params['phone'])) {
            $skuWhere = " AND c.phone='{$params['phone']}'";
        }
      
		//先查出符合筛选条件的父订单ID  因为要进行分页 所以不能一步到位
        $sql = "SELECT a.parentId
            FROM `order_record` AS a 
            INNER JOIN `order_buying_record` AS b ON b.orderId=a.id AND b.sourceType=2
            INNER JOIN `member`AS c ON c.id=a.memberId 
            INNER JOIN `sku`AS d ON d.id=b.sourceId
            INNER JOIN `spu` AS e on e.id=d.spuId
            INNER JOIN `order_record` AS f ON f.id=a.parentId
            {$where} {$skuWhere}
            GROUP BY a.parentId";
        $sql .= " UNION (SELECT a.parentId
            FROM `order_record` AS a 
            INNER JOIN `order_buying_record` AS b ON b.orderId=a.id AND b.sourceType=7
            INNER JOIN `member` AS c ON c.id=a.memberId 
            INNER JOIN `virtual_item` AS d ON d.id=b.sourceId
            INNER JOIN `order_record` AS f ON f.id=a.parentId
            {$where} {$virtualWhere}
        	GROUP BY a.parentId)";
        //echo $sql;exit;
        $count = Yii::$app->db->createCommand("select count(*) from ({$sql}) a")->queryScalar();
        //union时不能排序  
        $sql = "select * from ({$sql}) t order by t.parentId desc";
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 30,
            ]
        ]);

        return $dataProvider;
	}

	//获取订单的相关信息 如商品，购买人等
	public static function getOrderRelativeInfo($orderIds, $parentFlag=0)
	{
		$orderIds = implode(',', $orderIds);
		$idField = $parentFlag ? 'a.parentId' : 'a.id';
		//SKU商品的查询
		$sql = "SELECT a.id, a.parentId, a.price, a.finalPrice, f.pay, a.createTime, b.buyingCount, b.logisticsCode,b.dataPhoto,
			b.finalPrice AS singlePrice, concat(e.title,'（', d.title, '）') AS title, d.uniqueId, d.spuId, e.cover AS cover, c.phone, c.nickName, f.code, f.freight, f.locked, f.payStatus, f.deliverStatus, f.cancelStatus, f.closeStatus, f.finishStatus, b.id AS bid, f.isNeedAddress
            FROM `order_record` AS a 
            INNER JOIN `order_buying_record` AS b ON b.orderId=a.id AND b.sourceType=2
            INNER JOIN `member`AS c ON c.id=a.memberId
            INNER JOIN `sku`AS d ON d.id=b.sourceId
            INNER JOIN `spu` AS e ON e.id=d.spuId
            INNER JOIN `order_record` AS f ON f.id=a.parentId
            WHERE {$idField} in ({$orderIds})";
        //VIP卡的查询
        $sql .= " UNION SELECT a.id, a.parentId, a.price, a.finalPrice, f.pay, a.createTime, b.buyingCount, b.logisticsCode,b.dataPhoto,
        	b.finalPrice AS singlePrice, d.title AS title, '0' AS skuId ,'0' AS uniqueId, d.cover AS cover, c.phone, c.nickName, f.code, f.freight, f.locked, f.payStatus, f.deliverStatus, f.cancelStatus, f.closeStatus, f.finishStatus, b.id AS bid, f.isNeedAddress
            FROM `order_record` AS a 
            INNER JOIN `order_buying_record` AS b ON b.orderId=a.id AND b.sourceType=7
            INNER JOIN `member` AS c ON c.id=a.memberId
            INNER JOIN `virtual_item` AS d ON d.id=b.sourceId
            INNER JOIN `order_record` AS f ON f.id=a.parentId
            WHERE {$idField} in ({$orderIds})";
        $sql = "select * from ({$sql}) t order by t.parentId desc";
        $tmpOrders = Yii::$app->db->createCommand($sql)->queryAll();
        //整合订单列表
        $orders = self::tidyOrderList($tmpOrders);
		return $orders;
	}

    //整合订单列表数据
    public static function tidyOrderList($tmpOrders)
    {
        $orders = [];
        foreach ($tmpOrders as &$orderProduct) {
            $refund = '';
            //父订单ID
            $parentId = $orderProduct['parentId'];
            //先看订单是否被单个退了 取最新的一个状态为准
            $refund = Refund::find()->where("bid={$orderProduct['bid']} and status in (0,1,2,3)")->orderBy("applyTime desc")->asArray()->one();
            $refund = $refund ? $refund : Refund::find()->where("bid={$orderProduct['bid']} and status=-1")->orderBy("applyTime desc")->asArray()->one();
            //如果没有单个退  看是否存在整单退
            $refund = $refund ? $refund : Refund::find()->where("oid={$parentId} and bid=0 and status in (0,1,2,3)")->orderBy("applyTime desc")->asArray()->one();
            $refund = $refund ? $refund : Refund::find()->where("oid={$parentId} and bid=0 and status=-1")->orderBy("applyTime desc")->asArray()->one();
            $orderProduct['refund'] = $refund;
            //查询父订单的属性
            $orderProperty = OrderProperty::find()->where("orderId={$orderProduct['parentId']} and (propertyKey='address' or propertyKey='memberMemo' or propertyKey='staffMemo')")->all();
            //赋值收货地址 备注等信息
            foreach ($orderProperty as $key => $obj) {
                //不支持string类型后数组的写法 如$arr['address'][] 所以用$index计算索引
                $index = isset($orders[$parentId][$obj->propertyKey]) ? count($orders[$parentId][$obj->propertyKey]) : 0;
                $orders[$parentId][$obj->propertyKey][$obj->id] = $obj->propertyVal;
            }
            $addressJson = @end($orders[$parentId]['address']);
            //echo "<pre>";print_r($addressJson);exit;
            //买家手机  下单时间  运费  订单状态
            $orders[$parentId]['phone'] = $orderProduct['phone'];
            $orders[$parentId]['createTime'] = $orderProduct['createTime'];
            $orders[$parentId]['freight'] = $orderProduct['freight'];
            $orders[$parentId]['locked'] = $orderProduct['locked'];
            $orders[$parentId]['pay'] = $orderProduct['pay'];
            $orders[$parentId]['code'] = $orderProduct['code'];
            $orders[$parentId]['payStatus'] = $orderProduct['payStatus'];
            $orders[$parentId]['deliverStatus'] = $orderProduct['deliverStatus'];
            $orders[$parentId]['cancelStatus'] = $orderProduct['cancelStatus'];
            $orders[$parentId]['closeStatus'] = $orderProduct['closeStatus'];
            $orders[$parentId]['finishStatus'] = $orderProduct['finishStatus'];
            $orders[$parentId]['isNeedAddress'] = $orderProduct['isNeedAddress'];
            $orders[$parentId]['nickName'] = $orderProduct['nickName'];
            unset($orderProduct['freight']);
            unset($orderProduct['locked']);
            unset($orderProduct['pay']);
            unset($orderProduct['code']);
            unset($orderProduct['payStatus']);
            unset($orderProduct['deliverStatus']);
            unset($orderProduct['cancelStatus']);
            unset($orderProduct['closeStatus']);
            unset($orderProduct['finishStatus']);
            unset($orderProduct['nickName']);
            //获取父订单伪状态
            list($orders[$parentId]['status'],$orders[$parentId]['statusDesc']) = self::getOrderStatus($parentId);
            $orders[$parentId]['product'][] = $orderProduct;
            //如果有收件地址  则解析json数据 获取地址信息
            if (!empty($addressJson)) {
                $addressArr = json_decode($addressJson, true);
                $orders[$parentId]['consigneeAddress'] = str_replace(',', '', $addressArr['fullAreaName']).$addressArr['address'];
                $orders[$parentId]['consignee'] = $addressArr['name'];
                $orders[$parentId]['consigneePhone'] = $addressArr['phone'];
                unset($orders[$parentId]['address']);
            }
        }
        //echo "<pre>";print_r($orders);exit;
        return $orders;
    }

    //根据多字段判断整个订单状态
    public static function getOrderStatus($orderId)
    {
        $order = OrderRecord::findOne($orderId);
        //1-客户主动取消订单  2-客服关闭订单 3-支付超时  都算交易关闭
        if ($order->cancelStatus == 1 || $order->closeStatus == 1 || $order->payStatus == -1) {
            return [self::STATUS_CLOSED, '<span style="color:red">交易关闭</span>'];
        }
        if ($order->finishStatus == 1) {
            return [self::STATUS_FINISHED, '<span>交易完成</span>'];
        }
        if ($order->refundingStatus == 1) {
            return [self::STATUS_REFUNDING, '<span style="color:red">退款中</span>'];
        }
        if ($order->deliverStatus == 3) {
            return [self::STATUS_FINISHED, '<span>已签收</span>'];
        }
        if ($order->deliverStatus == 1 || $order->deliverStatus == 2) {
            return [self::STATUS_UNRECEIPTED, '<span>待收货</span>'];
        }
        if ($order->payStatus == 1) {
            return [self::STATUS_UNDELIVERED, '<span>待发货</span>'];
        }
        if ($order->payStatus == 0) {
            return [self::STATUS_UNPAID, '<span>待支付</span>'];
        }
    }
    

    //获取待发货的订单
    public static function getOrderForDelivery($params)
    {
        $where = " WHERE parentId is null and isNeedAddress=1 and payStatus=1 
            and cancelStatus=0 and closeStatus=0 
            and (deliverStatus<>3) and locked=0";
        //订单号
        if (!empty($params['id'])) {
            $where .= " AND id={$params['id']}";
        }
        //订单编码
        if (!empty($params['code'])) {
            $where .= " AND code='{$params['code']}'";
        }
        //下单时间
        if (!empty($params['minDate'])) {
            $where .= " AND createTime>=" . strtotime($params['minDate']);
        }
        if (!empty($params['maxDate'])) {
            $where .= " AND createTime<" . (strtotime($params['maxDate']) + 86400);
        }
        try {
            $transaction = Yii::$app->db->beginTransaction();
            $sql = "select a.id,a.parentId from order_record as a
        left join order_property as b on parentId=b.orderId
        where a.parentId in (select id from order_record {$where}) for update";

            # 获取待发货的订单
            $orders = OrderRecord::findBySql($sql)->indexBy('id')->asArray()->all();
            if(empty($orders)){
                throw new \Exception('没有待发货的订单');
            }
            $all_sku_orders = [];
            $sub_order_ids = array_keys($orders);
            while ($part_order_ids = array_splice($sub_order_ids, 0, 800)) {
                $all_sku_orders = array_merge($all_sku_orders,OrderBuyingRecord::find()->where(['orderId' => $part_order_ids])->asArray()->all());
            }
          //  $all_sku_orders = OrderBuyingRecord::find()->where(['orderId' => $sub_order_ids])->asArray()->all();
            $sku_ids = $spu_ids = $sku_orders = $skus = $spus = [];
            foreach ($all_sku_orders as $v) {
                $sku_orders[$v['orderId']][] = $v;
                $sku_ids[] = $v['sourceId'];
            }
            # 获取sku信息
            $sku_ids = array_unique($sku_ids);
            if ($sku_ids) {
                $skus = Sku::find()->where(['id' => $sku_ids])->indexBy('id')->asArray()->all();
                foreach ($skus as $sku) {
                    $spu_ids[] = $sku['spuId'];
                }
            }
            # 获取spu信息
            if ($spu_ids) {
                $spus = Spu::find()->where(['id' => $spu_ids])->indexBy('id')->asArray()->all();
            }
            # 合单
            ####################
            $memoArray = [];

            foreach ($orders as $order) {
                //子订单下的所有商品
                $orderProducts = $sku_orders[$order['id']];
                foreach ($orderProducts as $orderProduct) {
                    $sku_id = $orderProduct['sourceId'];
                    if (empty($skus[$sku_id])) continue;
                    $sku = $skus[$sku_id];
                    $spu_id = $sku['spuId'];
                    if (empty($spus[$spu_id])) continue;
                    $product_logistics_id = $spus[$spu_id]['logisticsId'];
                    //如果商品的物流渠道跟当前要导出的不同就跳过
                    if ($product_logistics_id != $params['Logistics']['id'] || $orderProduct['logisticsCode'] != '') {
                        continue;
                    }
                    $property = OrderProperty::find()
                        ->where([
                            'orderId' => $order['parentId'],
                            'propertyKey' => [
                                'address',
                                'memberMemo',
                                'staffMemo',
                            ],
                        ])
                        ->indexBy('propertyKey')
                        # 获取最新地址
                        ->orderBy('createTime asc')
                        ->asArray()
                        ->all();
                    if (!$property['address']) {
                        continue;
                    }
                    //父订单状态更新成已配货
                    $parentOrder = OrderRecord::findOne(['id' => $order['parentId']]);
                    $parentOrder->deliverStatus = 1;
                    $parentOrder->save(false);
                    $arr = json_decode($property['address']['propertyVal'], true);
                    $hash = md5($arr['name'] . $arr['phone'] . $arr['address']);
                    $orderProduct['parentId'] = $order['parentId'];
                    # 备注
                    $memberMemo = ArrayHelper::getValue($property, 'memberMemo.propertyVal', '');
                    $staffMemo = ArrayHelper::getValue($property, 'staffMemo.propertyVal', '');
                    if (!empty($memberMemo) || !empty($staffMemo)) {
                        $memoArray[$orderProduct['id']] = "[订单号:{$order['parentId']} {$memberMemo} {$staffMemo}]";
                    }
                    $merge_orders[$hash][] = $orderProduct;
                }
            }
            $final_orders = [];
            if (empty($merge_orders)) {
                throw new \Exception('订单为空');
            }
            foreach ($merge_orders as $hash => $orders) {
                //整理出各SKU总数
                $rtn = $memoData = [];
                foreach ($orders as $k => $order) {
                    $sku_id = $order['sourceId'];
                    if (isset($rtn[$sku_id])) {
                        $rtn[$sku_id]['buyingCount'] += $order['buyingCount'];
                        if (!empty($memoArray[$order['id']])) {
                            $rtn[$sku_id]['memo'] .= $memoArray[$order['id']];
                        }
                    } else {
                        # 初始化
                        $rtn[$sku_id]['buyingCount'] = $order['buyingCount'];
                        $rtn[$sku_id]['memo'] = !empty($memoArray[$order['id']]) ? $memoArray[$order['id']] : '';
                    }
                }
                foreach ($orders as $k => $order) {
                    if ($k == 0) {
                        $first_order_id = $order['parentId'];
                    }
                    $sku_id = $order['sourceId'];
                    //记录合并订单的日志
                    $order['orderId'] = $first_order_id;
                    self::mergeOrderHistory($order, $params['Logistics']['id']);
                    //数量是否已经赋值过
                    if (array_key_exists($sku_id, $rtn)) {
                        $final_orders[$hash][] = [
                            'id' => $order['id'],
                            'orderId' => $order['orderId'],
                            'sku_id' => $order['sourceId'],
                            'buyingCount' => $rtn[$sku_id]['buyingCount'],
                            'memo' => ArrayHelper::getValue($rtn, $sku_id . '.memo', ''),
                            'logisticsCode' => $order['logisticsCode'],
                        ];
                        unset($rtn[$sku_id]);
                    }
                }
            }
            #############
            //  $mergeOrders = self::mergeOrder($orders, $params['Logistics']['id']);

            //商品名称要重新根据编码库的商品名称生成
            $goods = Goods::find()->where("logistics_id={$params['Logistics']['id']}")->all();
            $units = $sku_ids = $spu_ids = $spus = [];
            foreach ($goods as $val) {
                if ($params['Logistics']['id'] == 4) {
                    $units[$val->code] = $val->unit;
                }
                $codeBrand[$val->code] = $val->name;
            }
            //echo "<pre>";print_r($codeBrand);exit;
            //循环导出的数据

            foreach ($final_orders as $hash => $orderProducts) {
                //导出物流Excel的固定字段
                $basicData = Yii::$app->params['SfColdExcelBasicData'];
                foreach ($orderProducts as $orderProduct) {
                    //如果这个订单商品已经有运单号了就跳过
                    if (!empty($orderProduct['logisticsCode'])) {
                        continue;
                    }
                    //SKU编码在编码库中不存在
                    $sku_uniqueId = ArrayHelper::getValue($skus, $orderProduct['sku_id'] . '.uniqueId', '');
                    if (empty($sku_uniqueId)) {
                        continue;
                    }
                    if (!isset($codeBrand[$sku_uniqueId])) {
                        throw new \Exception($sku_uniqueId . "不在商品库编码内");
                    }
                    $parentOrder = OrderRecord::findOne(['id' => $orderProduct['orderId']]);
                    //物流渠道
                    if ($params['Logistics']['id'] == Logistics::SF_COLD) { //顺丰冷运
                        $header1 = Yii::$app->params['SfColdExcelHeader'];
                        $data1['顺丰冷运'][] = [$basicData['codeNo'], $basicData['codeNo'], $parentOrder->id,
                            $basicData['orderType'], $basicData['typeName'], $basicData['houseCode'], $basicData['payType'],
                            $basicData['receiverPay'], $basicData['insteadCash'], $basicData['insured'], $basicData['statement'], $basicData['packingFee'],
                            $basicData['individuationService'], $basicData['carrierCode'],
                            $basicData['carrierType'], $parentOrder->getAddress('name'), $parentOrder->getAddress('name'),
                            $parentOrder->getAddress('phone'), $parentOrder->getAddress('phone'), $parentOrder->getAddress('address'), $basicData['consignor'],
                            $basicData['consignor'], $basicData['tel'], $basicData['tel'], $basicData['address'],
                            $sku_uniqueId, $codeBrand[$sku_uniqueId], $orderProduct['buyingCount'],
                            '', '', '', '', $basicData['receipt'], '', $orderProduct['memo'],
                        ];
                    }
                    //顺丰快递
                    if ($params['Logistics']['id'] == Logistics::SF_EXPRESS) {
                        throw new \Exception("暂不支持顺丰快递");
                    }
                    //德邦快递
                    if ($params['Logistics']['id'] == Logistics::DB_EXPRESS) {
                        $header3 = Yii::$app->params['DbExpressExcelHeader'];
                        $data3['德邦快递'][] = [$parentOrder->id, $parentOrder->getAddress('name'), $parentOrder->getAddress('phone'),
                            $parentOrder->getAddress('address'), '', $sku_uniqueId, $codeBrand[$sku_uniqueId],
                            $orderProduct['memo'], $orderProduct['buyingCount'],
                        ];
                    }
                    //德邦红酒
                    if ($params['Logistics']['id'] == Logistics::DB_WINE) {
                        $header4 = Yii::$app->params['DbWineExcelHeader'];
                        $unit = ArrayHelper::getValue($units,$sku_uniqueId,'');
                        $data4['德邦红酒'][] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            $parentOrder->id, $parentOrder->getAddress('name'), $parentOrder->getAddress('address'),
                            $parentOrder->getAddress('phone'), $codeBrand[$sku_uniqueId], $sku_uniqueId,
                            $orderProduct['buyingCount'].$unit, '', '', '', '', '',
                            $codeBrand[$sku_uniqueId], $orderProduct['memo'],
                        ];
                    }
                    //整条出库
                    if ($params['Logistics']['id'] == Logistics::ZTCK) {
                        $header5 = Yii::$app->params['ZsckExcelHeader'];
                        $sku = ArrayHelper::getValue($skus, $orderProduct['sku_id'], []);
                        if (empty($sku)) {
                            continue;
                        }
                        $spu_title = ArrayHelper::getValue($spus, $sku['spuId'] . '.title', '');
                        if (empty($spu_title)) {
                            continue;
                        }
                        $data5[$spu_title][] = ['', date('Y-m-d', time()), '', $spu_title . $sku['title'],
                            '', '', $parentOrder->id, $sku['title'], $parentOrder->getAddress('name'),
                            $parentOrder->getAddress('phone'), $parentOrder->getAddress('address'), $orderProduct['buyingCount'],
                            $orderProduct['memo'], '', '', '', $sku_uniqueId,
                        ];
                    }
                }
            }
            $transaction->commit();

            if ($params['Logistics']['id'] == Logistics::SF_COLD) {
                if (empty($data1)) {
                    throw new \Exception("无顺丰冷运订单");
                }
                $sheet1 = array_keys($data1);
                return ['data' => $data1, 'header' => $header1, 'sheet' => $sheet1, 'filename' => date('Ymd') . '小程序顺丰冷运订单'];
            }
            if ($params['Logistics']['id'] == Logistics::DB_EXPRESS) {
                if (empty($data3)) {
                    throw new \Exception("无德邦快递订单");
                }
                $sheet3 = array_keys($data3);
                return ['data' => $data3, 'header' => $header3, 'sheet' => $sheet3, 'filename' => date('Ymd') . '小程序德邦快递订单'];
            }
            if ($params['Logistics']['id'] == Logistics::DB_WINE) {
                if (empty($data4)) {
                    throw new \Exception("无德邦红酒订单");
                }
                $sheet4 = array_keys($data4);
                return ['data' => $data4, 'header' => $header4, 'sheet' => $sheet4, 'filename' => date('Ymd') . '小程序德邦红酒订单'];
            }
            if ($params['Logistics']['id'] == Logistics::ZTCK) {
                if (empty($data5)) {
                    throw new \Exception("无整条出库订单");
                }
                $sheet5 = array_keys($data5);
                return ['data' => $data5, 'header' => $header5, 'sheet' => $sheet5, 'filename' => date('Ymd') . '小程序整条出库订单'];
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new SmartException($e->getMessage());
        }
    }


    //更新订单物流信息
    public static function updateOrderLogistics($data, $logistics)
    {   
        //echo "<pre>";print_r($data);exit;
        switch ($logistics) {
            case Logistics::SF_COLD://顺丰冷运
                foreach ($data as $value) {
                    $v = current($value);
                    $orderId = $v['2'];//主订单号
                    $logisticsCode = $v['3'];//物流单号
                    $uniqueId = $v['6'];//SKU编码
                    //屏蔽空行
                    if (!$orderId || !$logisticsCode || !$uniqueId) {
                        continue;
                    }
                    //可能存在XXX-X的订单号
                    $tmp = explode('-', $orderId);
                    $orderId = $tmp[0];
                    //怕业务人员选错物流渠道  稍微做下数字验证
                    if (!is_numeric($logisticsCode) || !is_numeric($orderId)) {
                        continue;
                        //throw new SmartException("物流格式有误，请重新检查！");
                    }
                    //echo $orderId."---".$uniqueId."<br>";
                    if (!self::fillLogistics($orderId, $logisticsCode, Logistics::SF_COLD)) {
                        continue;
                    }
                }
                break;
            case Logistics::DB_EXPRESS://德邦快递
                foreach ($data as $value) {
                    $v = current($value);
                    $orderId = $v['0'];//主订单号
                    $logisticsCode = $v['4'];//物流单号
                    $uniqueId = $v['5'];//SKU编码
                    //屏蔽空行
                    if (!$orderId || !$logisticsCode || !$uniqueId) {
                        continue;
                    }
                    //可能存在XXX-X的订单号
                    $tmp = explode('-', $orderId);
                    $orderId = $tmp[0];
                    //业务人员选错物流渠道  因此稍微做下数字验证
                    if (!is_numeric($logisticsCode) || !is_numeric($orderId)) {
                        continue;
                    }
                    if (!self::fillLogistics($orderId, $logisticsCode, Logistics::DB_EXPRESS)) {
                        continue;
                    }
                }
                break;
            case Logistics::DB_WINE://德邦红酒
                foreach ($data as $value) {
                    $v = current($value);
                    $orderId = $v['3'];//主订单号
                    $logisticsCode = $v['11'];//物流单号
                    $uniqueId = $v['8'];//SKU编码
                    //可能存在XXX-X的订单号
                    $tmp = explode('-', $orderId);
                    $orderId = $tmp[0];
                    //业务人员选错物流渠道  因为稍微做下数字验证
                    if (!is_numeric($logisticsCode) || !is_numeric($orderId)) {
                        continue;
                    }
                    if (!self::fillLogistics($orderId, $logisticsCode, Logistics::DB_WINE)) {
                        continue;
                    }
                }
                break;
            case Logistics::ZTCK://整条出库
                foreach ($data as $value) {
                    $v = current($value);
                    $orderId = $v['6'];//主订单号
                    $logisticsCode = $v['15'];//物流单号
                    $uniqueId = $v['16'];//SKU编码
                    //可能存在XXX-X的订单号
                    $tmp = explode('-', $orderId);
                    $orderId = $tmp[0];
                    //业务人员选错物流渠道  因此稍微做下数字验证
                    if (!is_numeric($logisticsCode) || !is_numeric($orderId)) {
                        continue;
                    }
                    if (!self::fillLogistics($orderId, $logisticsCode, Logistics::ZTCK)) {
                        continue;
                    }
                }
                break;
        }
    }

    //回填物流
    public static function fillLogistics($orderId, $logisticsCode, $logisticsId)
    {
        $historys = MergeOrderHistory::find()->where("orderId={$orderId} and logisticsId={$logisticsId}")->all();
        foreach ($historys as $history) {
            $buyingRecord = OrderBuyingRecord::find()->where("id={$history->buyingRecordId}")->one();
            if (empty($buyingRecord)) {
                return false;
            }
            $buyingRecord->logisticsCode = trim($logisticsCode);
            $buyingRecord->save(false);
            $history->is_completed = 1;
            $history->save(false);
        }
        return true;
    }

    //记录合并订单的日志
    public static function mergeOrderHistory($order, $logisticsId)
    {
        $orderId = $order['orderId'];
        $buyingRecordId = $order['id'];
        $history = MergeOrderHistory::find()->where("orderId={$orderId} and buyingRecordId={$buyingRecordId} and logisticsId={$logisticsId}")->one();
        if (!$history) {
            $model = new MergeOrderHistory();
            $model->orderId = $orderId;
            $model->buyingRecordId = $buyingRecordId;
            $model->logisticsId = $logisticsId;
            $model->createTime = time();
            $model->save(false);
        }
    }

}