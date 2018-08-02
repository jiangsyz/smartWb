<?php
namespace store\controllers;

use yii;
use linslin\yii2\curl;
use store\models\Common;
use yii\web\SmartWebController;
use yii\base\SmartException;

class BaseController extends SmartWebController
{
	public function beforeAction($action)
	{
		//初始化http请求基本信息
		if (!@Yii::$app->session['staff']['phone']) {
            parent::response(2,array("uri"=>"./index.php?r=site/login"));
            //return $this->redirect(['site/login']);
        }

        if (!@Yii::$app->session['staff']['tokenTimeout'] || @Yii::$app->session['staff']['tokenTimeout'] < time() 
            || !@Yii::$app->session['staff']['token']) {
            $apiUrl = Yii::$app->params['apiUrl'].'?r=staff/api-get-token';
            $params = [
                'phone' => (string)(Yii::$app->session['staff']['phone']),
                'pwd' => (string)(md5(Yii::$app->session['staff']['pwd']))
            ];
            $response = Common::post($apiUrl, $params);
            $tmp = Yii::$app->session['staff'];
            if ($response['error'] == 0) {
                Yii::$app->session['staff'] = [
                    'phone' => $tmp['phone'],
                    'pwd' => $tmp['pwd'],
                    'isLogin' => 1,
                    'staffId' => $tmp['staffId'],
                    'token' => $response['data']['token'],
                    'tokenTimeout' => $response['data']['timeOut'],
                ];
            } else {
                throw new SmartException($response['msg']);
                //die($response['msg']);
            }
        }
		return parent::beforeAction($action);
	}
	
}