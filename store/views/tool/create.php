<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */

//$this->registerJsFile("@web/qiniu_ueditor/lang/zh-cn/zh-cn.js");

?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-10">
                <?php $form = ActiveForm::begin(['id'=>'create-goods-form', 'options'=>["enctype" => "multipart/form-data"]]); ?>
                    <!-- <input type="text" name="spu" value="" class="form-bom " > -->
                    <?= $form->field($model, 'logistics_id')->label('物流渠道')->dropDownlist($logisticsTree) ?>

                    <?= $form->field($model, 'name')->textInput() ?>

                    <?= $form->field($model, 'code')->textInput() ?>

                    <?= $form->field($model, 'unit')->textInput() ?>

                    <?= $form->field($model, 'cost')->textInput() ?>
 
                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>
