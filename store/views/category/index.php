<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use store\models\category\category;
use \yii\web\JsExpression;

/* @var $this yii\web\View */

//$this->title = '分类管理';

$this->registerCssFile("@web/zTree/css/zTreeStyle/zTreeStyle.css"); 
$this->registerJsFile("@web/zTree/js/jquery.ztree.core.js");  
$this->registerJsFile("@web/zTree/js/jquery.ztree.excheck.js");
$this->registerJsFile("@web/zTree/js/jquery.ztree.exedit.js");
$this->registerJsFile("@web/js/category/category.js");  

?>
<style type="text/css">
    .ztree li span.button.add {margin-left:2px; margin-right: -1px; background-position:-144px 0; vertical-align:top; *vertical-align:middle}
</style>

<div class="site-index">

    <div class="body-content">
        <div class="row">
            <div class="col-lg-10">
                <input type="hidden" id="categoryData" value='<?=$nodes?>'>
                <div class="zTreeDemoBackground left">
                    <ul id="categoryTree" class="ztree"></ul>
                </div>
            </div>
        </div>

    </div>
</div>
