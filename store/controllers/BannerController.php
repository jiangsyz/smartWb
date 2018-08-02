<?php
namespace store\controllers;
use Yii;
use yii\web\SmartWebController;
use yii\base\Exception;
use yii\base\SmartException;
use store\models\banner\Banner;
use store\models\product\Spu;

class BannerController extends BaseController
{
	//Banner图管理
	public function actionIndex()
	{
		try {
			$bannerId = Yii::$app->request->get('bannerId', 1);
			$banner = Banner::findOne($bannerId);
			if (!$banner) {
				$banner = new Banner();
				$banner->id = $bannerId;
				$banner->siteNo = 'index';
			}
			$postData = Yii::$app->request->post();
			if (Yii::$app->request->isPost) {
				if ($postData['Banner']['uri']) {
					$spuId = $postData['Banner']['uri'];
					$spu = Spu::find()->where("id={$spuId}")->one();
					if (!$spu) {
						throw new SmartException("链接的SPU不存在！");
					}
					$banner->uri = 'spu,'.$postData['Banner']['uri'];
				}
				$banner->title = $postData['Banner']['title'];
				$banner->image = $postData['Banner']['image'][0];
				$banner->sort = $postData['Banner']['sort'] ? $postData['Banner']['sort'] : 0;
				if (!$banner->save()) {
					throw new SmartException("保存失败！");
				}
				return $this->redirect(Yii::$app->request->referrer);
			} else {
				if ($banner->uri) {
					$tmpUri = explode(',', $banner->uri);
					$banner->uri = $tmpUri[1];
				}
				return $this->render('index', [
					'banner' => $banner
				]);
			}
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
        	return $this->redirect(Yii::$app->request->referrer);
		}
		
	}
	
}