<?php

/* @var $this yii\web\View */

//$this->title = '正善管理后台';
?>

<link rel="stylesheet" href="/smartWb/vendor/almasaeed2010/adminlte/bower_components/Ionicons/css/ionicons.min.css">
<?php $this->registerJsFile("@web/echarts/echarts.js");?>
<div class="site-index">
    <!-- Main content -->

    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?= $newOrderCnt ?></h3>
                    <p>昨日新增订单</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>

                <a href="<?=Yii::$app->urlManager->createUrl('order/list')?>" class="small-box-footer">更多 <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?= $newUserCnt ?></h3>
                    <p>昨日新增用户</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="#" class="small-box-footer">更多 <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?= $monthOrderCnt ?></h3>
                    <p>本月订单数</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="<?=Yii::$app->urlManager->createUrl('order/list')?>" class="small-box-footer">更多 <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>￥<?= $monthSalesAmount ?></h3>
                    <p>本月销售额</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="<?=Yii::$app->urlManager->createUrl('order/list')?>" class="small-box-footer">更多 <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-12 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom" id="sales-amount" style="width: 1637px;height:400px;">
                <!-- 为ECharts准备一个具备大小（宽高）的Dom -->
    
            </div>
            <!-- /.nav-tabs-custom -->
            <!-- /.box (chat box) -->
        </section>
        <!-- right col -->
    </div>
    <!-- /.row (main row) -->
    <!-- /.content -->
</div>

<script src="/smartWb/store/web/echarts/echarts.js"></script>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('sales-amount'));

    // 指定图表的配置项和数据
    var date = $("#dateStr").val();
    var amount = $("#amountStr").val();
    console.log(date);
    var option = {
        title: {
            text: '近10天销售额（单位：元）'
        },
        tooltip: {},
        legend: {
            data:['销售额']
        },
        xAxis: {
            data: date
        },
        yAxis: {},
        series: [{
            name: '销售额',
            type: 'bar',
            data: amount
        }]
    };
    // 获取原始数据
    $.ajax({
        url:"index.php?r=site/get-sales-amount",
        async:false,
        dataType:'json',
        type:'get',
        success:function(msg){
            // 使用刚指定的配置项和数据显示图表。
            console.log(msg);
            option.xAxis.data = msg.date;
            option.series = msg.amount;
            myChart.setOption(option);
        }
    });


    
</script>
