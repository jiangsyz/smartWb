<?php
namespace store\controllers;
use Yii;
use yii\web\SmartWebController;
use yii\base\Exception;
use yii\base\SmartException;
use store\models\banner\Banner;
use store\models\setting\WechatConf;
use store\models\Staff;

class SettingController extends BaseController
{
	//设置公众号自动回复
	public function actionGzhAutoResponse()
	{
		try {
			$postData = Yii::$app->request->post();
			if (Yii::$app->request->isPost) {
				//echo "<pre>";print_r($postData);exit;
				foreach ($postData['WechatConf'] as $val) {
					if ($val['confVal'] == '') {
						throw new SmartException("自动回复内容不能为空");
					}
					if (isset($val['id'])) {
						$model = WechatConf::find()->where("id={$val['id']}")->one();
						//$model->confKey = $val['confKey'];
						$model->confVal = $val['confVal'];
						$model->save(false);
					} else {
						$model = new WechatConf();
						$model->confKey = $val['confKey'];
						$model->confVal = $val['confVal'];
						$model->save(false);
					}
				}
				return $this->redirect(Yii::$app->request->referrer);
			} else {
				$wechatConf = WechatConf::find()->all();
				foreach ($wechatConf as $val) {
					if ($val->confKey == 'newUserResponse') {
						$val->confKey = "新用户关注：";
					} else {
						$val->confKey = "老用户键盘输入：";
					}
				}
				//echo "<pre>";print_r($wechatConf);exit;
				return $this->render('gzh-auto-response', [
					'wechatConf' => $wechatConf
				]);
			}
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
        	return $this->redirect(Yii::$app->request->referrer);
		}
	}

	public function actionDeleteGzhAutoResponse()
	{
		try {
			$id = Yii::$app->request->get('id');
			$model = WechatConf::find()->where("id={$id}")->one();
			$model->delete();
			$this->response(1, ['error'=>0]);
		} catch (Exception $e) {
			$this->response(1,array('error'=>-1,'msg'=>$e->getMessage()));
		}
	}


	//修改密码
    public function actionChangePassword()
    {
        try {
            if (Yii::$app->request->post()) {
                $oldPassword = Yii::$app->request->post('oldpassword');
                $newPassword = Yii::$app->request->post('newpassword');
                if (strlen($newPassword)<6 || strlen($newPassword)>12) {
                	throw new SmartException("密码长度只能在6~12位之间");
                }
                $staffId = Yii::$app->session['staff']['staffId'];
                $staff = Staff::find()->where("id={$staffId}")->one();
                if (md5($oldPassword) != $staff->pwd) {
                    throw new SmartException("原密码错误");
                }
                $staff->pwd = md5($newPassword);
                if (!$staff->save()) {
                    throw new SmartException("修改密码失败");
                }
                //Yii::$app->session->setFlash('success', "修改成功");
            	Yii::$app->staff->logout();  
        		return $this->goHome();
            } else {
                return $this->render('change-password');  
            }
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
	
}