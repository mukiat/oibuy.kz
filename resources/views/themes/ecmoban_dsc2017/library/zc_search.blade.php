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
                        <p class="num">{{ $item['baifen_bi'] }}%</p>
                        <p class="p-num">{{ $lang['reached'] }}</p>
                    </div>
                    <div class="fore2">
                        <p class="num">{{ config('shop.currency_format', '¥') }}{{ $item['join_money'] }}</p>
                        <p class="p-num">{{ $lang['Raise_sea'] }}</p>
                    </div>
                    <div class="fore3">
                        <p class="num">{{ $item['shenyu_time'] }}{{ $lang['day'] }}</p>
                        <p class="p-num">{{ $lang['residual_time'] }}</p>
                    </div>
                </div>
            </li>

@endforeach

        </ul>

@else

    	<div class="no_records pl450">
            <i class="no_icon_two"></i>
            <div class="no_info">
                <h3>{{ $lang['no_zc_goods'] }}</h3>
            </div>
        </div>

@endif

</div>

@if($page_arr)

<div id="page_div" class="zc_my_pages">
	<a href="javascript:void(0)" class="syy current">{{ $lang['page_prev'] }}</a>

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
