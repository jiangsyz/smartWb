<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use backend\models\category\Category;
use yii\grid\GridView;
use yii\widgets\LinkPager;
use yii\grid\CheckboxColumn;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */

//$this->title = '出售中的商品';

?>
<input type="hidden" class="recommendType" value="<?=Yii::$app->request->get('recommendType', 1)?>">

<div class="site-index">
    <div class="body-content">
        <ul class="nav nav-tabs">
            <li><a href="#identifier" class="tab-recommend">推荐商品</a></li>
            <li><a href="#identifier" class="tab-hot">热门商品</a></li>
        </ul>
        <div class="row">
            <div class="col-lg-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'layout'=> '{items}<div class="text-right tooltip-demo">{pager}</div>',
                    'pager'=>[
                        //'options'=>['class'=>'hidden'],//关闭自带分页
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
                            'attribute' => '商品ID',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '3%'],
                            'value' => function ($data) { 
                                return $data['id'];
                            }
                        ],
                        [
                            'label' => '图片',
                            'format' => [
                                'image', 
                                [
                                'width'=>'60',
                                'height'=>'60'
                                ]
                            ],
                            'value' => function ($data) { 
                                return $data['cover'] ? $data['cover'] : ''; 
                            }
                        ],
                        [
                            'attribute' => '标题',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '20%'],
                            'value' => function ($data) { 
                                return $data['title']; 
                            }
                        ],
                        [
                            'attribute' => '编码',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '15%'],
                            'value' => function ($data) { 
                                return $data['uniqueId']; 
                            }
                        ],
                        [
                            'attribute' => '排序',
                            'contentOptions' => ['style' => 'white-space: normal;', 'width' => '15%'],
                            'value' => function ($data) { 
                                return $data['sort']; 
                            }
                        ],
                        [
                            "class" => "yii\grid\ActionColumn",
                            "header" => "操作",
                            "template" => "{update}",
                            "buttons" => [
                                "update" => function ($url, $data, $key) {
                                    // return Html::a("编辑", Yii::$app->urlManager->createUrl(['product/update', 'id'=>$data['id']], ['target'=>'_blank', 'data' => ['pjax' => '0']]));},
                                    //echo "<pre>";print_r($data);exit;
                                    $url = Yii::$app->urlManager->createUrl(['product/update', 'id'=>$data['id']]);
                                    return '<a data-id="'.$data['id'].'" data-sort="'.$data['sort'].'"
                                       href="#" class="btn-white btn btn-xs popup-modal" data-url="<?= Url::to([\'order/get-order-modal\', \'id\' => $data[\'id\']]); ?>" data-toggle="modal" data-target="#common-modal">
                                        编辑排序
                                    </a>';},
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>

<?=Html::beginForm(Yii::$app->urlManager->createUrl(['product/recommend-sort']),'get');?>
    <div id="common-modal" class="fade modal in" role="dialog" tabindex="-1" style="display: none; padding-right: 17px;">
        <input type="hidden" name="id" value="" id="recommend-id">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">排序</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group field-modal-text required">
                        <input type="text" value="0" name="sort">
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                        <a href="#" class="btn btn-primary" data-dismiss="modal">关闭</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?=Html::endForm()?>


<script type="text/javascript">
    $(".popup-modal").click(function(){ 
        var id = $(this).attr('data-id');
        var sort = $(this).attr('data-sort');
        $("#recommend-id").val(id);
        $("#common-modal").show();
    }); 

    $(function(){
        //tab页切换
        var recommendType = $(".recommendType").val();
        var host = window.location.pathname;
        if (recommendType == 1 || recommendType == '') {
            $(".tab-recommend").tab("show");
        } else {
            $(".tab-hot").tab("show");
        }
        //$(".tab-spu").tab("show");
        $(".tab-recommend").click(function(){
            var url = host+'?r=product/recommend-list&recommendType=1';
            window.location.href = url;
        });
        $(".tab-hot").click(function(){
            var url = host+'?r=product/recommend-list&recommendType=2';
            window.location.href = url;
        });
    })
    
</script>
