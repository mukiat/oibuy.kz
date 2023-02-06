
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

<div id='zx_tig' zx_tig="{{ $zx_tig }}"></div>
