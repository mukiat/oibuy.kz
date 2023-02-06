@include('admin.base.header')

{{--商家后台选择分类、品牌、搜索商品 涉及js--}}
<link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}">
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/mobile/js/select_category.js') }}"></script>

<style type="text/css">
    .article {
        border: 1px solid #ddd;
        padding: 5px 5px 0 5px;
    }

    .cover {
        /*height: 160px;*/
        position: relative;
        margin-bottom: 5px;
        overflow: hidden;
    }

    .article .cover img {
        width: 100%;
        height: auto;
    }

    .article span {
        height: 40px;
        line-height: 40px;
        display: block;
        z-index: 5;
        position: absolute;
        width: 100%;
        bottom: 0px;
        color: #FFF;
        padding: 0 10px;
        background-color: rgba(0, 0, 0, 0.6)
    }

    .article_list {
        padding: 5px;
        border: 1px solid #ddd;
        border-top: 0;
        overflow: hidden;
    }

    .checkbox label {
        width: 100%;
        position: relative;
        padding: 0;
    }

    .checkbox .news_mask {
        position: absolute;
        left: 0;
        top: 0;
        background-color: #000;
        opacity: 0.5;
        width: 100%;
        height: 100%;
        z-index: 8; /*选择分类下拉为9 避免遮挡要小于9*/
    }

    .checkbox .news_mask img {
        width: 50px;
        position: absolute;
        left: 50%;
        top: 50%;
        margin-left: -25px;
        margin-top: -25px;
    }

    .goods_search_div .btn{
        height: 30px;
    }

    .goods-list h4 {
        min-height: 30px;
        max-height: 30px;
        text-overflow:ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    .switch_info {
        padding: 30px 20px 0px;
    }

    .goods-name em {
        height: 17px;
        width: 37px;
        float: left;
        font-size: 0;
        background: url('{{ asset('assets/admin/images/act_icon.png') }}') no-repeat;
    }

    /* 选择商品弹窗页面分页样式（基于平台样式 需要用到）*/
    .panel-footer .pagination {  float: right;  }
    .panel-footer .pagination li{ display:inline-block; margin-right:5px; min-width:20px; height:24px; padding:0 3px; line-height:24px; border:1px solid #eee; border-radius:3px;color:#7d7d7d; position:relative; font-size:12px;}
    .panel-footer .pagination li span{ display:block; min-width:20px; height:24px; float:left; text-align:center;}
    .panel-footer .pagination li .prev,.pagination li .next{ background:url('{{ asset('assets/seller/images/login_icon.png') }}') no-repeat; font-size:0; width:10px;  height:10px; display:inline-block; position:absolute;}
    .panel-footer .pagination li .prev{ background-position:-38px -169px; top:7px; left:7px;}
    .panel-footer .pagination li .next{ background-position:-51px -169px; top:7px; left:9px;}

</style>
<div class="fancy">

    <div class="panel panel-default" style="margin:0;">
        <div class="panel-heading">{{ __('admin/common.select_goods') }} </div>

        <div class="content">
            <div class="main-info">

                <div class="switch_info" style="overflow:inherit;">

                    <!--搜索-->
                    <form method="post" action="{{ route('seller/goodslabel/select_goods', ['label_id' => $filter['label_id'], 'type' => $filter['type']]) }}" role="form" id="group_buy_form" class="validation">

                    <div class="goods_search_div bor_bt_das">
                        <div class="search_select">
                            <div class="categorySelect">
                                <div class="selection">
                                    <input type="text" name="category_name" id="category_name" class="text w250 valid" value="{{ __('admin/common.please_category') }}" autocomplete="off" readonly="" data-filter="cat_name">
                                    <input type="hidden" name="category_id" id="category_id" value="0" data-filter="cat_id">
                                </div>
                                <div class="select-container" style="display: none;">
                                    {{--分类搜索--}}
                                    @include('admin.base.select_category')
                                    {{--分类搜索--}}
                                </div>
                            </div>
                        </div>
                        <div class="search_select">
                            <div class="brandSelect">
                                <div class="selection">
                                    <input type="text" name="brand_name" id="brand_name" class="text w120 valid" value="{{ __('admin/common.choose_brand') }}" autocomplete="off" readonly="" data-filter="brand_name">
                                    <input type="hidden" name="brand_id" id="brand_id" value="0" data-filter="brand_id">
                                </div>
                                <div class="brand-select-container" style="display: none;">
                                    <div class="brand-top">
                                        <div class="letter">
                                            <ul>
                                                <li><a href="javascript:void(0);" data-letter="">{{ __('admin/common.all_brand') }}</a></li>
                                                <li><a href="javascript:void(0);" data-letter="A">A</a></li>
                                                <li><a href="javascript:void(0);" data-letter="B">B</a></li>
                                                <li><a href="javascript:void(0);" data-letter="C">C</a></li>
                                                <li><a href="javascript:void(0);" data-letter="D">D</a></li>
                                                <li><a href="javascript:void(0);" data-letter="E">E</a></li>
                                                <li><a href="javascript:void(0);" data-letter="F">F</a></li>
                                                <li><a href="javascript:void(0);" data-letter="G">G</a></li>
                                                <li><a href="javascript:void(0);" data-letter="H">H</a></li>
                                                <li><a href="javascript:void(0);" data-letter="I">I</a></li>
                                                <li><a href="javascript:void(0);" data-letter="J">J</a></li>
                                                <li><a href="javascript:void(0);" data-letter="K">K</a></li>
                                                <li><a href="javascript:void(0);" data-letter="L">L</a></li>
                                                <li><a href="javascript:void(0);" data-letter="M">M</a></li>
                                                <li><a href="javascript:void(0);" data-letter="N">N</a></li>
                                                <li><a href="javascript:void(0);" data-letter="O">O</a></li>
                                                <li><a href="javascript:void(0);" data-letter="P">P</a></li>
                                                <li><a href="javascript:void(0);" data-letter="Q">Q</a></li>
                                                <li><a href="javascript:void(0);" data-letter="R">R</a></li>
                                                <li><a href="javascript:void(0);" data-letter="S">S</a></li>
                                                <li><a href="javascript:void(0);" data-letter="T">T</a></li>
                                                <li><a href="javascript:void(0);" data-letter="U">U</a></li>
                                                <li><a href="javascript:void(0);" data-letter="V">V</a></li>
                                                <li><a href="javascript:void(0);" data-letter="W">W</a></li>
                                                <li><a href="javascript:void(0);" data-letter="X">X</a></li>
                                                <li><a href="javascript:void(0);" data-letter="Y">Y</a></li>
                                                <li><a href="javascript:void(0);" data-letter="Z">Z</a></li>
                                                <li><a href="javascript:void(0);" data-letter="QT">{{ __('admin/common.other') }}</a></li>
                                            </ul>
                                        </div>
                                        <div class="b_search">
                                            <input name="search_brand_keyword" id="search_brand_keyword" type="text" class="b_text" placeholder="{{ __('admin/common.search_brand') }} " autocomplete="off">
                                            <a href="javascript:void(0);" class="btn-mini"><i class="fa fa-search"></i></a>
                                        </div>
                                    </div>
                                    <div class="brand-list ps-container ps-active-y">

                                        <!--品牌搜索-->
                                        @include('admin.base.select_brand_list')

                                        <div class="ps-scrollbar-x-rail" style="width: 234px; display: none; left: 0px; bottom: 3px;">
                                            <div class="ps-scrollbar-x" style="left: 0px; width: 0px;"></div>
                                        </div>
                                        <div class="ps-scrollbar-y-rail" style="top: 0px; height: 220px; display: inherit; right: 3px;">
                                            <div class="ps-scrollbar-y" style="top: 0px; height: 13px;"></div>
                                        </div>
                                    </div>
                                    <div class="brand-not" style="display:none;">{{ __('admin/common.no_brand_records') }}</div>
                                </div>
                            </div>
                        </div>


                        <div class="input">
                            @csrf
                            <input type="hidden" name="ru_id" value="{{ $filter['ru_id'] ?? -1 }}">
                            <input type="hidden" name="label_id" value="{{ $filter['label_id'] ?? 0 }}">
                            <input type="text" name="keyword" class="text w150" placeholder="{{ __('admin/common.keyword') }}" value="{{ $filter['keyword'] ?? '' }}" data-filter="keyword" autocomplete="off">

                            <input type="submit" value="{{ __('admin/common.button_search')  }}" class="btn search_button">
                        </div>
                    </div>

                    </form>

                </div>
            </div>
        </div>

        <div class="container-fluid">

            <div class="row" style="min-height: 200px;">

                @if(!empty($goods))

                    @foreach($goods as $k=>$v)

                    <div class="col-sm-3 col-md-2 col-lg-2 ">

                        <div class="checkbox article goods-list">
                            <label>
                                <input type="checkbox" name="goods[]"  @if(isset($v['checked']) && $v['checked'] == 1) checked @endif value="{{ $v['goods_id'] }}" class="hidden artlist"/>
                                <div class="goods-name">
                                    <div class="cover">
                                        <img src="{{ $v['goods_thumb'] }}" title="{{ $v['goods_name'] }}"/>

                                        <h4 class="pt5"> {{ $v['goods_name'] }}</h4>
                                    </div>

                                </div>
                                <div class="news_mask @if(isset($v['checked']) && $v['checked'] == 1) show @else hidden @endif "><img src="{{ asset('img/radio.png') }}"/></div>
                            </label>
                        </div>

                    </div>

                    @endforeach

                @else
                    <div class="no-records text-center">
                        {{ __('admin/common.no_records') }}
                    </div>
                @endif
            </div>

        </div>
        <div class="panel-footer">
            @include('seller.base.seller_pageview')
            <div class="info_btn of">
                <input type="button" value="{{ __('admin/common.button_submit')}}" class="button btn-danger bg-red confrim" />
                <input type="reset" value="{{ __('admin/common.button_reset') }}" class="button button_reset" />
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    $(function () {

        // 全局 session storage
        var storage_name = 'label_goods_ids_{{ $ru_id ?? 0 }}';

        var articleDate = window.sessionStorage ? sessionStorage.getItem(storage_name) : Session.read(storage_name);
        // 本页面
        var article = [];
        // 显示已经选中的
        if (articleDate) {
            articleDate.split(",").map(function (val, index) {
                //console.log(val)
                $("input[value=" + val + "]").attr("checked", 'checked');
                $("input[value=" + val + "]").siblings('.news_mask').removeClass("hidden");
                // 保存已有值
                article.push(val);
            });
        }
        // 点击选择与取消
        $(".artlist").click(function () {
            article = article.unique3(); // 去重

            var select_goods_id = $(this).val();

            // 选择
            if ($(this).is(":checked")) {

                $(this).siblings(".news_mask").removeClass("hidden");  // 显示遮罩 选中状态
                // 添加
                if (article.indexOf($(this).val()) == -1) {
                    article.push($(this).val());
                }

            } else {
                // 取消选择
                $(this).attr("checked", false);
                $(this).siblings(".news_mask").addClass("hidden");  // 移除遮罩  取消选中
                // 删除
                article.splice(article.indexOf($(this).val()), 1);
            }
            //article = article.unique3(); // 去重
            sessionStorage.setItem(storage_name, article);  // 存储sessionStorage
        });

        //选择提交
        $(".confrim").click(function () {
            var formArticleDate = '';
            formArticleDate = sessionStorage.getItem(storage_name);
            formArticleDate = formArticleDate ? formArticleDate.split(",") : ''; // 字符串转数组

            // 兼容
            var localArticle = [];
            $("input[type=checkbox]:checked").each(function () {
                localArticle.push($(this).val());
            });

            // 合并 本地+缓存已选择
            if (localArticle && formArticleDate) {
                formArticleDate = localArticle.concat(formArticleDate);
                formArticleDate = formArticleDate.unique3(); // 去重
            }

            formArticleDate = formArticleDate ? formArticleDate : localArticle;

            sessionStorage.removeItem(storage_name); // 清空 sessionStorage label_goods_ids

            // 数组转字符串
            var str = formArticleDate.toString();

            if (str) {
                // 请求
                var url = "{!! route('seller/goodslabel/bind_goods', ['label_id' => $filter['label_id'], 'handler' => 'import']) !!}";

                $.post(url, {goods_id:str}, function(data) {
                    layer.msg(data.msg);
                    if (data.error == 0) {
                        $('input[type="button"]').attr('disabled', true);
                        if (window.parent.$.fancybox) {
                            setTimeout(function () {
                                window.parent.$.fancybox.close();
                            }, 500);
                        }
                    }
                    return false;
                }, 'json');
            }

        });

        // 重置选择
        $(".button_reset").click(function () {
            sessionStorage.removeItem(storage_name);
            window.location.reload();
        });


        // 去重
        Array.prototype.unique3 = function () {
            var res = [];
            var json = {};
            for (var i = 0; i < this.length; i++) {
                if (!json[this[i]]) {
                    res.push(this[i]);
                    json[this[i]] = 1;
                }
            }
            return res;
        }
        // 查找位置
        Array.prototype.indexOf = function (val) {
            for (var i = 0; i < this.length; i++) {
                if (this[i] == val) return i;
            }
            return -1;
        };
        // 移除
        Array.prototype.remove = function (val) {
            var index = this.indexOf(val);
            if (index > -1) {
                this.splice(index, 1);
            }
        };

    })

</script>
@include('admin.base.footer')