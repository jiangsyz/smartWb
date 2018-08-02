<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\Category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\grid\CheckboxColumn;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */

//$this->title = '出售中的商品';

$this->registerJsFile("@web/js/product/product-list.js");  
?>
<div class="site-index">

    <div class="body-content">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if (Yii::$app->request->get('closed') == 0 ) { ?>
                <button type="button" class="btn btn-primary" id="batch-close">批量下架</button>
                <?php } else { ?>
                <button type="button" class="btn btn-primary batch-open" id="batch-open">批量上架</button>
                <?php } ?>
                <br>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'layout'=> '{items}<div class="text-right tooltip-demo">{pager}</div>',
                    'pager'=>[
                        //'options'=>['class'=>'hidden'],//关闭自带分页
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
                            'attribute' => '商品ID',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '3%'],
                            'value' => function ($data) { 
                                return $data['id'];
                            }
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
                            'attribute' => '编码',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '15%'],
                            'value' => function ($data) { 
                                return $data['uniqueId']; 
                            }
                        ],
                        [
                            'attribute' => '描述',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '20%'],
                            'value' => function ($data) { 
                                return $data['desc']; 
                            }
                        ],
                        [
                            'attribute' => '价格',
                            'value' => function ($data) { 
                                return number_format($data['price'], 2, '.', ''); 
                            }
                        ],
                        [
                            'attribute' => '库存',
                            'value' => function ($data) { 
                                return $data['count']; 
                            }
                        ],
                        [
                            'attribute' => '发布时间',
                            'value' => function ($data) { 
                                return $data['createTime'] ? date('Y-m-d H:i:s', $data['createTime']) : ''; 
                            }
                        ],
                        [
                            "class" => "yii\grid\ActionColumn",
                            "header" => "操作",
                            "template" => "{update}&nbsp;&nbsp;{skulist}&nbsp;&nbsp;{close}&nbsp;&nbsp;{lock}&nbsp;&nbsp;{qrcode}",
                            "buttons" => [
                                "update" => function ($url, $data, $key) {
                                    // return Html::a("编辑", Yii::$app->urlManager->createUrl(['product/update', 'id'=>$data['id']], ['target'=>'_blank', 'data' => ['pjax' => '0']]));},
                                    $url = Yii::$app->urlManager->createUrl(['product/update', 'id'=>$data['id']]);
                                    return '<a href="'.$url.'" target="_blank">编辑</a>';},
                                "skulist" => function ($url, $data, $key) {
                                    return Html::a("SKU列表", Yii::$app->urlManager->createUrl(['product/sku-list', 'id'=>$data['id']]));},
                                "close" => function ($url, $data, $key) {
                                    $updateVal = $data['closed'] ? 0 : 1;
                                    return Html::a($data['closed']?"上架":"下架", "javascript:;", ["onclick"=>"closeLockSpu('closed', ".$updateVal.",".$data['id'].");"]); },
                                "lock" => function ($url, $data, $key) {
                                    $updateVal = $data['locked'] ? 0 : 1;
                                    return Html::a($data['locked']?'<span style="color:red">解锁':'锁定', "javascript:;", ["onclick"=>"closeLockSpu('locked', ".$updateVal.",".$data['id'].");"]); },
                                "qrcode" => function ($url, $data, $key) {
                                    return Html::a("二维码", $url, [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#common-modal',
                                        'class' => 'update-modal',
                                        'data-id' => $data['id'],
                                        'data-url' => Url::toRoute(['/product/get-qrcode', 'id'=>$data['id']]),
                                        'data-title' => $data['title'],
                                    ]);
                                }
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>

 <?php 
    Modal::begin([
        'id' => 'common-modal',
        'header' => '<h4 class="modal-title"></h4>',
        /*'footer' =>  '<a href="#" class="btn btn-primary" data-dismiss="modal">保存</a><a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',*/
    ]);

$js = <<<JS

    $(".update-modal").click(function(){ 
        $('.modal-title').html('');
        $('.modal-body').html('');
        var url= $(this).attr('data-url');
        var title = $(this).attr('data-title');
        $.get(url, {}, function (data) {
            $('.modal-title').html(title);
            $('.modal-body').html(data);
            //$($(this).attr('data-target')+" .modal-title").text(title);
            //$($(this).attr('data-target')).modal("show").find(".modal-body").html(data); 
            return false;
        });
    }); 
JS;
    $this->registerJs($js);

    Modal::end(); 
    ?>  
