<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="referrer" content="never">
        <title>管理后台</title>
        <link href="/Public/favicon.ico" type="image/x-icon" rel="shortcut icon">
        <link rel="stylesheet" type="text/css" href="/Public/Admin/css/base.css" media="all">
        <link rel="stylesheet" type="text/css" href="/Public/Admin/css/common.css" media="all">
        <link rel="stylesheet" type="text/css" href="/Public/Admin/css/module.css">
        <link rel="stylesheet" type="text/css" href="/Public/Admin/css/style.css" media="all">
        <link rel="stylesheet" type="text/css" href="/Public/Admin/css/<?php echo (C("COLOR_STYLE")); ?>.css" media="all">
        <!--[if lt IE 9]>
       <script type="text/javascript" src="/Public/static/jquery-1.10.2.min.js"></script>
       <![endif]--><!--[if gte IE 9]><!-->
        <script type="text/javascript" src="/Public/static/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="/Public/Admin/js/jquery.mousewheel.js"></script>
        <script type="text/javascript" src="/Public/static/layer/layer.js"></script>
        <script type="text/javascript" src="/Public/Admin/js/public.js"></script>
        <!--<![endif]-->
    
</head>
<body>
    <!-- 头部 -->
    <div class="header">
        <!-- Logo -->
        <span class="logo"></span>
        <!-- /Logo -->

        <!-- 主导航 -->
        <ul class="main-nav">
            <?php if(is_array($__MENU__["main"])): $i = 0; $__LIST__ = $__MENU__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>"><a href="<?php echo (U($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <!-- /主导航 -->

        <!-- 用户栏 -->
        <div class="user-bar">
            <a href="javascript:;" class="user-entrance"><i class="icon-user"></i></a>
            <ul class="nav-list user-menu hidden">
                <li class="manager">你好，<em title="<?php echo session('user_auth.username');?>"><?php echo session('user_auth.username');?></em></li>
                <li><a href="<?php echo U('User/updatePassword');?>">修改密码</a></li>
                <li><a href="<?php echo U('User/updateNickname');?>">修改昵称</a></li>
                <li><a href="<?php echo U('Public/logout');?>">退出</a></li>
            </ul>
        </div>
    </div>
    <!-- /头部 -->

    <!-- 边栏 -->
    <div class="sidebar">
        <!-- 子导航 -->
        
            <div id="subnav" class="subnav">
                <?php if(!empty($_extra_menu)): ?>
                    <?php echo extra_menu($_extra_menu,$__MENU__); endif; ?>
                <?php if(is_array($__MENU__["child"])): $i = 0; $__LIST__ = $__MENU__["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
                    <?php if(!empty($sub_menu)): if(!empty($key)): ?><h3><i class="icon icon-unfold"></i><?php echo ($key); ?></h3><?php endif; ?>
                        <ul class="side-sub-menu">
                            <?php if(is_array($sub_menu)): $i = 0; $__LIST__ = $sub_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li>
                                    <a class="item" href="<?php echo (U($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a>
                                </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul><?php endif; ?>
                    <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        
        <!-- /子导航 -->
    </div>
    <!-- /边栏 -->

    <!-- 内容区 -->
    <div id="main-content">
        <div id="top-alert" class="fixed alert alert-error" style="display: none;">
            <button class="close fixed" style="margin-top: 4px;">&times;</button>
            <div class="alert-content">这是内容</div>
        </div>
        <div id="main" class="main">
            
                <!-- nav -->
                <?php if(!empty($_show_nav)): ?><div class="breadcrumb">
                        <span>您的位置:</span>
                        <?php $i = '1'; ?>
                        <?php if(is_array($_nav)): foreach($_nav as $k=>$v): if($i == count($_nav)): ?><span><?php echo ($v); ?></span>
                                <?php else: ?>
                                <span><a href="<?php echo ($k); ?>"><?php echo ($v); ?></a>&gt;</span><?php endif; ?>
                            <?php $i = $i+1; endforeach; endif; ?>
                    </div><?php endif; ?>
                <!-- nav -->
            

            
    <div class="main-title">
        <h2>模卡列表</h2>
    </div>

    <div class="cf">
        <button class="btn ajax-post confirm" url="<?php echo U('delOrder');?>" target-form="ids">删 除</button>

        <!-- 高级搜索 -->
        <div class="search-form fr cf">
            <div class="sleft">
                <select name="status">
                    <option value="-1" <?php if(($status) == "-1"): ?>selected<?php endif; ?>>所有</option>
                    <option value="0" <?php if(($status) == "0"): ?>selected<?php endif; ?>>待支付</option>
                    <option value="1" <?php if(($status) == "1"): ?>selected<?php endif; ?>>支付成功</option>
                    <option value="2" <?php if(($status) == "2"): ?>selected<?php endif; ?>>支付失败</option>
                </select>
            </div>
            <div class="sleft">
                <input type="hidden" name="status" id="status" value="<?php echo ($status); ?>">
                <input type="text" name="nickname" class="search-input" value="<?php echo I('nickname');?>" placeholder="请输入打赏用户名">
                <a class="sch-btn" href="javascript:;" id="search" url="<?php echo U('reward');?>"><i class="btn-search"></i></a>
            </div>
        </div>
    </div>

    <div class="data-table table-striped">
        <table>
            <thead>
            <tr>
                <th class="row-selected">
                    <input class="checkbox check-all" type="checkbox">
                </th>
                <th>ID</th>
                <th style="width: 10%;">打赏人</th>
                <th style="width: 10%">被打赏</th>
                <th style="width: 5%">金额</th>
                <th style="width: 15%">订单号</th>
                <th style="width: 15%">微信单号</th>
                <th style="width: 10%">支付状态</th>
                <th>下单时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($list)): if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                        <td><input class="ids row-selected" type="checkbox" name="id[]" value="<?php echo ($vo["id"]); ?>"></td>
                        <td><?php echo ($vo["id"]); ?></td>
                        <td><?php echo ($vo["u_nickname"]); ?></a></td>
                        <td><?php echo ($vo["r_nickname"]); ?></a></td>
                        <td><?php echo ($vo["amount"]); ?></td>
                        <td><?php echo ($vo["order_no"]); ?></td>
                        <td><?php echo ($vo["wx_no"]); ?></td>
                        <td>
                            <?php if($vo['status'] == 1): ?>支付成功
                                <?php elseif($vo['status'] == 2): ?>
                                支付失败
                                <?php else: ?>
                                待支付<?php endif; ?>
                        </td>
                        <td><?php echo (time_format($vo["create_time"])); ?></td>
                        <td>
                            <a title="编辑" href="<?php echo U('editCard',array('cid'=>$vo['id']));?>">编辑</a>
                            <a class="confirm ajax-get" title="删除" href="<?php echo U('delOrder',array('id'=>$vo['id']));?>">彻底删除</a>
                        </td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                <?php else: ?>
                <td colspan="12" class="text-center"> aOh! 暂时还没有内容! </td><?php endif; ?>
            </tbody>
        </table>
        <!-- 分页 -->
        <div class="page">
            <?php echo ($_page); ?>
        </div>
    </div>

        </div>
        <div class="cont-ft">
            <div class="copyright">
                <div class="fl">管理平台</div>
                <div class="fr"></div>
            </div>
        </div>
    </div>
    <!-- /内容区 -->
    <script type="text/javascript">
        var URL = '/index.php?s=/admin/moka';
        var SELF = '<?php echo US();?>';
        (function () {
             //指定当前组模块URL地址 

            var ThinkPHP = window.Think = {
                "ROOT": "", //当前网站地址
                "APP": "/index.php?s=", //当前项目地址
                "PUBLIC": "/Public", //项目公共目录地址
                "DEEP": "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
                "MODEL": ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
                "VAR": ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
            }
        })();
    </script>
    <script type="text/javascript" src="/Public/static/think.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/common.js"></script>
    <script type="text/javascript">
        +function () {
            var $window = $(window), $subnav = $("#subnav"), url;
            $window.resize(function () {
                $("#main").css("min-height", $window.height() - 130);
            }).resize();

            /* 左边菜单高亮 */
            url = window.location.pathname + window.location.search;
            url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
            $subnav.find("a[href='" + url + "']").parent().addClass("current");

            /* 左边菜单显示收起 */
            $("#subnav").on("click", "h3", function () {
                var $this = $(this);
                $this.find(".icon").toggleClass("icon-fold");
                $this.next().slideToggle("fast").siblings(".side-sub-menu:visible").
                        prev("h3").find("i").addClass("icon-fold").end().end().hide();
            });

            $("#subnav h3 a").click(function (e) {
                e.stopPropagation()
            });

            /* 头部管理员菜单 */
            $(".user-bar").mouseenter(function () {
                var userMenu = $(this).children(".user-menu ");
                userMenu.removeClass("hidden");
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function () {
                var userMenu = $(this).children(".user-menu");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function () {
                    userMenu.addClass("hidden")
                }, 100));
            });

            /* 表单获取焦点变色 */
            $("form").on("focus", "input", function () {
                $(this).addClass('focus');
            }).on("blur", "input", function () {
                $(this).removeClass('focus');
            });
            $("form").on("focus", "textarea", function () {
                $(this).closest('label').addClass('focus');
            }).on("blur", "textarea", function () {
                $(this).closest('label').removeClass('focus');
            });

            // 导航栏超出窗口高度后的模拟滚动条
            var sHeight = $(".sidebar").height();
            var subHeight = $(".subnav").height();
            var diff = subHeight - sHeight; //250
            var sub = $(".subnav");
            if (diff > 0) {
                $(window).mousewheel(function (event, delta) {
                    if (delta > 0) {
                        if (parseInt(sub.css('marginTop')) > -10) {
                            sub.css('marginTop', '0px');
                        } else {
                            sub.css('marginTop', '+=' + 10);
                        }
                    } else {
                        if (parseInt(sub.css('marginTop')) < '-' + (diff - 10)) {
                            sub.css('marginTop', '-' + (diff - 10));
                        } else {
                            sub.css('marginTop', '-=' + 10);
                        }
                    }
                });
            }
        }();
    </script>

    <script type="text/javascript">
        $(function () {
            //筛选
            $('[name="status"]').on('change', function () {
                var status = $(this).val();
                $('#status').val(status);
                $("#search").click();
            });
            //搜索功能
            $("#search").click(function () {
                var url = $(this).attr('url');
                var query = $('.search-form').find('input').serialize();
                query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
                query = query.replace(/^&/g, '');
                if (url.indexOf('?') > 0) {
                    url += '&' + query;
                } else {
                    url += '?' + query;
                }
                window.location.href = url;
            });
            //回车搜索
            $(".search-input").keyup(function (e) {
                if (e.keyCode === 13) {
                    $("#search").click();
                    return false;
                }
            });
            //点击排序
            $('.list_sort').click(function () {
                var url = $(this).attr('url');
                var ids = $('.ids:checked');
                var param = '';
                if (ids.length > 0) {
                    var str = new Array();
                    ids.each(function () {
                        str.push($(this).val());
                    });
                    param = str.join(',');
                }

                if (url != undefined && url != '') {
                    window.location.href = url + '/ids/' + param;
                }
            });
        });
    </script>
    <script type="text/javascript">
        //导航高亮
        highlight_subnav("<?php echo U('reward');?>");
    </script>

</body>
</html>