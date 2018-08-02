<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\models\category\category;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\dialog\DialogAsset;

//$this->registerCssFile("@web/css/product/release-product.css");  
$this->registerJsFile("@web/js/order/order-list.js");  
?>

<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">
                <?php 
                switch ($item) {
                    case 'changePrice':
                    case 'changeFreight':
                        $action = Url::toRoute(['/order/change-price']);
                        break;
                    case 'addMemo':
                        $action = Url::toRoute(['/order/add-memo']);
                        break;
                    case 'applyRefund':
                        $action = Url::toRoute(['/order/apply-refund']);
                        break;
                }
                $form = ActiveForm::begin([
                    'id'=>'update-order-form', 
                    'options'=>["enctype" => "multipart/form-data"],
                    'method' => 'post',
                    'action' => $action,
                ]); ?>
                   <!--  隐藏域存订单ID -->
                    <input type="hidden" name="item" value="<?=$item?>">
                    <?= $form->field($orderRecord, 'id')->textInput()->hiddenInput(['value'=>$orderRecord->id])->label(false); ?>
                    <?php if ($buyingRecord) { 
                        echo $form->field($buyingRecord, 'id')->textInput()->hiddenInput(['value'=>$buyingRecord->id])->label(false); 
                    }?>
                    <!-- 改价 -->
                    <?php if ($item == 'changePrice') { ?>
                        <?= $form->field($orderRecord, 'finalPrice')->textInput()->label('商品价格') ?>
                        <?= $form->field($changeOrderPriceLog, 'memo')->textarea(['rows' => 5])->label('备注') ?>
                    <?php } elseif ($item == 'changeFreight') { ?>
                        <?= $form->field($orderRecord, 'freight')->textInput()->label('运费') ?>
                        <?= HTML::button('免运费', ['class'=>'btn btn-primary', 'id' => 'set_free_freight']) ?>
                        <?= $form->field($changeOrderPriceLog, 'memo')->textarea(['rows' => 5])->label('备注') ?>
                    <?php } elseif ($item == 'addMemo') { ?>
                        <?= $form->field($orderProperty, 'propertyVal')->textarea(['rows' => 5])->label('备注') ?>
                    <?php } else {
                        $price = number_format($buyingRecord->finalPrice*$buyingRecord->buyingCount, 2, '.', '');
                        ?>
                        <?= $form->field($refund, 'price')->textInput()->label("退款金额<span style='color:red'>（该订单实付金额￥{$orderRecord->pay}元，累计退款金额不能超过实付金额）</span>") ?>
                        <?= $form->field($refund, 'applyMemo')->textarea(['rows' => 5])->label('备注') ?>
                    <?php } ?>
                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                        <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
                    </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $("button#set_free_freight").click(function(){ 
            $("#update-order-form #orderrecord-freight").val(0.00);
        });
    });
</script>
