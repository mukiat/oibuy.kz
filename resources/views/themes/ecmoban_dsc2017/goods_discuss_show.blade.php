<!doctype html>
<html lang="zh-Hans">
<head><meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Keywords" content="{{ $keywords }}" />
<meta name="Description" content="{{ $description }}" />

<title>{{ $page_title }}</title>



<link rel="shortcut icon" href="favicon.ico" />
@include('frontend::library/js_languages_new')
</head>

<body>
	@include('frontend::library/page_header_common')
	<div class="full-main-n">
        <div class="w w1200 relative">
			@include('frontend::library/ur_here')
			@include('frontend::library/goods_merchants_top')
        </div>
    </div>
    <div class="container">
    	<div class="w w1200">

@if(!$img_list)

            <div class="discuss-info">
                <div class="discuss-left">
                    <div class="discuss-info-warp">
                        <div class="u-ico"><img src="
@if($user_picture)
{{ $user_picture }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif
"></div>
                        <div class="ud-right d-i-info">
                            <div class="d-i-tit">
                                <p>
@if($discuss['nick_name'])
{{ $discuss['nick_name'] }}
@else
{{ $lang['anonymous'] }}
@endif
</p>
                                <p>{{ $discuss['add_time'] }}</p>
                            </div>
                            <div class="d-i-txt">{!! $discuss['dis_text'] !!}</div>
                        </div>
                    </div>
                    <div class="discuss-reply-info">
                        <div class="u-ico"><img src="
@if($user_id)

@if($user_info['user_picture'])
{{ $user_info['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif

@else
{{ skin('/images/avatar.png') }}
@endif
"></div>
                        <div class="ud-right d-r-warp">
                            <div class="user-name">{{ $lang['publish_reply'] }}</div>
                            <input type="hidden" id="dis_id" name="dis_id" value="{{ $discuss['dis_id'] }}">
                            <input type="hidden" id="quote_id" name="quote_id" value="">
                            <input type="hidden" id="nick_user" name="nick_user" value="{{ $discuss['user_id'] }}">
                            <div class="editor-quote fl" id="editor-quote" style="display: none;">
                                <blockquote class="quote"><a onclick="closeQuote();" class="close" title="删除引用">X</a>
                                    <input type="hidden" id="dis_id" name="dis_id" value="{{ $discuss['dis_id'] }}">
                                    <input type="hidden" id="quote_id" name="quote_id" value="">
                                    <input type="hidden" id="nick_user" name="nick_user" value="{{ $discuss['user_id'] }}">
                                    <div id="quoteTitle" class="quote_title">{{ $lang['reply_comment'] }}&#12288; <em class="userName"></em> </div>
                                    <div id="quoteContent" class="quote_content"> {{ $lang['reply_desc'] }}</div>
                                </blockquote>
                            </div>
                            <textarea name="" id="reply_content" class="textarea"></textarea>
                            <a href="#" class="btn sc-redBg-btn" ectype="publish">{{ $lang['reply_comment'] }}</a>
                        </div>
                    </div>
                    <div class="discuss-all">
                        <div class="tit">{{ $lang['all_comments'] }}（{{ $reply_discuss['record_count'] }}）</div>

@foreach($reply_discuss['list'] as $key => $list)

                        <div class="item">
                            <div class="u-ico"><img src="
@if($list['user_picture'])
{{ $list['user_picture'] }}
@else
{{ skin('/images/touxiang.jpg') }}
@endif
"></div>
                            <div class="ud-right d-a-info">

@if($list['quote'])

                                <div>
                                    <blockquote class="quote">
                                    <fieldset><legend>{{ $lang['reply_comment'] }}&nbsp;{{ $list['quote']['nick_name'] }}&nbsp;</legend>
                                    <div class="quote_content">
                                        {!! $list['quote']['dis_text'] !!}
                                    </div>
                                    </fieldset>
                                    </blockquote>
                                </div>

@endif

                                <div class="d-a-lie">
                                    <div class="user-name">{{ $list['nick_name'] }}</div>
                                    <div class="time">{{ $list['add_time'] }}</div>
                                </div>
                                <div class="d-i-txt">{!! $list['dis_text'] !!}</div>
                                <a href="#reply" class="reply_parent" dis_id="{{ $discuss['dis_id'] }}" quote_id="{{ $list['dis_id'] }}" userID="{{ $list['user_id'] }}" userName="{{ $list['nick_name'] }}" data="{!! $list['dis_text'] !!}">{{ $lang['reply_comment'] }}</a>
                            </div>
                        </div>

@endforeach

                    </div>
                </div>
                @include('frontend::library/discuss_right')
            </div>

@else

            <div class="album-info">
                <div class="album-big">
                    <div class="album-big-cen">
                        <ul>

@foreach($img_list as $key => $img)

                            <li><img src="{{ $img['comment_img'] }}" width="720" height="720"></li>

@endforeach

                        </ul>
                    </div>
                    <a href="javascript:void(0);" class="albumbig-prev"><i class="iconfont icon-left"></i></a>
                    <a href="javascript:void(0);" class="albumbig-next"><i class="iconfont icon-right"></i></a>
                </div>
                <div class="album-right">
                    <div class="album-list">
                        <div class="album-tit">{{ $lang['goods_album'] }}({{ $num }})</div>
                        <div class="album-list-warp">
                            <div class="album-ul">
                                <ul>
                                @foreach($img_list as $key => $img)
                                    @if($key <= 7)
                                    <li><img src="{{ $img['comment_img'] }}" width="64" height="64"></li>
                                    @endif
                                @endforeach
                                </ul>
                                @if(count($img_list) > 8)
                                    <ul>
                                        @foreach($img_list as $key => $img)
                                            @if($key > 7)
                                                <li><img src="{{ $img['comment_img'] }}" width="64" height="64"></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        <div class="album-nunber">
                            <a href="javascript:void(0);" class="album-prev"><i class="iconfont icon-left"></i></a>
                            <span class="a-p-s"></span>
                            <a href="javascript:void(0);" class="album-next"><i class="iconfont icon-right"></i></a>
                        </div>
                    </div>
                    <div class="pur-info">
                        <p>{{ $lang['shop_Price_dis'] }}：<span>{{ $goodsInfo['goods_price'] }}</span></p>
                        <p>{{ $lang['sale_amount'] }}：<b>{{ $goodsInfo['sales_volume'] }}</b>件</p>
                        <a href="javascript:addToCart({{ $goodsInfo['goods_id'] }})" class="btn sc-redBg-btn"><i class="iconfont icon-carts"></i>{{ $lang['btn_add_to_cart'] }}</a>
                    </div>
                </div>
            </div>

@endif

        </div>
    </div>

    {{-- DSC 提醒您：动态载入user_menu_position.lbi，显示首页分类小广告 --}}
{!! insert_user_menu_position() !!}

    @include('frontend::library/page_footer')


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/warehouse_area.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/cart_quick_links.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>
    <script type="text/javascript">
        $(".reply_parent").click(function(){
			var T = $(this);
			$("#dis_id").val(T.attr('dis_id'));
			$("#quote_id").val(T.attr('quote_id'));
			$("#nick_user").val(T.attr('userID'));
			$("#quoteContent").html(T.attr('data'));
			$(".userName").html(T.attr('userName'));
			$('.editor-quote').show();
		});

		$("*[ectype='publish']").on("click",function(){
			var dis_id = $("#dis_id").val();
			var quote_id = $("#quote_id").val();
			var nick_user = $("#nick_user").val();
			var reply_content = $("#reply_content").val();
			onsubmit_comm(dis_id, quote_id, reply_content, nick_user);
		});

		function closeQuote(){
			$("#dis_id").val({{ $discuss['dis_id'] }});
			$("#quote_id").val('');
			$("#nick_userId").val({{ $discuss['user_id'] }});
			$("#quoteContent").html('');
			$(".userName").html('');
			$('.editor-quote').hide();
		}

		function onsubmit_comm(id,quote_id,content,nick_user){
			if(content == ''){
				var message = "{{ $lang['reply_desc_one'] }}";
				pbDialog(message,"",0);
				return false;
			}

			Ajax.call('single_sun.php?act=check_comm', 'dis_id=' + id + '&quote_id=' + quote_id + '&comment_content=' + content + '&nick_user=' + nick_user, get_reply_comm, 'GET', 'JSON');
		}

		function get_reply_comm(res){
			if(res.error == 1){
				pbDialog(res.err_msg,"",0,'','','',true,function(){location.reload();});
				return false;
			}else if(res.error == 2){
				var back_url = "single_sun.php?act=discuss_show&did=" + res.dis_id;
				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
				return false;
			}else{
				location.reload();
			}
		}

		$(".album-info").slide({titCell:".album-list-warp li",mainCell:".album-big ul",effect:"left",autoPlay:false,prevCell:".albumbig-prev",nextCell:".albumbig-next",titOnClassName:"curr"});
		$(".album-list").slide({mainCell:".album-ul",effect:"left",autoPage:true,autoPlay:false,prevCell:".album-prev",nextCell:".album-next",pageStateCell:".a-p-s"});
    </script>
    <script type="text/javascript" id="bdshare_js" data="type=tools&amp;uid=692785" ></script>
    <script type="text/javascript" id="bdshell_js"></script>
    <script type="text/javascript">
    	document.getElementById("bdshell_js").src = "http://bdimg.share.baidu.com/static/js/shell_v2.js?cdnversion=" + new Date().getHours();
    </script>
</body>
</html>
