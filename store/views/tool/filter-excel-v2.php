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
$this->registerCssFile("@web/dropzone/css/dropzone.css");
$this->registerJsFile("@web/dropzone/js/dropzone.js",['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile("@web/js/order/order-list.js");
?>


<div class="site-index">
    <div class="row">
        <div class="col-lg-12">
            <div style="text-align:left;">
                <a class="btn btn-success" href="#" role="button" id="import" data-toggle="modal" data-target="#myModal3">导入待发货订单</a>
                <a class="btn btn-success" href="#"  role="button" data-toggle="modal" data-target="#myModal4">导出订单</a>
            </div>

            <div class="modal inmodal in" id="myModal3" tabindex="-1" role="dialog" aria-hidden="true" style="display: none; padding-right: 17px;">
                <div class="modal-dialog">
                    <div class="modal-content animated flipInY">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span
                                        class="sr-only">Close</span></button>
                        </div>
                        <div class="modal-body">
                           <!-- --><?php /*$form = ActiveForm::begin(['id'=>'my-awesome-dropzone',
                                'action' => Yii::$app->urlManager->createUrl(['tool/filter-excel-v2']),
                                'method' => "post",
                                'options'=>[
                                    "enctype" => "multipart/form-data",
                                    "class" => "dropzone dz-clickable",
                                ]
                            ]); */?>

                            <form id="zs-excel" enctype="multipart/form-data" class="dropzone dz-clickable">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                            <div class="form-group">
                                <div class="ibox-content">
                                        <div class="dropzone-previews"></div>
                                        <!-- <button id="uploads" type="submit" class="btn btn-primary pull-right upload">上传报告</button> -->
                                        <div class="dz-default dz-message"><span>Drop files here to upload</span></div>

                                </div>
                            </div>
                         <!--   --><?php /*ActiveForm::end(); */?>
                            </form>
                            <script type="text/javascript">
                                Dropzone.options.zsExcel = {
                                    url: "<?php echo Yii::$app->urlManager->createUrl(['tool/filter-excel-v2']);?>",//文件提交地址
                                    method:"post",  //也可用put
                                    paramName:"file", //默认为file
                                    maxFiles:1,//一次性上传的文件数量上限
                                    maxFilesize: 5, //文件大小，单位：MB
                                    acceptedFiles: ".xls,.xlsx", //上传的类型
                                    init:function(){
                                        this.on("success",function(file,data){
                                            //上传成功触发的事件
                                            location.reload();
                                            console.log('ok');
                                            angular.element(appElement).scope().file_id = data.data.id;
                                        });
                                        this.on("error",function (file,data) {
                                            //上传失败触发的事件
                                            console.log('fail');
                                            var message = '';
                                            //lavarel框架有一个表单验证，
                                            //对于ajax请求，JSON 响应会发送一个 422 HTTP 状态码，
                                            //对应file.accepted的值是false，在这里捕捉表单验证的错误提示
                                            if (file.accepted){
                                                $.each(data,function (key,val) {
                                                    message = message + val[0] + ';';
                                                })
                                                //控制器层面的错误提示，file.accepted = true的时候；
                                                alert(message);
                                            }
                                        });

                                    }
                                };
                            </script>
                        </div>
                    </div>
                </div>
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
                                            'tool/export-v2'
                                        ]),
                                        'method' => "post",
                                        'options'=>[
                                            "enctype" => "multipart/form-data",
                                            "class" => "dz-clickable",
                                        ]
                                    ]); ?>
                                        <?= $form->field($logistics, 'id')->label('物流渠道')->dropDownlist($logistics_data) ?>
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

        </div>
    </div>
</div>
