<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\Category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use store\models\order\Refund;
/* @var $this yii\web\View */
$this->registerCssFile("@web/dropzone/css/dropzone.css");
$this->registerJsFile("@web/dropzone/js/dropzone.js");
$this->registerJsFile("@web/js/order/order-list.js"); 

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
                <?=Html::beginForm(Yii::$app->urlManager->createUrl([
                    'order/list', 
                    'locked'=>Yii::$app->request->get('locked')]),'get',
                    ['id'=>'form',
                    'class'=>'form-inline',
                    'onsubmit' => 'return validate();'
                    ]);
                ?>
                <?=Html::label('订单ID','');?>
                <?=Html::textInput('id',Yii::$app->request->get('id'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('订单编号','');?>
                <?=Html::textInput('code',Yii::$app->request->get('code'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('商品名称','');?>
                <?=Html::textInput('title',Yii::$app->request->get('title'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('买家手机','');?>
                <?=Html::textInput('phone',Yii::$app->request->get('phone'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

                <?=Html::label('下单时间','');?>
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


                <?=Html::label('订单状态','');?>
                <?=Html::dropDownList('status', Yii::$app->request->get('status'), ['0'=>'全部', '1'=>'待支付', '2'=>'退款中', '3'=>'交易关闭', '4'=>'待发货', '5'=>'待收货', '6'=>'已完成'], ['class' => 'dropdownlist input-group','style'=>'width:120px;height:34px;bottom:0;']);?>

               
                <br>
                <div class="form-group" style="margin-top:15px;margin-bottom: 20px;">
                    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                    <?= Html::button('重置', ['class' => 'btn btn-default reset-search']) ?>
                </div>

                <?=Html::endForm()?>
            </div>

            <!-- tab导航，先不使用
            <ul id="myTab" class="nav nav-tabs">
                <li class="active"><a href="#home" data-toggle="tab">全部</a></li>
                <li><a href="#ios" data-toggle="tab">等待付款</a></li>
                <li><a href="#" id="myTabDrop1">等待发货</a></li>
                <li><a href="#" id="myTabDrop1">退款中</a></li>
            </ul>
            <br> -->
            <?php if (Yii::$app->request->get('locked')!=1) { ?>
            <div style="text-align:left;">
                <a class="btn btn-success" href="#"  role="button" data-toggle="modal" data-target="#myModal4">导出订单</a>
                <!-- <a class="btn btn-success" href="#" data-url="<?=Yii::$app->urlManager->createUrl(['order/export-order-for-delivery'])?>" role="button" id="export-dbwine">导出德邦红酒订单</a> -->
                <a class="btn btn-success" href="#" role="button" id="import" data-toggle="modal" data-target="#myModal3">导入物流</a>
            </div>

            <div style="margin-top: 10px;">共计<?=count($orders)?>条数据</div>
            <?php } ?>

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
                                            /*'id' => Yii::$app->request->get('id'),
                                            'title' => Yii::$app->request->get('title'),
                                            'minDate' => Yii::$app->request->get('minDate'),
                                            'maxDate' => Yii::$app->request->get('maxDate'),
                                            'status' => Yii::$app->request->get('status'),*/
                                        ]),
                                        'method' => "post",
                                        'options'=>[
                                            "enctype" => "multipart/form-data",
                                            "class" => "dz-clickable",
                                        ]
                                    ]); ?>
                                        <?= $form->field($logistics, 'id')->label('物流渠道')->dropDownlist(ArrayHelper::map($logisticsTree,'id', 'name')) ?>
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

            <div class="modal inmodal in" id="myModal3" tabindex="-1" role="dialog" aria-hidden="true" style="display: none; padding-right: 17px;">
                <div class="modal-dialog">
                    <div class="modal-content animated flipInY">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span
                                        class="sr-only">Close</span></button>
                            <h4 class="modal-title">导入物流</h4>
                        </div>
                        <div class="modal-body">
                            <?php $form = ActiveForm::begin(['id'=>'my-awesome-dropzone', 
                                'action' => Yii::$app->urlManager->createUrl(['order/import-order-logistics']),
                                'method' => "post",
                                'options'=>[
                                    "enctype" => "multipart/form-data",
                                    "class" => "dropzone dz-clickable",
                                ]
                            ]); ?>
                            
                            <div class="col-sm-10">
                                <?= $form->field($logistics, 'id')->label('物流渠道')->dropDownlist(ArrayHelper::map($logisticsTree,'id', 'name')) ?>
                            </div>
                            <div class="form-group">
                                <div class="ibox-content">
                                        <div class="dropzone-previews"></div>
                                        <!-- <button id="uploads" type="submit" class="btn btn-primary pull-right upload">上传报告</button> -->
                                        <div class="dz-default dz-message"><span>Drop files here to upload</span></div>
                                    <div>
                                        <div class="m text-right">
                                            <small></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
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
                            <!-- <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-sort="ascending"
                                aria-label="Rendering engine: activate to sort column descending"
                                style="width: 171px;">
                                <input type="checkbox" class="checkall"/>
                            </th> -->
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-sort="ascending"
                                aria-label="Rendering engine: activate to sort column descending" 
                                style="width: 171px;">商品信息
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
                                colspan="1" aria-sort="ascending"
                                aria-label="Rendering engine: activate to sort column descending"
                                style="width: 171px;">物流单号
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">维权状态
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">收货地址
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">运费
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">实付金额
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">订单状态
                            </th>
                            <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                                rowspan="1"
                                colspan="1" aria-label="CSS grade: activate to sort column ascending"
                                style="width: 112px;">操作
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($orders)) { ?>
                            <tr><td colspan="10">暂无数据</td></tr>
                        <?php } else { ?>
                        <?php foreach ($orders as $key => $order) {
                                ?>
                            <tr>
                                <td colspan="10" class="order-head">订单ID：<?=$key?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;订单编号：<?=$order['code']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下单时间：<?=date('Y-m-d H:i:s', $order['createTime'])?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;用户名：<?=$order['nickName']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;手机号：<?=$order['phone']?>
                                </td>
                            </tr>
                        <?php foreach ($order['product'] as $k => $v) {
                                    $data_photo = json_decode($v['dataPhoto'],true);  
                            /*编辑SPU的超链接*/
                            if ($v['spuId']) {
                                $href = Yii::$app->urlManager->createUrl(['product/update','id'=>$v['spuId']]);
                            } else {
                                $href = "javescript:void(0)";
                            }
                            ?>
                            <tr class="gradeA odd" role="row">
                                <!-- <td>
                                    <input type="checkbox" name="lock[]" value="<?php echo $v['id']; ?>"/>
                                </td> -->
                                <td style="text-align:left;vertical-align:middle;"><img src="<?=$v['cover']?>" height="40px" width="40px;";>&nbsp;&nbsp;<a href="<?=$href?>"><?= $v['title'] ?></a></td>
                                <td style="vertical-align:middle;">￥<?= number_format($v['singlePrice'], 2) ?></td>
                                <td style="vertical-align:middle;"><?= $v['buyingCount'] ?></td>
                                <td style="vertical-align:middle;">
                                    <?php
                                        if(!empty($data_photo['logistics']) && !empty($data_photo['logistics']['code']) && !empty($v['logisticsCode'])){
                                            echo '<a href="javescript:void(0);" class="btn-white btn btn-xs popup-modal" data-title="物流详情" data-url="'.Url::to(['zs-api/get-logistics-trace', 'com'=>$data_photo['logistics']['code'], 'num' => trim($v['logisticsCode']), 'from'=>'modal']).'" data-toggle="modal" data-target="#common-modal">'.$v['logisticsCode'].'</a>';
                                    ?>
                                    <?php
                                        }else{
                                    ?>
                                            <?= $v['logisticsCode'] ?>
                                     <?php
                                        }
                                    ?>
                                </td>
                                <td style="vertical-align:middle;">
                                    <?php if (!empty($v['refund']) && $v['refund']['status']!=-1 ) {
                                        switch ($v['refund']['status']) {
                                            case 0:
                                                echo "退款申请中";
                                                break;
                                            case 1:
                                                echo "打款中";
                                                break;
                                            case 2:
                                                echo "打款成功";
                                                break;
                                            case 3:
                                                echo "打款失败";
                                                break;
                                        }
                                    } else {
                                        //已发货的才能申请部分退款
                                        if ($order['payStatus']==1 && ($order['deliverStatus']!=0 || $order['isNeedAddress']==0) && $order['cancelStatus']==0 && $order['closeStatus']==0) {
                                            echo '<a href="javescript:void(0);" class="btn-white btn btn-xs popup-modal" data-title="申请退款" data-url="'.Url::to(['order/get-order-modal', 'id'=>$v['parentId'], 'bid' => $v['bid'], 'item'=>'applyRefund']).'" data-toggle="modal" data-target="#common-modal">
                                                申请退款</a>';
                                        } else {
                                            echo '无退款';
                                        }
                                    }
                                    ?>
                                </td>
                                <?php if($k==0) { $rowspan=count($order['product']); ?>
                                <td rowspan="<?=$rowspan?>" style="vertical-align:middle;width: 400px;" id="<?=$key?>_address"><?=@$order['consigneeAddress']?></td>
                                <td rowspan="<?=$rowspan?>" style="vertical-align:middle;"><?= number_format($order['freight'], 2, '.', '') ?></td>
                                <td rowspan="<?=$rowspan?>" style="vertical-align:middle;">￥<?= number_format($order['pay']/100, 2, '.', '') ?>
                                    <?php if ($order['status'] == 1) { ?>
                                    <br><a class="btn btn-primary btn-sm popup-modal" href="#" role="button" data-toggle="modal" data-target="#common-modal" data-title="改价" data-url="<?= Url::to(['order/get-order-modal', 'id'=>$v['parentId'], 'item'=>'changePrice']); ?>">改价</a>&nbsp;<a class="btn btn-primary btn-sm popup-modal" href="#" role="button" data-toggle="modal" data-target="#common-modal" data-title="改运费" data-url="<?= Url::to(['order/get-order-modal', 'id'=>$v['parentId'], 'item'=>'changeFreight']); ?>">改运费</a>
                                    <?php } ?>
                                </td>
                                <td rowspan="<?=$rowspan?>" style="vertical-align:middle;"><?=$order['statusDesc']?></td>
                                <?php } ?>
                                <?php if ($k==0) { ?>
                                <td class="center" rowspan="<?=$rowspan?>" style="vertical-align: middle;">
                                    <a data-id='1' data-val='<?php echo $v['id']; ?>' ;
                                       href="<?=Yii::$app->urlManager->createUrl(['order/view', 'id'=>$key])?>" class="btn-white btn btn-xs" target="_blank">
                                        查看
                                    </a>
                                    <a data-id='1' data-val='<?php echo $v['id']; ?>' ;
                                       href="javescript:void(0);" class="btn-white btn btn-xs popup-modal" data-title="添加备注" data-url="<?= Url::to(['order/get-order-modal', 'id' => $v['parentId'], 'item' => 'addMemo']); ?>" data-toggle="modal" data-target="#common-modal">
                                        添加备注
                                    </a>
                                    <a href="<?php echo \yii\helpers\Url::to(['order/lock', 'id' => $v['parentId']]); ?>"
                                       class="btn-white btn btn-xs">
                                        <?= $order['locked'] ? '解锁' : '锁定'; ?>
                                    </a>
                                    <?php if ($order['payStatus']==1 && $order['cancelStatus']==0 && $order['closeStatus']==0 && $order['finishStatus']==0 && $order['deliverStatus']==0) { ?>
                                    <a data-id='1' data-val='<?php echo $v['id']; ?>' ;
                                       href="javescript:void(0);" class="btn-white btn btn-xs popup-modal" data-title="修改收货信息" data-url="<?= Url::to(['order/get-address-modal', 'id' => $v['parentId']]); ?>" data-toggle="modal" data-target="#common-modal">
                                        修改收货信息
                                    </a>
                                    <?php } ?>
                                    <?php
                                    //判断是否满足整单退的条件
                                    $canRefundAll = Refund::find()->where("oid={$key} and status<>-1")->one() ? false : true;
                                    if ($order['payStatus']==1 && $order['cancelStatus']==0 && $order['closeStatus']==0 && ($order['deliverStatus']==0) && $canRefundAll) { ?>
                                        <a href="javescript:void(0);" class="btn-white btn btn-xs refund-all" data-oid="<?=$key?>">
                                            关闭订单
                                        </a>
                                    <?php } ?>
                                </td>
                                <?php } ?>
                            </tr>
                        <?php }  ?>
                            <tr><td colspan="10" style="text-align:left;word-break:break-all;word-wrap:break-word;">用户备注：<?= @current($order['memberMemo'])?></td></tr>
                            <tr><td colspan="10" style="text-align:left;word-break:break-all;word-wrap:break-word;">客服备注：<br>
                                <?php
                                if (!empty($order['staffMemo'])) {
                                    $i = 0;
                                    foreach ($order['staffMemo'] as $key => $staffMemo) { 
                                        echo (++$i)."）".$staffMemo."<br>";
                                    } } ?>
                            </td></tr>
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
        $('#common-modal .modal-title').html('');
        $('#common-modal .modal-body').html('');
        var url= $(this).attr('data-url');
        var title = $(this).attr('data-title');
        $.get(url, {}, function (data) {
            $('#common-modal .modal-title').html(title);
            $('#common-modal .modal-body').html(data);
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

