<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker; 


$this->registerJsFile("@web/js/product/search-product.js"); 
?>
<style>
    table {
        font-size: 14!important;
    }
</style>

<div class="product-search">

    <?=Html::beginForm(Yii::$app->urlManager->createUrl(['product/list', 'closed'=>Yii::$app->request->get('closed')]),'get',['id'=>'search-product-form','class'=>'form-inline']);?>

    <?=Html::label('标题','');?>
    <?=Html::textInput('title',Yii::$app->request->get('title'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

    <?=Html::label('编码','');?>
    <?=Html::textInput('uniqueId',Yii::$app->request->get('uniqueId'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

    <?=Html::label('价格','');?>
    <?=Html::textInput('minPrice',Yii::$app->request->get('minPrice'),['class'=>'form-control','style'=>'width:80px']);?> - <?=Html::textInput('maxPrice',Yii::$app->request->get('maxPrice'),['class'=>'form-control','style'=>'width:80px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

    <?=Html::label('库存','');?>
    <?=Html::textInput('minCount',Yii::$app->request->get('minCount'),['class'=>'form-control','style'=>'width:80px']);?> - <?=Html::textInput('maxCount',Yii::$app->request->get('maxCount'),['class'=>'form-control','style'=>'width:80px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

    <?=Html::label('发布时间','');?>
    <?=DatePicker::widget([ 
        'name' => 'minDate', 
        'options' => ['placeholder' => ''], 
        //注意，该方法更新的时候你需要指定value值 
        'value' => Yii::$app->request->get('minDate'), 
        'pluginOptions' => [
            'autoclose' => true, 
            'format' => 'yyyy-mm-dd', 
            'todayHighlight' => true 
        ] 
    ])?> - 
    <?=DatePicker::widget([ 
        'name' => 'maxDate', 
        'options' => ['placeholder' => ''], 
        //注意，该方法更新的时候你需要指定value值 
        'value' => Yii::$app->request->get('maxDate'), 
        'pluginOptions' => [
            'autoclose' => true, 
            'format' => 'yyyy-mm-dd', 
            'todayHighlight' => true 
        ] 
    ])?>
   
   <br>
    <div class="form-group" style="margin-top:15px;margin-bottom: 20px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('重置', ['class' => 'btn btn-default reset-search']) ?>
    </div>

    <?=Html::endForm()?>
    
</div>