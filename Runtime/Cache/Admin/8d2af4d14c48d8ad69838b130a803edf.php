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
            

            
    <style>
        .modelspic{height: 150px}
    </style>
    <div class="main-title">
        <h2><?php echo ($info['id']?'编辑':'新增'); ?>模卡风格</h2>
    </div>
    <form action="<?php echo U();?>" method="post" class="form-horizontal">
        <div class="form-item">
            <label class="item-label">别名<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="id_a" value="<?php echo ($info["id_a"]); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">排序<span class="check-tips">数字越小越靠前</span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="sort" value="<?php echo ($info["sort"]); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">风格标题<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="title" value="<?php echo ((isset($info["title"]) && ($info["title"] !== ""))?($info["title"]):''); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">风格名称<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="name" value="<?php echo ((isset($info["name"]) && ($info["name"] !== ""))?($info["name"]):''); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">设置图片数量<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="pic_num" value="<?php echo ((isset($info["pic_num"]) && ($info["pic_num"] !== ""))?($info["pic_num"]):''); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">操作<span class="check-tips"></span></label>
            <div class="controls">
                <input type="button" style="" class="btn" id="clearPics" value="清空所有图片"/>
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">选择图片<span class="check-tips"></span></label>
            <input type="hidden" name="pics" id="pics" value="<?php echo implode(',',$info['pics']);?>">
            <?php if(is_array($info["pics"])): foreach($info["pics"] as $key=>$item): ?><img class="modelspic" src="<?php echo ($item); ?>" alt=""><?php endforeach; endif; ?>
            <div class="controls" id="fileuploader" style="width: 100%;">
            </div>
        </div>

        <div class="form-item">
            <label class="item-label">图片点集合<span class="check-tips"></span></label>
            <div class="controls">
                <textarea name="points" id="" cols="90" rows="10"><?php echo ($info["points"]); ?></textarea>
            </div>
        </div>

        <div class="form-item">
            <label class="item-label">扩展信息<span class="check-tips"></span></label>
            <div class="controls">
                <span>淘宝等级：</span><input type="text" class="text input-normall" name="ext[level]" value="<?php echo ($info["ext_info"]["level"]); ?>">&nbsp;&nbsp;
                <span>淘气值：</span><input type="text" class="text input-normall" name="ext[naughty]" value="<?php echo ($info["ext_info"]["naughty"]); ?>">
            </div>
            <div class="controls">
                <span>地&nbsp;&nbsp;区：</span><input type="text" class="text input-small" name="ext[province]" value="<?php echo ($info["ext_info"]["province"]); ?>">省&nbsp;&nbsp;
                <input type="text" class="text input-small" name="ext[city]" value="<?php echo ($info["ext_info"]["city"]); ?>">市
            </div>
            <div class="controls">
                <span>身&nbsp;&nbsp;高：</span><input type="text" class="text input-small" name="ext[height]" value="<?php echo ($info["ext_info"]["height"]); ?>">cm&nbsp;&nbsp;
                <span>胸&nbsp;&nbsp;围：</span><input type="text" class="text input-small" name="ext[chestline]" value="<?php echo ($info["ext_info"]["chestline"]); ?>">cm&nbsp;&nbsp;
                <span>腰&nbsp;&nbsp;围：</span><input type="text" class="text input-small" name="ext[waistline]" value="<?php echo ($info["ext_info"]["waistline"]); ?>">cm&nbsp;&nbsp;
                <span>臀&nbsp;&nbsp;围：</span><input type="text" class="text input-small" name="ext[hipline]" value="<?php echo ($info["ext_info"]["hipline"]); ?>">cm&nbsp;&nbsp;
            </div>
            <div class="controls">
                <span>体&nbsp;&nbsp;重：</span><input type="text" class="text input-small" name="ext[weight]" value="<?php echo ($info["ext_info"]["weight"]); ?>">kg
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">状态<span class="check-tips"></span></label>
            <div class="controls">
                <input type="radio" name="status" value="1" <?php if(($info["status"]) != "0"): ?>checked="checked"<?php endif; ?>>启用 &nbsp; &nbsp;
                <input type="radio" name="status" value="0" <?php if(($info["status"]) == "0"): ?>checked="checked"<?php endif; ?>>禁用
            </div>
        </div>
        <div class="form-item">
            <input type="hidden" name="id" value="<?php echo ((isset($info["id"]) && ($info["id"] !== ""))?($info["id"]):''); ?>">
            <button class="btn submit-btn ajax-post" id="submit" type="submit" target-form="form-horizontal">确 定</button>
            <button class="btn btn-return" onclick="javascript:history.back(-1);return false;">返 回</button>
        </div>
    </form>

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

    <script type="text/javascript" src="/Public/Admin/js/jquery.uploadfile.min.js"></script>
    <script type="text/javascript">
        //导航高亮
        highlight_subnav("<?php echo U('index');?>");
        $(function(){
            $("#fileuploader").uploadFile({
                url:"Admin/File/upload",
                dragDropStr:'点击选择图片',
                uploadStr:'点击选择图片',
                allowedTypes:'jpg,png,jpeg,gif',
                extErrorStr:'只能上传以下格式的文件:',
                fileName:"myfile",
                onSuccess:function(files, response, xhr, pd){
                    if(parseInt(response.status) == 1){ //上传成功
                        var file = response.data.myfile.thumb;
                        var pics = $('#pics').val();
                        pics += pics ? ',' + file : file;
                        $('#pics').val(pics);
                        $('#fileuploader').before('<img class="modelspic" src="'+ file +'" />');
                    }else{
                        alert('图片上传失败');
                    }
                }
            });
        })

        $('#clearPics').click(function(){
            $('.modelspic').remove();
            $('#pics').val('');
        })
    </script>

</body>
</html>