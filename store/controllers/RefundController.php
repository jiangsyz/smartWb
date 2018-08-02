<?php
namespace store\controllers;

use Yii;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use store\models\order\Order;
use store\models\order\OrderRecord;
use store\models\order\OrderProperty;
use store\models\order\Refund;
use store\controllers\BaseController;
use store\models\Common;

class RefundController extends BaseController
{
	//public $enableCsrfValidation = false;
	//退款记录列表
	public function actionList()
	{
		try {
			//echo "<pre>";print_r($_POST);exit;
			$dataProvider = Refund::search(Yii::$app->request->get());
			$refunds = Refund::tidyRefunds($dataProvider->getModels());
			//echo "<pre>";print_r($refunds);exit;
			return $this->render('list', [
				'refunds' => $refunds,
				'page'=>$dataProvider->pagination,
			]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//退款驳回弹窗
	public function actionGetRefundModal()
	{
		try {
			$id = Yii::$app->request->get('id');
			return $this->renderAjax('refund-modal', [
				'refund' => new Refund(),
				'id' => $id,
	        ]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//同意或驳回
	public function actionUpdateRefundStatus()
	{
		try {
			$id = Yii::$app->request->post('id');
			$status = Yii::$app->request->post('status');
			if (!$id) {
				throw new SmartException("退款ID不能为空");
			}
			if (!isset($status) || $status=='') {
				throw new SmartException("退款状态不能为空");
			}
			$refund = Refund::findOne($id);
			$data = [
				'token' => Yii::$app->session['staff']['token'],
                'orderId' => (string)($refund->oid),
                'refundId' => (string)($id),
			];
			switch ($status) {
				case -1: //驳回
					if (!$rejectMemo = Yii::$app->request->post('Refund')['rejectMemo']) {
						throw new SmartException("驳回原因不能为空");
					}
					$apiUrl = Yii::$app->params['apiUrl'].'?r=refund/api-reject';
					$data['memo'] = (string)($rejectMemo);
					$response = Common::post($apiUrl, $data);
					break;
				case 0: //重新激活
					$apiUrl = Yii::$app->params['apiUrl'].'?r=refund/api-reopen&';
					$response = Common::get($apiUrl, $data);
					break;
				case 1: //打款中
					$apiUrl = Yii::$app->params['apiUrl'].'?r=refund/api-refund&';
					$response = Common::get($apiUrl, $data);
					break;
				case 9: //重置
					$apiUrl = Yii::$app->params['apiUrl'].'?r=refund/api-reset&';
					$response = Common::get($apiUrl, $data);
					break;
			}
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	
}