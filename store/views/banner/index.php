<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use store\models\category\Category;
use \zh\qiniu\QiniuFileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */

//$this->title = '商品发布';

$this->registerCssFile("@web/css/product/release-product.css");  
 
$this->registerJsFile("@web/qiniu_ueditor/ueditor.config.js");
$this->registerJsFile("@web/qiniu_ueditor/ueditor.all.min.js");
//$this->registerJsFile("@web/qiniu_ueditor/lang/zh-cn/zh-cn.js");
$this->registerJsFile("@web/js/product/release-product.js"); 
?>
<div class="site-index">
    <div class="body-content">
        <ul class="nav nav-tabs">
            <li <?php if (Yii::$app->request->get('bannerId')==1){echo "class='active'";} ?>><a href="<?=Yii::$app->urlManager->createUrl(['banner/index', 'bannerId'=>1])?>">banner图1</a></li>
            <li <?php if (Yii::$app->request->get('bannerId')==2){echo "class='active'";} ?>><a href="<?=Yii::$app->urlManager->createUrl(['banner/index', 'bannerId'=>2])?>">banner图2</a></li>
            <li <?php if (Yii::$app->request->get('bannerId')==3){echo "class='active'";} ?>><a href="<?=Yii::$app->urlManager->createUrl(['banner/index', 'bannerId'=>3])?>">banner图3</a></li>
        </ul>
        <br>
        <div class="row">
            <div class="col-lg-10">
                <?php $form = ActiveForm::begin(['id'=>'banner-form', 'options'=>["enctype" => "multipart/form-data"]]); ?>
                    <!-- <input type="text" name="spu" value="" class="form-bom " > -->

                    <?= $form->field($banner, 'title')->textInput()->label("标题") ?>
                    <?= $form->field($banner, 'image')->label("图片")->widget(QiniuFileInput::className(),[
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

                    <?= $form->field($banner, 'uri')->label("链接SPUID")->textInput() ?>
                    <?= $form->field($banner, 'sort')->label("排序")->textInput() ?>
                    <br>
                    
                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>
