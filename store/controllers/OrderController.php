<?php
namespace store\controllers;
use Yii;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use store\models\model\Source;
use store\models\recommend\RecommendRecord;
use store\models\order\Order;
use common\models\Excel;
use store\models\order\OrderRecord;
use store\models\order\OrderProperty;
use store\models\order\Refund;
use store\models\order\OrderBuyingRecord;
use store\models\logistics\Logistics;
use store\controllers\BaseController;
use store\models\order\ChangeOrderPriceLog;
use store\models\Common;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use store\models\SmartAreaRecord;
use store\models\member\Member;

class OrderController extends BaseController
{
	public $enableCsrfValidation = false;

	//订单列表
	public function actionList()
	{
		try {
			//echo "<pre>";print_r($_POST);exit;
			$dataProvider = Order::search(Yii::$app->request->get());
			$models = $dataProvider->getModels();
			$orders = $orderIds = [];
			//整合订单ID
			foreach ($models as $key => $value) {
				$orderIds[] = $value['parentId'];
			}
			//构建订单，订单商品数组
			if ($orderIds) {
				$orders = Order::getOrderRelativeInfo($orderIds, 1);
			}
			//物流渠道
			$logistics = Logistics::find()->all();
			return $this->render('list', [
				'logistics' => new Logistics(),
				'orders' => $orders,
				'page'=>$dataProvider->pagination, 
				'logisticsTree' => $logistics
			]);
		} catch (Exception $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//订单详情
	public function actionView()
	{
		try {
			$orderId = Yii::$app->request->get('id');
			$orders = Order::getOrderRelativeInfo([$orderId], 1);
			return $this->render('view', [
				'orders' => $orders,
			]);
		} catch (Exception $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//导出待发货订单
	public function actionExportOrderForDelivery()
	{
		try {
			ini_set('max_execution_time', '0');
			ini_set("memory_limit","-1");
            $result = Order::getOrderForDelivery(Yii::$app->request->post());
            Excel::exportData($result['data'], $result['header'], $result['sheet'], $result['filename']);
            return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//导入订单物流信息
	public function actionImportOrderLogistics()
	{	
		try {
			ini_set('max_execution_time', '0');
			ini_set("memory_limit","-1");
            ini_set('upload_max_filesize', '256M');
			if (!$logistics = Yii::$app->request->post('Logistics')['id']) {
				throw new SmartException("缺少物流渠道参数");
			}
            if (!isset($_FILES)) {
            	throw new SmartException("请上传文件");
            }
            //文件信息
            $error = $_FILES['file']['error'];
            $tmpName = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            //顺丰冷运读单个sheet
            if ($logistics == Logistics::SF_COLD || $logistics == Logistics::DB_EXPRESS) { 
            	$data = Excel::read($fileName, $tmpName, $error);
            }
            //德邦红酒读多个sheet  改成单sheet
            if ($logistics == Logistics::DB_WINE || $logistics == Logistics::ZTCK) { 
            	$data = Excel::read($fileName, $tmpName, $error, $logistics);
            }
            if (isset($data['message'])) {
            	throw new SmartException($data['message']);
            }
            
            //解析数据  回填物流
            Order::updateOrderLogistics($data['data'], $logistics);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//订单改价、添加备注、申请退款弹出框
	public function actionGetOrderModal()
	{
		try {
            $item = Yii::$app->request->get('item');
            if (!$item) {
            	throw new SmartException('缺少item参数');
            }
            $orderId = Yii::$app->request->get('id');
        	if (!$orderId) {
            	throw new SmartException('缺少订单ID参数');
            }
            $orderRecord = OrderRecord::findOne($orderId);
           	$orderRecord->pay = $orderRecord->pay/100;
            //申请退款
            if ($item == 'applyRefund') {
            	$bid = Yii::$app->request->get('bid');
            	if (!$bid) {
	            	throw new SmartException('缺少bid参数');
	            }
	            $buyingRecord = OrderBuyingRecord::findOne($bid);
	        }
            
            return $this->renderAjax('order-modal', [
				'orderRecord' => $orderRecord,
				'buyingRecord'=> @$buyingRecord,
	            'orderProperty' => new OrderProperty(),
	            'changeOrderPriceLog' => new ChangeOrderPriceLog(),
	            'refund' => new Refund(),
	            'item' => $item,
	        ]);
		} catch (Exception $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//修改收货信息弹框
	public function actionGetAddressModal()
	{
		try {
			$orderId = Yii::$app->request->get('id');
			if (!$orderId) {
				throw new SmartException("订单ID不能为空");
			}
			$address = OrderProperty::find()->where("orderId={$orderId} and propertyKey='address'")->orderBy("createTime desc")->one();
			if (!$address) {
				throw new SmartException("该订单无收货地址");
			}
			$address = json_decode($address->propertyVal, true);
			$areaId = $address['areaId'];
			$url = Yii::$app->params['apiUrl'].'?r=area/api-get-area&';
			$response = Common::get($url, ['areaId'=>(string)($areaId)]);
			$pcdIds = explode(',', $response['data']['full_area_id']);
			$provinceModels = SmartAreaRecord::find()->where("level=1")->all();
			$cityModels = SmartAreaRecord::find()->where("level=2 and parent_id={$pcdIds[0]}")->all();
			$districtModels = SmartAreaRecord::find()->where("level=3 and parent_id={$pcdIds[1]}")->all();
			//echo "<pre>";print_r($address);exit;
			return $this->renderAjax('address-modal', [
				'address' => $address,
				'provinceId' => $pcdIds[0],
				'cityId' => $pcdIds[1],
				'districtId' => $pcdIds[2],
				'provinceModels' => $provinceModels,
				'cityModels' => $cityModels,
				'districtModels' => $districtModels
	        ]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//获取当前区域下的子区域
	public function actionGetChildArea()
	{
		try {
			$areaId = Yii::$app->request->get('areaId');
			$childAreas = SmartAreaRecord::find()->where("parent_id={$areaId}")->asArray()->all();
			if (!$childAreas) {
				return null;
			}
			//echo "<pre>";print_r($childAreas);exit;
			$firstChildArea = $childAreas[0];
			//echo "<pre>";print_r($firstChildArea);exit;
			if ($firstChildArea['level'] == 2) {
				$cityAreas = $childAreas;
				$cityId = $firstChildArea['area_id'];
				$districtAreas = SmartAreaRecord::find()->where("parent_id={$cityId}")->asArray()->all();
				$tmp = $districtAreas[0];
				$districtId = $tmp['area_id'];
			}
			if ($firstChildArea['level'] == 3) {
				$districtAreas = $childAreas;
				$districtId = $firstChildArea['area_id'];
			}
			$this->response(1, [
				'error'=>0,
				'cityAreas'=>isset($cityAreas) ? $cityAreas : '',
				'cityId' => isset($cityId) ? $cityId : '',
				'districtAreas' => isset($districtAreas) ? $districtAreas : '',
				'districtId' => isset($districtId) ? $districtId : '',
			]);
		} catch (Exception $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//订单改价
	public function actionChangePrice()
	{
		try {
            $item = Yii::$app->request->post('item');
            $memo = Yii::$app->request->post('ChangeOrderPriceLog')['memo'];
            if ($memo == '') {
            	throw new SmartException("备注不能为空");
            }
            //修改订单商品价格
            if ($item == 'changePrice') {
            	$price = Yii::$app->request->post('OrderRecord')['finalPrice'];
            	if (!is_numeric($price) || $price <= 0) {
            		throw new SmartException("请输入正确的商品价格");
            	}
            	$url = Yii::$app->params['apiUrl'].'?r=order/api-change-price';
            	$params = [
            		'token' => Yii::$app->session['staff']['token'],
            		'orderId' => Yii::$app->request->post('OrderRecord')['id'],
            		'price' => $price,
            		'memo' => $memo,
            	];
            } else { //修改订单运费价格
            	$freight = Yii::$app->request->post('OrderRecord')['freight'];
            	if (!is_numeric($freight) || $freight < 0) {
            		throw new SmartException("请输入正确的运费");
            	}
            	$url = Yii::$app->params['apiUrl'].'?r=order/api-change-freight';
            	$params = [
            		'token' => Yii::$app->session['staff']['token'],
            		'orderId' => Yii::$app->request->post('OrderRecord')['id'],
            		'freight' => $freight,
            		'memo' => $memo,
            	]; 
            }
            $response = Common::post($url, $params);
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//添加备注
	public function actionAddMemo()
	{
		try {
            $orderId = Yii::$app->request->post('OrderRecord')['id'];
            $propertyVal = Yii::$app->request->post('OrderProperty')['propertyVal'];
            if (!$orderId || !$propertyVal) {
            	throw new SmartException('miss params');
            }
            $orderProperty = new OrderProperty();
            $orderProperty->orderId = $orderId;
            $orderProperty->propertyKey = 'staffMemo';
            $orderProperty->propertyVal = $propertyVal;
            $orderProperty->createTime = time();
            //echo "<pre>";print_r($orderProperty);exit;
            if (!$orderProperty->save()) {
            	throw new Exception("添加备注失败");
            }
            //添加备注后自动锁定订单
            $orderRecord = OrderRecord::find()->where("id={$orderId}")->one();
            if (!$orderRecord) {
            	throw new Exception("订单不存在");
            }
            $orderRecord->locked = 1;
            $orderRecord->save(false);
            return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//锁定订单
	public function actionLock()
	{
		try {
			$orderId = Yii::$app->request->get('id');
			if (!$orderId) {
				throw new SmartException('订单号不能为空');
			}
			$orderRecord = OrderRecord::findOne($orderId);
			if (!$orderRecord) {
				throw new SmartException('该订单不存在');
			}
			if ($orderRecord->locked < 0) {
				throw new SmartException('该订单存在异常，无法解锁');
			}
			$orderRecord->locked = !$orderRecord->locked;
			if (!$orderRecord->save(false)) {
				throw new SmartException('操作失败');
			}
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//单个商品申请退款
	public function actionApplyRefund()
	{
		try {
			//单位是分
			$refundPrice = (string)(Yii::$app->request->post('Refund')['price']*100);
			$applyMemo = Yii::$app->request->post('Refund')['applyMemo'];
			$orderId = Yii::$app->request->post('OrderRecord')['id'];
			$buyingRecordId = Yii::$app->request->post('OrderBuyingRecord')['id'];
			$orderRecord = OrderRecord::findOne($orderId);
			if (!is_numeric($refundPrice) || $refundPrice<=0) {
				throw new SmartException('请输入正确的退款金额');
			}
			if (!$applyMemo) {
				throw new SmartException('退款备注不能为空');
			}
			$apiUrl = Yii::$app->params['apiUrl'].'?r=refund/api-refund-by-buying-record';
			$data = [
				'token' => Yii::$app->session['staff']['token'],
                'orderId' => $orderId,
                'buyingRecordId' => $buyingRecordId,
                'price' => $refundPrice,
                'memo' => $applyMemo
			];
			//echo "<pre>";print_r($data);exit;
			$response = Common::post($apiUrl, $data);
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
            //return $this->refresh();
		}
	}

	//整单退
	public function actionRefundAll()
	{
		try {
			$orderId = Yii::$app->request->get('id');
			if (!$orderId) {
				throw new SmartException("订单号不能为空");
			}
			$refund = Refund::findOne("oid={$orderId} and status<>-1");
			if ($refund) {
				throw new SmartException("已存在退款记录，无法再次申请整单退");
			}
			$url = Yii::$app->params['apiUrl'].'?r=order/api-close&';
			$params = [
				'token' => Yii::$app->session['staff']['token'],
				'orderId' => $orderId,
			];
			$response = Common::get($url, $params);
			if (!isset($response['error'])) {
				throw new SmartException("网络请求失败");
			}
			if ($response['error']!=0) {
				throw new SmartException($response['msg']);
			}
			$this->response(1, array('error'=>0));
		} catch (Exception $e) {
			$this->response(1, array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//修改收货信息
	public function actionModifyAddress()
	{
		try {
			//echo "<pre>";print_r($_POST);exit;
			$url = Yii::$app->params['apiUrl'].'?r=order/api-change-address-by-staff';
			$postData = [
				'token' => Yii::$app->session['staff']['token'],
				'orderId' => (string)(Yii::$app->request->post('oid')),
				'name' => Yii::$app->request->post('name'),
				'phone' => (string)(Yii::$app->request->post('phone')),
				'areaId' => (string)(Yii::$app->request->post('areaId')),
				'address' => Yii::$app->request->post('address'),
				'postCode' => '',	
			];
			//echo "<pre>";var_dump($postData);exit;
			$response = Common::post($url, $postData);
			if ($response['error'] != 0) {
				throw new SmartException($response['msg']);
			}
			$this->response(1, array('error'=>0, 'newAddress' => Yii::$app->request->post('province').Yii::$app->request->post('city')
				.Yii::$app->request->post('district').Yii::$app->request->post('address')));
		} catch (Exception $e) {
			$this->response(1, array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}


	/**
     * 首页统计
     *
     * @return string
     */
    public function actionIndex()
    {
        $yesterday = strtotime("-1 day");
        $today = strtotime("today")-1;
        $lastmonth = strtotime(date('Y').'-'.date('m').'-01');
        $now = time();
        //昨日新增订单
        $newOrderCnt = OrderRecord::find()->where("code is not null and createTime between {$yesterday} and {$today}")->count();
        //昨日新增用户
        $newUserCnt = Member::find()->where("createTime between {$yesterday} and {$today}")->count();
        //本月订单量
        $monthOrderCnt = OrderRecord::find()->where("createTime between {$lastmonth} and {$now} and code is not null")->count();
        //本月销售额
        $monthSalesAmount = OrderRecord::find()->select("sum(pay) as pay")->where("code is not null and createTime between {$lastmonth} and {$now} and payStatus=1 and cancelStatus=0 and closeStatus=0 and finishStatus=1")->asArray()->one();
        $monthSalesAmount = $monthSalesAmount['pay']/100;
        //echo $monthSalesAmount;exit;
        
//echo "<pre>";print_r($dateStr);exit;
        //$amountArr = array_values($amountArr);
        //echo "<pre>";print_r($monthSalesAmount);exit;
        return $this->render('index', [
            'newOrderCnt' => $newOrderCnt,
            'newUserCnt' => $newUserCnt,
            'monthOrderCnt' => $monthOrderCnt,
            'monthSalesAmount' => $monthSalesAmount,
        ]);
    }

}