<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\grid\CheckboxColumn;

/* @var $this yii\web\View */

//$this->title = '出售中的商品';

$this->registerJsFile("@web/js/product/product-list.js");  
?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-12">

                <?=Html::beginForm(Yii::$app->urlManager->createUrl(['tool/goods-list']),'get',['id'=>'form','class'=>'form-inline']);?>

                <?=Html::label('物流渠道','');?>
                <?=Html::dropDownList('logistics_id', Yii::$app->request->get('logistics_id'), ['0'=>'全部', '1'=>'顺丰冷运', '2'=>'顺丰快递', '3'=>'德邦快递', '4'=>'德邦红酒', '5'=>'整条出库', '6'=>'不粘锅'], ['class' => 'dropdownlist input-group','style'=>'width:120px;height:34px;bottom:0;']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('商品名称','');?>
                <?=Html::textInput('name',Yii::$app->request->get('name'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('商品编码','');?>
                <?=Html::textInput('code',Yii::$app->request->get('code'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;
               
                <br>
                <div class="form-group" style="margin-top:15px;margin-bottom: 20px;">
                    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                    <!-- <?= Html::button('重置', ['class' => 'btn btn-default reset-search']) ?> -->
                </div>

                <?=Html::endForm()?>

                <a class="btn btn-primary" href="<?=Yii::$app->urlManager->createUrl(['tool/create-goods'])?>" role="button">添加编码</a>
                <button type="button" class="btn btn-primary" id="batch-delete">批量删除</button>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout'=> '{items}<div class="text-right tooltip-demo">{pager}</div>',
                    'pager'=>[
                               //'options'=>['class'=>'hidden']//关闭自带分页
                        'firstPageLabel'=>"<<",
                        'prevPageLabel'=>'<',
                        'nextPageLabel'=>'>',
                        'lastPageLabel'=>'>>',
                    ],
                    'columns' => [
                        [
                            'class'=>CheckboxColumn::className(),
                            'name'=>'id',
                            'headerOptions' => ['width'=>'30'],
                             'checkboxOptions' => function($data, $key, $index, $column) {
                                return ['value' => $data['id']];
                            }
                            /*'value' => function ($data) { 
                                return $data['cover']; 
                            }*/
                        ],
                        [
                            'attribute' => '物流渠道',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '20%'],
                            'value' => function ($data) { 
                                switch ($data->logistics_id) {
                                    case '1':
                                        return '顺丰冷运';
                                        break;
                                    case '2':
                                        return '顺丰快递';
                                        break;
                                    case '3':
                                        return '德邦快递';
                                        break;
                                    case '4':
                                        return '德邦红酒';
                                        break;
                                    case '5':
                                        return '整条出库';
                                        break;
                                    default:
                                        return '不粘锅';
                                        break;
                                }
                            }
                        ],
                        [
                            'attribute' => '商品名称',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '20%'],
                            'value' => function ($data) { 
                                return $data->name; 
                            }
                        ],
                        [
                            'attribute' => '商品编码',
                            'value' => function ($data) { 
                                return $data->code; 
                            }
                        ],
                        [
                            'attribute' => '规格',
                            'value' => function ($data) {
                                return $data->unit;
                            }
                        ],
                        [
                            'attribute' => '成本',
                            'value' => function ($data) {
                                return $data->cost;
                            }
                        ],
                        [
                            "class" => "yii\grid\ActionColumn",
                            "header" => "操作",
                            "template" => "{update}",
                            "buttons" => [
                                "update" => function ($url, $data, $key) {
                                    return Html::a("编辑", Yii::$app->urlManager->createUrl(['tool/update-goods', 'id'=>$data->id]));},
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $("#batch-delete").click(function(){
            //var ids = $(".grid-view").yiiGridView("getSelectedRows");
            var ids = new Array();
            $("input[name='id[]']").each(function(){
                if ($(this).is(':checked')) {
                    ids.push($(this).val());
                }
            });
            $.ajax({
                type: "post",
                url: "index.php?r=tool/delete-goods",
                data: {ids:ids},
                dataType: "json",
                success: function(data){
                    if (data.error == 0) {
                        krajeeDialog.alert('操作成功');
                        setTimeout("location.reload()", 2000);
                    } else {
                        krajeeDialog.alert('操作失败');
                    }
                }
            });
        });
    });
</script>
