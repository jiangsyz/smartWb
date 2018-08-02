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
               <!--  隐藏域存订单ID -->
                <input type="hidden" name="oid" id="oid" value="<?=Yii::$app->request->get('id')?>">
                <label>收货人姓名</label><br>
                <input type="text" value="<?=$address['name']?>" id="name"><br><br>

                <label>收货人手机</label><br>
                <input type="text" value="<?=$address['phone']?>" id="phone"><br><br>

                <label>省</label>
                <select class="dropdownlist input-group" id="sct-province">
                    <?php 
                    foreach ($provinceModels as $provinceModel) { ?>
                        <option <?php if($provinceModel->area_id==$provinceId){echo "selected";} ?> value="<?=$provinceModel->area_id?>"><?=$provinceModel->area_name?></option>
                    <?php } ?>
                </select><br>
                <label>市</label>
                <select class="dropdownlist input-group" id="sct-city">
                    <?php 
                    foreach ($cityModels as $cityModel) { ?>
                        <option <?php if($cityModel->area_id==$cityId){echo "selected";} ?> value="<?=$cityModel->area_id?>"><?=$cityModel->area_name?></option>
                    <?php } ?>
                </select><br>
                <label>区</label>
                <select class="dropdownlist input-group" id="sct-district">
                    <?php 
                    foreach ($districtModels as $districtModel) { ?>
                        <option <?php if($districtModel->area_id==$districtId){echo "selected";} ?> value="<?=$districtModel->area_id?>"><?=$districtModel->area_name?></option>
                    <?php } ?>
                </select><br>
                <br>

                <label>详细地址</label>
                <textarea class="form-control" rows="3" cols="20" id="address"><?=$address['address']?></textarea><br>
                
                <div class="form-group">
                    <a href="#" class="btn btn-primary" id="address-submit">保存</a>
                    <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
                </div>
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
