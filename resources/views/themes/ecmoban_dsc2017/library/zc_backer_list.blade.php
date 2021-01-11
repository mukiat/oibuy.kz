
@if($backer_list)

<div class="pro-support">
	<ul class="item-ul">

@foreach($backer_list as $backer)

		<li class="item-li">
			<div class="item-img">
				<img width="80" height="80" src="
@if($backer['user_picture'])
{{ $backer['user_picture'] }}
@else
{{ skin('/images/no-img_mid_.jpg') }}
@endif
">
				<em class="item-shadow"></em>
			</div>
			<div class="item-detail">
				<p class="item-name" title="{{ $backer['user_name'] }}">{{ $backer['user_name'] }}</p>
				<p class="item-support">{{ $lang['Support_project'] }}{{ $backer['formated_price'] }}{{ $lang['yuan'] }}</p>
				<p class="item-num">
                   <!--<span>{{ $lang['Launch'] }}：</span>
				   <span class="num">{{ $backer['back_num'] }}</span>
                   <span class="line"></span>-->
                   <span>{{ $lang['Support'] }}：</span>
                   <span class="num">{{ $backer['back_num'] }}</span>
				</p>
			</div>
		</li>

@endforeach

	</ul>
</div>
<div class="zhoucou_page">
	<ul class="fr mr20">

@if($pager['page_prev'])
<li class="up_page"><a href="javascript:get_backer_list({{ $zcid }},{{ $prev_page }});">{{ $lang['page_prev'] }}</a></li>
@endif


@if($pager['page_count'] > $prev_page)
<li class="page_cur"><a href="javascript:get_backer_list({{ $zcid }},{{ $curr_page }});">{{ $curr_page }}</a></li>
@endif


@if($pager['page_count'] > $curr_page)
<li class="page_default"><a href="javascript:get_backer_list({{ $zcid }},{{ $next_page }});">{{ $next_page }}</a></li>
@endif


@if($pager['page_count'] > $next_page)
<li class="page_default"><a href="javascript:get_backer_list({{ $zcid }},{{ $third_page }});">{{ $third_page }}</a></li>
@endif


@if($pager['page_next'])
<li class="up_page"><a href="javascript:get_backer_list({{ $zcid }},{{ $next_page }});">{{ $lang['page_next'] }}</a></li>
@endif

	</ul>
</div>

@else

<p style="font-size: 26px;color: #ccc;margin-top: 30px;">{{ $lang['no_supporter'] }}</p>

@endif

