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
use store\models\yz\YZGetTokenClient;
use store\models\yz\YZTokenClient;


//有赞数据迁移到我司服务器
class YzApiController extends Controller
{
	const APPID = 'wx4fadd384b39658cd';
	const APPSECRET = '67ec14c4bd96a2386748f9bc127037ee';

	const GZ_APPID = "wx21dca8cae24e856e";
	const GZ_APPSECRET = "a7a7f51b8bc6a0a4224ac7144c0d6693";

	//用户数据
	public function actionGetUsers()
	{
		try {
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);

			$method = 'youzan.scrm.customer.search'; //要调用的api名称
			$api_version = '3.1.0'; //要调用的api版本号
			
			$my_params = [
				'page_size' => 50,
				'page_no' => 500
			];
			$my_files = [];
			$response = $client->post($method, $api_version, $my_params, $my_files);
			echo "<pre>";print_r($response);exit;
			exit;
			echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	//会员卡列表
	public function actionGetCards()
	{
		try {
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);

			$method = 'youzan.scrm.card.list'; //要调用的api名称
			$api_version = '3.0.0'; //要调用的api版本号
			
			$my_params = [
				'page' => 1,
			];
			$my_files = [];
			$response = $client->post($method, $api_version, $my_params, $my_files);
			echo "<pre>";print_r($response);exit;
			exit;
			echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	//会员卡详情
	public function actionCardDetail()
	{
		try {
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);

			$method = 'youzan.scrm.card.get'; //要调用的api名称
			$api_version = '3.0.0'; //要调用的api版本号
			
			$my_params = [
				'card_alias' => '3ngioz3nj9b98E'
			];
			$my_files = [];
			$response = $client->post($method, $api_version, $my_params, $my_files);
			echo "<pre>";print_r($response);exit;
			exit;
			echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	//会员卡对应的会员
	public function actionCardCustomer()
	{
		try {
			ini_set('max_execution_time', '0');
			$card_alias_arr = [
				'27bc57h6cvxoci', //新月卡
				'3nk88zea60z7gA',//双日卡
				'3nsw7t21m1vn0C',//月卡
				'3ngioz3nj9b98E'//年卡
			];
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);

			$method = 'youzan.scrm.customer.search'; //要调用的api名称
			$api_version = '3.0.0'; //要调用的api版本号
			
			//根据3类会员卡 获取所有会员
			$users = [];
			foreach ($card_alias_arr as $card_alias) {
				$page = 1;
				$my_params = [
					'page' => $page,
	    			'card_alias' => $card_alias,
				];
				$my_files = [];
				$response = $client->post($method, $api_version, $my_params, $my_files);
				//如果取不到用户信息了
				while (!empty($response['response']['items'])) {
					$items = $response['response']['items'];
					$users = array_merge($users, $items);
					$page++;
					$my_params = [
						'page' => $page,
		    			'card_alias' => $card_alias,
					];
					$my_files = [];
					$response = $client->post($method, $api_version, $my_params, $my_files);
					//echo "<pre>";print_r($response);exit;
				}
			}
			//从不同的卡里拉取的用户可能重复 所以用mobile+fansid去重
			$user_arr = [];
			foreach ($users as $user) {
				$user['mobile'] = isset($user['mobile']) ? $user['mobile'] : '';
				$user['fans_id'] = isset($user['fans_id']) ? $user['fans_id'] : '';
				$key = $user['mobile'].$user['fans_id'];
				if (!isset($user_arr[$key])) {
					$user_arr[$key] = $user;
				}
			}

			/*foreach ($user_arr as $user) {
				$user['mobile'] = isset($user['mobile']) ? $user['mobile'] : '';
				$user['fans_id'] = isset($user['fans_id']) ? $user['fans_id'] : '';
				$sql = "insert into card(mobile, fansid) 
					values('{$user['mobile']}', '{$user['fans_id']}')";
				Yii::$app->db->createCommand($sql)->execute();
			}*/
			//根据mobile获取会员卡列表
			$method = 'youzan.scrm.customer.card.list'; //要调用的api名称
			$api_version = '3.0.0'; //要调用的api版本号
			$cnt = 0;
			foreach ($user_arr as $key => $user) {
				$mobile = isset($user['mobile']) ? $user['mobile'] : 0;
				$fans_id = isset($user['fans_id']) ? $user['fans_id'] : 0;
				$my_params = [
				    'open_user_id' => 0,
				    'page' => '1',
				    'fans_id' => $fans_id,
				    'mobile' => $mobile,
				];

				$my_files = [];
				$response = $client->post($method, $api_version, $my_params, $my_files);
//echo "<pre>";echo $user['mobile']."<br>";
//print_r($response);echo "-----------------------------------------------------------------------------";

//echo "<pre>";print_r($response);
				if (empty($response['response']['items'])) {
					continue;
				}
				$minDate = $maxDate = 0;
				//单个用户的所有会员卡
				foreach ($response['response']['items'] as $card) {
					//判断这个card_no是不是已经插入过
					$exist_card = Yii::$app->db->createCommand("select * from youzan_card where card_no='{$card['card_no']}'")->queryAll();
					if (!empty($exist_card)) {
						continue;
					}
					//如果不是日卡月卡年卡 就跳过
					if ($card['card_alias'] == '3nk88zea60z7gA') {
						$card_title = "两日卡";
					} else if ($card['card_alias'] == '3nsw7t21m1vn0C') {
						$card_title = "月卡";
					} else if ($card['card_alias'] == '3ngioz3nj9b98E') {
						$card_title = "年卡";
					} else if ($card['card_alias'] == '27bc57h6cvxoci') {
						$card_title = "新月卡";
					} else {
						continue;
					}
					//从不同卡里计算最大时间范围的有效期
					/*if ($minDate > strtotime($card['card_start_time']) || $minDate == 0) {
						$minDate = strtotime($card['card_start_time']);
					}
					if ($maxDate < strtotime($card['card_end_time'])) {
						$maxDate = strtotime($card['card_end_time']);
					}
					$minDate = date('Y-m-d H:i:s', $minDate);
					$maxDate = date('Y-m-d H:i:s', $maxDate);*/
					$minDate = strtotime($card['card_start_time']);
					$maxDate = strtotime($card['card_end_time']);
					$sql = "insert into youzan_card(card_no, mobile, card_alias, card_title, start_time, end_time, fansid) values('{$card['card_no']}', '{$mobile}', '{$card['card_alias']}', '{$card_title}', '{$minDate}', '{$maxDate}', {$fans_id})";
					Yii::$app->db->createCommand($sql)->execute();
					$cnt++;
				}
				
			}
			echo "共插入数据".$cnt."条";
			//echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	public function actionGetOpenid()
	{
		try {
			ini_set('max_execution_time', '0');
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);
			$users = Yii::$app->db->createCommand("select * from youzan_card where yz_openid=''")->queryAll();
			foreach ($users as $user) {
				if ($user['fansid']) {
					$method = 'youzan.users.weixin.follower.get'; //要调用的api名称
					$api_version = '3.0.0'; //要调用的api版本号
					$my_params = [
					    'fans_id' => $user['fansid'],
					];
					$my_files = [];
					$response = $client->post($method, $api_version, $my_params, $my_files);
					$openid = $response['response']['user']['weixin_openid'];
					$where = " where fansid={$user['fansid']}";
				} else {
					$method = 'youzan.user.weixin.openid.get'; //要调用的api名称
					$api_version = '3.0.0'; //要调用的api版本号
					$my_params = [
					    'mobile' => $user['mobile'],
					];
					$my_files = [];
					$response = $client->post($method, $api_version, $my_params, $my_files);
					$openid = $response['response']['open_id'];
					$where = " where mobile={$user['mobile']}";
				}
				$sql = "update youzan_card set yz_openid='{$openid}' {$where}";
				Yii::$app->db->createCommand($sql)->execute();
			}
			//echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	public function actionGetUnionid()
	{
		try {
			ini_set('max_execution_time', '0');
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::GZ_APPID."&secret=".self::GZ_APPSECRET;
			$response = $this->curl_get($url);
			$response = json_decode($response, true);
			$access_token = $response['access_token'];
			$users = Yii::$app->db->createCommand("select * from youzan_card where unionid is null")->queryAll();
			//echo "<pre>";print_r($users);exit;
			foreach ($users as $user) {
				$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$user['yz_openid']."&lang=zh_CN";
				//echo $url;exit;
				$response = $this->curl_get($url);
				$response = json_decode($response, true);
				if (isset($response['unionid'])) {
					$sql = "update card set unionid='{$response['unionid']}' where yz_openid='{$user['yz_openid']}'";
					Yii::$app->db->createCommand($sql)->execute();
				}
			}
			//echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	//会员卡对应的会员
	public function actionCustomerCard()
	{
		try {
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);

			$method = 'youzan.scrm.customer.card.list'; //要调用的api名称
			$api_version = '3.0.0'; //要调用的api版本号
			
			$my_params = [
				'open_user_id' => '',
			    'page' => '1',
			    'fans_id' => '4372387132',
			    'mobile' => '',
			    'fans_type' => '0',
			];
			$my_files = [];
			$response = $client->post($method, $api_version, $my_params, $my_files);
			echo "<pre>";print_r($response);exit;
			exit;
			echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
	}

	public function actionGetToken()
	{
	    $clientId = Yii::$app->params['yz']['clientId'];//请填入有赞云控制台的应用client_id
	    $clientSecret = Yii::$app->params['yz']['clientSecret'];//请填入有赞云控制台的应用client_secret
	    $token = new YZGetTokenClient($clientId, $clientSecret);
	    $type = 'self';
	    $keys['kdt_id'] = Yii::$app->params['yz']['kdtId'];//10355212 是正善店铺ID, token有效期为604800秒，就是7天
	    $tmp_token = $token->get_token($type, $keys)['access_token'];
	    return $tmp_token;
	}

	public function actionGetMobile()
	{
		try {
			$token = self::actionGetToken();
			$client = new YZTokenClient($token);

			$method = 'youzan.scrm.customer.get'; //要调用的api名称
			$api_version = '3.1.0'; //要调用的api版本号
			
			$no_mobile = Yii::$app->db->createCommand("select fansid from youzan_card where mobile='' group by fansid")->queryAll();
			foreach ($no_mobile as $val) {
				$my_params = [
				    'account' => '{"account_type":"FansID", "account_id":"'.$val['fansid'].'"}',
				];
				$my_files = [];
				$response = $client->post($method, $api_version, $my_params, $my_files);
				echo "<pre>";print_r($response);
			}
			exit;
			echo json_encode(['error'=>0, 'data'=>$data['data']], JSON_UNESCAPED_UNICODE);
		} catch (Exception $e) {
			echo json_encode(['error'=>-1,'msg'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
		}
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
        $out = json_decode($output, true);
        return $out;
    }
	
}