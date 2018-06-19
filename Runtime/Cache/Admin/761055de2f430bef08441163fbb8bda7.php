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
        <h2><?php echo ($info['id']?'编辑':'新增'); ?>问题</h2>
    </div>
    <form action="<?php echo U();?>" method="post" class="form-horizontal">
        <div class="form-item">
            <label class="item-label">标题<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="title" value="<?php echo ((isset($info["title"]) && ($info["title"] !== ""))?($info["title"]):''); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">状态<span class="check-tips"></span></label>
            <div class="controls">
                <input type="radio" name="status" value="1" <?php if(($info["status"]) != "0"): ?>checked="checked"<?php endif; ?>>启用 &nbsp; &nbsp; <input type="radio" name="status" value="0" <?php if(($info["status"]) == "0"): ?>checked="checked"<?php endif; ?>>禁用
            </div>
        </div> 
        <div class="form-item">
            <label class="item-label">排序<span class="check-tips">（用于分组显示的顺序）</span></label>
            <div class="controls">
                <input type="text" class="text input-small" name="listorder" value="<?php echo ((isset($info["listorder"]) && ($info["listorder"] !== ""))?($info["listorder"]):0); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">发布时间<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" id="datetimepicker" class="text input-large time" name="create_time" value="<?php echo ((isset($info["create_time"]) && ($info["create_time"] !== ""))?($info["create_time"]):''); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">内容<span class="check-tips"></span></label>
            <div class="controls">
                <label class="textarea">
                    <textarea name="content"><?php echo ($info["content"]); ?></textarea>
                    <?php echo hook('adminArticleEdit', array('name'=>'content','value'=>$info['content']));?>
                </label>
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

    <!-- 时间选择 -->
    <link rel='stylesheet' type="text/css" href="/Public/static/datetimepicker/jquery.datetimepicker.css" />
    <script type="text/javascript" src="/Public/static/datetimepicker/jquery.datetimepicker.js"></script>
    <!-- 图片上传 -->
    <script type="text/javascript" src="/Public/static/webuploader/webuploader.js"></script>
    <script type="text/javascript">
        highlight_subnav("<?php echo U('Article/index');?>");
        $(function(){
            $('#datetimepicker').datetimepicker();

            //调用上传图片
            var uploader = WebUploader.create({
                // 选完文件后，是否自动上传。
                auto: false,
                // swf文件路径
                swf: '/Public/static/webuplader/Uploader.swf',
                // 文件接收服务端。
                server: "<?php echo U('File/uploadPicture');?>",
                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                pick: {
                    id: '#filePicker',
                    multiple: false
                },
                //限制图片数量
                fileSizeLimit: 40 * 1024 * 1024,
                fileSingleSizeLimit: 8 * 1024 * 1024,
                // 只允许选择图片文件。
                accept: {
                    title: 'Images',
                    extensions: 'jpg,gif,png,jpeg',
                    mimeTypes: 'image/jpg,image/gif,image/png,image/jpeg'
                }
            });

            // 当有文件添加进来的时候
            uploader.on('error', function (handler) {
                if (handler == 'Q_EXCEED_SIZE_LIMIT' || handler == 'F_EXCEED_SIZE') {
                    layer.msg('上传图片最大不超过8M');
                    return;
                }
            });

            // 当有文件添加进来的时候
            uploader.on('fileQueued', function (file) {
                uploader.upload();
            });

            // 文件上传过程中创建进度条实时显示。
            uploader.on('uploadProgress', function (file) {

            });

            // 文件上传成功，给item添加成功class, 用样式标记上传成功。
            uploader.on('uploadSuccess', function (file, ret) {
                if (ret.status == 0) {
                    layer.msg(ret.info);
                } else {
                    $('#thumb').val(ret.data.file.url);
                    $("#img").attr('src', ret.data.file.url);
                }
            });

            // 文件上传失败，显示上传出错。
            uploader.on('uploadError', function (file) {
                layer.msg('上传出错');
            });

            // 完成上传完了，成功或者失败，先删除进度条。
            uploader.on('uploadComplete', function (file) {

            });
        });
    </script>

</body>
</html>