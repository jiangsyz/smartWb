<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */

$this->registerJsFile("@web/js/refund/refund-list.js"); 

?>
<style>
td,th{
    border:1px solid #ddd!important;
}
.seperate {
    border-left-style:none!important;
    border-right-style:none!important;
    border-bottom-style:none!important;
}
.order-head {
    text-align: left;
    font-weight: bold;
}
</style>

<div class="site-index">
    <div class="row">
        <div class="col-lg-12">
            <div class="order-search">
                <?=Html::beginForm(Yii::$app->urlManager->createUrl(['refund/list']),'get',['id'=>'form','class'=>'form-inline']);?>

                <?=Html::label('订单编号','');?>
                <?=Html::textInput('code',Yii::$app->request->get('code'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('买家手机','');?>
                <?=Html::textInput('phone',Yii::$app->request->get('phone'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('申请退款时间','');?>
                <?=DatePicker::widget([ 
                    'name' => 'minDate', 
                    'options' => ['placeholder' => ''], 
                    //注意，该方法更新的时候你需要指定value值 
                    'value' => Yii::$app->request->get('minDate'), 
                    'pluginOptions' => [
                        'autoclose' => true, 
                        'format' => 'yyyy-mm-dd', 
                        'todayHighlight' => true,
                        'language'=>'zh'
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
                        'todayHighlight' => true,
                        'language'=>'zh'
                    ] 
                ])?>&nbsp;&nbsp;&nbsp;&nbsp;


                <?=Html::label('退款状态','');?>
                <?=Html::dropDownList('status', Yii::$app->request->get('status'), ['99'=>'全部','0'=>'待处理', '1'=>'打款中', '2'=>'打款成功', '3'=>'打款失败', '-1'=>'驳回'], ['class' => 'dropdownlist input-group','style'=>'width:120px;height:34px;bottom:0;']);?>
               
                <br>
                <div class="form-group" style="margin-top:15px;margin-bottom: 20px;">
                    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                    <?= Html::button('重置', ['class' => 'btn btn-default reset-search']) ?>
                </div>

                <?=Html::endForm()?>
            </div>

            <div id="myModal4" class="fade modal in" role="dialog" tabindex="-1" style="display: none;">
                <div class="modal-dialog ">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title">导出订单</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php $form = ActiveForm::begin(['id'=>'export-dropzone', 
                                        'action' => Yii::$app->urlManager->createUrl([
                                            'order/export-order-for-delivery'
                                        ]),
                                        'method' => "post",
                                        'options'=>[
                                            "enctype" => "multipart/form-data",
                                            "class" => "dz-clickable",
                                        ]
                                    ]); ?>
                                    <div class="form-group">
                                        <a class="btn btn-primary" href="javascript:void(0)" role="button" id="export">导出</a>
                                        <!-- <button type="submit" class="btn btn-primary" id="export">导出</button> -->
                                    </div>
                                    <?php ActiveForm::end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="margin-top:20px;">
                <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline">
                    <table style="text-align: center;" class="table table-bordered dataTables-example dataTable dtr-inline"
                           id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info" style="border: 2px solid black;">
                        <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                        <thead>
                        <tr role="row">
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-sort="ascending"
                                aria-label="Rendering engine: activate to sort column descending"
                                style="width: 171px;">商品名称
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-sort="ascending"
                                aria-label="Rendering engine: activate to sort column descending"
                                style="width: 171px;">单价
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-sort="ascending"
                                aria-label="Rendering engine: activate to sort column descending"
                                style="width: 171px;">数量
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">退款金额
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">退款状态
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">操作
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($refunds)) { ?>
                            <tr><td colspan="10">暂无数据</td></tr>
                        <?php } else { ?>
                        <?php foreach ($refunds as $key => $refund) { ?>
                            <tr>
                                <td colspan="10" class="order-head">订单编号：<?=$refund['code']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;昵称：<?=$refund['nickName']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;手机号：<?=$refund['phone']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;申请时间：<?=date('Y-m-d H:i:s', $refund['applyTime'])?>
                                </td>
                            </tr>
                        <?php foreach ($refund['product'] as $k => $v) { ?>
                            <tr class="gradeA odd" role="row">
                                <td style="text-align:left;vertical-align:middle;"><img src="<?=$v['cover']?>" height="40px" width="40px;";>&nbsp;&nbsp;<?= $v['title'] ?></td>
                                <td style="vertical-align:middle;">￥<?= number_format($v['singlePrice'], 2) ?></td>
                                <td style="vertical-align:middle;"><?= $v['buyingCount'] ?></td>
                                <?php if($k==0) { $rowspan=count($refund['product']); ?>
                                <td rowspan="<?=$rowspan?>" style="vertical-align:middle;"><?=$refund['price']?></td>
                                <?php } ?>
                                <?php if($k==0) { $rowspan=count($refund['product']); ?>
                                <td rowspan="<?=$rowspan?>" style="vertical-align:middle;">
                                    <?php 
                                    switch ($refund['status']) {
                                        case 0:
                                            echo "待处理";
                                            break;
                                        case 1:
                                            echo "打款中";
                                            break;
                                        case 2:
                                            echo '<span style="color: rgb(0, 186, 60);">打款成功';
                                            break;
                                        case 3:
                                            echo '<span style="color:red">打款失败';
                                            break;
                                        case -1:
                                            echo '<span style="color:red">驳回';
                                            break;
                                    }
                                    ?>
                                </td>
                                <?php } ?>
                                <?php if ($k==0 && $refund['status']==0) { ?>
                                <td class="center" rowspan="<?=$rowspan?>" style="vertical-align: middle;">
                                    <a href="javescript:void(0)" class="btn-white btn btn-xs agree-refund" data-id="<?=$refund['id']?>">
                                        同意
                                    </a>
                                    <a href="javescript:void(0);" class="btn-white btn btn-xs popup-modal" data-title="售后管理" data-url="<?= Url::to(['refund/get-refund-modal', 'id' => $refund['id']]); ?>" data-toggle="modal" data-target="#common-modal">
                                        驳回
                                    </a>
                                </td>
                                <?php } ?>
                                <?php if ($k==0 && $refund['status']==-1) { ?>
                                <td class="center" rowspan="<?=$rowspan?>" style="vertical-align: middle;">
                                    <a href="javescript:void(0)" class="btn-white btn btn-xs reopen" data-id="<?=$refund['id']?>">
                                        重新激活
                                    </a>
                                </td>
                                <?php } ?>
                                <?php if ($k==0 && $refund['status']==3) { ?>
                                <td class="center" rowspan="<?=$rowspan?>" style="vertical-align: middle;">
                                    <a href="javescript:void(0)" class="btn-white btn btn-xs reset" data-id="<?=$refund['id']?>">
                                        重置退款
                                    </a>
                                </td>
                                <?php } ?>
                            </tr>
                        <?php }  ?>
                            <tr><td colspan="10" style="text-align:left;">退款备注：<?=$refund['applyMemo']?></td></tr>
                            <tr><td colspan="10" class="seperate"></td></tr>
                        <?php }  } ?>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>

    <?php 
        Modal::begin([
            'id' => 'common-modal',
            'header' => '<h4 class="modal-title"></h4>',
            /*'footer' =>  '<a href="#" class="btn btn-primary" data-dismiss="modal">保存</a><a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',*/
        ]);

$js = <<<JS
    $(".popup-modal").click(function(){ 
        $('.modal-title').html('');
        $('.modal-body').html('');
        var url= $(this).attr('data-url');
        var title = $(this).attr('data-title');
        $.get(url, {}, function (data) {
            $('.modal-title').html(title);
            $('.modal-body').html(data);
            return false;
        });
    }); 
JS;
    $this->registerJs($js);

    Modal::end(); 
    ?>

            <div class="text-left tooltip-demo">
                <?php echo yii\widgets\LinkPager::widget([
                    'pagination' => $page,
                    'firstPageLabel' => "<<",
                    'prevPageLabel' => '<',
                    'nextPageLabel' => '>',
                    'lastPageLabel' => '>>',
                    //'options' => ['style' => 'text-align:right']
                ]) ?>
            </div>
        </div>
    </div>
</div>

