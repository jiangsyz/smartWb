<?php
namespace store\controllers;
use Yii;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use store\models\model\Source;
use store\models\category\Category;
use store\models\category\CategoryRecord;
use store\controllers\BaseController;

class CategoryController extends BaseController
{
	//树状分类编辑
	public function actionIndex()
	{
		try {
			$nodes = Category::find()->select("id,pid as pId,name")->asArray()->all();
			//单独构造一级顶级分类  这样才能增加一级分类
			$nodes[] = ['id'=>0, 'pId'=>-1, 'name'=>'分类'];
			//echo "<pre>";print_r($nodes);exit;
			return $this->render('index', [
				'nodes' => json_encode($nodes)
			]);
		} catch (SmartException $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//更新分类
	public function actionSaveCategory()
	{
		try {
			$id = Yii::$app->request->get('id');
			$pid = Yii::$app->request->get('pid');
			$name = Yii::$app->request->get('name');

			$category = Category::findOne($id);
			if (!$category) {
				if ($pid === '') {
					throw new SmartException("pid miss");
				}
				$category = new Category();
				$category->pid = $pid;
			}
			$category->name = $name;
			if (!$category->validate()) {
				throw new SmartException("category validate failed");
			}
			if (!$category->save(false)) {
				throw new SmartException("category save failed");
			}
			$this->response(1, ['error'=>0]);
		} catch (SmartException $e) {
			$this->response(1, array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}

	//删除分类
	public function actionDeleteCategory()
	{
		try {
			$id = Yii::$app->request->get('id');
			$category = Category::findOne($id);
			if (!$category->delete()) {
				throw new SmartException("category delete failed");
			}
			$this->response(1, ['error'=>0]);
		} catch (SmartException $e) {
			$this->response(1, array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}
}