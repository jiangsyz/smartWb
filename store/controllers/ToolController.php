<?php
namespace store\controllers;

use app\models\PlatformOrder;
use Yii;
use yii\base\SmartException;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use store\models\logistics\Logistics;
use common\models\Excel;
use store\controllers\BaseController;
use store\models\Goods;
use yii\data\ActiveDataProvider;

class ToolController extends BaseController
{
    public function actionIndex()
    {
        $string = "T%E9%AA%A8%E7%89%9B%E6%8E%92%20%E4%BB%8A%E5%A4%A9%E5%8F%91%E8%B4%A7%EF%BC%81%EF%BC%81%EF%BC%81#%25@#%EF%BF%A5adf";
        //$string = "T%e9%aa%a8%e7%89%9b%e6%8e%92kaldf";
        $string1 = rawurldecode($string);
        $string3 = rawurlencode($string1);
        echo $string."<br>";
        echo $string3;exit;
    }

    //根据有赞导出的Excel过滤掉退款的
    public function actionFilterExcel()
    {
    	try {
            ini_set('max_execution_time', '0');
            ini_set('memory_limit', '256M');
            ini_set('upload_max_filesize', '256M');
    		if (!empty($_FILES)) {
    			$error = $_FILES['file']['error'];
                $temp_name = $_FILES['file']['tmp_name'];
                $file_name = $_FILES['file']['name'];
                $data = Excel::read($file_name, $temp_name, $error);
                if (!$data['error']) {
                    throw new SmartException($data['message']);
                }
                Yii::$app->wbdb->createCommand("delete from platform_order where origin=1")->execute();
                $filter_cnt = $insert_cnt = 0;
                //echo "<pre>";print_r($data['data']);exit;
                //插入到数据库
                foreach ($data['data'] as $val) {
                    $v = $val[0];
                    if ($v['3'] != '等待商家发货' && $v['3'] != '退款关闭' && $v['3'] != '该商品已部分退款') {
                        $filter_cnt++;
                        continue;
                    }
                    $order_id = trim($v['0']); //订单号
                    $spu_title = $v['34']; //商品名称
                    $sku_title = trim($v['14']); //SKU规格
                    $sku_code = trim($v['15']); //SKU编码
                    $store_code = trim($v['16']); //商家编码
                    $consignee = addslashes($v['18']); //收件人
                    $address = $v['22']; //收件地址
                    $phone = $v['29']; //收件人手机
                    $quantity = $v['37']; //宝贝数量
                    $buyer_memo = $v['41']; //订单留言
                    $memo = $v['45']; //备注
                    $pay_time = !empty($v['31'])? strtotime(gmdate('Y-m-d H:i:s',\PHPExcel_Shared_Date::ExcelToPHP($v['31']))):0;

                    $hash = md5($consignee.$address.$phone);
                    //SKU编码和商家编码都空时，跳过
                    if (empty($sku_code) && empty($store_code)) {
                        continue;
                    }
                    $sql = "insert into platform_order(order_id, spu_title, sku_title ,sku_code, store_code, consignee, address, phone, hash, quantity, buyer_memo, memo, origin, pay_time)
                        values('{$order_id}', '{$spu_title}', '{$sku_title}', '{$sku_code}', '{$store_code}', '{$consignee}', '{$address}', '{$phone}', '{$hash}', {$quantity}, '{$buyer_memo}', '{$memo}', 1, {$pay_time})";
                    //echo $sql;exit;
                    $rs = Yii::$app->wbdb->createCommand($sql)->execute();
                    if ($rs) {
                        $insert_cnt++;
                    }
                }
                echo '<script>alert("导入'.$insert_cnt.'条记录，过滤'.$filter_cnt.'条记录");</script>';exit;
                echo "<pre>";print_r($data);
    		} else {
                //$this->layout = false;
                $logistics = Logistics::find()->all();
    			return $this->render('filter-excel', [
                    'logistics' => new Logistics(),
					'logisticsTree' => $logistics
				]);
    		}

    	} catch (Exception $e) {
    		$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
    	}
    }

    //导出拆单合并的订单
    public function actionExport()
    {
        $logisticsId = Yii::$app->request->post('Logistics')['id'];
        $goods = Goods::find()->all();
       // echo "<pre>";print_r($goods);exit;
        $codeStr = '';
        foreach ($goods as $val) {
            if ($val->logistics_id == $logisticsId) {
                $codeStr .= '"'.$val->code.'"'.',';
            }
        }
        $codeStr = substr($codeStr, 0, strlen($codeStr)-1);
        //echo $codeStr;exit;
        if($logisticsId == Logistics::ZTCK){
            $orderBy = 'sku_code';
        }elseif($logisticsId == Logistics::SF_COLD){
            $orderBy = 'pay_time';
        }else{
            $orderBy = 'order_id';
        }
        $orders = Yii::$app->wbdb->createCommand("select * from platform_order where (sku_code in ({$codeStr}) or store_code in ({$codeStr})) and origin=1 order by {$orderBy} asc")->queryAll();
        if (empty($orders)) {
            Yii::$app->session->setFlash('danger', '没有相关订单');
            return $this->redirect(Yii::$app->request->referrer);
        }

        //将收件人收件地址收人手机一样的订单放到一起
        foreach ($orders as $order) {
            $order_key = in_array($logisticsId,Logistics::$not_combine_logistics) ? $order['sku_code'] : $order['hash'];
            $merge_orders[$order_key][] = $order;
        }
        //整条出库不用合单
        if (in_array($logisticsId,Logistics::$not_combine_logistics)) {
            $final_orders = $merge_orders;
        }else{
            //echo "<pre>";print_r($merge_orders);exit;
            //遍历合并后的订单  看是否有相同SKU可以合并 然后将后面的订单号都改成第一个订单号
            $rtn = [];
            $final_orders = [];
            foreach ($merge_orders as $key => $orders) {
                //整理出各SKU总数
                foreach ($orders as $k => $order) {
                    $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                    if (isset($rtn[$sku_code])) {
                        $rtn[$sku_code] += $order['quantity'];
                    } else {
                        $rtn[$sku_code] = $order['quantity'];
                    }
                }
                foreach ($orders as $k => $order) {
                    if ($k == 0) {
                        $first_order_id = $order['order_id'];
                    }
                    $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                    //数量是否已经赋值过
                    if (array_key_exists($sku_code, $rtn)) {
                        $order['quantity'] = $rtn[$sku_code];
                        $order['order_id'] = $first_order_id;
                        $final_orders[$key][] = $order;
                        unset($rtn[$sku_code]);
                    }
                }
            }
        }
        //echo "<pre>";print_r($merge_orders);exit;
        //echo "<pre>";print_r($final_orders);exit;
        //导出物流Excel的固定字段
        $basicData = Yii::$app->params['SfColdExcelBasicData'];
        $goods = Goods::find()->all();
        $units = [];
        foreach ($goods as $val) {
            if ($logisticsId == 4) {
                $units[$val->code] = $val->unit;
            }
            $codeBrand[$val->code] = $val->name;
        }
        switch ($logisticsId) {
            case '1'://顺丰冷运
                $header = Yii::$app->params['SfColdExcelHeader'];
                $file_name = '有赞'.date('Ymd').'顺丰冷运订单';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                         $data['顺丰冷运'][] = [$basicData['codeNo'], $basicData['codeNo'], $order['order_id'],
                            $basicData['orderType'], $basicData['typeName'], $basicData['houseCode'], $basicData['payType'],
                            $basicData['receiverPay'], $basicData['insteadCash'], $basicData['insured'], $basicData['statement'], $basicData['packingFee'],
                            $basicData['individuationService'], $basicData['carrierCode'],
                            $basicData['carrierType'], $order['consignee'], $order['consignee'],
                            $order['phone'], $order['phone'], $order['address'], $basicData['consignor'],
                            $basicData['consignor'], $basicData['tel'], $basicData['tel'], $basicData['address'],
                            $sku_code, $brand, $order['quantity'],
                            '', '', '', '', $basicData['receipt'], '', ''
                        ];
                    }
                }
                break;
            case '2'://顺丰快递
                $header = Yii::$app->params['SfColdExcelHeader'];
                $file_name = '有赞'.date('Ymd').'顺丰快递';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                         $data['顺丰冷运'][] = [$basicData['codeNo'], $basicData['codeNo'], $order['order_id'],
                            $basicData['orderType'], $basicData['typeName'], $basicData['houseCode'], $basicData['payType'],
                            $basicData['receiverPay'], $basicData['insteadCash'], $basicData['insured'], $basicData['statement'], $basicData['packingFee'],
                            $basicData['individuationService'], $basicData['carrierCode'],
                            $basicData['carrierType'], $order['consignee'], $order['consignee'],
                            $order['phone'], $order['phone'], $order['address'], $basicData['consignor'],
                            $basicData['consignor'], $basicData['tel'], $basicData['tel'], $basicData['address'],
                            $sku_code, $brand, $order['quantity'],
                            '', '', '', '', $basicData['receipt'], '', ''
                        ];
                    }
                }
                break;
            case '3'://德邦快递
                $header = Yii::$app->params['DbExpressExcelHeader'];
                $file_name = '有赞'.date('Ymd').'德邦快递';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        $data['德邦快递'][] = [$order['order_id'], $order['consignee'], $order['phone'],
                            $order['address'], '', $sku_code, $brand,
                            '', $order['quantity']
                        ];
                    }
                }
                break;
            case '4'://德邦红酒
                $header = Yii::$app->params['DbWineExcelHeader'];
                $file_name = '有赞'.date('Ymd').'德邦红酒';
                $tmp = [];
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $sku_code = trim($sku_code);
                        $brand = @$codeBrand[$sku_code];
                        $unit = !empty($units[$sku_code]) ? $units[$sku_code] : '';
                        $tmp[] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            $order['order_id'], $order['consignee'], $order['address'],
                            $order['phone'], $brand, $sku_code,
                            $order['quantity'].$unit, '', '', '', '', '', $brand
                        ];
                    }
                }
                $data['红酒统计'] = Goods::array_sort($tmp, 8, SORT_ASC, SORT_STRING);
                break;
            case '5'://整条出库
                $header = Yii::$app->params['ZsckExcelHeader'];
                $file_name = '有赞'.date('Ymd').'整条出库';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        $data['整条出库'][] = ['', date('Y-m-d', time()), '', $brand,
                            '', '', $sku_code, $order['order_id'], $order['sku_title'],
                            $order['consignee'], $order['phone'], $order['address'], $order['quantity'], $order['buyer_memo'], $order['memo'], '', ''
                        ];
                    }
                }
                break;
            case '6'://不粘锅
                $header = Yii::$app->params['DbWineExcelHeader'];
                $file_name = '有赞'.date('Ymd').'不粘锅';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        //红酒名称太长 sheet不兼容 所以处理一下
                        $data[$brand][] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            $order['order_id'], $order['consignee'], $order['address'],
                            $order['phone'], $brand, $sku_code,
                            $order['quantity'], '', '', '', '', '', $brand
                        ];
                    }
                }
                break;
        }
        $sheet = array_keys($data);
        //echo "<pre>";print_r($data);exit;
        Excel::exportData($data, $header, $sheet, $file_name);
    }



    //过滤标点符号
    public static function actionFilterMark($str){
        $char = "。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）";

        $pattern = array(
            "/[[:punct:]]/i", //英文标点符号
            '/['.$char.']/u', //中文标点符号
            '/[ ]{2,}/'
        );
        $str = preg_replace($pattern, ' ', $str);
        return $str;
    }

    //商品编码列表
    public function actionGoodsList()
    {
        try {
            $goods = new Goods();
            $query = Goods::find();
            $where = " 1=1 ";
            if ($logistics_id = Yii::$app->request->get('logistics_id')) {
                $where .= " and logistics_id={$logistics_id}";
            }
            if ($name = Yii::$app->request->get('name')) {
                $where .= " and name like '%{$name}%'";
            }
            if ($code = Yii::$app->request->get('code')) {
                $where .= " and code='{$code}'";
            }
            //echo $where;exit;
            $dataProvider = new ActiveDataProvider([
                'query' => $query->where($where),
                'pagination' => [
                    'pageSize' => 100,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_ASC,
                    ]
                ],
            ]);
            return $this->render('list', [
                'dataProvider' => $dataProvider,
                'logisticsTree' => Logistics::getLogisticsMap()

            ]);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //添加商品编码
    public function actionCreateGoods()
    {
        try {
            $model = new Goods();
            if (Yii::$app->request->isPost) {
                if (!$model->load(Yii::$app->request->post())) {
                    throw new SmartException("load数据失败");
                }
                if (!$model->save()) {
                    throw new SmartException("保存失败");
                }
                return $this->redirect(['tool/goods-list']);
            } else {
                $logisticsTree = Logistics::getLogisticsMap();
                return $this->render("create", [
                    'model' => $model,
                    'logisticsTree' => $logisticsTree
                ]);
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //更新商品编码
    public function actionUpdateGoods()
    {
        try {
            $id = Yii::$app->request->get('id');
            $model = Goods::findOne($id);
            if (Yii::$app->request->isPost) {
                if (!$model->load(Yii::$app->request->post())) {
                    throw new SmartException("load数据失败");
                }
                if (!$model->save()) {
                    throw new SmartException("保存失败");
                }
                return $this->redirect(['tool/goods-list']);
            } else {
                $logisticsTree = Logistics::getLogisticsMap();
                return $this->render("create", [
                    'model' => $model,
                    'logisticsTree' => $logisticsTree
                ]);
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //删除商品编码
    public function actionDeleteGoods()
    {
        try {
            $ids = Yii::$app->request->post('ids');
            if (Yii::$app->request->isPost) {
                //echo "<pre>";print_r($ids);exit;
                $ids = implode(',', $ids);
                $rs = Goods::deleteAll("id in ({$ids})");
                return $this->redirect(['tool/goods-list']);
            } else {
                $logisticsTree = Logistics::getLogisticsMap();
                return $this->render("create", [
                //    'model' => $model,
                    'logisticsTree' => $logisticsTree
                ]);
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionTmpGoods()
    {
        $tmpGoods = Yii::$app->wbdb->createCommand("select * from tmp_goods")->queryAll();
        //echo "<pre>";print_r($tmpGoods);exit;
        foreach ($tmpGoods as $val) {
            $goods = Goods::find()->where("code='{$val['code']}'")->one();
            if (!$goods) {
                Yii::$app->wbdb->createCommand("insert into goods(name,code,logistics_id) values('{$val['name']}','{$val['code']}',{$val['logistics_id']})")->execute();
            }
        }
    }

    public function actionCancelVipForLulu()
    {
        Yii::$app->db->createCommand("delete from member_lv where memberId=2")->execute();
    }

    public function actionAddVipForLulu()
    {
        Yii::$app->db->createCommand("insert into member_lv(memberId,lv,start,end,orderId) values(2,1,1527609600,1559145600,0)")->execute();
    }

    public function actionFilterExcelV2()
    {
        try {
            ini_set('max_execution_time', '0');
            ini_set("memory_limit","-1");
            ini_set('upload_max_filesize', '512M');
            ini_set('post_max_size', '2047M'); ;
            $logistics_data = Logistics::getLogisticsMap();
            if (!empty($_FILES)) {
                $origin_type = 3;
                $error = $_FILES['file']['error'];
                $temp_name = $_FILES['file']['tmp_name'];
                $file_name = $_FILES['file']['name'];
                $data = Excel::read($file_name, $temp_name, $error);
                if ($data['error'] !== true) {
                    throw new \Exception($data['message']);
                }
                Yii::$app->wbdb->createCommand("delete from platform_order where origin={$origin_type}")->execute();
                $filter_cnt = $insert_cnt = $quantity_count = 0;
                //插入到数据库
                $error_order_id = [];
                foreach ($data['data'] as $val) {
                    $v = $val[0];
                    $logistics_id = array_search($v['6'],$logistics_data);
                    if( $logistics_id === false ){
                        # 不在运输方式中,跳过
                        $filter_cnt ++ ;
                        $error_order_id[] = $v['0'];
                        continue;
                    }
                    $order_id = trim($v['0']); //订单号
                    $spu_title = $v['19']; //商品名称
                    $sku_title = $v['20']; //SKU规格
                    $sku_code = trim($v['28']); //SKU编码
                    $store_code = ''; //商家编码
                    $consignee = addslashes($v['2']); //收件人
                    $address = addslashes($v['12']); //收件地址
                    $phone = addslashes($v['13']); //收件人手机
                    $quantity = $v['22']; //宝贝数量
                    $buyer_memo = addslashes($v['15']); //订单留言
                    $memo = addslashes($v['14']); //备注

                    $hash = md5($consignee.$address.$phone);
                    //SKU编码和商家编码都空时，跳过
                    if (empty($sku_code)) {
                        $error_order_id[] = $v['0'];
                        $filter_cnt ++ ;
                        continue;
                    }
                    $sql = "insert into platform_order(order_id, spu_title, sku_title ,sku_code, store_code, consignee, address, phone, hash, quantity, buyer_memo, memo, logistics_id,origin)
                        values('{$order_id}', '{$spu_title}', '{$sku_title}', '{$sku_code}', '{$store_code}', '{$consignee}', '{$address}', '{$phone}', '{$hash}', {$quantity}, '{$buyer_memo}', '{$memo}', '{$logistics_id}', {$origin_type})";
                    //echo $sql;exit;
                    $rs = Yii::$app->wbdb->createCommand($sql)->execute();
                    if ($rs) {
                        $insert_cnt++;
                        $quantity_count += $quantity;
                    }
                }
                $msg = "导入{$insert_cnt}条记录，产品总数{$quantity_count},过滤{$filter_cnt}条记录";
                Yii::$app->session->setFlash('success', $msg);
                if($error_order_id){
                    $error_msg = '</br>未导入订单号:</br>';
                    $error_msg .= implode('</br>',$error_order_id);
                    Yii::$app->session->setFlash('error', $error_msg);
                }
                $this->response(1,array('error'=>0,'msg'=>'ok'));
            } else {
                //$this->layout = false; 
                return $this->render('filter-excel-v2', [
                    'logistics' => new Logistics(),
                    'logistics_data' => $logistics_data
                ]);
            }

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            $this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
        }
    }

    //导出拆单合并的订单
    public function actionExportV2()
    {
        try{
            ini_set('max_execution_time', '0');
            ini_set("memory_limit","-1");  
            $logisticsId = Yii::$app->request->post('Logistics')['id'];
            $logistics_data = Logistics::getLogisticsMap();
            if(empty($logistics_data[$logisticsId])){
                throw new \Exception('运输方式出错');
            }

            $logistics_name = $logistics_data[$logisticsId];
            $file_name = date('Ymd').$logistics_name.'订单';

            $orders = Yii::$app->wbdb->createCommand("select * from platform_order where logistics_id = {$logisticsId} and origin=3 order by order_id asc")->queryAll();
            if (empty($orders)) {
                throw new \Exception('没有相关订单');
            }
            //导出物流Excel的固定字段
            $basicData = Yii::$app->params['SfColdExcelBasicData'];
            $data = $header = $goods_costs = [];
            switch ($logisticsId) {
                case Logistics::SF_COLD://顺丰冷运
                    $header = Yii::$app->params['SfColdExcelHeader'];
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $goods_data = $this->getGoodByCode($sku_code);
                        $name = ArrayHelper::getValue($goods_data, 'name', $order['spu_title']);
                        $support_value = $this->getSFsupportValue($order['order_id'],Logistics::SF_COLD);
                        $insured = !empty($support_value) ? 'Y' : 'N';
                        $data['顺丰冷运'][] = [$basicData['codeNo'], $basicData['codeNo'], $order['order_id'],
                            $basicData['orderType'], $basicData['typeName'], $basicData['houseCode'], $basicData['payType'],
                            $basicData['receiverPay'], $basicData['insteadCash'], $insured, $support_value, $basicData['packingFee'],
                            $basicData['individuationService'], $basicData['carrierCode'],
                            $basicData['carrierType'], $order['consignee'], $order['consignee'],
                            $order['phone'], $order['phone'], $order['address'], $basicData['consignor'],
                            $basicData['consignor'], $basicData['tel'], $basicData['tel'], $basicData['address'],
                            $sku_code, $name, $order['quantity'],
                            '', '', '', '', $basicData['receipt'], '', ''
                        ];
                    }
                    break;
                case Logistics::SF_EXPRESS://顺丰速运
                    $header = Yii::$app->params['SfColdExcelHeader'];
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $goods_data = $this->getGoodByCode($sku_code);
                        $name = ArrayHelper::getValue($goods_data, 'name', $order['spu_title']);
                        $support_value = $this->getSFsupportValue($order['order_id'],Logistics::SF_EXPRESS);
                        $insured = !empty($support_value) ? 'Y' : 'N';
                        $data['顺丰快递'][] = [$basicData['codeNo'], $basicData['codeNo'], $order['order_id'],
                            $basicData['orderType'], $basicData['typeName'], $basicData['houseCode'], $basicData['payType'],
                            $basicData['receiverPay'], $basicData['insteadCash'], $insured, $support_value, $basicData['packingFee'],
                            $basicData['individuationService'], $basicData['carrierCode'],
                            $basicData['carrierType'], $order['consignee'], $order['consignee'],
                            $order['phone'], $order['phone'], $order['address'], $basicData['consignor'],
                            $basicData['consignor'], $basicData['tel'], $basicData['tel'], $basicData['address'],
                            $sku_code, $name, $order['quantity'],
                            '', '', '', '', $basicData['receipt'], '', ''
                        ];
                    }
                    break;
                case Logistics::DB_EXPRESS://德邦调料
                    $header = Yii::$app->params['DbExpressExcelHeader'];
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $goods_data = $this->getGoodByCode($sku_code);
                        $name = ArrayHelper::getValue($goods_data, 'name', $order['spu_title']);
                        $data['德邦快递'][] = [$order['order_id'], $order['consignee'], $order['phone'],
                            $order['address'], '', $sku_code, $name,
                            '', $order['quantity']
                        ];
                    }
                    break;
                case Logistics::DB_WINE://德邦红酒
                    $header = Yii::$app->params['DbWineExcelHeader'];
                    $tmp = [];
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $goods_data = $this->getGoodByCode($sku_code);
                        $name = ArrayHelper::getValue($goods_data, 'name', $order['spu_title']);
                        $unit = ArrayHelper::getValue($goods_data, 'unit', '');
                      //  $support_value = $this->getDBsupportValue($order['order_id'],Logistics::DB_WINE);
                        $tmp[] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            $order['order_id'], $order['consignee'], $order['address'],
                            $order['phone'], $name, $sku_code,
                            $order['quantity'] . $unit, '', '', '', '', '', $name, $order['buyer_memo'] .' '. $order['memo'],
                        ];
                    }
                    $data['红酒统计'] = Goods::array_sort($tmp,8,SORT_ASC,SORT_STRING);
                    break;
                case Logistics::ZTCK://整条出库
                    $header = Yii::$app->params['ZsckExcelHeader'];
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $goods_data = $this->getGoodByCode($sku_code);
                        $name = ArrayHelper::getValue($goods_data, 'name', $order['spu_title']);
                        $data['整条出库'][] = ['', date('Y-m-d', time()), '', $name,
                            '', '', $sku_code, $order['order_id'], $order['sku_title'],
                            $order['consignee'], $order['phone'], $order['address'], $order['quantity'], $order['buyer_memo'], $order['memo'], '', ''
                        ];
                    }
                    break;
                case Logistics::OFFICE://不粘锅
                    $header = Yii::$app->params['DbWineExcelHeader'];
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $goods_data = $this->getGoodByCode($sku_code);
                        $name = ArrayHelper::getValue($goods_data, 'name', $order['spu_title']);
                        $title = mb_substr(str_replace(array('*', ':', '/', '\\', '?', '[', ']'), '', $name),0,30,'utf-8');
                        $data[$title][] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            $order['order_id'], $order['consignee'], $order['address'],
                            $order['phone'], $order['spu_title'], $sku_code,
                            $order['quantity'], '', '', '', '', '', $name
                        ];
                    }
                    break;
            }
            $sheet = array_keys($data);
            Excel::exportData($data, $header, $sheet, $file_name);

        }catch (\Exception $e){
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }


    public function actionSms()
    {
        try {
            ini_set('max_execution_time', '0');
            ini_set("memory_limit","-1");
            ini_set('upload_max_filesize', '512M');
            ini_set('post_max_size', '2047M');
            if (!empty($_FILES)) {
                $error = $_FILES['file']['error'];
                $temp_name = $_FILES['file']['tmp_name'];
                $file_name = $_FILES['file']['name'];
                $data = Excel::read($file_name, $temp_name, $error);
                if (!$data['error']) {
                    throw new \Exception($data['message']);
                }
                $error_order_msg = $tmp_phones = [];
                $insert_cnt = 0;
                if(!empty($data['data'])){
                    foreach ($data['data'] as $val) {
                        $phone = ArrayHelper::getValue($val,'0.0', null);
                        if (empty($phone) || in_array($phone, $tmp_phones)) {
                            continue;
                        }
                        $tmp_phones[] = $phone;
                        $res = $this->sendSms($phone);
                        if(!empty($res['error'])){
                            $error_order_msg[] = "{$phone} {$res['msg']}{$res['error']}";
                        }else{
                            $insert_cnt++;
                        }
                    }
                }
                $msg = "成功发送{$insert_cnt}条";
                Yii::$app->session->setFlash('success', $msg);
                if($error_order_msg){
                    $error_msg = '</br>未发送成功手机号:</br>';
                    $error_msg .= implode('</br>',$error_order_msg);
                    Yii::$app->session->setFlash('error', $error_msg);
                }
                $this->response(1,array('error'=>0,'msg'=>'ok'));
            } else {
                return $this->render('sms');
            }

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            $this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
        }
    }

    /**
     * 获取顺丰声明价值
     *
     * @param $order_id
     * @param $logistics_id
     * @return mixed
     */
    public function getSFSupportValue($order_id, $logistics_id)
    {
        static $order_support_value = [];
        if (!isset($order_support_value[$order_id])) {
            $count = 0;
            $orders = PlatformOrder::find()->where(['order_id' => $order_id])->andWhere(['logistics_id' => $logistics_id])->asArray()->all();
            foreach ($orders as $order) {
                $goods = $this->getGoodByCode($order['sku_code']);
                $cost = ArrayHelper::getValue($goods, 'cost', 0);
                $count += $cost * $order['quantity'];
            }
            if ($count <= 500) {
                $order_support_value[$order_id] = 500;
            } else {
                $order_support_value[$order_id] = (int)round($count, -3);
            }
        }
        return $order_support_value[$order_id];
    }

    /**
     * 获取德邦保价
     *
     * @param $order_id
     * @param $logisticsId
     * @return mixed
     */
    public function getDBSupportValue($order_id, $logisticsId)
    {
        static $order_support_value = [];
        if (!isset($order_support_value[$order_id])) {
            $count = 0;
            $orders = PlatformOrder::find()->where(['order_id' => $order_id])->andWhere(['logistics_id' => $logisticsId])->asArray()->all();
            foreach ($orders as $order) {
                $goods = $this->getGoodByCode($order['sku_code']);
                $cost = ArrayHelper::getValue($goods, 'cost', 0);
                $count += $cost * $order['quantity'];
            }
            if ($count <= 500) {
                $order_support_value[$order_id] = 1;
            } elseif ($count <= 1000) {
                $order_support_value[$order_id] = 2;
            } else {
                $order_support_value[$order_id] = $count * 0.005;
            }
        }
        return $order_support_value[$order_id];
    }


    public function sendSms($phone = null, $message = '')
    {
        if(empty($message)){
            $message = '尊敬的客户:您的订单已在今日发货订单里，物流信息预计晚上6前上传更新。请耐心等待. 牛肉哥竭诚为您服务 021-61124655。';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:key-7c8db73264eb6b90ccb5b03c6b6ae7db');

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $phone, 'message' => "{$message}【正善牛肉】"));

        $res = curl_exec($ch);
        curl_close($ch);
//$res  = curl_error( $ch );
        return json_decode($res, true);
    }

    public function getGoodByCode($code)
    {
        static $goods = [];
        if (!isset($goods[$code])) {
            $goods[$code] = Goods::find()->where(['code'=>$code])->asArray()->one();
        }
        return $goods[$code];
    }

    public function actionLink()
    {
        echo ("<script>window.open('http://server1.zsbutcher.cn/smartTj/backend/web/index.php');</script>");
        //return $this->redirect(Yii::$app->request->referrer);
        //return $this->redirect('http://server1.zsbutcher.cn/smartTj/backend/web/index.php');
    }
}


