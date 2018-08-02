<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?=Yii::$app->session['staff']['phone']?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <!-- <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form> -->
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    [
                        'label' => '网站设置',
                        'icon' => 'file',
                        'url' => '#',
                        'items' => [
                            ['label' => 'banner图', 'url' => ['/banner/index', 'bannerId'=>1],],
                            ['label' => '公众号自动回复', 'url' => ['/setting/gzh-auto-response'],],
                        ],
                    ],
                    ['label' => '会员管理', 'icon' => 'user', 'url' => ['/member/list']],
                    ['label' => '会员卡管理', 'icon' => 'credit-card', 'url' => ['/card/list']],
                    ['label' => '分类管理', 'icon' => 'dashboard', 'url' => ['/category']],
                    [
                        'label' => '商品管理',
                        'icon' => 'archive',
                        'url' => '#',
                        'items' => [
                            ['label' => '商品发布', 'url' => ['/product/create'],],
                            ['label' => '出售中的商品', 'url' => ['/product/list','closed'=>0],],
                            ['label' => '仓库中的商品', 'url' => ['/product/list','closed'=>1],],
                        ],
                    ],
                    [
                        'label' => '订单管理',
                        'icon' => 'newspaper-o',
                        'url' => '#',
                        'items' => [
                            ['label' => '未锁订单', 'url' => ['/order/list', 'locked'=>0]],
                            ['label' => '已锁订单', 'url' => ['/order/list', 'locked'=>1]],
                        ],
                    ],
                    ['label' => '售后管理', 'icon' => 'phone', 'url' => ['/refund/list']],
                    [
                        'label' => '小工具',
                        'icon' => 'wrench',
                        'url' => '#',
                        'items' => [
                            ['label' => '有赞导单', 'url' => ['/tool/filter-excel']],
                            ['label' => '淘宝导单', 'url' => ['/tb-tool/import']],
                            ['label' => 'ERP导单', 'url' => ['/tool/filter-excel-v2']],
                            ['label' => '商品编码', 'url' => ['/tool/goods-list']],
                            ['label' => '短信群发', 'url' => ['/tool/sms']],
                        ],
                    ],
                    [
                        'label' => '权限管理',
                        'icon' => 'bookmark',
                        'url' => '#',
                        'items' => [
                            ['label' => '分配', 'url' => ['/admin/assignment']],
                            ['label' => '角色列表', 'url' => ['/admin/role']],
                            ['label' => '权限列表', 'url' => ['/admin/permission']],
                            ['label' => '路由列表', 'url' => ['/admin/route']],
                            ['label' => '规则列表', 'url' => ['/admin/rule']],
                        ],
                    ],
                ],
            ]
        ) ?>

        <div id="back-up" onclick="goToWhere(0)"
        style="background:#3c8dbc; color: #fff; padding: 4px 6px; width: 15px;box-sizing: content-box; position: fixed; cursor: pointer; right: 10px; bottom: 150px;">返回顶部
        </div>

    </section>

</aside>

<script type="text/javascript">
    var goToWhere = function (where)
    {
        // var me = this;
        // clearInterval (me.interval);
        // me.site = [];
        // var dom = !/.*chrome.*/i.test (navigator.userAgent) ? document.documentElement : document.body;
        // var height = !!where ? dom.scrollHeight : 0;
        // me.interval = setInterval (function ()
        // {
        //     var speed = (height - dom.scrollTop) / 16;
        //     if (speed == me.site[0])
        //     {
        //         clearInterval (me.interval);
        //         return null;
        //     }
        //     dom.scrollTop += speed;
        //     me.site.unshift (speed);
        // }, 16);
        scrollTo(0, 0);
    };
</script>
