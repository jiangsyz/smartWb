<?php
namespace store\models;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use linslin\yii2\curl;

//========================================
class Common extends SmartActiveRecord
{
    //判断token是否过期，过期的话重新取一次
    public static function tokenIsTimeout()
    {
        if (!@Yii::$app->session['staff']['tokenTimeout'] || Yii::$app->session['staff']['tokenTimeout'] < time()) {
            $apiUrl = Yii::$app->params['apiUrl'].'?r=staff/api-get-token';
            $params = [
                'phone' => (string)(Yii::$app->session['staff']['phone']),
                'pwd' => (string)(md5(Yii::$app->session['staff']['pwd']))
            ];
            $response = self::post($apiUrl, $params);
            $response = json_decode($response, true);
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
    }
    
    //封装好的zs api post请求
    public static function post($url, $params)
    {
        $curl = new curl\Curl();
        $apiSecret = Yii::$app->params['apiSecret'];

        //添加时间戳
        $params['requestTime'] = (string)(self::curl_get(Yii::$app->params['apiUrl']."?r=test"));
        //字典序升序排列
        ksort($params);
        $sign = json_encode($params, JSON_UNESCAPED_UNICODE) . '^' . $apiSecret;
        $sign = md5($sign);
        $params['signature'] = $sign;
        $response = $curl->setPostParams($params)->post($url);
        //echo "<pre>";print_r($response);exit;
        $response = json_decode($response, true);
        if (!isset($response['error'])) {
            Yii::$app->session->setFlash('danger', '网络请求失败');
        } else {
            if($response['error'] != 0){
                if ($response['error'] == -1) {
                    if ($response['msg'] == 'miss token') {
                        Yii::$app->staff->logout();  
                        header("location:".Yii::$app->urlManager->createUrl(['site/login']));
                    }
                    Yii::$app->session->setFlash('danger', '系统错误');
                } elseif ($response['error'] == -2) {
                    Yii::$app->session->setFlash('danger', $response['msg']);
                } else {
                    Yii::$app->session->setFlash('danger', 'request error(' . $response['error'] . ')');
                }
            } 
        }

        return $response;
    }

    //封装好的zs api get请求
    public static function get($url, $params)
    {
        $curl = new curl\Curl();
        $apiSecret = Yii::$app->params['apiSecret'];
        //添加时间戳
        $params['requestTime'] = (string)(self::curl_get(Yii::$app->params['apiUrl']."?r=test"));
        //字典序升序排列
        ksort($params);
        $sign = json_encode($params, JSON_UNESCAPED_UNICODE).'^'.$apiSecret;
        //echo "<pre>";print_r($sign)."<br>";exit;
        $sign = md5($sign);
        $params['signature'] = $sign;
        $data = http_build_query($params);
        $response = $curl->get($url.$data);
        //echo "<pre>";print_r($response);exit;
        $response = json_decode($response, true);
        //echo "<pre>";var_dump($params);
        if (!isset($response['error'])) {
            Yii::$app->session->setFlash('danger', '网络请求失败');
        }
        if ($response['error'] == -1) {
            if ($response['msg'] == 'miss token') {
                Yii::$app->staff->logout();  
                header("location:".Yii::$app->urlManager->createUrl(['site/login']));
            }
            Yii::$app->session->setFlash('danger', '系统错误');
        }
        if ($response['error'] == -2) {
            Yii::$app->session->setFlash('danger', $response['msg']);
        }
        return $response;
    }

    //普通curl get
    public static function curl_get($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $data;
    }

    public static function get_http_array($url,$post_data) {
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

    //请求微信二维码
    public static function api_notice_increment($url, $data){
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