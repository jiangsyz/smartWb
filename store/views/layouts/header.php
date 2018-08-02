<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">
    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">正善管理后台</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu" style="margin-right:300px;">
            <ul class="nav navbar-nav">        
                <li>
                    <a href="<?=Yii::$app->urlManager->createUrl(['site/index'])?>">首页</a>
                </li>
                <li>
                    <a href="<?=Yii::$app->urlManager->createUrl(['setting/change-password'])?>">修改密码</a>
                </li>   
                <li>
                    <a href="<?=Yii::$app->urlManager->createUrl(['site/logout'])?>">注销</a>
                </li>
                <li>
                    <a href="http://server1.zsbutcher.cn/smartTj/backend/web/index.php" target="_blank">销售统计</a>
                </li>

                <!-- <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li> -->
            </ul>
        </div>
    </nav>
</header>
