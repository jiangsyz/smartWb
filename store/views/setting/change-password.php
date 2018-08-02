<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */

//$this->title = '商品发布';
?>

<?php $form = ActiveForm::begin(['id'=>'change-password-form',  'options'=>["enctype" => "multipart/form-data"],]); ?>
<div class="form-group field-spu-desc">
    <label>原密码</label>&nbsp;<input type="password" id="oldpassword" name="oldpassword"><br>
    <label>新密码</label>&nbsp;<input type="password" id="newpassword" name="newpassword"><br>

    <div class="form-group" style="margin-top:20px;">
        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    /*function validate() {
        var newpassword = $("#newpassword").val();
        if (newpassword.length<6 || newpassword.length>12) {
            alert("密码长度必须在6~12位之间");
            return false;
        }
        return true;
    }  */
</script>