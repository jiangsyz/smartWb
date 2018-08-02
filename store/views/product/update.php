<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use store\models\category\Category;
use yii\widgets\DetailView;
use \zh\qiniu\QiniuFileInput;
use yii\grid\GridView;
use yii\helpers\Url;
use kartik\dialog\DialogAsset;
use yii\widgets\LinkPager;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */

//$this->title = '商品编辑';

$this->registerCssFile("@web/css/product/release-product.css");
$this->registerJsFile("@web/qiniu_ueditor/ueditor.config.js");
$this->registerJsFile("@web/qiniu_ueditor/ueditor.all.min.js");  
$this->registerJsFile("@web/js/product/release-product.js");
$this->registerJsFile("@web/js/product/product-list.js");
?>
<input type="hidden" id="tab" value="<?=Yii::$app->request->get('tab','spu')?>">
<input type="hidden" id="spuId" value="<?=Yii::$app->request->get('id')?>">

<div class="site-index">
    <div class="body-content">
        <ol class="breadcrumb">
            <li><a href="<?=Yii::$app->urlManager->createUrl(['product/list', 'closed'=>0]);?>">商品列表</a></li>
            <li class="active"><?=$spu->title?></li>
        </ol>

        <ul class="nav nav-tabs">
            <li><a href="#identifier" class="tab-spu">SPU信息</a></li>
            <li><a href="#identifier" class="tab-sku">SKU信息</a></li>
        </ul>
        <br>
        <div class="row div-spu">
            <div class="col-lg-10">
                <!-- <button type="button" class="btn btn-primary" id="batch-close" style="margin-bottom:20px;">预览二维码</button> -->
                <?=Html::a("预览二维码", Yii::$app->urlManager->createUrl(['product/get-qrcode']), [
                    'data-toggle' => 'modal',
                    'data-target' => '#common-modal',
                    'class' => 'update-modal btn btn-primary',
                    'data-id' => $spu->id,
                    'data-url' => Url::toRoute(['/product/get-qrcode', 'id'=>$spu->id]),
                    'data-title' => $spu->title,
                    'style' => 'margin-bottom:20px'
                ]);?>
                <br>

                <?php $form = ActiveForm::begin([
                    'id'=>'create-product-form', 
                    'options'=>[
                        "enctype" => "multipart/form-data",
                        'onsubmit' => 'return validate();'
                    ]]); ?>

                    <?= $form->field($categoryRecord, 'categoryId')->label('分类')->dropDownlist($categoryTree) ?>

                    <?= $form->field($spu, 'logisticsId')->label('物流渠道')->dropDownlist($logisticsTree) ?>

                    <?= $form->field($spu, 'title')->textInput() ?>

                    <?= $form->field($spu, 'uniqueId')->textInput() ?>

                    <?= $form->field($spu, 'desc')->textarea(['rows'=>5]) ?>

                    <?= $form->field($spu, 'cover')->widget(QiniuFileInput::className(),[
                        'uploadUrl' => 'http://up-z0.qiniu.com',//文件上传地址 不同地区的空间上传地址不一样 参见官方文档
                        'qlConfig' => [
                            'accessKey' => Yii::$app->params['qiniu']['accessKey'],
                            'secretKey' => Yii::$app->params['qiniu']['secretKey'],
                            'scope' => Yii::$app->params['qiniu']['bucket'],
                            'cdnUrl' => Yii::$app->params['qiniu']['domain'],//外链域名
                        ],
                        'clientOptions' => [
                            'max' => 5,//最多允许上传图片个数  默认为3
                            'size' => 204800,//每张图片大小
                            'btnName' => 'upload',//上传按钮名字
                            'accept' => 'image/jpeg,image/gif,image/png'//上传允许类型
                        ],
                        'pluginEvents' => [
                            //'delete' => 'function(item){console.log(item)}',
                            //'success' => 'function(res){console.log(res)}'
                        ]
                    ]) ?>       

                    <script id="detail" name="Spu[detail]" type="text/plain" style="height:400px;"></script>
                    <input type="hidden" id="old-detail" value='<?=$spu->detail?>'>

                    <div class="form-group field-spu-desc has-success">
                        <label class="control-label" for="spu-desc">商品规格</label><br>
                        <table class="sku-list">
                            <tr>
                                <th>标题</th><th>编码</th><th>属性名称</th><th>属性值</th><th>价格</th><th>VIP价格</th><th></th>
                            </tr>

                            <?php foreach ($spu->skus as $key => $sku) { ?>
                            <tr class="sku" cnt="<?=$sku->id?>">
                                <td><input type="text" name='Sku[skuid_<?=$sku->id?>][title]' value="<?=$sku->title?>" class="form-bom" disabled></td>
                                <td><input type="text" name='Sku[skuid_<?=$sku->id?>][uniqueId]' value="<?=$sku->uniqueId?>" class="form-bom" disabled></td>
                                <td><input type="text" name='Sku[skuid_<?=$sku->id?>][propertyKey]' value="<?=$sku->getProperty()->sourcePropertyConf->cName?>" class="form-bom" disabled></td>
                                <td><input type="text" name='Sku[skuid_<?=$sku->id?>][propertyVal]' value="<?=$sku->getProperty()->propertyVal?>" class="form-bom" disabled></td>
                                <td><input type="text" name='Sku[skuid_<?=$sku->id?>][price]' value="<?=$sku->getLevelPrice(0)?>" class="form-bom" disabled></td>
                                <td><input type="text" name='Sku[skuid_<?=$sku->id?>][vprice]' value="<?=$sku->getLevelPrice(1)?>" class="form-bom" disabled></td>
                            </tr>
                            <?php } ?>
                        </table>

                        <br>
                        <?= Html::button('+添加规格', ['class' => 'btn btn-primary add-sku']) ?>
                       
                        <!-- <p class="help-block help-block-error"></p> -->
                        
                    </div>

                    <?= $form->field($spu, 'distributeType')->inline()->radioList(['1'=>'冷链', '2'=>'非冷链']) ?>

                    <?= $form->field($spu, 'closed')->label('是否下架 （下架后自动取消热门及推荐）')->inline()->radioList(['0'=>'上架', '1'=>'下架']) ?>

                    <?= $form->field($recommendRecord, 'isHot')->inline()->radioList(['0'=>'否', '1'=>'是']) ?>

                    <?= $form->field($recommendRecord, 'isRecommend')->inline()->radioList(['0'=>'否', '1'=>'是']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>


        <div class="row div-sku" style="display:none">
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
    </div>
</div>
