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
<link rel="stylesheet" type="text/css" href="{{ skin('css/other/crowdfunding.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.css') }}" />
</head>
<body class="page-header">
@include('frontend::library/page_header_common')


@if($action == 'default')

    <div class="z_container">
        <div class="w_c banner_c">
            <div class="wrap_c">
				{{-- DSC 提醒您：动态载入zc_index_banner.lbi，显示众筹首页轮播图 --}}
{!! insert_get_adv_child(['ad_arr' => $zc_index_banner]) !!}
            </div>
            <div class="ban_nav"><ul></ul></div>
            <a class="btn_Left" href="javascript:;"></a>
            <a class="btn_Right" href="javascript:;"></a>
        </div>
        <div class="z_main">
            <div class="z_mod_tit">
                <i class="icon"></i>
                <h2 class="font20">{{ $lang['Boutique_project'] }}</h2>
                <div class="search">
                    <div class="searchInput">
                        <input type="text" class="searchtext s-placeholder" id="w" placeholder="{{ $lang['Keyword_search'] }}">
                        <a href="javascript:;" class="searchbtn" id='sousuo'>{{ $lang['search'] }}</a>
                    </div>
                    <span class="line"></span>
                    <span class="pro-more">
                        <a target="_blank" href="crowdfunding.php?act=xm">{{ $lang['more_projects'] }} &gt;</a>
                    </span>
                </div>
            </div>
            <div class="query-list">
                <div class="attr">
                    <div class="a-key">{{ $lang['category'] }}：</div>
                    <div class="a-values">
                        <div class="v-option" style="display: none;">
                            <b></b><span>{{ $lang['more'] }}</span>
                        </div>
                        <div class="v-fold v-list">
                            <ul class="f-list" id="parent_catagory">
                                <li class="current"><a name="parentId" href="javascript:;" code="0">{{ $lang['project_all'] }}</a></li>

@foreach($cate_one as $item)

                                <li><a name="parentId" href="javascript:;" code="{{ $item['cat_id'] }}">{{ $item['cat_name'] }}</a></li>

@endforeach

                            </ul>
                        </div>
                        <div class="v-second-list">

@foreach($cate_two as $key => $res)

                                <ul class="s-list">

@foreach($res as $item)

                                    <li><a name="category" parentid="{{ $key }}" code="{{ $item['cat_id'] }}" href="javascript:;">{{ $item['cat_name'] }}</a></li>

@endforeach

                                </ul>

@endforeach

                        </div>
                    </div>
                </div>
                <div class="attr">
                    <div class="a-key">{{ $lang['sort'] }}：</div>
                    <div class="a-values">
                        <div class="v-fold v-order">
                            <ul class="f-list" code="zhtj" id="sort">
                                <li class="current">
                                    <a name="sort" code="zhtj" href="javascript:;">{{ $lang['Comprehensive_rec'] }}</a>
                                </li>
                                <li>
                                    <a name="sort" code="zxsx" href="javascript:;">{{ $lang['on_line_new'] }}</a>
                                </li>
                                <li>
                                    <a name="sort" code="jezg" href="javascript:;">{{ $lang['Maximum_amount'] }}</a>
                                </li>
                                <li>
                                    <a name="sort" code="zczd" href="javascript:;">{{ $lang['Maximum_Support'] }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="data-list" id="projectlist">

@if($zc_arr)


@foreach($zc_arr as $item)

                    <div class="Module_c">
                        <a target="_blank" href="crowdfunding.php?act=detail&id={{ $item['id'] }}"><img src="{{ $item['title_img'] }}" width="520" height="263" title="" alt=""></a>
                        <div class="Module_text">
                            <div class="Module_topic">
                                <h3><a target="_blank" href="crowdfunding.php?act=detail&id={{ $item['id'] }}">{{ $item['title'] }}</a></h3>
                                <p title={{ $item['des'] }}>{{ $item['duan_des'] }}</p>
                            </div>
                            <div class="Module_progress">
                                <span><i
@if($item['baifen_bi'] > 100)
 style="width:100%"
@else
 style="width:{{ $item['baifen_bi'] }}%"
@endif
></i></span>
                                <em class="ing">{{ $item['zc_status'] }}</em>
                            </div>
                            <div class="Module_op">
                                <ul>
                                    <li><p>{{ $item['baifen_bi'] }}%</p><span>{{ $lang['reached'] }}</span></li>
                                    <li class="gap" style="width:100px;"><p>{{ config('shop.currency_format', '¥') }}{{ $item['join_money'] }}</p><span>{{ $lang['Raise'] }}</span></li>
                                    <li class="gap"><p>{{ $item['shenyu_time'] }}{{ $lang['day'] }}</p><span>{{ $lang['residual_time'] }}</span></li>
                                </ul>
                            </div>
                            <div class="Module_fav">
                                <p><span style="margin-right:10px;">{{ $lang['Support'] }}：{{ $item['join_num'] }}</span></p>
                            </div>
                        </div>
                        <div class="Module_shadow_wrap">
                            <div class="Module_shadow Module_shadow_top"></div>
                            <div class="Module_shadow Module_shadow_bottom"></div>
                        </div>
                    </div>

@endforeach


@else

               		<div class="no_records">
						<i class="no_icon_two"></i>
						<div class="no_info no_info_line">
							<h3>{{ $lang['information_null'] }}</h3>
							<div class="no_btn">
								<a href="index.php" class="btn sc-redBg-btn">{{ $lang['back_home'] }}</a>
							</div>
						</div>
					</div>

@endif

            </div>



@if($gengduo > 5)

			<div class="data-more" id="data-more">{{ $lang['see_more'] }}<span class="sim"></span></div>

@endif

        </div>
        <div class="z_sidebar w264 mt20">
            <div class="White_c">
                <div class="z_mod_tit">
                    <i class="icon"></i>
                    <h2 class="font18">{{ $lang['zx_Recommend'] }}</h2>
                </div>
                <div id="winners">

@foreach($sp_zc_list as $one)

				  <div class="sp-zc-info">
					<a href="crowdfunding.php?act=detail&id={{ $one['id'] }}" target="_blank"><img src="{{ $one['title_img'] }}" width="250" style="margin:7px;"></a>
                    <a href="crowdfunding.php?act=detail&id={{ $one['id'] }}" target="_blank">{{ $one['title'] }}</a>
				  </div>

@endforeach

                </div>
            </div>
        </div>
    </div>

<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/compare.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
	<script type="text/javascript">
	$(".banner_c").slide({titCell:".ban_nav ul",mainCell:".wrap_c ul",effect:"left",autoPlay:false,autoPage:true,scroll:1,vis:1,prevCell:".btn_Left",nextCell:".btn_Right"});

    $('#parent_catagory li').on('click',function(){
        $('.s-list a').removeClass('curr');
        var code = $(this).find('a').attr('code');
        var wenzi = $.trim($('#w').val());
        if(code==0){
            $.post('crowdfunding.php?act=quanbu',{code:code,wenzi:wenzi},function(data){
                $('#projectlist').remove();
                $('#data-more').remove();
                $('.z_main').append(data);
            },'json');
        }else{
            $.post('crowdfunding.php?act=cate',{code:code,wenzi:wenzi},function(data){
                $('#projectlist').remove();
                $('#data-more').remove();
                $('.z_main').append(data);
            },'json');
        }
    });

    $('.s-list a').on('click',function(){
        var code = $(this).attr('code');
        var wenzi = $.trim($('#w').val());
        $(this).parent().siblings().find('a').removeClass('curr');
        $(this).addClass('curr');
        $.post('crowdfunding.php?act=cate_child',{code:code,wenzi:wenzi},function(data){
            $('#projectlist').remove();
            $('#data-more').remove();
            $('.z_main').append(data);
        },'json');
    })

    $('body').on('click','#data-more',function(){
        var wenzi = $.trim($('#w').val());
        var pid = $('#parent_catagory').find('li[class=current]').children('a').attr('code');
        var tid = $('.s-list').find('a[class=curr]').attr('code');
        var len = $('#projectlist').find('div[class=Module_c]').length;

        if(tid){
            $.post('crowdfunding.php?act=gengduo_tid',{id:tid,len:len,wenzi:wenzi},function(data){
                $('#projectlist').append(data);
                var zx_tig = $('#zx_tig').attr('zx_tig');
                if(zx_tig<=0){
                    $('#data-more').hide();
                }
                $('#zx_tig').remove();
            },'json');
        }else{
            if(pid==0){
                $.post('crowdfunding.php?act=gengduo_pid_zero',{id:pid,len:len,wenzi:wenzi},function(data){
                    $('#projectlist').append(data);
                    var zx_tig = $('#zx_tig').attr('zx_tig');
                    if(zx_tig<=0){
                        $('#data-more').hide();
                    }
                    $('#zx_tig').remove();
                },'json');
            }else{
                $.post('crowdfunding.php?act=gengduo_pid',{id:pid,len:len,wenzi:wenzi},function(data){
                    $('#projectlist').append(data);
                    var zx_tig = $('#zx_tig').attr('zx_tig');
                    if(zx_tig<=0){
                        $('#data-more').hide();
                    }
                    $('#zx_tig').remove();
                },'json');
            }
        }
    })

    $('body').on('click','#sort li',function(){
        var wenzi = $.trim($('#w').val());
        var sig = $(this).find('a').attr('code');
        var pid = $('#parent_catagory').find('li[class=current]').children('a').attr('code');
        var tid = $('.s-list').find('a[class=curr]').attr('code');
        var len = $('#projectlist').find('div[class=Module_c]').length;

        if(tid){
            $.post('crowdfunding.php?act=paixu_tid',{id:tid,len:len,sig:sig,wenzi:wenzi},function(data){
                $('#projectlist').remove();
                $('#data-more').remove();
                $('.z_main').append(data);
            },'json');
        }else{
            if(pid==0){
                $.post('crowdfunding.php?act=paixu_pid_zero',{id:pid,len:len,sig:sig,wenzi:wenzi},function(data){
                    $('#projectlist').remove();
                    $('#data-more').remove();
                    $('.z_main').append(data);
                },'json');
            }else{
                $.post('crowdfunding.php?act=paixu_pid',{id:pid,len:len,sig:sig,wenzi:wenzi},function(data){
                    $('#projectlist').remove();
                    $('#data-more').remove();
                    $('.z_main').append(data);
                },'json');
            }

        }

    })

    $('#sousuo').on('click',function(){
        var wenzi = $.trim($('#w').val());
        var sig = $('#sort').find('li[class=current]').find('a').attr('code');
        var pid = $('#parent_catagory').find('li[class=current]').children('a').attr('code');
        var tid = $('.s-list').find('a[class=curr]').attr('code');
        var len = $('#projectlist').find('div[class=Module_c]').length;
        if(tid){
            $.post('crowdfunding.php?act=paixu_tid',{id:tid,len:len,sig:sig,wenzi:wenzi},function(data){
                $('#projectlist').remove();
                $('#data-more').remove();
                $('.z_main').append(data);
            },'json');
        }else{
            if(pid==0){
                $.post('crowdfunding.php?act=paixu_pid_zero',{id:pid,len:len,sig:sig,wenzi:wenzi},function(data){
                    $('#projectlist').remove();
                    $('#data-more').remove();
                    $('.z_main').append(data);
                },'json');
            }else{
                $.post('crowdfunding.php?act=paixu_pid',{id:pid,len:len,sig:sig,wenzi:wenzi},function(data){
                    $('#projectlist').remove();
                    $('#data-more').remove();
                    $('.z_main').append(data);
                },'json');
            }
        }

    })
	</script>

@endif





@if($action == 'detail')

    <div class="detail w1200 w">
        <div class="project clearfix">
        	<div class="project_wrap">
            	<div class="project-img"><i class="zc-icon
@if($zhongchou['result'] == 0)
zc-green-ing
@elseif ($zhongchou['result'] == 1)
zc-grey-sb
@elseif ($zhongchou['result'] == 2)
zc-violet-cg
@endif
"></i><img src="{{ $zhongchou['title_img'] }}" width="790" height="400" /></div>
            	<div class="project-introduce">
                	<p class="p-title">{{ $zhongchou['title'] }}</p>
                    <p class="p-have">{{ $lang['Raise'] }}</p>
                    <p class="p-num">{{ $zhongchou['format_join_money'] }}</p>
                    <div class="p-bar">
                        <div
@if($zhongchou['baifen_bi'] > 100)
 style="width:100%"
@else
 style="width:{{ $zhongchou['baifen_bi'] }}%"
@endif
 class="p-bar-green"></div>
                    </div>
                    <p class="p-progress">
                        <span class="fl green">{{ $lang['Current_progress'] }}{{ $zhongchou['baifen_bi'] }}%</span><span class="fr">{{ $zong_zhichi }}{{ $lang['Supporter'] }}</span>
                    </p>
                    <p class="p-target" id="projectMessage">{{ $lang['project_Prompt_one'] }} <span class="f_red">{{ $zhongchou['zw_end_time'] }} </span>{{ $lang['project_Prompt_two'] }} <span class="f_red"><i>{{ config('shop.currency_format', '¥') }}</i>{{ $zhongchou['amount'] }}</span>{{ $lang['project_Prompt_three'] }}
@if($zhongchou['zc_status'] == 1)
{{ $lang['remaining'] }}<span class="f_red"> {{ $zhongchou['shenyu_time'] }}</span>{{ $lang['day'] }}！
@endif
</p>
                    <p class="p-btns">
                        <a id="a_focus" href="javascript:;" class="p-btn follow" onclick="hotClick(this,{{ request()->get('id') }},1)" data-focus_status="{{ $focus_status }}"><span id="focus">
@if($focus_status == 1)
{{ $lang['already'] }}
@endif
{{ $lang['follow'] }}</span><span class="num" id="focusCount">({{ $zhongchou['focus_num'] }})</span></a>
                        <a id="a_prais" href="javascript:;" class="p-btn not-praise" onclick="hotClick(this,{{ request()->get('id') }},2)" data-prais_status="{{ $prais_status }}"><span id="prais">
@if($prais_status == 1)
{{ $lang['already'] }}
@endif
{{ $lang['Fabulous'] }}</span><span class="num" id="praisCount">({{ $zhongchou['prais_num'] }})</span></a>
                    </p>
                    <p class="p-share">{{ $lang['Share_to'] }}</p>
                    <ul class="p-list">
                    	<li><a target="_blank" href="http://service.weibo.com/share/share.php?url={{ $share_url }}&title={{ $share_title }}&pic={{ $share_img }}" class="i-sina"></a></li>
                    	<li><a target="_blank" href="http://share.v.t.qq.com/index.php?c=share&a=index&title={{ $share_title }}&url={{ $share_url }}&pic={{ $share_img }}" class="i-weibo"></a></li>
                    	<li><a target="_blank" href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url={{ $share_url }}&title={{ $share_title }}&pics={{ $share_img }}" class="i-zoom"></a></li>
                        <li><a target="_blank" href="http://www.douban.com/share/service?image={{ $share_url }}&href={{ $share_url }}&text={{ $share_title }}" class="i-dou"></a></li>
                        <li><a target="_blank" href="http://widget.renren.com/dialog/share?resourceUrl={{ $share_url }}&images={{ $share_img }}&title={{ $share_title }}" class="i-renren"></a></li>
                        <li>
                        	<a class="i-wechart" href="javascript:void(0);"></a>
                            <div class="code" style="display: none;">
                                <span class="code-close"></span>
                                <img class="code-img" src="{{ $weixin_img_url }}">
                                <p class="code-p">{{ $lang['smfx_WeChat'] }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="wrap-details">
            	<div class="details-l">
                	<div class="tab-bg">
                    	<div class="tab-wrap" id="tabWrap">
                        	<div class="tab-name current-now">{{ $lang['Project_Home'] }}</div>
                            <div id="qaBtn" class="tab-name">{{ $lang['project_debriefing'] }}<span class="tab-bubble">{{ $zc_evolve_list_num }}</span></div>
                            <div id="topicBtn" class="tab-name">{{ $lang['conversation'] }}<span class="tab-bubble">{{ $topic_num }}</span></div>
                            <div id="supporterBtn" class="tab-name">{{ $lang['Supporter'] }}<span class="tab-bubble">{{ $backer_num }}</span></div>
                            <div class="clear"></div>
                            <div class="tab-line"></div>
                        </div>
                    </div>
                    <div class="tab-body" id="menu_con">
                        <div class="tab_cont tab-current">
                            <div class="tab-img-group">
                            	<br>
                                <p>
                                	{!! $zhongchou['details'] !!}
                                </p>
                            </div>
                        </div>
                        <div class="tab_cont">
                            <div class="zc-dev-box" id="qaProgress" style="border-bottom: none;">
                                <div class="zc-d-a-tips">{{ $lang['project_Record'] }}</div>

@if($zc_evolve_list[0])

                                <div class="zc_evolve">

@foreach($zc_evolve_list as $vo)

                                        <div class="zc_evolve_list">
                                            <div class="pro-detail">
                                                <span class="pro-point"></span>

@if($vo['pro-day'] == 0)

                                                <span class="pro-day">{{ $lang['Today'] }}</span>

@else

                                                <span class="pro-day">{{ $vo['pro-day'] }}{{ $lang['Days_ago'] }}</span>

@endif

                                                <p>{{ $vo['progress'] }}</p>
                                            </div>
                                            <div class="pro-img">
                                                <ul class="pro-img-ul">
                                                    <li class="pro-img-li">

@if($vo['img'])


@foreach($vo['img'] as $vo_img)


@if($vo_img!='./')

                                                                <img src="{{ $vo_img }}" alt="" width="80" height="80">

@endif


@endforeach


@endif

                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

@endforeach

                                    </div>

@else

                                <p style="font-size: 26px;color: #ccc;margin-top: 30px;">{{ $lang['Progress_null'] }}</p>

@endif

                                <div class="zc-d-c-more" id="zc-d-c-more" style="display: none;">
                                    <a href="#" clstag="jr|keycount|zc_detail|zc_zd_ckxq">{{ $lang['couponstype_view'] }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="tab_cont" id="topicList">
                            <div class="topicArea">
                                <div class="publishBlock">
                                    <div class="zc-s-q-tit">{{ $lang['on_input'] }}<span class="moreWord" id="topicMoreWord">140</span>{{ $lang['word'] }}</div>
                                    <div class="zc-s-q-cont">
                                        <textarea name="zc-submitTextarea" class="zc-submitTextarea" node-type="zc-submitTextarea" id="publishTopic" onkeyup="check_words_num(this,'topicMoreWord')"></textarea>
                                    </div>
                                    <div class="zc-s-q-foot">
                                        <div class="zc-sq-oprate">
                                            <div class="zc-sqo-submit" style="display: block;" id="login">
                                                <input type="button" value="{{ $lang['lang_crowd'] }}" class="common-btn" clstag="jr|keycount|zc_detail|zc_htfbht" data-url="crowdfunding.php?act=detail&id={{ $id }}" id="repyTopBtn" style="cursor:pointer;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="topic_list">
                                {!! $topic_list !!}
                                </div>
                            </div>
                        </div>
                        <div class="tab_cont">
                            <div id="backer_list">
@if($backer_list)

                            	{!! $backer_list !!}

@else

                            	<p style="font-size: 26px;color: #ccc;margin-top: 30px; padding-bottom: 30px;">{{ $lang['no_supporter'] }}</p>

@endif

                            </div>
                        </div>
                    </div>
                </div>
                <div class="details-r">
                	<div class="box-promoters">
                    	<div class="common-title">{{ $lang['project_Promoter'] }}</div>
                        <div class="promoters clearfix">
                        	<div class="promoters-img"><img height="70" width="70" src="{{ $init['img'] }}" alt=""></div>
                            <div class="promoters-detail">
                            	<div class="promoters-name">
                                	<span class="fl">{{ $init['name'] }}</span>
                                    <i class="ico-crown">
                                    	<div class="alt"></div>

@foreach($init['logo'] as $logo)

                                            <img src='{{ $logo['img'] }}' title='{{ $logo['logo_name'] }}'>

@endforeach

                                    </i>
                                </div>
                                <div class="promoters-title">{{ $init['intro'] }}</div>
                                <div class="promoters-num">
                                    <div class="fl start"><span>{{ $lang['Launch'] }}</span><span class="num">{{ $init['start_count'] }}</span></div>
                                    <div class="line"></div>
                                    <div class="fl"><span>{{ $lang['Support'] }}</span><span class="num">{{ $zong_zhichi }}</span></div>
                                </div>
                                <div class="promoters-btns">

                                    <a id="IM" onclick="openWin(this)" href="javascript:void(0);" goods_id="{{ $goods['goods_id'] }}" class="seller-btn"><i class="icon"></i>{{ $lang['Contact_us'] }}</a>

                                </div>
                            </div>
                        </div>
                    </div>

@foreach($goods_arr as $item)

                    <div class="box-grade">
                    	<div class="common-title">
                        	<div class="t-price
@if($item['shenyu_ren'] == 0)
t-delete
@endif
">{{ config('shop.currency_format', '¥') }}<span>{{ $item['price'] }}</span></div>

@if($item['shenyu_ren'] == 0)

                            <div class="t-full"></div>
                            <div class="t-arrow"></div>

@endif

                            <div class="t-people"><span>{{ $item['backer_num'] }}</span>{{ $lang['bit_support'] }}</div>
                            <div class="clear"></div>
                        </div>
                        <div class="box-content"
@if($item['shenyu_ren'] == 0)
style="display:none;"
@endif
>

                        	<div class="box-limit">

@if($item['limit'] == '-1')

                                <span class="limit-num">{{ $lang['Infinite_amount'] }}</span>

@else

                                <span class="limit-num">{{ $lang['Quota'] }} <span>{{ $item['limit'] }}</span>{{ $lang['bit'] }} | {{ $lang['remaining'] }} <span>{{ $item['shenyu_ren'] }}</span>{{ $lang['bit'] }}</span>

@endif

							</div>
                            <p class="box-intro">{{ $item['content'] }}</p>
                            <div class="box-imglist">
                            	<ul>
                                	<li><img class="alertPic img-s" src="{{ $item['img'] }}" width="80"></li>
                                </ul>
                            </div>
                            <p class="box-item">{{ $lang['shipping_fee'] }}：<span class="font-b">
@if($item['shipping_fee'] == 0)
{{ $lang['Free_shipping'] }}
@else
{{ config('shop.currency_format', '¥') }}{{ $item['shipping_fee'] }}
@endif
</span></p>
                            <p class="box-item">{{ $lang['zc_Prompt_one'] }}：<span class="font-b">{{ $lang['zc_Prompt_two'] }}<span class="font-red">{{ $item['return_time'] }}</span>{{ $lang['Days'] }}</span></p>
                            <p class="box-btn">

@if($zhongchou['zc_status'] == 0)

                                <button type="button" class="common-btn" disabled="true">{{ $lang['Coming_soon'] }}</button>

@elseif ($zhongchou['zc_status'] == 1)


@if($item['shenyu_ren'] > '0'||$item['limit'] == '-1')

									<button type="button" class="common-btn" onclick="zc_goods(this)" gid="{{ $item['id'] }}">{{ $lang['Support'] }}{{ config('shop.currency_format', '¥') }}{{ $item['price'] }}</button>

@else

									<button type="button" class="common-btn-unuse" onclick="" gid="{{ $item['id'] }}">{{ $lang['Support'] }}{{ config('shop.currency_format', '¥') }}{{ $item['price'] }}</button>

@endif


@else

                                <button type="button" class="common-btn-unuse" disabled="true">{{ $lang['project_end'] }}</button>

@endif

                            </p>
                        </div>
                    </div>

@endforeach

                    <div class="box-grade">
                    	<div class="common-title">
                            <div class="t-price">{{ $lang['risk_describe'] }}</div>
                        </div>
                        <div class="box-content">
                        	<p class="box-intro mt20 mb20">{!! $zhongchou['describe'] !!}</p>
                        </div>
                    </div>
                    <div class="box-grade">
                        <div class="common-title">
                            <div class="t-price">{{ $lang['Risk_description'] }}</div>
                        </div>
                        <div class="box-content">
                            <p class="box-intro mt20 mb20">{!! $zhongchou['risk_instruction'] !!}</p>
                        </div>
                    </div>
                    <div class="box-grade">
                    	<div class="common-title">
                            <div class="t-price">{{ $lang['history'] }}</div>
                            <div class="color-a5 t-hands"><a href="javascript:delete_zc_history()">{{ $lang['zc_clear'] }}</a></div>
                            <div class="clear"></div>
                        </div>
                        <div class="box-content">
                        	<ul class="box-recent-list">

@foreach($history as $list)

                            	<li><a href="crowdfunding.php?act=detail&id={{ $list['id'] }}"><div class="recent-img"><img src="{{ $list['title_img'] }}" /></div><div class="recent-p"><p>{{ $list['title'] }}</p></div></a></li>

@endforeach

							</ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="javascript:void(0);" onclick="fn_prev()" class="goPages goPages-pre"></a>
    <a href="javascript:void(0);" onclick="fn_next()" class="goPages goPages-next"></a>
    <script type="text/javascript">
		var zc_goods = function(obj){
			var gid = $(obj).attr('gid');
			window.location.href = 'crowdfunding.php?act=checkout&gid='+gid;
		}

		//清空浏览历史 by wu
		function delete_zc_history()
		{
			$.ajax({
				type:'get',
				url:'crowdfunding.php',
				data:'act=delete_zc_history',
				dataType:'json',
				success:function(data){
					if(data.error == 1)
					{
						$(".box-recent-list").html('');
					}
				}
			});
		}

		//字数验证 by wu
		function check_words_num(obj,select_jsId)
		{
			var words=$(obj).val();
			var num=words.length;
			if(num<=140)
			{
				$("#"+select_jsId).html(140-num);
			}
			else
			{
				$(obj).val(words.substr(0,140));
			}
		}

		//打开输入框 by wu
		function open_area(obj,topic_id,type,parent_id)
		{
			var area=$(".topic-info-area[data-topicid="+topic_id+"]");
			area.attr("data-type",type);
			area.attr("data-parentid",parent_id);
			area.toggle();
			if(type==2)
			{
				var user_name=$("#topic_user_"+parent_id).html().replace("：","");
				name_arr=user_name.split(json_languages.reply_comment);
				area.find("textarea").attr("placeholder",json_languages.reply_comment+name_arr[0]);
			}
			else
			{
				area.find("textarea").attr("placeholder","");
			}
		}

		//评论回复 by wu | type 0:众筹,1:话题,2:回复
		function post_topic(obj)
		{
			var topic_id=$(obj).parent("div").data("topicid");
			var type=$(obj).parent("div").data("type");
			var parent_id=$(obj).parent("div").data("parentid");
			var topic_content=$(obj).siblings("textarea").val();
			if(topic_content=="")
			{
				pbDialog(json_languages.Pleas_content,"",0);
				return;
			}
			$.ajax({
				type:'get',
				url:'crowdfunding.php',
				data:'act=post_topic&topic_id='+topic_id+'&type='+type+'&parent_id='+parent_id+'&topic_content='+topic_content,
				dataType:'json',
				success:post_success
			})
		}

		//发布话题 by wu
		$("#repyTopBtn").click(function(){
			var topic_content=$("#publishTopic").val();
			if(topic_content=="")
			{
				pbDialog(json_languages.Pleas_content,"",0);
				return;
			}
			else
			{
				$.ajax({
					type:'post',
					url:'crowdfunding.php',
					data:'act=submit_topic&zcid={{ request()->get('id') }}&topic_content='+topic_content,
					dataType:'json',
					success:post_success
				})
			}
		})

		function post_success(data){
			if(data.error==9)
			{
				var back_url=$("#repyTopBtn").data("url");
				$.notLogin("get_ajax_content.php?act=get_login_dialog",back_url);
				//刷新列表
				get_topic_list({{ request()->get('id') }},1);
				//数量更新
				//var topic_num=parseInt($("#topicBtn").find(".tab-bubble").html());
				$("#topicBtn").find(".tab-bubble").html(data.content.zc_topic_num);
			}
			if(data.error==1)
			{
				//alert(data.message);
				pbDialog(data.message,'',0,'','',135);
				//刷新列表
				get_topic_list({{ request()->get('id') }},1);
				//数量更新
				//var topic_num=parseInt($("#topicBtn").find(".tab-bubble").html());
				$("#topicBtn").find(".tab-bubble").html(data.content.zc_topic_num);
				$("#publishTopic").val('');
			}

			if(data.error==8)
			{
				//alert(data.message);
				pbDialog(data.message,'',0,'','',65);
				//刷新列表
				get_topic_list({{ request()->get('id') }},1);
			}
		}


		//鼠标点击图片显示二维码 by wu
		$(".i-wechart").click(function(){
			$(this).next().show();
		});
		$(".code-close").click(function(){
			$(this).parents(".code").hide();
		});

		//关注点赞 by wu start
		function hotClick(obj,zcid,type)
		{
			var focus_status=$('#a_focus').data('focus_status'); //关注状态
			var prais_status=$('#a_prais').data('prais_status'); //点赞状态
			if((focus_status==0 && type==1) || (prais_status==0 && type==2))
			{
				$.ajax({
					type:'get',
					url:'crowdfunding.php',
					data:'act=statistical&zcid='+zcid+'&type='+type,
					dataType:'json',
					success:function(data){
						if(data.error>0)
						{
							//关注
							if(type==1)
							{
								//只有登陆用户才能关注
								if(data.error==9)
								{
									//alert(data.message);
									window.location.href = 'user.php';
								}
								if(data.error==2 || data.error==3)
								{
									focus_dialog();
									$(obj).find('span').first().html(json_languages.follow_yes);
									if(data.error==2)
									{
										var numObj=$(obj).find('span').eq(1);
										var numVal=numObj.html().match(/\d+/g);
										numObj.html(numObj.html().replace(/\d+/g,parseInt(numVal)+1));
										$('#a_focus').data('focus_status',1);
									}
								}
							}
							//点赞
							if(type==2)
							{
								if(data.error==4 || data.error==5)
								{
									$(obj).find('span').first().html(json_languages.Fabulous_yes);
									if(data.error==4)
									{
										var numObj=$(obj).find('span').eq(1);
										var numVal=numObj.html().match(/\d+/g);
										numObj.html(numObj.html().replace(/\d+/g,parseInt(numVal)+1));
										$('#a_prais').data('prais_status',1);
									}
								}
							}
						}
					}
				})
			}
		}
		//关注点赞 by wu end

		//关注成功
		function focus_dialog()
		{
			var result = json_languages.collect_zc_success;
			var content = '<div class="tip-box icon-box">' +'<span class="warn-icon m-icon"></span>' + '<div class="item-fore">' +'<h3 class="rem ftx-04">'+result+'</h3>' +'</div>' +'</div>';
			pb({
				id:'',
				title:json_languages.follow_zc,
				width:455,
				height:58,
				content:content, 	//调取内容
				drag:false,
				foot:false,
			});
		}

        $("#tabWrap .tab-name").click(function(){
			var index = $(this).index();
			var _this = $(this);
			_this.addClass("current-now").siblings(".tab-name").removeClass("current-now");
			_this.siblings(".tab-line").animate({"margin-left":(192*index)},300);

			$("#menu_con").find('.tab_cont').eq(index).addClass("tab-current").siblings(".tab_cont").removeClass("tab-current");
		});

        var d_id = "{{ request()->get('id') }}";
        var n_id = parseInt(d_id)+1;
        var p_id = parseInt(d_id)-1;
        var fn_next = function(){
            window.location.href = './crowdfunding.php?act=detail&id='+n_id;
        }

        var fn_prev = function(){
            window.location.href = './crowdfunding.php?act=detail&id='+p_id;
        }

		//展开收起
		$(".t-arrow").on("click",function(){
			if($(this).parents(".box-grade").hasClass("i-box")){
				$(this).parents(".box-grade").removeClass("i-box");
				$(this).parents(".box-grade").find(".box-content").hide();
			}else{
				$(this).parents(".box-grade").addClass("i-box");
				$(this).parents(".box-grade").find(".box-content").show();
			}
		});
    </script>

@endif





@if($action == 'order')

    <div class="mt20">
    	<div class="z_container">
        	<div class="order_process">
                <ul>
                    <li class="active">
                        {{ $lang['zc_order_input'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['zc_order_confirm'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['payment'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['complete'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                </ul>
            </div>
            <div class="module_wrap mt20">
            	<div class="common_tit"><h1 class="common_tit_name">{{ $g_title }}</h1></div>
                <div class="module_con">
                	<div>
                    	<div class="module_item">
                            <dl>
                                <dt>{{ $lang['Support_amount'] }}：</dt>
                                <dd><span class="f_red20">{{ $goods_arr['price'] }}</span></dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['shipping_fee'] }}：</dt>
                                <dd>
@if($goods_arr['shipping_fee'] == '0' )
<span>{{ $lang['Free_shipping'] }}</span>
@else
{{ $goods_arr['shipping_fee'] }}
@endif
</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['Return_content'] }}：</dt>
                                <dd>{{ $goods_arr['content'] }}</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['zc_invoice'] }}：</dt>
                                <dd>
                                    <div class="bor_t_li">
                                        <input type="radio" name="invoiceFlag" id="if0" value="0" onclick="changeInvoiceFlag();" checked="checked"> {{ $lang['zc_invoice_not'] }}
                                        <p>
                                            <input type="radio" name="invoiceFlag" id="if1" value="1" onclick="newInvoiceTitle();"> {{ $lang['zc_invoice_need'] }}
                                        </p>
                                    </div>
                                    <div class="new_add pt10" style="display: none;" id="invoiceTitleDetail">
                                        <dl style="padding-left: 62px;">
                                            <dt><span class="f_red">*</span> {{ $lang['Invoice_header'] }}：</dt>
                                            <dd><input name="invoiceTitle" id="_invoiceTitle" type="text" class="inp145" value="{{ $lang['personal'] }}"></dd>
                                        </dl>
                                    </div>
                                </dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['Remarks'] }}：</dt>
                                <dd><input name="_remarks" id="_remarks" type="text" placeholder="{{ $lang['zc_placeholder'] }}" class="inp_remark"></dd>
                            </dl>
                        </div>
                        <div class="module_item">
                            <dl>
                                <dt>{{ $lang['Consignee'] }}：</dt>
                                <dd>
                                    <div class="write_repeat" id="showAddress" style="display: block;"><p>{{ $consignee['consignee'] }}
@if($consignee['mobile'])
 {{ $consignee['mobile'] }}
@else
 {{ $consignee['tel'] }}
@endif
 <span style="color: #ff0000;">{{ $lang['zc_Remarks_one'] }}</span></p><p>
@if($consignee['address'])
 {{ $consignee['address'] }}
@else
 {{ $b['province'] }}{{ $lang['province'] }} {{ $b['city'] }}{{ $lang['city'] }} {{ $b['district'] }}
@endif
 &nbsp;&nbsp;&nbsp;<a class="f_blue repeat" href="crowdfunding.php?act=consignee&gid={{ $goods_arr['id'] }}">{{ $lang['modify'] }}</a></p></div>
                                </dd>
                        	</dl>
                		</div>
                    </div>
                    <div class="risk_tips">
                        {{ $lang['zc_Remarks_two'] }}
                    </div>
                    <div class="common_button">
                        <form action="crowdfunding.php?act=done" method="post">
                            <input type="hidden" name="country"  value="{{ $consignee['country'] }}">
                            <input type="hidden" name="province" value="{{ $consignee['province'] }}">
                            <input type="hidden" name="city" value="{{ $consignee['city'] }}">
                            <input type="hidden" name="district" value="{{ $consignee['district'] }}">
                            <input type="hidden" name="consignee" value="{{ $consignee['consignee'] }}">
                            <input type="hidden" name="address" value="{{ $consignee['address'] }}">
                            <input type="hidden" name="tel" value="{{ $consignee['tel'] }}">
                            <input type="hidden" name="mobile" value="{{ $consignee['mobile'] }}">
                            <input type="hidden" name="email" value="{{ $consignee['email'] }}">
                            <input type="hidden" name="best_time" value="{{ $consignee['best_time'] }}">
                            <input type="hidden" name="sign_building" value="{{ $consignee['sign_building'] }}">
							<input type="hidden" id='inv_payee' name="inv_payee" value="">
                            <input type="hidden" id='liuyan' name="postscript" value="">
                            <input type="hidden" name="goods_amount" value="{{ $goods_arr['price'] }}">
                            <input type="hidden" name="shipping_fee" value="{{ $goods_arr['yunfei'] }}">
                            <input type="hidden" name="order_amount" value="{{ $goods_arr['price'] }}">
                            <input type="hidden" name="huibao" value="{{ $goods_arr['content'] }}">
                            <input type="hidden" name="g_title" value="{{ $g_title }}">
                            <input type="hidden" name="xm_id" value="{{ $goods_arr['goods_id'] }}">
                            <input type="hidden" name="gid" value="{{ request()->get('gid') }}">
                            <input type="submit" id="btn_sub" value="{{ $lang['lang_crowd_next_step'] }}">
                        @csrf </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script>
		$('#_remarks').on('blur',function(){
			$('#liuyan').val($(this).val());
		})
		$('#_invoiceTitle').on('blur',function(){
			$('#inv_payee').val($(this).val());
		})
	</script>

@endif





@if($action == 'checkout')

    <div class="mt20">
    	<div class="z_container">
        	<div class="order_process">
                <ul>
                    <li class="active">
                        {{ $lang['zc_order_input'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['zc_order_confirm'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['payment'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['complete'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                </ul>
            </div>
            <div class="module_wrap mt20">
            	<div class="common_tit"><h1 class="common_tit_name">{{ $g_title }}</h1></div>
                <div class="module_con">
                	<div>
                    	<div class="module_item">
                            <dl>
                                <dt>{{ $lang['Support_amount'] }}：</dt>
                                <dd><span class="f_red20">{{ config('shop.currency_format', '¥') }}{{ $goods_arr['price'] }}</span></dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['shipping_fee'] }}：</dt>
                                <dd>
@if($goods_arr['shipping_fee'] == '0' )
<span>{{ $lang['Free_shipping'] }}</span>
@else
{{ $goods_arr['shipping_fee'] }}
@endif
</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['Return_content'] }}：</dt>
                                <dd>{{ $goods_arr['content'] }}</dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['zc_invoice'] }}：</dt>
                                <dd>
                                    <div class="ck-step-cont" id='inv_content'>
                                        <div class="invoice-warp">
                                            <div class="invoice-part">
                                                <span>
                                                    <em class="invoice_type">{{ $lang['Ordinary_invoice'] }}</em>
                                                    <em class="inv_payee">{{ $lang['personal'] }}</em>
                                                    <em class="inv_content">{{ $inv_content }}</em>
                                                </span>
                                                <a href="javascript:void(0);" class="i-edit" ectype="invEdit" data-value='{"divid":"edit_invoice","url":"ajax_dialog.php?act=edit_invoice&from=crowfunding","title":"{{ $lang['Invoice_information'] }}"}'>{{ $lang['edit'] }}</a>
                                                <input type="hidden" name="inv_payee" value="{{ $lang['personal'] }}">
                                                <input type="hidden" name="inv_content" value="{{ $inv_content }}">
                                                <input type="hidden" name="invoice_type" value="0">
												<input type="hidden" name="from" value="crowfunding">
                                                <input type="hidden" name="tax_id" value="">
                                            </div>
                                        </div>
                                    </div>

                                </dd>
                            </dl>
                            <dl>
                                <dt>{{ $lang['Remarks'] }}：</dt>
                                <dd><input name="_remarks" id="_remarks" type="text" placeholder="{{ $lang['zc_placeholder'] }}" class="inp_remark"></dd>
                            </dl>
                        </div>
                        <div class="module_item">
                            <dl>
                                <dt>{{ $lang['Consignee'] }}：</dt>
                                <dd>
                                    <div class="write_repeat" id="showAddress">
                                        <span>{{ $consignee['consignee'] }}</span>
                                        <span>
@if($consignee['address'])
{{ $consignee['region'] }}&nbsp;{{ $consignee['address'] }}
@else
{{ $lang['address_null'] }}
@endif
</span>
                                        <span>
@if($consignee['mobile'])
{{ $consignee['mobile'] }}
@else
{{ $consignee['tel'] }}
@endif
</span>
                                        <span><a class="f_blue repeat" href="javascript:void(0);" id="editRepeat">
@if($consignee['address'])
{{ $lang['edit_address'] }}
@else
{{ $lang['add_address'] }}
@endif
</a></span>
                                    </div>
                                    <div id="consignee-addr" class="zc_address">
                                        <div class="consignee-addr">
                                            <div class="consignee-cont">
                                                <ul class="ui-switchable-panel-main">

@foreach($user_address as $address)

                                                    <li
@if($consignee['address_id'] == $address['address_id'])
class="item-selected"
@endif
 data-addressid="{{ $address['address_id'] }}">
                                                        <input type="radio"
@if($consignee['address_id'] == $address['address_id'])
checked="checked"
@endif
 class="ui-radio" name="consignee_radio" value="{{ $address['address_id'] }}" id="radio_{{ $address['address_id'] }}" class="hookbox" />
                                                        <label class="ui-radio-label">
                                                            <div class="name">{{ $address['consignee'] }}</div>
                                                            <div class="tel">{{ $address['mobile'] }}</div>
                                                            <div class="address">&nbsp; {{ $address['region'] }} &nbsp; {{ $address['address'] }}</div>
                                                        </label>
                                                        <div class="op-btns">

@if($user_id > 0)

                                                                <a href="javascript:void(0);" class="ftx-05 del-consignee" data-dialog="edit_address" data-id="{{ $address['address_id'] }}">{{ $lang['edit'] }}</a>
                                                                <a href="javascript:void(0);" class="ftx-05 del-consignee" data-dialog="del_address" data-id="{{ $address['address_id'] }}" >{{ $lang['drop'] }}</a>

@else

                                                                <a href="javascript:void(0);" class="ftx-05 del-consignee" data-dialog="edit_address">{{ $lang['edit'] }}</a>

@endif

                                                        </div>
                                                    </li>

@endforeach

                                                </ul>
                                            </div>
                                        </div>
                                        <div class="address-btns">
                                        	<input id="addNewAddress" class="btn-normal" type="button" value="{{ $lang['add_address_zc'] }}">
                                            <input id="confirmAddress" class="btn-normal" type="button" value="{{ $lang['confirm_address_zc'] }}">
                                        </div>
                                	</div>
                                </dd>
                        	</dl>
                		</div>
                        <div class="module_item">
                        	<dl class="order-prompt">
                            	<dt>{{ $lang['Risk_description'] }}：</dt>
                                {!! $goods_arr['risk_instruction'] !!}
                            </dl>
                        </div>
                    </div>
                    <div class="common_button" id="common_button" >
                        <form action="crowdfunding.php?act=done" method="post">
                            <input type="hidden" name="country"  value="{{ $consignee['country'] }}">
                            <input type="hidden" name="province" value="{{ $consignee['province'] }}">
                            <input type="hidden" name="city" value="{{ $consignee['city'] }}">
                            <input type="hidden" name="district" value="{{ $consignee['district'] }}">
                            <input type="hidden" name="consignee" value="{{ $consignee['consignee'] }}">
                            <input type="hidden" name="address" value="{{ $consignee['address'] }}">
                            <input type="hidden" name="tel" value="{{ $consignee['tel'] }}">
                            <input type="hidden" name="mobile" value="{{ $consignee['mobile'] }}">
                            <input type="hidden" name="email" value="{{ $consignee['email'] }}">
                            <input type="hidden" name="best_time" value="{{ $consignee['best_time'] }}">
                            <input type="hidden" name="sign_building" value="{{ $consignee['sign_building'] }}">
                            <input type="hidden" id='inv_payee' name="inv_payee" value="">
                            <input type="hidden" id='liuyan' name="postscript" value="">
                            <input type="hidden" name="goods_amount" value="{{ $goods_arr['price'] }}">
                            <input type="hidden" name="shipping_fee" value="{{ $goods_arr['shipping_fee'] }}">
                            <input type="hidden" name="order_amount" value="{{ $goods_arr['price'] }}">
                            <input type="hidden" name="huibao" value="{{ $goods_arr['content'] }}">
                            <input type="hidden" name="g_title" value="{{ $g_title }}">
                            <input type="hidden" name="xm_id" value="{{ $goods_arr['pid'] }}">
                            <input type="hidden" name="gid" value="{{ request()->get('gid') }}">

							<input type="hidden" name="inv_payee" value="{{ $lang['personal'] }}">
							<input type="hidden" name="inv_content" value="{{ $inv_content }}">
							<input type="hidden" name="invoice_type" value="0">
							<input type="hidden" name="from" value="crowfunding">
							<input type="hidden" name="tax_id" value="">

                            <input type="submit" id="btn_sub" value="下一步">
                        @csrf </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script>
		$('#_remarks').on('blur',function(){
			$('#liuyan').val($(this).val());
		});
		$('#_invoiceTitle').on('blur',function(){
			$('#inv_payee').val($(this).val());
		})
		function newInvoiceTitle(){
			$("#invoiceTitleDetail").show();
			$("#invoiceFlag").val('1');
		}
		function changeInvoiceFlag(){
			$("#invoiceTitleDetail").hide();
			$("#invoiceFlag").val('0');
		}

		$(document).on("click",".consignee-cont li",function(){
			var address_id = $(this).data('addressid');
			$(this).addClass("item-selected").siblings().removeClass("item-selected");
			zc_Consignee(address_id);
		});

		function zc_Consignee(address_id){
			var consignee = document.getElementById('radio_' + address_id);
			if(consignee){
				consignee.checked = true;
			}
		}

		$(document).on("click","#editRepeat",function(){
			var zc_address = $(".zc_address");
			if(zc_address.is(":hidden")){
				zc_address.show();
			}else{
				zc_address.hide();
			}
		});

		$(document).on('click',"#confirmAddress",function(){
			var gid = {{ request()->get('gid') }};
			var obj = document.getElementsByName("consignee_radio");
			for(var i=0; i<obj.length; i ++){
				if(obj[i].checked){
					Ajax.call('crowdfunding.php?act=confirmAddress','consignee_id= '+obj[i].value +'&gid=' + gid , confirmAddressResponse, 'POST','JSON');
				}
			}
		});

		function confirmAddressResponse(result){
			if(result.error == 0){
				$("#showAddress").html(result.content);
				$("#common_button").html(result.common);
				$(".zc_address").css('display','none');

			}
		};

		$(document).on('click',"#addNewAddress",function(){
			var gid = {{ request()->get('gid') }};
			Ajax.call('crowdfunding.php?act=add_Consignee','gid=' + gid , consigneeResponse, 'POST','JSON');
		});

		//编辑删除地址
		$(document).on('click',"*[data-dialog='edit_address']",function(){
			var id = $(this).data("id");
            var gid = {{ request()->get('gid') }};
			Ajax.call('crowdfunding.php?act=add_Consignee','address_id='+id+'&gid='+ gid,  consigneeResponse, 'POST','JSON');
		});

		function consigneeResponse(result){
			 pb({
				 id:"zcDig",
				 title:json_languages.edit_address_zc,
                 width:720,
                 height:200,
                 content:result.content,
                 drag:false,
                 foot:false
			 });
		 };

		$(document).on('click',"*[data-dialog='del_address']",function(){
			var gid = {{ request()->get('gid') }};
			var address_id = $(this).data("id");
			pb({
				 id:"zcdelDig",
				 title:json_languages.drop_address_zc,
                 width:310,
                 height:30,
                 content:json_languages.drop_address_zc,
                 drag:false,
				 ok_title: json_languages.determine,
				 cl_title: json_languages.cancel,
				 onOk:function(){
					Ajax.call('crowdfunding.php?act=delete_Consignee','address_id='+ address_id +'&gid=' + gid , del_ConsigneeResponse, 'POST','JSON');
				 }
			 });
		});

		function del_ConsigneeResponse(result){


			$('#consignee-addr').html(result.content);

			$('#common_button').html(result.common);

			$('#zcdelDig').remove();

			$('#pb-mask').remove();

			if(result.error == 2){

				window.location.href='crowdfunding.php?act=checkout&gid=' + result.gid;

			}
		}
	</script>

@endif





@if($action == 'done')

    <div class="mt20">
    	<div class="z_container">
        	<div class="order_process">
                <ul>
                    <li>
                        {{ $lang['zc_order_input'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li class="active">
                        {{ $lang['zc_order_confirm'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['payment'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                    <li>
                        {{ $lang['complete'] }}
                        <span class="order_behind_arrow order_arrow"></span>
                        <span class="order_ahead_arrow order_arrow"></span>
                    </li>
                </ul>
            </div>
            <div class="module_wrap mt20">
            	<div class="common_tit"><h1 class="common_tit_name icon_ok">{{ $lang['zc_order_success'] }}：{{ $g_title }}</h1></div>
                <div class="module_con">
                    <div class="module_item">
                        <dl>
                            <dt>{{ $lang['order_number'] }}：</dt>
                            <dd>{{ $order['order_sn'] }}</dd>
                        </dl>
                        <dl>
                            <dt>{{ $lang['Contacts'] }}：</dt>
                            <dd>{{ $order['consignee'] }}</dd>
                        </dl>
                        <dl>
                            <dt>{{ $lang['Contact_information'] }}：</dt>
                            <dd>
@if($order['mobile'])
 {{ $order['mobile'] }}
@else
 {{ $order['tel'] }}
@endif
</dd>
                        </dl>
                        <dl>
                            <dt>{{ $lang['Remarks'] }}：</dt>
                            <dd>{{ $order['postscript'] }}</dd>
                        </dl>
                    </div>
                    <div class="module_item">
                        <dl>
                            <dt>{{ $lang['consignee_info'] }}：</dt>
                            <dd>
                                <div class="write_repeat">
                                    <span>{{ $order['consignee'] }}</span>
                                    <span>
@if($order['address'])
{{ $order['address'] }}
@else
{{ $b['province'] }} {{ $b['city'] }} {{ $b['district'] }}
@endif
</span>
                                    <span>
@if($order['mobile'])
{{ $order['mobile'] }}
@else
{{ $order['tel'] }}
@endif
</span>
                                </div>
                            </dd>

                        </dl>
                    </div>
                    <table border="0" cellspacing="0" cellpadding="0" class="table01 mt20">
                        <thead>
                        <tr><th>{{ $lang['project_name'] }}</th>
                        <th>{{ $lang['Return_content'] }}</th>
                        <th>{{ $lang['Support_amount'] }}</th>
                        <th>{{ $lang['shipping_fee'] }}</th>
                        </tr></thead>
                        <tbody>
                        <tr>
                            <td><a target="_blank" href="crowdfunding.php?act=detail&id={{ $xm_id }}">{{ $g_title }}</a></td>
                            <td>
                                <div class="default_txt pr">
                                    <div style="width:455px;">{{ $huibao }}</div>
                                </div>
                            </td>
                            <td><span class="f_red">{{ $order['format_goods_amount'] }}</span></td>
                            <td>{{ $order['format_shipping_fee'] }}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="total930">
                        <strong class="f_gery14">{{ $lang['Amount_payable'] }}：</strong><span class="f_red28">{{ $order['format_order_amount'] }}</span>
                    </div>

                    <div class="common_button" style="width:900px;margin:0 auto;">

                        <div class="pay_more" style="width:900px;">
                            <ul>

@foreach($pay_online_button as $key => $vo)

                                <li style="float: left;height:42px; overflow:hidden; margin:5px 10px" order_sn="{{ $order['order_sn'] }}" flag="{{ $key }}">{!! $vo !!}</li>

@endforeach

                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
	<script>
		$('#zf').find('input').addClass('btn_zf');
		$('#zf').find('input').val("{{ $lang['Immediate_payment'] }}");


		$(function(){
			//微信支付定时查询订单状态 by wanglu
    		checkOrder();

			//微信扫码
			$("[data-type='wxpay']").on("click",function(){
				var content = $("#wxpay_dialog").html();
				pb({
					id: "scanCode",
					title: "",
					width: 716,
					content: content,
					drag: true,
					foot: false,
					cl_cBtn: false,
					cBtn: false
				});
			});
		});

        var timer;
        function checkOrder(){
            var pay_name = "{{ $order['pay_name'] }}";
            var pay_status = "{{ $order['pay_status'] }}";
            var url = "ajax_flow_pay.php?step=checkorder&order_id={{ $order['order_id'] }}";
            var log_id = "{{ $order['log_id'] }}";
            if(pay_name == json_languages.payment_is_online && pay_status == 0){
                $.get(url, {}, function(data){
                    //已付款
                    if(data.code > 0 && data.pay_code == 'wxpay'){
                        clearTimeout(timer);
                        location.href = "respond.php?code=" + data.pay_code + "&status=1&log_id=" + log_id;
                    }
                },'json');
            }
            timer = setTimeout("checkOrder()", 5000);
        }
	</script>

@endif





@if($action == 'consignee')

	@include('frontend::library/page_header_flow')
    <div class="flow_warp w">

    <script type="text/javascript">
      region.isAdmin = false;

@foreach($lang['flow_js'] as $key => $item)

      var {{ $key }} = "{!! $item !!}";

@endforeach



      onload = function() {
        if (!document.all)
        {
          document.forms['theForm'].reset();
        }
      }

    </script>


@foreach($consignee_list as $sn => $consignee)

    <form action="crowdfunding.php" method="post" name="theForm" id="theForm" onsubmit="return checkConsignee(this)">
    @include('frontend::library/zc_consignee')
    @csrf </form>

@endforeach

    </div>

@endif





@if($action == "pay_success" )

        <div class="shopend-warp">

            <div class="shopend-info">
                <div class="s-i-left"><i class="ico-success"></i></div>
                <div class="s-i-right">
                    <h3>{{ $lang['payment_Success'] }}</h3>
                    <div class="s-i-tit">
                        <p>{{ $lang['order_number'] }}：<em id="nku">{{ $order['order_sn'] }}</em></p>
                        <p>{{ $lang['Total_amount_payable'] }}：<em>{{ $order['order_amount'] }}</em></p>
                    </div>
                    <div class="s-i-btn">

@if($is_zc_order)

                        <a href="user_crowdfund.php?act=crowdfunding" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@else

                        <a href="user_order.php?act=order_list" class="btn sc-redBg-btn mr10">{{ $lang['view_order'] }}</a>

@endif

                        <a href="{{ url('/') }}" class="ftx-05">{{ $lang['back_home'] }}</a>
                    </div>
                </div>
            </div>
        </div>


@endif






@if($action == 'xm')

    <div class="query-result">
        <div class="query-condition">
            <div class="searchNew">
                <input type="text" class="search-text" id="w" value="" placeholder="{{ $lang['Keyword_search_placeholder'] }}">
                <a href="javascript:;" id="sousuo" class="searchNewbtn"><i class="iconfont icon-search"></i></a>
            </div>
            <div class="query-list">
                <div class="attr">
                    <div class="a-key"><i class="iconfont icon-sort"></i>{{ $lang['category'] }}：</div>
                    <div class="a-values">
                        <div class="v-fold v-list">
                            <ul class="f-list f-left" id="parent_catagory">
                                <li class="current"><a href="javascript:;" code="0">{{ $lang['all_attribute'] }}</a></li>

@foreach($cate_one as $item)

                                    <li><a href="javascript:;" code="{{ $item['cat_id'] }}" >{{ $item['cat_name'] }}</a></li>

@endforeach

                            </ul>
                        </div>
                        <div class="v-second-list">

@foreach($cate_two as $key => $res)

                                <ul class="s-list">

@foreach($res as $item)

                                    <li><a name="category" parentid="{{ $key }}" code="{{ $item['cat_id'] }}" href="javascript:;">{{ $item['cat_name'] }}</a></li>

@endforeach

                                </ul>

@endforeach

                        </div>
                    </div>
                </div>
                <div class="attr">
                    <div class="a-key"><i class="iconfont icon-list"></i>{{ $lang['sort'] }}：</div>
                    <div class="a-values">
                        <div class="v-fold v-order">
                            <ul class="f-list" id="sort" code="zhtj">
                                <li class="current">
                                    <a href="javascript:;" code="zhtj">{{ $lang['Comprehensive_rec'] }}</a>
                                </li>
                                <li>
                                    <a href="javascript:;" code="zxsx">{{ $lang['on_line_new'] }}</a>
                                </li>
                                <li>
                                    <a href="javascript:;" code="jezg">{{ $lang['Maximum_amount'] }}</a>
                                </li>
                                <li>
                                    <a href="javascript:;" code="zczd">{{ $lang['Maximum_Support'] }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="query-result-outer">

@if($zc_arr)

            <ul>

@foreach($zc_arr as $item)

                    <li class="item-li">
                        <a target="_blank" href="crowdfunding.php?act=detail&id={{ $item['id'] }}" class="item-a"><img src="{{ $item['title_img'] }}" width="280" height="220" class="item-img"></a>
                        <h3 class="item-title"><a target="_blank" href="crowdfunding.php?act=detail&id={{ $item['id'] }}">{{ $item['title'] }}</a></h3>
                        <div class="p-outer">
                            <div class="p-bar">
                                <div style="width: {{ $item['baifen_bi'] }}%" class="p-bar-purple"></div>
                            </div>
                        </div>
                        <div class="p-i-infos">
                            <div class="fore1">
                                <p class="num">{{ $lang['reached'] }}<span>{{ $item['baifen_bi'] }}%</span></p>
                            </div>
                            <div class="fore2">
                                <p class="num"><span>{{ $item['join_money_formated'] }}</span>{{ $lang['Raise'] }}</p>
                            </div>
                            <div class="fore3">
                                <p class="num">{{ $lang['remaining'] }}<span>{{ $item['shenyu_time'] }}</span>{{ $lang['day'] }}</p>
                            </div>
                        </div>
                    </li>

@endforeach

            </ul>

@else

            <div class="no_records">
				<i class="no_icon_two"></i>
				<div class="no_info no_info_line">
					<h3>{{ $lang['information_null'] }}</h3>
					<div class="no_btn">
						<a href="index.php" class="btn sc-redBg-btn">{{ $lang['back_home'] }}</a>
					</div>
				</div>
			</div>

@endif

        </div>

@if($page_arr)

        <div id="page_div" class="zc_my_pages">
            <a href="javascript:void(0)" class="syy">{{ $lang['page_prev'] }}</a>

@foreach($page_arr as $key => $item)

            <a href="javascript:;"
@if($key == 0)
 class="current"
@endif
>{{ $item }}</a>

@endforeach

            <a href="javascript:;" class="xyy">{{ $lang['page_next'] }}</a>
        </div>

@endif

    </div>
	<script type="text/javascript">
	$('#parent_catagory li').on('click',function(){
		$('.s-list a').removeClass('curr');
		var code = $(this).find('a').attr('code');
		var wenzi = $.trim($('#w').val());
		if(code==0){
			$.post('crowdfunding.php?act=search_quanbu',{code:code,wenzi:wenzi},function(data){
				$('.query-result-outer').remove();
				$('#page_div').remove();
				$('.query-result').append(data);
			},'json');
		}else{
			$.post('crowdfunding.php?act=search_cate',{code:code,wenzi:wenzi},function(data){
				$('.query-result-outer').remove();
				$('#page_div').remove();
				$('.query-result').append(data);
			},'json');
		}
	})

	$('.s-list a').on('click',function(){
		var code = $(this).attr('code');
		var wenzi = $.trim($('#w').val());
		$(this).parent().siblings().find('a').removeClass('curr');
		$(this).addClass('curr');
		$.post('crowdfunding.php?act=search_cate_child',{code:code,wenzi:wenzi},function(data){
			$('.query-result-outer').remove();
			$('#page_div').remove();
			$('.query-result').append(data);
		},'json');
	})

	$('body').on('click','#sort li',function(){
		var wenzi = $.trim($('#w').val());
		var sig = $(this).find('a').attr('code');
		var pid = $('#parent_catagory').find('li[class=current]').children('a').attr('code');
		var tid = $('.s-list').find('a[class=curr]').attr('code');

		if(tid){
			$.post('crowdfunding.php?act=search_paixu_tid',{id:tid,sig:sig,wenzi:wenzi},function(data){
				$('.query-result-outer').remove();
				$('#page_div').remove();
				$('.query-result').append(data);
			},'json');
		}else{
			if(pid==0){
				$.post('crowdfunding.php?act=search_paixu_pid_zero',{id:pid,sig:sig,wenzi:wenzi},function(data){
					$('.query-result-outer').remove();
					$('#page_div').remove();
					$('.query-result').append(data);
				},'json');
			}else{
				$.post('crowdfunding.php?act=search_paixu_pid',{id:pid,sig:sig,wenzi:wenzi},function(data){
					$('.query-result-outer').remove();
					$('#page_div').remove();
					$('.query-result').append(data);
				},'json');
			}
		}
	})


	$('#sousuo').on('click',function(){
		var wenzi = $.trim($('#w').val());
		var sig = $('#sort').find('li[class=current]').find('a').attr('code');
		var pid = $('#parent_catagory').find('li[class=current]').children('a').attr('code');
		var tid = $('.s-list').find('a[class=curr]').attr('code');

		if(tid){
			$.post('crowdfunding.php?act=search_paixu_tid',{id:tid,sig:sig,wenzi:wenzi},function(data){
				$('.query-result-outer').remove();
				$('#page_div').remove();
				$('.query-result').append(data);
			},'json');
		}else{
			if(pid==0){
				$.post('crowdfunding.php?act=search_paixu_pid_zero',{id:pid,sig:sig,wenzi:wenzi},function(data){
					$('.query-result-outer').remove();
					$('#page_div').remove();
					$('.query-result').append(data);
				},'json');
			}else{
				$.post('crowdfunding.php?act=search_paixu_pid',{id:pid,sig:sig,wenzi:wenzi},function(data){
					$('.query-result-outer').remove();
					$('#page_div').remove();
					$('.query-result').append(data);
				},'json');
			}
		}

	})

	$('body').on('click','#page_div a',function(){
		var page = $("#page_div a[class='current']").text();
		var last_page = $('#page_div a').last().prev().text();
		var val = $(this).text();
		var res_page = null;

		if(page==val){
			return false;
		}

		if(page==1 && val==page_prev){
			return false;
		}

		if(page==last_page && val==page_next){
			return false;
		}

		if(val==page_prev){
			res_page = parseInt(page)-1;
		}

		if(val==page_next){
			res_page = parseInt(page)+1;
		}

		if(val!=page_prev && val!=page_next){
			res_page = $(this).text();
		}

		var wenzi = $.trim($('#w').val());
		var sig = $('#sort').find('li[class=current]').find('a').attr('code');
		var pid = $('#parent_catagory').find('li[class=current]').children('a').attr('code');
		var tid = $('.s-list').find('a[class=curr]').attr('code');

		if(tid){
			$.post('crowdfunding.php?act=page_tid',{id:tid,sig:sig,wenzi:wenzi,page:res_page},function(data){
				$('.query-result-outer').remove();
				$('#page_div').remove();
				$('.query-result').append(data);
			},'json');
		}else{
			if(pid==0){
				$.post('crowdfunding.php?act=page_pid_zero',{id:pid,sig:sig,wenzi:wenzi,page:res_page},function(data){
					$('.query-result-outer').remove();
					$('#page_div').remove();
					$('.query-result').append(data);
				},'json');
			}else{
				$.post('crowdfunding.php?act=page_pid',{id:pid,sig:sig,wenzi:wenzi,page:res_page},function(data){
					$('.query-result-outer').remove();
					$('#page_div').remove();
					$('.query-result').append(data);
				},'json');
			}
		}
	});
	</script>


@endif


    <script type="text/javascript" src="{{ skin('js/dsc-common.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/region.js') }}"></script>
    <script type="text/javascript" src="{{ skin('js/jquery.purebox.js') }}"></script>


<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/common.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/compare.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/parabola.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/region.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/shopping_flow.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.SuperSlide.2.1.1.js') }}"></script>
	@include('frontend::library/page_footer')
</body>
</html>
