<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\models\category\category;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\dialog\DialogAsset;

//$this->registerCssFile("@web/css/product/release-product.css");  
$this->registerJsFile("@web/js/product/product-list.js");  
?>

<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-10">
                <?php $form = ActiveForm::begin([
                    'id'=>'create-product-form', 
                    'options'=>["enctype" => "multipart/form-data"],
                    'method' => 'post',
                    'action' => Url::toRoute(['/product/update-sku']),
                ]); ?>
                   <!--  隐藏域存放SKUID -->
                    <input type="hidden" name="item" value="<?=$item?>">
                    <?= $form->field($sku, 'id')->textInput()->hiddenInput(['value'=>$sku->id])->label(false); ?>
                    <!-- 修改SKU基本信息 -->
                    <?php if ($item == 'basicInfo') { ?>
                        <?= $form->field($sku, 'title')->textInput() ?>
                        <?= $form->field($sku, 'uniqueId')->textInput() ?>
                        <?= $form->field($sourceProperty, 'id')->textInput()->hiddenInput(['value'=>$sourceProperty->id])->label(false); ?>
                        <?= $form->field($sourceProperty, 'propertyKey')->dropDownList($sourceProConfTree) ?>
                        <?= $form->field($sourceProperty, 'propertyVal')->textInput() ?>
                    <?php } ?>
                    <!-- 修改SKU库存 -->
                    <?php if ($item == 'count') { ?>
                        <?= $form->field($sku, 'count')->textInput() ?>
                    <?php } ?>
                    <!-- 修改SKU原价 -->
                    <?php if ($item == 'price') { ?>
                        <?= $form->field($skuMemberPrice, 'price')->label('原价')->textInput() ?>
                    <?php } ?>
                     <!-- 修改SKU的VIP价 -->
                    <?php if ($item == 'vprice') { ?>
                        <?= $form->field($skuMemberPrice, 'price')->label('VIP价')->textInput() ?>
                    <?php } ?>

                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                        <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
                    </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
