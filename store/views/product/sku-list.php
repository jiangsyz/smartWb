<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\Category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\dialog\DialogAsset;

/* @var $this yii\web\View */

//$this->title = $spu->title;
//$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile("@web/js/product/product-list.js");  
?>

<div class="site-index">
    <div class="body-content">
        <ol class="breadcrumb">
            <li><a href="<?=Yii::$app->urlManager->createUrl(['product/list', 'closed'=>0]);?>">商品列表</a></li>
            <li class="active"><?=$spu->title?></li>
        </ol>
        <div class="row">
            <div class="col-lg-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'layout'=> '{items}<div class="text-right tooltip-demo">{pager}</div>',
                    /*'pager'=>[
                               //'options'=>['class'=>'hidden']//关闭自带分页
                        'firstPageLabel'=>"<<",
                        'prevPageLabel'=>'<',
                        'nextPageLabel'=>'>',
                        'lastPageLabel'=>'>>',
                    ],*/
                    'columns' => [
                        [
                            'attribute' => '标题',
                            'value' => function ($model) { 
                                return $model->title; 
                            }
                        ],
                        [
                            'attribute' => '编码',
                            'value' => function ($model) { 
                                return $model->uniqueId; 
                            }
                        ],
                        [
                            'attribute' => '属性名称',
                            'value' => function ($model) { 
                                return @$model->getProperty()->sourcePropertyConf->cName;
                            }
                        ],
                        [
                            'attribute' => '属性值',
                            'value' => function ($model) { 
                                return @$model->getProperty()->propertyVal!=='' ? $model->getProperty()->propertyVal : ''; 
                            }
                        ],
                        [
                            'attribute' => '库存',
                            'value' => function ($model) { 
                                return $model->count; 
                            }
                        ],
                        [
                            'attribute' => '原价',
                            'value' => function ($model) { 
                                return $model->getLevelPrice(0); 
                            }
                        ],
                        [
                            'attribute' => 'VIP价',
                            'value' => function ($model) { 
                                return $model->getLevelPrice(1); 
                            }
                        ],
                        [
                            "class" => "yii\grid\ActionColumn",
                            "header" => "编辑",
                            "template" => "{base}&nbsp;&nbsp;&nbsp;{count}&nbsp;&nbsp;&nbsp;{price}&nbsp;&nbsp;&nbsp;{vprice}&nbsp;&nbsp;&nbsp;{close}&nbsp;&nbsp;&nbsp;{lock}",
                            "buttons" => [
                                "base" => function ($url, $model, $key) {
                                    return Html::a("基本信息", $url, [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#common-modal',
                                        'class' => 'update-modal',
                                        'data-id' => $key,
                                        'data-url' => Url::toRoute(['/product/get-sku-modal', 'id'=>$key, 'item'=>'basicInfo']),
                                        'data-title' => '基本信息',
                                    ]);
                                },
                                "count" => function ($url, $model, $key) {
                                    return Html::a("库存", $url, [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#common-modal',
                                        'class' => 'update-modal',
                                        'data-id' => $key,
                                        'data-url' => Url::toRoute(['/product/get-sku-modal', 'id'=>$key, 'item'=>'count']),
                                        'data-title' => '库存',
                                    ]);
                                },
                                "price" => function ($url, $model, $key) {
                                    return Html::a("原价", $url, [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#common-modal',
                                        'class' => 'update-modal',
                                        'data-id' => $key,
                                        'data-url' => Url::toRoute(['/product/get-sku-modal', 'id'=>$key, 'item'=>'price']),
                                        'data-title' => '价格',
                                    ]);
                                },
                                "vprice" => function ($url, $model, $key) {
                                    return Html::a("VIP价", $url, [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#common-modal',
                                        'class' => 'update-modal',
                                        'data-id' => $key,
                                        'data-url' => Url::toRoute(['/product/get-sku-modal', 'id'=>$key, 'item'=>'vprice']),
                                        'data-title' => '价格',
                                    ]);
                                },
                                "close" => function ($url, $model, $key) {
                                    return Html::a($model->closed?"<span style='color:red'>上架</span>":"下架", "javascript:;", ["onclick"=>"closeLockSku('closed', ".$model->id.");"]); },
                                "lock" => function ($url, $model, $key) {
                                    return Html::a($model->locked?"解锁":"锁定", "javascript:;", ["onclick"=>"closeLockSku('locked', ".$model->id.");"]); },
                            ],
                        ],
                    ],
                ]); ?>

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

                <a class="btn btn-primary"  href="<?=Yii::$app->urlManager->createUrl(['product/list', 'closed'=>0]);?>" role="button" id="sku-return">返回</a>
            </div>

        </div>
    </div>
</div>
