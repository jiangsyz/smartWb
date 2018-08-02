<?php
namespace store\controllers;

use store\models\Common;
use Yii;
use yii\web\SmartWebController;
use yii\base\SmartException;
use yii\base\Exception;
use store\models\model\Source;
use store\models\member\Member;
use store\models\member\MemberLv;
use store\controllers\BaseController;
use store\models\member\Address;
use yii\data\ActiveDataProvider;
use store\models\order\OrderRecord;

class MemberController extends BaseController
{
	//会员管理
	public function actionList()
	{
        $member = new Member();
        $dataProvider = $member->search(Yii::$app->request->get());
        $models = $dataProvider->getModels();

        $now = time();
        $vipCardInfo = [];
        foreach ($models as $item) {
            $vipCards = MemberLv::find()
                ->where(['memberId' => $item['id']])
                ->andWhere(['closed' => 0])
                ->andWhere(['>=', 'end', $now])
                ->asArray()->all();
            if (!empty($vipCards)) {
                $vipCardsStart = $vipCardsEnd = [];
                foreach ($vipCards as $card) {
                    $vipCardsStart[] = $card['start'];
                    $vipCardsEnd[] = $card['end'];
                }
                $vipCardInfo[$item['id']] = [
                    'vipStart' => min($vipCardsStart),
                    'vipEnd' => max($vipCardsEnd),
                ];;
            }
        }
        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'vipCardInfo' => $vipCardInfo,
        ]);
	}


	//查看会员详情
	public function actionView()
	{
		try {
			$id = Yii::$app->request->get('id');
			$member = Member::findOne($id);
			$memberLvs = MemberLv::find()->where("memberId={$id}")->all();
			$address = Address::find()->where("memberId={$id}")->all();
			$memberLv = reset($memberLvs);
			//echo "<pre>";print_r($memberLvs);exit;
			return $this->render('view', [
				'member' => $member,
				'memberLvs' => $memberLvs,
				'address' => $address,
			]);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
	}

	//锁定会员
	public function actionLock()
	{
		try {
			$memberId = Yii::$app->request->get('id');
			$member = Member::findOne($memberId);
			if (!$member) {
				throw new SmartException("用户不存在");
			}
			$member->locked = !$member->locked;
			if (!$member->save(false)) {
				throw new SmartException("操作失败");
			}
			return $this->redirect(Yii::$app->request->referrer);
		} catch (Exception $e) {
			Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
		}
		
	}

    /**
     * 取消会员卡
     *
     * @return \yii\web\Response
     */
    public function actionCancelVip()
    {
        try {
            if (!Yii::$app->session['staff']['token']) {
                throw new SmartException("权限有误");
            }

            $id = Yii::$app->request->post('id');
            $remark = Yii::$app->request->post('remark');
            if (empty($remark)) {
                throw new SmartException("备注不能为空");
            }
            $action = Yii::$app->request->post('action');
            if (empty($id) || empty($action)) {
                throw new SmartException("参数错误");
            }

            if ($action == 'cancelVip') {
                $r = MemberLv::findOne($id);
                if (!$r) {
                    throw new SmartException("数据不存在");
                }
                $url = Yii::$app->params['apiUrl'] . '?r=member/api-close-vip';
                $params = [
                    'token' => Yii::$app->session['staff']['token'],
                    'memberLvId' => (string)$r->id,
                    'memo' => (string)$remark,
                ];
                Common::post($url, $params);
            } else {
                throw new SmartException("action错误");
            }
            return $this->redirect(Yii::$app->request->referrer);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 弹框
     *
     * @return string|\yii\web\Response
     */
    public function actionGetModal()
    {
        try {
            $action = Yii::$app->request->get('action');
            if (!$action) {
                throw new SmartException('缺少action参数');
            }
            $id = Yii::$app->request->get('id');
            if (!$id) {
                throw new SmartException('缺少id参数');
            }

            $item = MemberLv::findOne($id);
            return $this->renderAjax('modal', [
                'item' => $item,
                'action' => $action,
            ]);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    public function actionNicknameModal()
    {
        try {
            $id = Yii::$app->request->get('id');
            $member = Member::find()->where("id={$id}")->one();
            return $this->renderAjax('nickname-modal', [
                'member' => $member,
            ]);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    //客服获取用户真实姓名后，更新到昵称中
    public function actionUpdateNickname()
    {
        try {
            $id = Yii::$app->request->post('id');
            $nickname = Yii::$app->request->post('Member')['nickName'];
            if (empty($nickname)) {
                throw new SmartException("昵称不能为空");
            }
            if (empty($id)) {
                throw new SmartException("id不能为空");
            }
            $member = Member::find()->where("id={$id}")->one();
            if (!$member) {
                throw new SmartException("用户不存在");
            }
            $member->nickName = $nickname;
            if (!$member->save(false)) {
                throw new SmartException("操作失败");
            }
            return $this->redirect(Yii::$app->request->referrer);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
}