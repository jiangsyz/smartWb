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
<div class="site-index">
    <div class="body-content">
        <ol class="breadcrumb">
            <li><a href="<?=Yii::$app->urlManager->createUrl(['card/list', 'closed'=>0]);?>">会员卡列表</a></li>
            <li class="active"><?=$model->title?></li>
        </ol>

        <br>
        <div class="row div-spu">
            <div class="col-lg-10">
                <?php $form = ActiveForm::begin(['id'=>'update-card-form', 'options'=>["enctype" => "multipart/form-data"]]); ?>

                    <?= $form->field($model, 'title')->label('标题')->textInput() ?>

                    <?= $form->field($model, 'cover')->label('图片')->widget(QiniuFileInput::className(),[
                        'uploadUrl' => 'http://up-z0.qiniu.com',//文件上传地址 不同地区的空间上传地址不一样 参见官方文档
                        'qlConfig' => [
                            'accessKey' => Yii::$app->params['qiniu']['accessKey'],
                            'secretKey' => Yii::$app->params['qiniu']['secretKey'],
                            'scope' => Yii::$app->params['qiniu']['bucket'],
                            'cdnUrl' => Yii::$app->params['qiniu']['domain'],//外链域名
                        ],
                        'clientOptions' => [
                            'max' => 5,//最多允许上传图片个数  默认为3
                            'size' => 2048000,//每张图片大小
                            'btnName' => 'upload',//上传按钮名字
                            'accept' => 'image/jpeg,image/gif,image/png'//上传允许类型
                        ],
                        'pluginEvents' => [
                            //'delete' => 'function(item){console.log(item)}',
                            //'success' => 'function(res){console.log(res)}'
                        ]
                    ]) ?>

                    <?= $form->field($model, 'price')->label('价格')->textInput() ?>

                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
