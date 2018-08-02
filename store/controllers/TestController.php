<?php
namespace store\controllers;

use Yii;
use yii\web\Controller;
use store\models\logistics\Logistics;
use common\models\Excel;
use store\controllers\BaseController;
use store\models\Goods;
use store\models\member\Address;
use store\models\product\Spu;
use store\models\order\OrderRecord;

class TestController extends BaseController
{
    public function actionIndex()
    {
        $parentOrder = OrderRecord::findOne(['id'=>1221]);
        $parentOrder->getAddress('staffMemo');
    }

    //正则提取图片
    public function actionSubImg()
    {
        $images = [];
        $i = 0;
        $goods = Yii::$app->db->createCommand("select * from spu")->queryAll();
        //echo "<pre>";print_r($goods);exit;
        /*$html = '<a href="http://www.baidu.com" target="_blank" title="百度"><img src="http://p33mnuvro.bkt.clouddn.com/uploads/0410/152333922416664591.jpg"/></a><a href="http://www.baidu.com" target="_blank" title="百度"><img src="http://p33mnuvro.bkt.clouddn.com/uploads/0410/152333922466284678.jpg"/></a><img alt="" src="http://p33mnuvro.bkt.clouddn.com/2018/6/5xxczn3js0.jpg"/><img alt="" src="http://p33mnuvro.bkt.clouddn.com/2018/4/65c16brc19.jpg"/><a href="http://p33mnuvro.bkt.clouddn.com/uploads/0625/1529916372129979276.mp4" target="_blank"><img src="http://p33mnuvro.bkt.clouddn.com/uploads/0625/1529919438191328438.jpg" title="uploads/0625/1529919438191328438.jpg" alt="httpp33mnuvro.bkt.clouddn.com20185lp8uqw6s6p.jpg"/></a>';*/
        foreach ($goods as $val) {
            $html = $val['detail'];
            //先找出所有带<a>标签的图片
            $pa = '%<a.*?>(.*?)</a>%si';
            preg_match_all($pa, $html, $match);
            //echo "<pre>";print_r($match[0]);exit;
            foreach ($match[0] as $content) {
                $pat = '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';   
                preg_match_all($pat, $content, $m);
                //echo "<pre>";print_r($m);exit;
                $href = $m[2][0];
                if (strpos($href, 'mp4') !== false) {
                    $images[$i]['video'] = $href;
                    $images[$i]['href'] = '';
                } else {
                    $images[$i]['video'] = '';
                    $images[$i]['href'] = $href;
                }
                $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
                preg_match_all($pregRule, $content, $out,PREG_PATTERN_ORDER);
                $images[$i]['src'] = @$out[1][0];
                $i++;
            }
            //echo "<pre>";print_r($images);exit;
            //再找出所有不带<a>标签的图片
            $content = preg_replace("/<(a.*?)>(.*?)<(\/a.*?)>/si","",$html); 
            $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
            preg_match_all($pregRule, $content, $out,PREG_PATTERN_ORDER);
            
            foreach ($out[1] as $val) {
                $images[$i]['src'] = $val;
                $images[$i]['href'] = $images[$i]['video'] = '';
                $i++;
            }
        }
        
        echo "<pre>";print_r($images);exit;
        return json_encode($images);
    }

    public function actionImportCode()
    {
        Goods::deleteAll();
        $params = Yii::$app->params['express'];
        $brand = Yii::$app->params['code_brand'];
        foreach ($params as $logistics_id => $codeArr) {
            foreach ($codeArr as $code) {
                $model = new Goods();
                $model->logistics_id = (int)($logistics_id);
                $model->code = $code;
                $model->name = $brand[$code];
                $model->save(false);
            }
        }
    }

    public function actionHappyDay()
    {
        $love = "2018-05-30";
        $love_stamp = strtotime($love);
        for ($i=1; $i<=99; $i++) {
            $happy_day_stamp = $love_stamp+100*$i*86400;
            echo (100*$i).'天纪念日 '.date('Y-m-d', $happy_day_stamp)."<br>";
        }
        
    }


}