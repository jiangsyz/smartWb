<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
?>

<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $form = ActiveForm::begin([
                    'id'=>'modal-form',
                    'options'=>["enctype" => "multipart/form-data"],
                    'method' => 'post',
                    'action' => Url::toRoute('member/update-nickname'),
                ]); ?>
                   <!--  隐藏域存订单ID -->
                    <input type="hidden" name="id" value="<?=$member->id?>">   
                    <div class="form-group field-modal-text required">
                        <label class="control-label" for="modal-tex">昵称</label>
                        <?= $form->field($member, 'nickName')->textInput()->label(false) ?>
                        <p class="help-block help-block-error"></p>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                        <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
                    </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
