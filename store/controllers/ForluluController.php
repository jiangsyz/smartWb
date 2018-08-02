<?php
namespace store\controllers;

use Yii;
use yii\web\SmartWebController;
use yii\base\Exception;
use yii\base\SmartException;
use linslin\yii2\curl;

class ForluluController extends SmartWebController
{
	public $enableCsrfValidation = false;

	//demo版本 直撸不用model
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
	
	const APPID = 'wx4fadd384b39658cd';
	const APPSECRET = '67ec14c4bd96a2386748f9bc127037ee';

	const GZ_APPID = "wx21dca8cae24e856e";
	const GZ_APPSECRET = "a7a7f51b8bc6a0a4224ac7144c0d6693";


	/*解密wx.getUserInfo返回的加密信息*/
	public function actionEncrypteUnionid()
	{
		try{
			$jsCode = Yii::$app->request->post('jscode');
			//加密的用户数据
			$encryptedData = Yii::$app->request->post('encryptedData');
			//加密算法的初始向量
			$iv = Yii::$app->request->post('iv');
			/*-----------------------------根据js_code获取openid和session_key--------------------------------*/
			$url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::APPID."&secret=".self::APPSECRET."&js_code=".$jsCode."&grant_type=authorization_code";
			$curl = new curl\Curl();
			$response = $curl->get($url);
			$response = json_decode($response, true);
			if (!isset($response['openid'])) {
				throw new SmartException($response['errcode'].":".$response['errmsg']);
			}
			$openId = $response['openid'];
			echo "<pre>";print_r($openId);exit;
			$sessionKey = $response['session_key'];
			/*-----------------------------------------------------------------------------------------------*/
			if (strlen($sessionKey) != 24) {
				//return self::$IllegalAesKey;
				throw new SmartException("sessionKey不合法");
			}
			$aesKey = base64_decode($sessionKey);

			if (strlen($iv) != 24) {
				//return self::$IllegalIv;
				throw new SmartException("iv不合法");
			}
			$aesIV = base64_decode($iv);
			$aesCipher = base64_decode($encryptedData);
			$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
			$dataObj = json_decode($result);
			if($dataObj  == NULL) {
				throw new SmartException("解密后的数据为空");
			}
			if($dataObj->watermark->appid != self::APPID) {
				throw new SmartException("APPID不匹配");
			}
			$this->response(1,array('error'=>0,'data'=>json_decode($result, true)));
    	}
    	catch(Exception $e) {
    		$this->response(1,array('error'=>$e->getCode()?$e->getCode():-1,'msg'=>$e->getMessage()));
    	}
	}

	/*解密wx.getPhoneNumber返回的encryptedData*/
	public function actionEncrypteData()
	{
		try{
			$jsCode = Yii::$app->request->post('jscode');
			//加密的用户数据
			$encryptedData = Yii::$app->request->post('encryptedData');
			//加密算法的初始向量
			$iv = Yii::$app->request->post('iv');
			/*-----------------------------根据js_code获取openid和session_key--------------------------------*/
			$url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::APPID."&secret=".self::APPSECRET."&js_code=".$jsCode."&grant_type=authorization_code";
			$curl = new curl\Curl();
			$response = $curl->get($url);
			$response = json_decode($response, true);
			if (!isset($response['openid'])) {
				throw new SmartException($response['errcode'].":".$response['errmsg']);
			}
			$openId = $response['openid'];
			$sessionKey = $response['session_key'];
			$arr = [
				'jsCode' => $jsCode,
				'encryptedData' => $encryptedData,
				'iv' => $iv,
				'openId' => $openId,
				'sessionKey' => $sessionKey
			];
			//echo "<pre>";print_r(json_encode($arr));exit;
			/*-----------------------------------------------------------------------------------------------*/
			if (strlen($sessionKey) != 24) {
				//return self::$IllegalAesKey;
				throw new SmartException("sessionKey不合法");
			}
			$aesKey = base64_decode($sessionKey);

			if (strlen($iv) != 24) {
				//return self::$IllegalIv;
				throw new SmartException("iv不合法");
			}
			$aesIV = base64_decode($iv);
			$aesCipher = base64_decode($encryptedData);
			$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
			$dataObj = json_decode($result);
			if($dataObj  == NULL) {
				throw new SmartException("解密后的数据为空");
			}
			if($dataObj->watermark->appid != self::APPID) {
				throw new SmartException("APPID不匹配");
			}
			$this->response(1,array('error'=>0,'data'=>json_decode($result, true)));
    	}
    	catch(Exception $e) {
    		$this->response(1,array('error'=>$e->getCode()?$e->getCode():-1,'msg'=>$e->getMessage()));
    	}
	}

	//获取小程序二维码
	public function actionGetQrcode()
	{
		//header('content-type:image/gif');
		//获取access_token
		//$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::APPID."&secret=".self::APPSECRET;
		//echo $url;exit;
		//$response = $this->curl_get($url);
		//$response = json_decode($response, true);
		$access_token = "11_e3s7-ZOeKLVwsW4-LqCI_xIJ60pxQFuWfqXxDDHJX7AQEeMLikg-8YDP1MDrZazrtq95n9-Gpp9iMhwLpeUNi-q6a4Q38tzU3xFuomVMgd3AgqaRGWQjw8-abK1ZDKC1BWRjyjVs3j74j_vyZMUaAEAPCK";//$response['access_token'];
		//echo "<pre>";print_r($access_token);exit;
		//header('content-type:image/gif');  
		header('Content-Type:image/jpg');
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
		$params = [
			'scene' => 'refrenceCode=lulu&id=1',
			'page' => 'pages/detail',
			'width' => '430'
		];
		$params = json_encode($params);
		$response = $this->api_notice_increment($url,$params);
		$file ="./images/qrcode/".time().".jpg";
        file_put_contents($file,$response);
		//$response = $this->get_http_array($url,$params);
		echo "<pre>";print_r($response);exit;
	}

	//通过微信API拉取所有openid,然后去获取unionId
	public function actionGetUnionid()
	{
		ini_set('max_execution_time', '0');
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::GZ_APPID."&secret=".self::GZ_APPSECRET;
		$response = $this->curl_get($url);
//echo "<pre>";print_r($url);exit;
		$response = json_decode($response, true);
		$access_token = $response['access_token'];

		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=oy1Thsm5fK2YIiTjRdX2jiqaw1oc&lang=zh_CN";
		$response = $this->curl_get($url);
		echo "<pre>";print_r($response);exit;
		
		/***************************************轮询取openid********************************************/
		$openids = [];
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token;
		$response = $this->curl_get($url);
		$response = json_decode($response, true);
		$openids = array_merge($openids, $response['data']['openid']);
		$alreadyCnt = $response['count'];
		//最大用户数量超过10000
//$response['total'] = 7000;
//$alreadyCnt = 7000;
		while ($response['total'] > $alreadyCnt) {
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&next_openid=".$response['next_openid'];
			$response = $this->curl_get($url);
			$response = json_decode($response, true);
			$openids = array_merge($openids, $response['data']['openid']);
			$alreadyCnt += $response['count'];
		}
//echo "<pre>";print_r($openids);exit;
		/***************************************轮询取openid结束********************************************/

		/***************************************轮询取unionid********************************************/
		$arr['user_list'] = $unionids = $rtn = [];
		$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=".$access_token;
		for ($i=0; $i<$alreadyCnt; $i++) {
			$arr['user_list'][] = [
				'openid' => $openids[$i],
				'lang' => 'zh-CN'
			];
			if (($i+1)%100 == 0) {
				$response = $this->get_http_array($url, json_encode($arr));
				if (!isset($response['user_info_list'])) {
					echo "<pre>";print_r($response);
				}
				$unionids = isset($response['user_info_list']) ? array_merge($unionids, $response['user_info_list']) : $unionids;
				$arr['user_list'] = [];
			}
		}
		//拿余下非100整除的数据换取unionid
		if (!empty($arr['user_list'])) {
			$response = $this->get_http_array($url, json_encode($arr));
			$unionids = array_merge($unionids, $response['user_info_list']);
		}
		foreach ($unionids as $k => $unionid) {
			$rtn[$k]['openid'] = $unionid['openid'];
			$rtn[$k]['unionid'] = $unionid['unionid'];
		}
		echo "<pre>";print_r($rtn);exit;
		/***************************************轮询取unionid结束********************************************/
		
	}


	public function curl_get($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $data;
    }

    public function get_http_array($url,$post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   //没有这个会自动输出，不用print_r();也会在后面多个1
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        echo "<pre>";print_r($output);exit;
        $out = json_decode($output, true);
        return $out;
    }

    public function api_notice_increment($url, $data){
        $ch = curl_init();
        $header[] = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        //        exit;
        if (curl_errno($ch)) {
            return false;
        }else{
            // var_dump($tmpInfo);
            return $tmpInfo;
        }
    }
	
}