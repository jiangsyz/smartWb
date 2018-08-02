<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\models\category\category;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\dialog\DialogAsset;

//$this->registerCssFile("@web/css/product/release-product.css");  
$this->registerJsFile("@web/js/refund/refund-list.js");  
?>

<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
                <?php 
                $form = ActiveForm::begin([
                    'id'=>'update-refund-form', 
                    'options'=>["enctype" => "multipart/form-data"],
                    'method' => 'post',
                    'action' => Yii::$app->urlManager->createUrl(['refund/update-refund-status']),
                ]); ?>
                    <!--  隐藏域存退款ID -->
                    <input type="hidden" name="id" value="<?=$id?>">
                    <input type="hidden" name="status" value="-1">
                    
                    <?= $form->field($refund, 'rejectMemo')->textarea(['rows' => 5])->label('驳回原因') ?>
       
                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                        <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
                    </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
