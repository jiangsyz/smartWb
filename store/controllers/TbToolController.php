<?php
namespace store\controllers;

use Yii;
use yii\web\Controller;
use store\models\logistics\Logistics;
use common\models\Excel;
use store\controllers\BaseController;
use store\models\Goods;
use yii\data\ActiveDataProvider;
use store\models\order\Order;
use yii\base\SmartException;

class TbToolController extends BaseController
{
    public $enableCsrfValidation=false;
    //导入淘宝的子母订单
    public function actionImport()
    { 
    	try {
            ini_set('max_execution_time', '0');
            //ini_set('memory_limit', '256M');
            ini_set("memory_limit","-1");
            ini_set('upload_max_filesize', '256M');
            ini_set('post_max_size', '2047M');
    		if (!empty($_FILES)) {
                $otype = Yii::$app->request->post('Order')['otype'];
    			$error = $_FILES['file']['error'];
                $temp_name = $_FILES['file']['tmp_name'];
                $file_name = $_FILES['file']['name'];
                $data = Excel::read($file_name, $temp_name, $error);
                //echo "<pre>";print_r($data);exit;
                if (isset($data['message'])) {
                    //$backtrace=debug_backtrace(false,false);
                    //echo "<pre>";print_r($backtrace);exit;
                    throw new SmartException($data['message']);
                }
                $filter_cnt = $insert_cnt = 0;
                //子订单
                if ($otype == 1) {
                    //删除原先的淘宝订单
                    Yii::$app->wbdb->createCommand("delete from platform_order where origin=2")->execute();
                    //echo "<pre>";print_r($data['data']);exit;
                    //插入到数据库
                    foreach ($data['data'] as $val) {
                        if ($val['8'] != '买家已付款，等待卖家发货') {
                            $filter_cnt++;
                            continue;
                        }
                        $order_id = trim($val['0']); //订单号
                        $spu_title = $val['1']; //商品名称
                        $single_price = trim($val['2']); //SKU单价
                        $sku_title = trim($val['5']); //SKU规格
                        $sku_code = trim($val['4']); //SKU编码
                        $order_status = $val['8']; //订单状态
                        $store_code = trim($val['9']); //商家编码
                        //$consignee = $val['18']; //收件人
                        //$address = $val['22']; //收件地址
                        //$phone = $val['29']; //收件人手机
                        $quantity = $val['3']; //宝贝数量
                        //$buyer_memo = $val['41']; //订单留言
                        //$memo = $val['45']; //备注

                        //$hash = md5($consignee.$address.$phone);
                        //SKU编码和商家编码都空时，跳过
                        if (empty($sku_code) && empty($store_code)) {
                            continue;
                        }
                        $sql = "insert into platform_order(order_id, spu_title, sku_title ,sku_code, store_code, quantity, single_price, order_status, origin) 
                            values('{$order_id}', '{$spu_title}', '{$sku_title}', '{$sku_code}', '{$store_code}', {$quantity}, {$single_price}, '{$order_status}', 2)";
                        //echo $sql;exit;
                        $rs = Yii::$app->wbdb->createCommand($sql)->execute();
                        if ($rs) {
                            $insert_cnt++;
                        }
                    }
                //主订单
                } else {
                    foreach ($data['data'] as $val) {
                        //淘宝导入订单孙腾飞人工过滤退款订单  不再判断订单状态  直接导入
                        /*if ($val['10'] != '买家已付款，等待卖家发货') {
                            $filter_cnt++;
                            continue;
                        }*/
                        //$val = $val[0];
                        //echo "<pre>";print_r($val);exit;
                        $order_id = str_replace("'", '', $val['0']); //订单号
                        $goods_price = trim($val['5']); //应付货款
                        $post_fee = trim($val['6']); //应付邮费
                        $real_pay = trim($val['10']); //实付金额
                        $consignee = addslashes($val['14']); //收件人
                        $address = $val['41']!='null' ? addslashes($val['41']) : addslashes($val['15']);
                        $phone = trim(str_replace("'", '', $val['18'])); //收件人手机
                        $order_created = $val['19']; //订单创建时间
                        $order_payed = $val['20']; //订单支付时间
                        $buyer_memo = addslashes($val['13']); //订单留言
                        $hash = trim(md5($consignee.$address.$phone));
                        $pay_time = !empty($val['20']) ? strtotime($val['20']) : 0;
                        $sql = "update platform_order set consignee='{$consignee}', address='{$address}', phone='{$phone}', hash='{$hash}', buyer_memo='{$buyer_memo}', goods_price={$goods_price}, post_fee={$post_fee}, real_pay={$real_pay}, order_created='{$order_created}', order_payed='{$order_payed}',pay_time='{$pay_time}' where order_id={$order_id} and origin=2";
                        $rs = Yii::$app->wbdb->createCommand($sql)->execute();
                        if ($rs) {
                            $insert_cnt++;
                        }
                    }
                }
                echo '<script>alert("更新'.$insert_cnt.'条记录，过滤'.$filter_cnt.'条记录");</script>';exit;
                echo "<pre>";print_r($data);
    		} else {
                //$this->layout = false;
                $logistics = Logistics::find()->all();
    			return $this->render('import', [
                    'logistics' => new Logistics(),
                    'order' =>  new Order(),
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
        ini_set('max_execution_time', '0');
        //ini_set('memory_limit', '128M');
        ini_set("memory_limit","-1");
        ini_set('upload_max_filesize', '128M');
        ini_set('post_max_size', '2047M');
        $logisticsId = Yii::$app->request->post('Logistics')['id'];
        //老李的淘宝订单需求单独一个方法处理
        if ($logisticsId == 7) {
            self::actionTbOrderExport();
            exit;
        }
        $goods = Goods::find()->all();
       // echo "<pre>";print_r($goods);exit;
        $codeStr = '';
        foreach ($goods as $val) {
            if ($val->logistics_id == $logisticsId || $logisticsId == 7) {
                $codeStr .= '"'.$val->code.'"'.',';
            }
        }
        $codeStr = substr($codeStr, 0, strlen($codeStr)-1);
        //echo $codeStr;exit;
        $orderBy = ($logisticsId == Logistics::SF_COLD) ? 'pay_time' : 'order_id';
        $sql = "select * from platform_order where (sku_code in ({$codeStr}) or store_code in ({$codeStr})) and origin=2 order by {$orderBy} asc";
        //echo $sql;exit;
        $orders = Yii::$app->wbdb->createCommand($sql)->queryAll();
        if (empty($orders)) {
            Yii::$app->session->setFlash('danger', '没有相关订单');
            return $this->redirect(Yii::$app->request->referrer);
        }
        //将收件人收件地址收人手机一样的订单放到一起
        foreach ($orders as $order) {
            $merge_orders[$order['hash']][] = $order; 
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
                $file_name = '淘宝'.date('Ymd').'顺丰冷运订单';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                         $data['顺丰冷运'][] = [$basicData['codeNo'], $basicData['codeNo'], ' '.$order['order_id'],
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
                $file_name = '淘宝'.date('Ymd').'顺丰快递';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                         $data['顺丰冷运'][] = [$basicData['codeNo'], $basicData['codeNo'], ' '.$order['order_id'],
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
                $file_name = '淘宝'.date('Ymd').'德邦快递';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        $data['德邦快递'][] = [' '.$order['order_id'], $order['consignee'], $order['phone'],
                            $order['address'], '', $sku_code, $brand, 
                            '', $order['quantity']
                        ];
                    }
                }
                break;
            case '4'://德邦红酒
                $header = Yii::$app->params['DbWineExcelHeader'];
                $file_name = '淘宝'.date('Ymd').'德邦红酒';
                $tmp = [];
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        $unit = !empty($units[$sku_code]) ? $units[$sku_code] : '';
                        $tmp[] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            ' '.$order['order_id'], $order['consignee'], $order['address'],
                            $order['phone'], $brand, $sku_code,
                            $order['quantity'].$unit, '', '', '', '', '', $brand
                        ];
                    }
                }
                $data['红酒统计'] = Goods::array_sort($tmp,8,SORT_ASC,SORT_STRING);
                break;
            case '5'://整条出库
                $header = Yii::$app->params['ZsckExcelHeader'];
                $file_name = '淘宝'.date('Ymd').'整条出库';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        $data[$brand][] = ['', date('Y-m-d', time()), '', $brand,
                            '', '', ' '.$order['order_id'], $order['sku_title'],
                            $order['consignee'], $order['phone'], $order['address'], $order['quantity'], $order['buyer_memo'], $order['memo'], '', '', $sku_code
                        ];
                    }
                }
                break;
            case '6'://不粘锅
                $header = Yii::$app->params['DbWineExcelHeader'];
                $file_name = '淘宝'.date('Ymd').'不粘锅';
                foreach ($final_orders as $sku_code => $orders) {
                    foreach ($orders as $order) {
                        $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
                        $brand = @$codeBrand[$sku_code];
                        //红酒名称太长 sheet不兼容 所以处理一下
                        $data[$brand][] = [date('Y-m-d', time()), $basicData['address'], $basicData['tel'],
                            ' '.$order['order_id'], $order['consignee'], $order['address'],
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

    public static function actionTbOrderExport()
    {
        $goods = Goods::find()->all();
       // echo "<pre>";print_r($goods);exit;
        $sql = "select * from platform_order where origin=2 order by order_id asc";
        //echo $sql;exit;
        $orders = Yii::$app->wbdb->createCommand($sql)->queryAll();
        if (empty($orders)) {
            Yii::$app->session->setFlash('danger', '没有相关订单');
            Yii::$app->getResponse()->redirect(Yii::$app->request->referrer);
        }
       
        $header = ['订单号', '商品名称', '商品编码', '价格', '数量', '订单状态', '应付货款', '应付邮费', '实付金额', '订单创建时间', '订单付款时间', '收货地址'];
        $file_name = '淘宝订单'.date('Ymd');
        foreach ($orders as $key => $order) {
            $sku_code = $order['sku_code'] ? $order['sku_code'] : $order['store_code'];
            $data['淘宝订单'][] = [$order['order_id'], $order['spu_title'], $sku_code, $order['single_price'], $order['quantity'], $order['order_status'], $order['goods_price'], $order['post_fee'], $order['real_pay'], $order['order_created'], $order['order_payed'], $order['address']];
        }
        $sheet = array_keys($data);
        //echo "<pre>";print_r($data);exit;
        Excel::exportData($data, $header, $sheet, $file_name);
    }
}