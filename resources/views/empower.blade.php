<!doctype html>
<html lang="zh-Hans">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="Keywords" content="b2b2c,多用户商城系统,商城系统开发,商城系统"/>
    <meta name="Description"
          content="大商创是B2B2C多用户商城系统，全端覆盖S2B2C新零售电商系统，帮用户快速搭建商城平台。商城系统开发定制，免费提供系统源码，满足您所有需求。咨询热线:4001-021-758"/>
    <title>大商创授权激活</title>
    <!-- <link rel="shortcut icon" href="favicon.ico" /> -->
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/ecmoban_dsc2017/css/base.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/ecmoban_dsc2017/css/style.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('themes/ecmoban_dsc2017/css/purebox.css') }}"/>

    <script type="text/jscript" src="{{ asset('js/jquery-1.12.4.min.js') }}"></script>
    <script type="text/jscript" src="{{ asset('js/jquery.json.js') }}"></script>
    <script type="text/jscript" src="{{ asset('js/transport_jquery.js') }}"></script>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <style type="text/css">
        #emp_dialog .tip-box {
            position: absolute;
            top: 140px;
            left: 223px;
            width: auto;
            margin: 0;
        }
        #emp_dialog .tip-box .pb-ft{
            margin: 0;
            padding: 0;
        }

        #emp_dialog .tip-box .pb-ft .pb-btn{
            height: 30px;
            line-height: 30px;
            color: #fff;
            font-size: 16px;
            padding: 0 16px;
            background: #ec5051;
            border-radius: 5px;
            display: block;
            float: left;
            cursor: pointer;
        }

        .emp_content {
            font-size: 18px;
            line-height: 33px;
            text-align: center;
            padding: 45px 0;
        }

        .green {
            color: #98ba2a !important;
        }

        .textone{
            color: #020202 !important;
        }
    </style>
</head>
<body class="author">
<div class="authorization">
    <div class="author_banner">
        <div class="author_title">大商创授权</div>
        <div class="hd-search">
            <form action="empower.php" name="search_form" method="post" class="article_search">
                @csrf
                <div class="f-search">
                    <input name="appKey" type="text" id="" value="" class="textone" placeholder="请输入您的激活码"
                           autocomplete="off"/>
                    <button href="javascript:void(0);" type="button" class="author-submit" ectype="searchSubmit">激活授权
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="author_count">
        <div class="author_n">联系我们</div>
        <div class="author_c">
            <div class="item">
                <div class="item-icon">
                    <i class="item-icon-img icon-img1"></i>
                </div>
                <div class="item-info">
                    <div class="phone">4001-021-758</div>
                    <span>大商创咨询热线</span>
                </div>
            </div>
            <div class="item">
                <div class="item-icon">
                    <i class="item-icon-img icon-img2"></i>
                </div>
                <div class="item-info">
                    <div class="phone">562382947</div>
                    <span>大商创官方交流群</span>
                </div>
            </div>
        </div>
        <div class="author_c">
            <div class="item bor">
                <div class="item-icon">
                    <i class="item-icon-img icon-img3"></i>
                </div>
                <div class="item-info">
                    <div class="phone">https://www.dscmall.cn</div>
                    <span>大商创网址</span>
                </div>
            </div>
            <div class="item bor">
                <div class="item-icon">
                    <i class="item-icon-img icon-img4"></i>
                </div>
                <div class="item-info">
                    <div class="phone">上海市普陀区中山北路3553号伸大厦3层</div>
                    <span>大商创地址</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/jscript" src="{{ asset('themes/ecmoban_dsc2017/js/jquery.purebox.js') }}"></script>

<script type="text/javascript">

    $(function () {
        $("*[ectype='searchSubmit']").on("click", function () {

            var appKey = $("input[name='appKey']").val();

            $.ajax({
                type: 'post',
                url: 'empower/key',
                data: 'appKey=' + appKey,
                dataType: 'json',
                success: function (data) {
                    suc(data.error, data.version);
                }
            });
        });

        function suc(key, version) {
            var content = '';
            if (key == 1) {
                content = '<div class="emp_content"><span class="red"><p>激活失败，激活码可能不正确</p></span></div>';
            } else {
                content = '<div class="emp_content"><span class="green"><p>恭喜您，您的网站已授权！</p><p>所属产品：' + version + '</p></span></div>';
            }
            pb({
                id: "emp_dialog",
                width: 510,
                height: 200,
                cl_title: "确定",
                content: content,
                drag: true,
                foot: true,
                cBtn: false
            })
        }
    });

</script>

</body>
</html>
