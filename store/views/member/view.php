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
        <li><a href="<?=Yii::$app->urlManager->createUrl(['order/list']);?>">用户列表</a></li>
        <li class="active">订单：<?=$member->id?></li>
    </ol>

    <table class="table order-info">
        <thead>
            <tr>
                <th style="border:0!important;width:8%">用户基本信息</th>
                <th style="border:0!important"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:0!important;width:8%">用户ID：</td>
                <td style="border:0!important"><?=$member->id?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">昵称：</td>
                <td style="border:0!important"><?=$member->nickName?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">手机号：</td>
                <td style="border:0!important"><?=$member->phone?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">注册时间：</td>
                <td style="border:0!important"><?=date('Y-m-d H:i:s',$member->createTime)?></td>
            </tr>
            <tr>
                <td style="border:0!important;width:8%">是否冻结：</td>
                <td style="border:0!important"><?=$member->locked?'是':'否'?></td>
            </tr>
        </tbody>
    </table>
    <?php if(!empty($memberLvs)){?>
    <table class="table order-info">
        <thead>
            <tr>
                <th style="border:0!important;width:8%">会员卡购买记录</th>
                <th style="border:0!important"></th>
                <th style="border:0!important"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:0!important">#</td>
                <td style="border:0!important">会员等级</td>
                <td style="border:0!important">开始时间</td>
                <td style="border:0!important">结束时间</td>
             <!--   <td style="border:0!important">订单详情</td>-->
                <td style="border:0!important">来源</td>
                <td style="border:0!important">状态</td>
                <td style="border:0!important">操作</td>
            </tr>
            <?php  foreach ($memberLvs as $memberLv) { ?>
            <tr>
                <td><?=$memberLv->id?></td>
                <td><?=$memberLv->lv?></td>
                <td><?=date('Y-m-d H:i:s', $memberLv->start)?></td>
                <td><?=date('Y-m-d H:i:s', $memberLv->end)?></td>
          <!--      <td><a target="_blank" href="<?php /*echo \yii\helpers\Url::toRoute(['order/view', 'id' => $memberLv->orderId]);*/?>">详情</a></td>-->
                <td><?php 
                    switch ($memberLv->handlerType) {
                        case 8:
                            echo "订单";
                            break;
                        case 999:
                            echo "有赞";
                            break;
                        case 998:
                            echo "奖励";
                            break;
                    }
                ?></td>
                <td><?php echo ($memberLv->closed == 0)?'正常' :'关闭';?></td>
                <td>
                    <?php
                    $now = time();
                    if($memberLv->closed == 0 && $memberLv->end>$now){ ?>
                        <?php
                        echo Html::a('关闭', '/account/changepwd', [
                            'id' => 'create',
                            'class' => 'btn btn-primary',
                            'data-toggle' => 'modal',
                            'data-url' => \yii\helpers\Url::to(['member/get-modal', 'id'=>$memberLv->id, 'action'=>'cancelVip']),
                            'data-title' => '取消会员卡',
                            'data-target' => '#create-modal',
                        ]);
                        ?>
                    <?php }else{
                        echo Html::a('关闭', '#', [
                            'class' => 'btn btn-primary',
                            'disabled' => true
                        ]);?>
                    <?php }?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php }?>
    <a class="btn btn-primary" href="<?php echo \yii\helpers\Url::toRoute('member/list')?>"  role="button">返回</a>
</div>
<?php
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">关闭备注</h4>',
]);
$requestUrl = \yii\helpers\Url::toRoute('member/get-modal');
$js = <<<JS
    $(document).on('click', '#create', function () {
            var url= $(this).attr('data-url');
            $.get(url, {},
                function (data) {
                    $('.modal-body').html(data);
                }
            );
        });

JS;
$this->registerJs($js);
Modal::end();
?>