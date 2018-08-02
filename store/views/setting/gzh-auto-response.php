<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */

//$this->title = '商品发布';
 
$this->registerJsFile("@web/js/setting/gzh-auto-response.js"); 
?>

<style>
.response {
    margin-bottom:5px;
}
.response-list td {
    padding-right: 3px;
    padding-bottom: 3px;
}
</style>

<?php $form = ActiveForm::begin(['id'=>'create-product-form', 'options'=>["enctype" => "multipart/form-data"]]); ?>
<div class="form-group field-spu-desc">
    <label class="control-label" for="spu-desc">公众号自动回复配置</label><br>
    
    <table class="response-list">
        <?php foreach ($wechatConf as $key => $val) { ?>
        <tr class="response" cnt="<?=$key?>">
            <input type="hidden" name="WechatConf[<?=$key?>][id]" value="<?=$val->id?>">
            <td><input type="text" name="WechatConf[<?=$key?>][confKey]" value="<?=$val->confKey?>" class="form-bom response-td" disabled></td>
            <td><input type="text" name="WechatConf[<?=$key?>][confVal]" value="<?=$val->confVal?>" class="form-bom response-td"></td>
        </tr>
        <?php } ?>
    </table>
    
    <br>
    <div class="form-group" style="margin-top:20px;">
        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>