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
        <h2><?php echo ($info['id']?'编辑':'新增'); ?>订单</h2>
    </div>
    <script type="text/javascript" src="/Public/static/uploadify/jquery.uploadify.min.js"></script>
    <form action="<?php echo U();?>" method="post" class="form-horizontal">
        <div class="form-item">
            <label class="item-label">订单类型<span class="check-tips"></span></label>
            <div class="controls">
                <select name="type" id="orderType">
                    <option value="1">试用拍照</option>
                    <option value="2">主图拍照</option>
                    <option value="3">详情拍摄</option>
                    <option value="4">详情拍摄</option>
                    <option value="5">其他</option>
                </select>
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">名称<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="name" value="<?php echo ((isset($info["name"]) && ($info["name"] !== ""))?($info["name"]):''); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">价格<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="price" value="<?php echo ($info["price"]); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">描述<span class="check-tips"></span></label>
            <div class="controls">
                <textarea name="descript" id="descript" cols="60" rows="20"><?php echo ($info["descript"]); ?></textarea>
            </div>
        </div>

        <div class="form-item cf">
            <label class="item-label">产品图片<span class="check-tips"></span></label>
            <input type="hidden" name="pic" id="pic" value="<?php echo ($info["pic"]); ?>">
            <?php if(info.pic): ?><img class="modelspic" src="<?php echo ($info["pic"]); ?>" alt=""><?php endif; ?>
            <div class="controls" id="fileuploader_pic" style="width: 100%;">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">模特人数<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="num" value="<?php echo ($info["num"]); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">账号等级<span class="check-tips"></span></label>
            <div class="controls">
                <select name="level">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
        </div>
        <div class="form-item cf">
            <label class="item-label">头像<span class="check-tips"></span></label>
            <input type="hidden" name="thumb" id="thumb" value="<?php echo ($info["thumb"]); ?>">
            <?php if(info.thumb): ?><img class="modelspic" src="<?php echo ($info["thumb"]); ?>" alt=""><?php endif; ?>
            <div class="controls" id="fileuploader" style="width: 100%;">
            </div>
        </div>

        <div class="form-item">
            <label class="item-label">昵称<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="nickname" value="<?php echo ($info["nickname"]); ?>">
            </div>
        </div>

        <div class="form-item">
            <label class="item-label">淘气值<span class="check-tips"></span></label>
            <div class="controls">
                <select name="naughty" id="naughty">
                    <option value="1">1000以下</option>
                    <option value="2">1000-1500</option>
                    <option value="3">1500以上</option>
                </select>
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">联系方式<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="contact" value="<?php echo ($info["contact"]); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">奖品<span class="check-tips"></span></label>
            <div class="controls">
                <input type="text" class="text input-large" name="prize" value="<?php echo ($info["prize"]); ?>">
            </div>
        </div>
        <div class="form-item">
            <label class="item-label">状态<span class="check-tips"></span></label>
            <div class="controls">
                <input type="radio" name="status" value="1" <?php if(($info["status"]) != "0"): ?>checked="checked"<?php endif; ?>>启用
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
        highlight_subnav("<?php echo U('order');?>");

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
                        var file = response.data[0];
                        $('#thumb').val(file);
                        $('.modelspic').remove();
                        $('#fileuploader').before('<img class="modelspic" src="'+ file +'" />');
                    }else{
                        alert('图片上传失败');
                    }
                }
            });

            $("#fileuploader_pic").uploadFile({
                url:"Admin/File/upload",
                dragDropStr:'点击选择图片',
                uploadStr:'点击选择图片',
                allowedTypes:'jpg,png,jpeg,gif',
                extErrorStr:'只能上传以下格式的文件:',
                fileName:"myfile",
                onSuccess:function(files, response, xhr, pd){
                    if(parseInt(response.status) == 1){ //上传成功
                        var file = response.data[0];
                        $('#pic').val(file);
                        $('.modelspic').remove();
                        $('#fileuploader_pic').before('<img class="modelspic" src="'+ file +'" />');
                    }else{
                        alert('图片上传失败');
                    }
                }
            });
        })
    </script>

</body>
</html>