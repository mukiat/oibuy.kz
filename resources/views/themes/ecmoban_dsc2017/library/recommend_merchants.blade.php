
@if($ad_child)


@if($cat_id)

	<div class="selectbrand" id="selectbrand">
		<div class="sb-hd">
			<h2>{{ $lang['selection_brand'] }}</h2>
		</div>
		<div class="sb-bd">
			<div class="selectbrand-slide">
				<a href="javascript:;" class="prev"><i class="iconfont icon-left"></i></a>
				<a href="javascript:;" class="next"><i class="iconfont icon-right"></i></a>
				<div class="hd">
					<ul></ul>
				</div>
				<div class="bd">
					<ul>
						
@foreach($ad_child as $ad)

						<li>
							<a href="{{ $ad['ad_link'] }}">
								<img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}" alt="" class="cover">
								<div class="logo-wrap"><div class="sbs-logo"><img src="{{ $ad['ad_bg_code'] }}" alt=""></div></div>
								<div class="intro">
									<span>{{ $ad['b_title'] }}</span>
									<em>{{ $ad['s_title'] }}</em>
								</div>
							</a>
						</li>
						
@endforeach

					</ul>
				</div>
			</div>
		</div>
	</div>

@else

<div class="store-channel" id="storeRec">
	<div class="ftit"><h3>{{ $lang['recommended_store'] }}</h3></div>
	<div class="rec-store-list">
		
@foreach($ad_child as $ad)

		<div class="rec-store-item opacity_img">
			<a href="{{ $ad['ad_link'] }}" target="_blank">
            <div class="p-img"><img src="{{ $ad['ad_code'] }}" width="{{ $ad['ad_width'] }}" height="{{ $ad['ad_height'] }}"></div>
            <div class="info">
                <div class="s-logo"><div class="img"><img src="{{ $ad['ad_bg_code'] }}"></div></div>
                <div class="s-title">
                        <div class="tit">{{ $ad['b_title'] }}</div>
                    <div class="ui-tit">{{ $ad['s_title'] }}</div>
                </div>
            </div>
			</a>
		</div>
		
@endforeach

	</div>
</div>

@endif


@endif
