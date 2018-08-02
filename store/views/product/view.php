<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use store\models\category\category;
use yii\widgets\DetailView;

/* @var $this yii\web\View */

$this->title = '商品详情';

$this->registerCssFile("@web/css/product/release-product.css");  
$this->registerJsFile("@web/js/product/release-product.js");  
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-10">
                <?php $form = ActiveForm::begin(['id'=>'create-product-form', 'options'=>["enctype" => "multipart/form-data"]]); ?>
                   
                    <?= $form->field($spu->getTopCategory()->one(), 'name')->textInput() ?>

                    <?= $form->field($spu, 'title')->textInput() ?>

                    <?= $form->field($spu, 'uniqueId')->textInput() ?>

                    <?= $form->field($spu, 'desc')->textarea(['rows'=>5]) ?>

                    <?=Html::label('封面图','');?>
                    <div style="margin-bottom: 20px;">
                        <img src="<?=$spu->cover?>" height="160" width="160">
                    </div>

                    <?= $form->field($spu, 'detail')->widget(\crazydb\ueditor\UEditor::className()) ?>

                    <div class="form-group field-spu-desc has-success">
                        <label class="control-label" for="spu-desc">商品规格</label><br>
                        <table class="sku-list">
                            <tr>
                                <th>标题</th><th>编码</th><th>属性</th><th>属性值</th><th>库存</th><th>价格</th><th>VIP价格</th>
                            </tr>

                            <?php foreach ($spu->skus as $key => $sku) { ?>
                            <tr class="sku" cnt="0">
                                <td><input type="text" name="sku[0][title]" value="<?=$sku->title?>" class="form-bom"></td>
                                <td><input type="text" name="sku[0][uniqueId]" value="<?=$sku->uniqueId?>" class="form-bom"></td>
                                <td><input type="text" name="sku[0][propertyKey]" value="<?=$sku->getProperty()->propertyKey?>" class="form-bom"></td>
                                <td><input type="text" name="sku[0][propertyVal]" value="<?=$sku->getProperty()->propertyVal?>" class="form-bom"></td>
                                <td><input type="text" name="sku[0][count]" value="<?=$sku->count?>" class="form-bom"></td>
                                <td><input type="text" name="sku[0][price]" value="<?=$sku->getLevelPrice(0)?>" class="form-bom"></td>
                                <td><input type="text" name="sku[0][vprice]" value="<?=$sku->getLevelPrice(1)?>" class="form-bom"></td>
                            </tr>
                            <?php } ?>
                        </table>
                       
                        <!-- <p class="help-block help-block-error"></p> -->
                        
                    </div>

                    <?php $spu->closed = 0; $spu->locked = 0; $spu->distributeType = 1;?>

                    <?= $form->field($spu, 'distributeType')->inline()->radioList(['1'=>'冷链', '2'=>'非冷链']) ?>

                    <?= $form->field($spu, 'closed')->inline()->radioList(['0'=>'否', '1'=>'是']) ?>

                    <?= $form->field($spu, 'locked')->inline()->radioList(['0'=>'否', '1'=>'是']) ?>

                    <div class="form-group">
                        <?= Html::a('返回', Yii::$app->urlManager->createUrl(['product/list', 'closed'=>0]) ,['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>
