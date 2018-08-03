<?php
namespace store\controllers;
use Yii;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use store\models\product\Spu;
use store\models\product\Sku;
use store\models\model\Source;
use store\models\source\SourceProperty;
use store\models\product\SkuMemberPrice;
use store\models\category\CategoryRecord;
use store\models\category\Category;
use store\models\ProductSearch;
use store\models\recommend\RecommendRecord;
use linslin\yii2\curl;
use store\models\logistics\Logistics;
use store\models\source\SourcePropertyConf;
use store\models\Common;
use store\controllers\BaseController;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class ProductController extends BaseController
{
	//发布商品
	public function actionCreate()
	{
		$spu = new Spu();
		$sku = new Sku();
		$sourceProperty = new SourceProperty();
		$skuMemberPrice = new SkuMemberPrice();
		$categoryRecord = new CategoryRecord();
		$recommendRecord = new RecommendRecord();
		$postData = Yii::$app->request->post();
		if (Yii::$app->request->isPost) {
			//开启事务
			$transaction = Yii::$app->db->beginTransaction();
			try {
				//TODO 以后改成OOP的风格
				//插入spu
				$spu->saveSpu($postData);
				$postData['spuId'] = $spu->id;
				//插入sku,source_property,sku_member_price
				$sku->saveSku($postData);
				//插入category_record
				$categoryRecord->saveCategoryRecord($postData);
				$recommendRecord->saveRecommendRecord($postData);
				$transaction->commit();
				$this->redirect(['/product/update','id'=>$spu->id]);
			} catch (Exception $e) {
				$transaction->rollBack();
				Yii::$app->session->setFlash('danger', $e->getMessage());
            	return $this->redirect(Yii::$app->request->referrer);
			}
		} else {
			//树状分类
			$category = new Category();  
	        $categorTree = $category->getOptions();
	        //渠道分类
	        $logisticsTree = Logistics::getLogisticsMap();
	        //属性名称
	        $sourceProConfTree = SourcePropertyConf::getSourcePropertyConfMap();
	        //echo "<pre>";print_r($logisticsTree);exit;
			return $this->render('create', [
				'spu' => $spu,
				'测试' => $sku,
				'sourceProperty' => $sourceProperty,
				'skuMemberPrice' => $skuMemberPrice,
				'categoryRecord' => $categoryRecord,
				'categoryTree' => $categorTree,
				'logisticsTree' => $logisticsTree,
				'sourceProConfTree' => $sourceProConfTree,
				'recommendRecord' => $recommendRecord
			]);
		}
	}

	//异步验证参数
	public function actionValidateForm()
	{
	    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
	    $model = new Spu();
	    $model->load(Yii::$app->request->post());
	    $model->cover = $model->cover[0];
	    $model->createTime = time();
	    $model->memberId = Yii::$app->session['staff']['staffId'];
	    return \yii\widgets\ActiveForm::validate($model);  
	}

	//因为商品detail不是用yii自带的约束验证的  所以单独用ajax验证
	public function actionValidateDetail()
	{
		try {
			$detail = Yii::$app->request->post('detail');
			Spu::checkDetail($detail);
			$this->response(1, ['error'=>0]);
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1,'msg'=>'<div id="detail-error" class="form-group has-error"><p class="help-block help-block-error">'.$e->getMessage().'</p></div>']);
			//echo '<p class="help-block help-block-error">'.$e->getMessage().'</p>';
		}
	}

	//商品列表（出售中的商品，仓库中的商品）
	public function actionList()
	{
		//echo "<pre>";print_r(Yii::$app->session['staff']);exit;
		try {
			$searchModel = new ProductSearch();
			$dataProvider = $searchModel->search(Yii::$app->request->get());
			$models = $dataProvider->getModels();
			return $this->render('list', [
				'dataProvider' => $dataProvider,
				'models' => $models,
				'searchModel' => $searchModel,
				'page'=>$dataProvider->pagination,
				'sort'=>$dataProvider->sort,  
			]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//浏览商品详情
	public function actionView()
	{
		try {
			$spuId = Yii::$app->request->get('id');
			$spu = Spu::findOne($spuId);
			//echo "<pre>";print_r($spu);exit;
			return $this->render('view', [
				'spu' => $spu
			]);
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1, 'msg'=>$e->getMessage()]);
		}
	}

	//编辑商品
	public function actionUpdate()
	{
		$spuId = Yii::$app->request->get('id');
		$tab = Yii::$app->request->get('tab', 'spu');
		$postData = Yii::$app->request->post();
		$postData['spuId'] = $spuId;
		$spu = Spu::findOne($spuId);
		//echo "<pre>";print_r($spu);exit;
		$categoryRecord = CategoryRecord::find()->where("sourceType=".Source::TYPE_SPU." and sourceId=".$spuId)->one();
		$recommendRecord = new RecommendRecord();
		$sku = new Sku();
		
		if (Yii::$app->request->isPost) {
			//开启事务
			$transaction = Yii::$app->db->beginTransaction();
			try {
				//保存spu
				$spu->saveSpu($postData);
				//添加了sku才保存  sku,source_property,sku_member_price
				if (!empty($postData['Sku'])) {
					$sku->saveSku($postData);
				}
				//保存category_record
				$categoryRecord->saveCategoryRecord($postData);
				$recommendRecord->saveRecommendRecord($postData);
				$transaction->commit();
				return $this->redirect(['/product/update','id'=>$spuId, 'tab'=>$tab]);
			} catch (Exception $e) {
				$transaction->rollBack();
				Yii::$app->session->setFlash('danger', $e->getMessage());
            	return $this->redirect(Yii::$app->request->referrer);
			}
		} else {
			//获取分类树
			$category = new Category();
			$categorTree = $category->getOptions();
			//获取物流渠道
			$logisticsTree = Logistics::getLogisticsMap();
			//属性名称
	        $sourceProConfTree = SourcePropertyConf::getSourcePropertyConfMap();
			//是否为推荐或热门商品
			$recommendRecord->isHot = $recommendRecord->isHotOrRecommend($spuId, RecommendRecord::RECOMMEND_TYPE_HOT);
			$recommendRecord->isRecommend = $recommendRecord->isHotOrRecommend($spuId, RecommendRecord::RECOMMEND_TYPE_RECOMMEND);
			//SKU信息
			$dataProvider = new ActiveDataProvider([
		        'query' => Sku::find()->where("spuId={$spuId}")->orderBy('id'),
		         'pagination' => [
			        'pageSize' => 100,
			    ],
		    ]);
			return $this->render('update', [
				'spu' => $spu,
				'categoryRecord' => $categoryRecord,
				'categoryTree' => $categorTree,
				'logisticsTree' => $logisticsTree,
				'sourceProConfTree' => $sourceProConfTree,
				'recommendRecord' => $recommendRecord,
				'dataProvider' => $dataProvider
			]);
		}
	}

	//某个SPU下的SKU列表
	public function actionSkuList()
	{
		try {
			$spuId = Yii::$app->request->get('id');
			$dataProvider = new ActiveDataProvider([
		        'query' => Sku::find()->where("spuId={$spuId}")->orderBy('id'),
		        'pagination' => [
			        'pageSize' => 100,
			    ],
		    ]);
			return $this->render('sku-list', [
				'dataProvider' => $dataProvider,
				'spu' => Spu::findOne($spuId),
				'referUrl' => Yii::$app->request->referrer,
			]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
        	return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//异步获取SKU的modal信息
	public function actionGetSkuModal()
	{
		try {
			$skuId = Yii::$app->request->get('id');
			$item = Yii::$app->request->get('item');
			$sku = Sku::findOne($skuId);
			switch ($item) {
				case 'basicInfo':
					$sourceProperty = $sku->getProperty();
					$sourceProConfTree = SourcePropertyConf::getSourcePropertyConfMap();
					return $this->renderAjax('sku-modal', [
			            'sourceProperty' => $sourceProperty,
			            'sku' => $sku,
			            'item' => $item,
			            'sourceProConfTree' => $sourceProConfTree
			        ]);
					break;
				case 'count':
					return $this->renderAjax('sku-modal', [
			            'sku' => $sku,
			            'item' => $item,
			        ]);
					break;
				case 'price':
					$skuMemberPrice = SkuMemberPrice::find()->where("skuId={$sku->id} and lv=0")->one();
					return $this->renderAjax('sku-modal', [
						'sku' => $sku,
			            'skuMemberPrice' => $skuMemberPrice,
			            'item' => $item,
			        ]);
					break;
				case 'vprice':
					$skuMemberPrice = SkuMemberPrice::find()->where("skuId={$sku->id} and lv=1")->one();
					return $this->renderAjax('sku-modal', [
						'sku' => $sku,
			            'skuMemberPrice' => $skuMemberPrice,
			            'item' => $item,
			        ]);
					break;
			}
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1, 'msg'=>$e->getMessage()]);
		}
	}

	//异步保存SKU基本信息，库存，价格
	public function actionUpdateSku()
	{
		try {
			$postData = Yii::$app->request->post();
			$sku = Sku::findOne($postData['Sku']['id']);
			$level = false;
			switch ($postData['item']) {
				case 'basicInfo':
					$sourceProperty = sourceProperty::findOne($postData['SourceProperty']['id']);
					if (!$sku->load($postData) || !$sku->validate()) {
						Yii::$app->session->setFlash('danger', 'sku参数验证失败');
					}
					if (!$sku->save(false)) {
						Yii::$app->session->setFlash('danger', 'sku保存失败');
					}
					if (!$sourceProperty->load($postData) || !$sourceProperty->validate()) {
						Yii::$app->session->setFlash('danger', '属性验证失败');
					}
					if (!$sourceProperty->save(false)) {
						Yii::$app->session->setFlash('danger', '属性保存失败');
					}
					break;
				case 'count':
					$apiUrl = Yii::$app->params['apiUrl'].'?r=product/api-update-sku-keep-count&';
					$data = [
						'token' => Yii::$app->session['staff']['token'],
		                'skuId' => $postData['Sku']['id'],
		                'keepCount' => $postData['Sku']['count']
					];
					$response = Common::get($apiUrl, $data);
					break;
				case 'price':
					$apiUrl = Yii::$app->params['apiUrl'].'?r=product/api-update-sku-price&';
					$data = [
		                'level' => '0',
		                'price' => $postData['SkuMemberPrice']['price'],
		                'skuId' => $postData['Sku']['id'],
		                'token' => Yii::$app->session['staff']['token'],
					];
					$response = Common::get($apiUrl, $data);
					break;
				case 'vprice':
					$apiUrl = Yii::$app->params['apiUrl'].'?r=product/api-update-sku-price&';
					$data = [
						'level' => '1',
						'price' => $postData['SkuMemberPrice']['price'],
						'skuId' => $postData['Sku']['id'],
						'token' => Yii::$app->session['staff']['token'],
					];
					$response = Common::get($apiUrl, $data);
					break;
			}
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1, 'msg'=>$e->getMessage()]);
		}
	}

	//SPU上下架 锁定解锁
	public function actionCloseLockSpu()
	{
		try {
			$spuIds = Yii::$app->request->get('id');
			//字段
			$field = Yii::$app->request->get('field');
			//要更新成的值
			$updateVal = Yii::$app->request->get('updateVal');
			if (is_array($spuIds)) {
				$spuIds = implode(',', $spuIds);
			}
			$rtn = Spu::updateAll([$field => $updateVal], "id in ({$spuIds})");
			$rtn = RecommendRecord::deleteAll("sourceType=".source::TYPE_SPU." AND sourceId in ({$spuIds})");
			$this->response(1, ['error'=>0]);
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1, 'msg'=>$e->getMessage()]);
		}
	}

	//SKU上下架 锁定解锁
	public function actionCloseLockSku()
	{
		try {
			$skuId = Yii::$app->request->get('id');
			$field = Yii::$app->request->get('field');
			$sku = Sku::findOne($skuId);
			$sku->$field = !$sku->$field;
			if (!$sku->save(false)) {
				throw new SmartException("sku save failed");
			}
			$this->response(1, ['error'=>0]);
		} catch (Exception $e) {
			$this->response(1, ['error'=>-1, 'msg'=>$e->getMessage()]);
		}
	}

	public function actionGetPropertyConf()
	{
		$option = '';
		$propertyConf = SourcePropertyConf::find()->all();
		foreach ($propertyConf as $conf) {
			$option .= '<option value="'.$conf->eName.'">'.$conf->cName.'</option>';
		}
		$this->response(1, ['error'=>0, 'option'=>$option]);
	}

	public function actionGetQrcode()
    {
    	$spuId = Yii::$app->request->get('id');
		//获取access_token
		$access_token = Yii::$app->smartWechat->getAccessToken(Yii::$app->params['miniApp']['appId'], Yii::$app->params['miniApp']['appSecret']);
		/*$access_token = "11_e3s7-ZOeKLVwsW4-LqCI_xIJ60pxQFuWfqXxDDHJX7AQEeMLikg-8YDP1MDrZazrtq95n9-Gpp9iMhwLpeUNi-q6a4Q38tzU3xFuomVMgd3AgqaRGWQjw8-abK1ZDKC1BWRjyjVs3j74j_vyZMUaAEAPCK";*/
		//echo "<pre>";print_r($access_token);exit;
		//header('content-type:image/gif');  
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
		$params = [
			'scene' => $spuId,
			'page' => 'pages/detail',
		];
		$params = json_encode($params);
		$response = Common::api_notice_increment($url,$params);
		$file = "./images/qrcode/spu_".$spuId.".jpg";
        file_put_contents($file,$response);
        $src = "./images/qrcode/spu_".$spuId.".jpg";
		//$response = $this->get_http_array($url,$params);
		echo '<img src="'.$src.'">';
    }

    //推荐热门商品
    public function actionRecommendList()
    {
    	try {
    		$model = new RecommendRecord();
			$dataProvider = $model->search(Yii::$app->request->get());
			$models = $dataProvider->getModels();
			return $this->render('recommend-list', [
				'dataProvider' => $dataProvider,
				'models' => $models,
				'page'=>$dataProvider->pagination,
				'sort'=>$dataProvider->sort,  
			]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
    }

    //推荐热门商品排序
    public function actionRecommendSort()
    {
    	try {
    		$id = Yii::$app->request->get('id');
    		$sort = Yii::$app->request->get('sort');
    		if (!$id) {
    			throw new SmartException("id不能为空");
    		}
    		if (!$sort) {
    			throw new SmartException("序号不能为空");
    		}
    		$model = RecommendRecord::find()->where("id={$id}")->one();
    		//echo "<pre>";print_r($model);exit;
    		$model->sort = $sort;
    		if (!$model->save(false)) {
    			throw new SmartException("操作失败");
    		}
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
    }


}