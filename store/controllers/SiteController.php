<?php
namespace store\controllers;
use Yii;
use yii\web\SmartWebController;
use store\models\member\Member;
use store\models\order\OrderRecord;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use store\models\Staff;
use store\models\StaffLoginForm;
use store\models\Common;
use yii\base\SmartException;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $yesterday = strtotime("-1 day");
        $today = strtotime("today")-1;
        $lastmonth = strtotime(date('Y').'-'.date('m').'-01');
        $now = time();
        //昨日新增订单
        $newOrderCnt = OrderRecord::find()->where("code is not null and createTime between {$yesterday} and {$today}")->count();
        //昨日新增用户
        $newUserCnt = Member::find()->where("createTime between {$yesterday} and {$today}")->count();
        //本月订单量
        $monthOrderCnt = OrderRecord::find()->where("createTime between {$lastmonth} and {$now} and code is not null")->count();
        //本月销售额
        $monthSalesAmount = OrderRecord::find()->select("sum(pay) as pay")->where("code is not null and createTime between {$lastmonth} and {$now} and payStatus=1 and cancelStatus=0 and closeStatus=0 and finishStatus=1")->asArray()->one();
        $monthSalesAmount = $monthSalesAmount['pay']/100;
        //echo $monthSalesAmount;exit;
        
//echo "<pre>";print_r($dateStr);exit;
        //$amountArr = array_values($amountArr);
        //echo "<pre>";print_r($monthSalesAmount);exit;
        return $this->render('index', [
            'newOrderCnt' => $newOrderCnt,
            'newUserCnt' => $newUserCnt,
            'monthOrderCnt' => $monthOrderCnt,
            'monthSalesAmount' => $monthSalesAmount,
        ]);
    }

    public function actionGetSalesAmount()
    {
        $today = strtotime("today")-1;
        //构造日期数组
        $dateArr = [];
        for ($i=10; $i>0; $i--) {
            $dateArr[] = date('m-d',strtotime('-'.$i.' days'));
        }
        //找近10天的所有订单
        $stime = strtotime('-10 days');
        $amountArr = [];
        $orders = OrderRecord::find()->where("code is not null and createTime between {$stime} and {$today} and payStatus=1 and cancelStatus=0 and closeStatus=0 and finishStatus=1")->all();
        foreach ($orders as $order) {
            $date = date('m-d', $order->createTime);
            if (isset($amountArr[$date])) {
                $amountArr[$date] += $order->pay/100;
            } else {
                $amountArr[$date] = $order->pay/100;
            }
        }
        foreach ($dateArr as $date) {
            if (!isset($amountArr[$date])) {
                $amountArr[$date] = 0;
            }
        }
        $amountArr = array_values($amountArr);
        //echo "<pre>";print_r($amountArr);exit;
        echo json_encode(['date'=>$dateArr, 'amount'=>[['name'=>'销售额', 'type'=>'bar', 'data'=>$amountArr]]]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        //if (!\Yii::$app->user->isGuest)  
        if (Yii::$app->session['staff']['phone']) {
            return $this->redirect(['site/index']);
        }
  
        $model = new StaffLoginForm();//管理员登录表单  
        $post = Yii::$app->request->post();
        if($post) {
            if ($model->load($post) && $model->login()) {
                //登录成功
                //用户信息存放session
                $session = Yii::$app->session;
                //获取token
                $apiUrl = Yii::$app->params['apiUrl'].'?r=staff/api-get-token';
                $params = [
                    'phone' => (string)($model->phone),
                    'pwd' => (string)(md5($model->pwd)),
                ];
                $response = Common::post($apiUrl, $params);
                if ($response['error'] == 0) {
                    $session['staff'] = [
                        'phone' => $model->phone,
                        'pwd' => $model->pwd,
                        'isLogin' => 1,
                        'staffId' => Staff::find()->where("phone='{$model->phone}'")->one()->id,
                        'token' => $response['data']['token'],
                        'tokenTimeout' => $response['data']['timeOut'],
                    ];
                } else {
                    throw new SmartException($response['msg']);
                }
                return $this->redirect(['site/index', 'closed'=>0]);
            }
        }  
        //$this->layout = false; //不使用布局  
        return $this->render('login', [
            'model' => $model,  
        ]);  


        /*try {
            if (isset(Yii::$app->session['staff']['isLogin'])) {
                return $this->redirect(['product/list', 'closed'=>0]);
            }
            $model = new Staff();
            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                if ($model->login($post)) {
                    return $this->redirect(['product/list', 'closed'=>0]);
                }
            }
            return $this->render('login', ['model' => $model]);
        } catch (Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }*/
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        //Yii::$app->user->logout();  
        Yii::$app->staff->logout();  
        return $this->goHome();
        /*//删除session
        Yii::$app->session->removeAll();
        if (!isset($session['staff']['isLogin'])) {
            //跳转页面
            $this->redirect(['site/login']);
            Yii::$app->end();
        }
        //返回
        $this->goback();*/
    }

}