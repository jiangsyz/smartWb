<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\Category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\grid\CheckboxColumn;
use kartik\date\DatePicker;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */


$this->registerJsFile("@web/js/member/member-list.js");  
?>
<div class="site-index">
    <div class="body-content">
        <div class="order-search">

            <?=Html::beginForm(Yii::$app->urlManager->createUrl(['member/list']),'get',['id'=>'form','class'=>'form-inline']);?>

            <?=Html::label('用户ID','');?>
            <?=Html::textInput('id',Yii::$app->request->get('id'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

            <?=Html::label('手机号','');?>
            <?=Html::textInput('phone',Yii::$app->request->get('phone'),['class'=>'form-control','style'=>'width:150px']);?>&nbsp;&nbsp;&nbsp;&nbsp;

            <?=Html::label('注册时间','');?>
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

           <br>
            <div class="form-group" style="margin-top:15px;margin-bottom: 20px;">
                <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                <?= Html::button('重置', ['class' => 'btn btn-default reset-search']) ?>
            </div>

            <?=Html::endForm()?>
        </div>

        <div class="row">
            <div class="col-lg-12">
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
                            'label' => '用户ID',
                            'value' => function ($data) { 
                                return $data['id']; 
                            }
                        ],
                        [
                            'attribute' => '手机号',
                            'value' => function ($data) { 
                                return $data['phone']; 
                            }
                        ],
                        [
                            'attribute' => '昵称',
                            'value' => function ($data) { 
                                return $data['nickName'] ? $data['nickName'] : ''; 
                            }
                        ],
                       [
                           'attribute' => 'VIP开始时间',
                           'value' => function ($data) use ($vipCardInfo) {
                               return !empty($vipCardInfo[$data['id']]) ? date('Y-m-d H:i:s', $vipCardInfo[$data['id']]['vipStart']) : '';
                           }
                       ],
                       [
                           'attribute' => 'VIP到期时间',
                           'value' => function ($data) use ($vipCardInfo) {
                               return !empty($vipCardInfo[$data['id']]) ? date('Y-m-d H:i:s', $vipCardInfo[$data['id']]['vipEnd']) : '';
                           }
                       ],
                        [
                            'attribute' => '注册时间',
                            'value' => function ($data) { 
                                return $data['createTime'] ? date('Y-m-d H:i:s', $data['createTime']) : ''; 
                            }
                        ],
                        [
                            "class" => "yii\grid\ActionColumn",
                            "header" => "操作",
                            "template" => "{view}&nbsp;&nbsp;{lock}&nbsp;&nbsp;{nickname}",
                            "buttons" => [
                                "view" => function ($url, $data, $key) {
                                    return Html::a('查看', Yii::$app->urlManager->createUrl(['member/view', 'id'=>$data['id']]));},
                                "lock" => function ($url, $data, $key) {
                                    $text = $data['locked'] ? '<span style="color:red">解锁</span>' : '锁定';
                                    return Html::a($text, Yii::$app->urlManager->createUrl(['member/lock', 'id'=>$data['id']]));},
                                "nickname" => function ($url, $data, $key) {
                                    return Html::a("编辑昵称", $url, [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#common-modal',
                                        'class' => 'update-modal',
                                        'data-id' => $data['id'],
                                        'data-url' => Url::toRoute(['/member/nickname-modal', 'id'=>$data['id']]),
                                        'data-title' => '修改昵称',
                                        'data-nickname' => $data['nickName'],
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>

<?php 
    Modal::begin([
        'id' => 'common-modal',
        'header' => '<h4 class="modal-title"></h4>',
        /*'footer' =>  '<a href="#" class="btn btn-primary" data-dismiss="modal">保存</a><a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>',*/
    ]);

$js = <<<JS
    $(".update-modal").click(function(){ 
        $('.modal-title').html('');
        $('.modal-body').html('');
        var url= $(this).attr('data-url');
        var title = $(this).attr('data-title');
        $.get(url, {}, function (data) {
            $('.modal-title').html(title);
            $('.modal-body').html(data);
            //$($(this).attr('data-target')+" .modal-title").text(title);
            //$($(this).attr('data-target')).modal("show").find(".modal-body").html(data); 
            return false;
        });
    }); 
JS;
    $this->registerJs($js);

    Modal::end(); 
?>  
