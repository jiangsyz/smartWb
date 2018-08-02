<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */

//$this->title = '出售中的商品';
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
.order-info {
    border:none!important;
}
</style>

<iframe src="about:blank" name="hiddenIframe" class="hide"></iframe>

<div class="site-index">
    <ol class="breadcrumb">
        <li><a href="<?=Yii::$app->urlManager->createUrl(['order/list']);?>">订单列表</a></li>
        <li class="active">订单：<?=key($orders)?></li>
    </ol>
    <!-- <div class="progress progress-striped active">
        <div class="progress-bar progress-bar-info" role="progressbar"
             aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
             style="width: 25%;">
            <span style="font-weight:600px;color:black;">买家下单</span>
        </div>
        <div class="progress-bar progress-bar-info" role="progressbar"
             aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
             style="width: 25%;">
            <span style="font-weight:600px;color:black;">买家付款</span>
        </div>
        <div class="progress-bar progress-bar-info" role="progressbar"
             aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
             style="width: 25%;">
            <span style="font-weight:600px;color:black;">商家发货</span>
        </div>
        <div class="progress-bar progress-bar-info" role="progressbar"
             aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"
             style="width: 25%;">
            <span style="font-weight:600px;color:black;">交易完成</span>
        </div>
    </div> -->

    <?php foreach ($orders as $orderId => $order) { ?>
    <table class="table order-info">
        <thead>
            <tr>
                <th style="border:0!important;width:8%">订单信息</th>
                <th style="border:0!important"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:0!important;width:8%">订单ID：</td>
                <td style="border:0!important"><?=$orderId?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">订单编号：</td>
                <td style="border:0!important"><?=$order['code']?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">订单状态：</td>
                <td style="border:0!important"><?=$order['statusDesc']?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">收件地址：</td>
                <td style="border:0!important"><?=@$order['consigneeAddress']?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">收件人：</td>
                <td style="border:0!important"><?=@$order['consignee']?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">收件人手机：</td>
                <td style="border:0!important"><?=@$order['consigneePhone']?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">用户备注：</td>
                <td style="border:0!important"><?=@current($order['memberMemo'])?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">买家：</td>
                <td style="border:0!important"><?=$order['phone']?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">支付方式：</td>
                <td style="border:0!important">微信</td>
            </tr>
        </tbody>
    </table>
    <?php } ?>


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
                        colspan="1" aria-label="CSS grade: activate to sort column ascending"
                        style="width: 112px;">商品编码
                    </th>
                    <th style="text-align: center;" tabindex="0" aria-controls="DataTables_Table_0"
                        rowspan="1"
                        colspan="1" aria-label="CSS grade: activate to sort column ascending"
                        style="width: 112px;">物流单号
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
                </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)) { ?>
                    <tr><td colspan="10">暂无数据</td></tr>
                <?php } else { ?>
                <?php foreach ($orders as $key => $order) { ?>
                <?php foreach ($order['product'] as $k => $v) { 
                    /*编辑SPU的超链接*/
                    if ($v['spuId']) {
                        $href = Yii::$app->urlManager->createUrl(['product/update','id'=>$v['spuId']]);
                    } else {
                        $href = "javescript:void(0)";
                    }
                    ?>
                    <tr class="gradeA odd" role="row">
                        <td style="text-align:left;vertical-align:middle;"><img src="<?=$v['cover']?>" height="40px" width="40px;";><a href="<?=$href?>"><?= $v['title'] ?></a></td>
                        <td style="vertical-align:middle;">￥<?= number_format($v['singlePrice'], 2) ?></td>
                        <td style="vertical-align:middle;"><?= $v['buyingCount'] ?></td>
                         <td style="vertical-align:middle;"><?= $v['uniqueId'] ?></td>
                        <td style="vertical-align:middle;"><?=$v['logisticsCode']?></td>
                        <?php if($k==0) { $rowspan=count($order['product']); ?>
                        <td rowspan="<?=$rowspan?>" style="vertical-align:middle;"><?=@$order['consigneeAddress']?></td>
                        <td rowspan="<?=$rowspan?>" style="vertical-align:middle;"><?= number_format($order['freight'], 2, '.', '') ?></td>
                        <td rowspan="<?=$rowspan?>" style="vertical-align:middle;">￥<?= number_format($order['pay']/100, 2, '.', '') ?></td>
                        <td rowspan="<?=$rowspan?>" style="vertical-align:middle;"><?=$order['statusDesc']?></td>
                        <?php } ?>
                    </tr>
                <?php }  ?>
                    <tr><td colspan="10" style="text-align:left;">客服备注：<br>
                        <?php
                        if (!empty($order['staffMemo'])) {
                            $i = 1;
                            foreach ($order['staffMemo'] as $key => $staffMemo) { 
                                echo ($i++)."）".$staffMemo."<br>";
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
    <a class="btn btn-primary" href="<?=Yii::$app->request->referrer;?>"  role="button">返回</a>
</div>