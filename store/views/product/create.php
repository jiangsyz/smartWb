<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\file\FileInput;
use store\models\category\Category;
use \zh\qiniu\QiniuFileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */

//$this->title = '商品发布';

$this->registerCssFile("@web/css/product/release-product.css");  
 
$this->registerJsFile("@web/qiniu_ueditor/ueditor.config.js");
$this->registerJsFile("@web/qiniu_ueditor/ueditor.all.min.js");
//$this->registerJsFile("@web/qiniu_ueditor/lang/zh-cn/zh-cn.js");
$this->registerJsFile("@web/js/product/release-product.js"); 
?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <div class="col-lg-10">
                <?php $form = ActiveForm::begin([
                    'id'=>'create-product-form', 
                    'enableAjaxValidation' => true,
                    'validationUrl' => Url::toRoute(['validate-form']),
                    'options'=>["enctype" => "multipart/form-data",'onsubmit' => 'return validate();'],
                ]); ?>
                    <!-- <input type="text" name="spu" value="" class="form-bom " > -->

                    <?= $form->field($categoryRecord, 'categoryId')->label('分类')->dropDownlist($categoryTree) ?>

                    <?= $form->field($spu, 'logisticsId')->label('物流渠道')->dropDownlist($logisticsTree) ?>

                    <?= $form->field($spu, 'title')->textInput() ?>

                    <?= $form->field($spu, 'uniqueId')->textInput() ?>

                    <?= $form->field($spu, 'desc')->textarea(['rows'=>5]) ?>

                    <?= $form->field($spu, 'cover')->widget(QiniuFileInput::className(),[
                        'uploadUrl' => 'http://up-z0.qiniu.com',//文件上传地址 不同地区的空间上传地址不一样 参见官方文档
                        'qlConfig' => [
                            'accessKey' => Yii::$app->params['qiniu']['accessKey'],
                            'secretKey' => Yii::$app->params['qiniu']['secretKey'],
                            'scope' => Yii::$app->params['qiniu']['bucket'],
                            'cdnUrl' => Yii::$app->params['qiniu']['domain'],//外链域名
                        ],
                        'clientOptions' => [
                            'max' => 5,//最多允许上传图片个数  默认为3
                            'size' => 2048000,//每张图片大小
                            'btnName' => 'upload',//上传按钮名字
                            'accept' => 'image/jpeg,image/gif,image/png'//上传允许类型
                        ],
                        'pluginEvents' => [
                            //'delete' => 'function(item){console.log(item)}',
                            //'success' => 'function(res){console.log(res)}'
                        ]
                    ]) ?>

                    <?php //echo $form->field($spu, 'detail')->widget('kucha\ueditor\UEditor', []) ?>

                    <script id="detail" name="Spu[detail]" type="text/plain" style="height:400px;"></script>

                    <br>
                    <div class="form-group field-spu-desc">
                        <label class="control-label" for="spu-desc">商品规格（请至少添加一个SKU）</label><br>
                        <table class="sku-list">
                            <tr>
                                <th>标题</th><th>编码</th><th>属性名称</th><th>属性值</th><!-- <th>库存</th> --><th>价格</th><th>VIP价格</th>
                            </tr>

                            <tr class="sku" cnt="0">
                                <td><input type="text" name="Sku[0][title]" value="" class="form-bom sku-td" placeholder="100克"></td>
                                <td><input type="text" name="Sku[0][uniqueId]" value="" class="form-bom sku-td" placeholder="100001"></td>
                                <td><select name="Sku[0][propertyKey]" style="height:26px;width:137px;">
                                    <?php foreach ($sourceProConfTree as $v => $text) { ?>
                                    <option value="<?=$v?>"><?=$text?></option>
                                    <?php } ?>
                                </select></td>
                                <td><input type="text" name="Sku[0][propertyVal]" value="" class="form-bom sku-td" placeholder="100"></td>
                                <!-- <td><input type="text" name="Sku[0][count]" value="" class="form-bom sku-td" placeholder="999"></td> -->
                                <td><input type="text" name="Sku[0][price]" value="" class="form-bom sku-td" placeholder="200"></td>
                                <td><input type="text" name="Sku[0][vprice]" value="" class="form-bom sku-td" placeholder="190"></td>
                            </tr>
                        </table>
                        
                        <br>
                        <?= Html::button('+添加规格', ['class' => 'btn btn-primary add-sku']) ?>
                    </div>

                    <?php 
                        //各种状态的默认值
                        $spu->distributeType = 1; 
                        $spu->closed = 0; 
                        $recommendRecord->isHot = 0; 
                        $recommendRecord->isRecommend = 0;
                    ?>

                    <?= $form->field($spu, 'distributeType')->inline()->radioList(['1'=>'冷链', '2'=>'非冷链']) ?>

                    <?= $form->field($spu, 'closed')->label('是否下架 （下架后自动取消热门及推荐）')->inline()->radioList(['0'=>'上架', '1'=>'下架']) ?>

                    <?= $form->field($recommendRecord, 'isHot')->inline()->radioList(['0'=>'否', '1'=>'是']) ?>

                    <?= $form->field($recommendRecord, 'isRecommend')->inline()->radioList(['0'=>'否', '1'=>'是']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>
