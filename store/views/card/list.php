<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\Category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\grid\CheckboxColumn;

/* @var $this yii\web\View */

//$this->title = '出售中的商品';

$this->registerJsFile("@web/js/card/card.js");  
?>
<div class="site-index">

    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout'=> '{items}<div class="text-right tooltip-demo">{pager}</div>',
                    'pager'=>[
                               //'options'=>['class'=>'hidden']//关闭自带分页
                        'firstPageLabel'=>"<<",
                        'prevPageLabel'=>'<',
                        'nextPageLabel'=>'>',
                        'lastPageLabel'=>'>>',
                    ],
                    'columns' => [
                        [
                            'class'=>CheckboxColumn::className(),
                            'name'=>'id',
                            'headerOptions' => ['width'=>'30'],
                             'checkboxOptions' => function($data, $key, $index, $column) {
                                return ['value' => $data['id']];
                            }
                            /*'value' => function ($data) { 
                                return $data['cover']; 
                            }*/
                        ],
                        [
                            'label' => '图片',
                            'format' => [
                                'image', 
                                [
                                'width'=>'60',
                                'height'=>'60'
                                ]
                            ],
                            'value' => function ($data) { 
                                return $data['cover'] ? $data['cover'] : ''; 
                            }
                        ],
                        [
                            'attribute' => '标题',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '20%'],
                            'value' => function ($data) { 
                                return $data['title']; 
                            }
                        ],
                        [
                            'attribute' => '价格',
                            'value' => function ($data) { 
                                return number_format($data['price'], 2, '.', ''); 
                            }
                        ],
                        [
                            'attribute' => '是否上架',
                            'value' => function ($data) { 
                                return $data['closed'] ? '否' : '是'; 
                            }
                        ],
                        [
                            'attribute' => '是否锁定',
                            'value' => function ($data) { 
                                return $data['locked'] ? '是' : '否'; 
                            }
                        ],
                        [
                            "class" => "yii\grid\ActionColumn",
                            "header" => "操作",
                            "template" => "{update}&nbsp;&nbsp;{close}&nbsp;&nbsp;{lock}",
                            "buttons" => [
                                "update" => function ($url, $data, $key) {
                                    return Html::a("编辑", Yii::$app->urlManager->createUrl(['card/update', 'id'=>$data['id']]));},
                                "close" => function ($url, $data, $key) {
                                    $updateVal = $data['closed'] ? 0 : 1;
                                    return Html::a($data['closed']?"上架":"下架", "javascript:;", ["onclick"=>"closeLockCard('closed', ".$updateVal.",".$data['id'].");"]); },
                                "lock" => function ($url, $data, $key) {
                                    $updateVal = $data['locked'] ? 0 : 1;
                                    return Html::a($data['locked']?'<span style="color:red">解锁':'锁定', "javascript:;", ["onclick"=>"closeLockCard('locked', ".$updateVal.",".$data['id'].");"]); },
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>
