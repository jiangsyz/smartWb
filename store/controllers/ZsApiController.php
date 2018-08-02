<?php
namespace store\controllers;

use Yii;
use yii\web\Controller;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use store\models\Common;
use linslin\yii2\curl;
use store\controllers\BaseController;


class ZsApiController extends Controller
{
	//物流跟踪信息
	public function actionGetLogisticsTrace()
	{
		try {
			if (!Yii::$app->request->get('com')) {
				throw new SmartException("快递公司编码不能为空");
			}
			if (!Yii::$app->request->get('num')) {
				throw new SmartException("运单号不能为空");
			}
			$from = Yii::$app->request->get('from', '');
			$url = Yii::$app->params['kd100']['url']."/poll/query.do";
			$params = [
				'com' => Yii::$app->request->get('com'),
				'num' => Yii::$app->request->get('num'),
			];
			$params = json_encode($params);

			$data = [
				'customer' => Yii::$app->params['kd100']['customer'],
				'param' => $params,
				'sign' => strtoupper(md5($params.Yii::$app->params['kd100']['key'].Yii::$app->params['kd100']['customer'])),
			];
			$o = '';	
			foreach ($data as $k=>$v) {
			    $o .= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
			}
			//echo "<pre>";print_r($data);exit;
			$data = substr($o,0,-1);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$result = curl_exec($ch);
			curl_close($ch);
			//$result = iconv('UTF-8', 'GB2312//IGNORE', $result); 
			//$data = str_replace("\&quot;",'"',$result );
			$data = json_decode($result, true);
			if (!isset($data['status']) && !isset($data['result'])) {
				throw new SmartException("网络请求失败");
			}
			if (isset($data['returnCode'])) {
				throw new SmartException($data['message']);
			}
			//如果是管理后台要查看物流  封装一下html格式
			$html = '';
			if ($from == 'modal') {
				foreach ($data['data'] as $val) {
					$html .= $val['time'].'&nbsp&nbsp';
					$html .= $val['context']."<br>";
				}
				echo $html;
			} else {
				echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
			}
		} catch (Exception $e) {
			echo json_encode(['error'=>-2,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		} 
	}
	
}