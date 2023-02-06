@include('admin.base.header')

<script src="{{ asset('assets/mobile/vendor/common/imitate_select.js') }}" type="text/javascript"></script>

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

    .goods-name .distribution {
        background-position: -383px 0;
    }
</style>
<div class="fancy">

    <div class="panel panel-default" style="margin:0;">
        <div class="panel-heading">{{ __('admin/common.select_goods') }} </div>

        <div class="content">
            <div class="main-info">

                <div class="switch_info" style="overflow:inherit;">

                    <!--搜索-->
                    <form method="post" action="{{ route('admin/goodslabel/select_goods', ['label_id' => $filter['label_id'], 'type' => $filter['type']]) }}" role="form" id="group_buy_form" class="validation">

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
                            <input type="hidden" name="ru_id" value="0">
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
            @include('admin.base.pageview')
            <div class="info_btn of">
                <input type="button" value="{{ __('admin/common.button_submit')}}" class="button btn-danger bg-red confrim" />
                <input type="reset" value="{{ __('admin/common.button_reset') }}" class="button button_reset" />
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    $(function () {

        // 选择品牌
        $('input[name="brand_name"]').click(function () {
            $(".brand-select-container").hide();
            $(this).parents(".selection").next(".brand-select-container").show();
            //$(".brand-list").perfectScrollbar("destroy");
            //$(".brand-list").perfectScrollbar();
        });

        /* AJAX选择品牌 */
        // 根据首字母查询
        $('.letter').find('a[data-letter]').click(function () {
            var goods_id = $("input[name=goods_id]").val();
            var letter = $(this).attr('data-letter');
            $(".brand-not strong").html(letter);
            $.post("{{ route('admin/base/search_brand') }}", {goods_id: goods_id, letter: letter}, function (result) {
                if (result.content) {
                    $(".brand-list").html(result.content);
                    $(".brand-not").hide();
                } else {
                    $(".brand-list").html("");
                    $(".brand-not").show();
                }
                //$(".brand-list").perfectScrollbar("destroy");
                //$(".brand-list").perfectScrollbar();
            }, 'json')
        });


        // 根据关键字查询品牌
        $('.b_search').find('a').click(function () {
            var goods_id = $("input[name=goods_id]").val();
            var keyword = $(this).prev().val();
            $(".brand-not strong").html(keyword);
            $.post("{{ route('admin/base/search_brand') }}", {goods_id: goods_id, keyword: keyword}, function (result) {
                if (result.content) {
                    $(".brand-list").html(result.content);
                    $(".brand-not").hide();
                } else {
                    $(".brand-list").html("");
                    $(".brand-not").show();
                }
                //$(".brand-list").perfectScrollbar("destroy");
                //$(".brand-list").perfectScrollbar();
            }, 'json')
        });

        // 选择品牌
        $('.brand-list').on('click', 'li', function () {
            $(this).parents('.brand-select-container').prev().find('input[data-filter=brand_id]').val($(this).data('id'));
            $(this).parents('.brand-select-container').prev().find('input[data-filter=brand_name]').val($(this).data('name'));
            $('.brand-select-container').hide();
        });

        jQuery.category = function () {
            $('.selection input[name="category_name"]').click(function () {
                $(this).parents(".selection").next('.select-container').show();
            });

            /*点击分类获取下级分类列表*/
            $(document).on('click', '.select-list li', function () {
                var obj = $(this);
                var cat_id = $(this).data('cid');
                $.post("{{ route('admin/base/select_category') }}", {cat_id: cat_id}, function (result) {
                    if (result.content) {
                        obj.parents(".categorySelect").find("input[data-filter=cat_name]").val(result.cat_nav); //修改cat_name
                        obj.parents(".select-container").html(result.content);
                        //$(".select-list").perfectScrollbar("destroy");
                        //$(".select-list").perfectScrollbar();
                    }
                }, 'json');
                obj.parents(".categorySelect").find("input[data-filter=cat_id]").val(cat_id); //修改cat_id

                var cat_level = obj.parents(".categorySelect").find(".select-top a").length; //获取分类级别
                if (cat_level >= 3) {
                    $('.categorySelect .select-container').hide();
                }
            });

            //点击a标签返回所选分类 by wu
            $(document).on('click', '.select-top a', function () {
                var obj = $(this);
                var cat_id = $(this).data('cid');
                $.post("{{ route('admin/base/select_category') }}", {cat_id: cat_id}, function (result) {
                    if (result.content) {
                        obj.parents(".categorySelect").find("input[data-filter=cat_name]").val(result.cat_nav); //修改cat_name
                        obj.parents(".select-container").html(result.content);
                        //$(".select-list").perfectScrollbar("destroy");
                        //$(".select-list").perfectScrollbar();
                    }
                }, 'json');
                obj.parents(".categorySelect").find("input[data-filter=cat_id]").val(cat_id); //修改cat_id
            });
            /*分类搜索的下拉列表end*/
        }


        $(document).click(function (e) {
            /*
             **点击空白处隐藏展开框
             */

            //会员搜索
            if (e.target.id != 'user_name' && !$(e.target).parents("div").is(".select-container")) {
                $('.selection_select .select-container').hide();
            }
            //品牌
            if (e.target.id != 'brand_name' && !$(e.target).parents("div").is(".brand-select-container")) {
                $('.brand-select-container').hide();
                $('.brandSelect .brand-select-container').hide();
            }
            //分类
            if (e.target.id != 'category_name' && !$(e.target).parents("div").is(".select-container")) {
                $('.categorySelect .select-container').hide();
            }

            //日期选择插件
            if (!$(e.target).parent().hasClass("text_time")) {
                $(".iframe_body").removeClass("relative");
            }
        });


        //分类选择
        $.category();

        $(".categorySelect .select-container ul li").click(function () {
            var cate = $(this).attr("data-cname")
            $("#category_name").val(cate)
        });



        // 全局 session storage
        var storage_name = 'label_goods_ids';

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
                var url = "{!! route('admin/goodslabel/bind_goods', ['label_id' => $filter['label_id'], 'handler' => 'import']) !!}";

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