<?php
namespace store\controllers;

use Yii;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use store\models\model\Source;
use store\models\member\Member;
use store\models\member\MemberLv;
use store\controllers\BaseController;
use store\models\source\VirtualItem;

class CardController extends BaseController
{
	//会员卡管理
	public function actionList()
	{
		try {
			$virtualItem = new VirtualItem();
			$dataProvider = $virtualItem->search(Yii::$app->request->get());
			$models = $dataProvider->getModels();
			
			return $this->render('list', [
				'dataProvider' => $dataProvider,
			]);
		} catch (Exception $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//会员卡编辑
	public function actionUpdate()
	{
		try {
			$cardId = Yii::$app->request->get('id');
			$virtualItem = VirtualItem::findOne($cardId);
			if (Yii::$app->request->isPost) {
				//echo "<pre>";print_r($_POST);exit;
				$virtualItem->load(Yii::$app->request->post());
				$virtualItem->cover = Yii::$app->request->post('VirtualItem')['cover'][0];
				if (!$virtualItem->validate()) {
					//echo "<pre>";print_r($virtualItem);exit;
					throw new SmartException("会员卡参数校验失败");
				}
				if (!$virtualItem->save(false)) {
					throw new SmartException("会员卡保存失败");
				}
				return $this->redirect(Yii::$app->request->referrer);
			} else {
				return $this->render('update', [
					'model' => $virtualItem,
				]);
			}
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
			return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//上下架 锁定会员卡
	public function actionCloseLockCard()
	{
		try {
			$ids = Yii::$app->request->get('id');
			//字段
			$field = Yii::$app->request->get('field');
			//要更新成的值
			$updateVal = Yii::$app->request->get('updateVal');
			if (is_array($ids)) {
				$ids = implode(',', $ids);
			}
			$rtn = VirtualItem::updateAll([$field => $updateVal], "id in ({$ids})");
			$this->response(1, ['error'=>0]);
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1, 'msg'=>$e->getMessage()]);
		}
	}
}