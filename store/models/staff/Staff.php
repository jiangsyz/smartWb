<?php
namespace store\models\staff;

use yii\db\ActiveRecord;
use Yii;
use linslin\yii2\curl;
use store\models\Common;
use yii\base\SmartException;

class Staff extends ActiveRecord
{
    //================================
    //表的映射
    public static function tableName()
    {
        return '{{%staff}}';
    }
    //================================
    //验证规则
    public function rules()
    {
        return [
            [['phone', 'pwd'], 'required'],
            //['rememberMe', 'boolean'],
            ['pwd', 'validatePassword'],
        ];
    }
    //================================
    //回调函数，判断密码和手机号
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $data = self::find()->where('phone=:phone AND pwd=:pwd', [':phone' => $this->phone, ':pwd' => md5($this->pwd)])->one();
            //echo "<pre>";print_r($data);exit;
            if (empty($data)) {
                $this->addError('phone', '用户名或密码错误');
            } else {
                $this->id = $data->id;
            }
        }
    }
    //===============================
    //登录
    public function login($post)
    {
        if ($this->load($post) && $this->validate()) {
            //用户信息存放session
            $session = Yii::$app->session;
            //获取token
            $apiUrl = Yii::$app->params['apiUrl'].'?r=staff/api-get-token';
            $params = [
                'phone' => (string)($this->phone),
                'pwd' => (string)(md5($this->pwd)),
            ];
            $response = Common::post($apiUrl, $params);
            if ($response['error'] == 0) {
                $session['staff'] = [
                    'phone' => $this->phone,
                    'pwd' => $this->pwd,
                    'isLogin' => 1,
                    'staffId' => $this->id,
                    'token' => $response['data']['token'],
                    'tokenTimeout' => $response['data']['timeOut'],
                ];
            } else {
                throw new SmartException($response['msg']);
            }
            return (bool)$session['staff']['isLogin'];
        }
        return false;
    }

}
