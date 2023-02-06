
<div class="footer user-footer">
	<div class="dsc-copyright">
		<div class="w w1200">

@if($navigator_list['bottom'])

			<p class="footer-ecscinfo pb10">

@foreach($navigator_list['bottom'] as $nav)

				<a href="{{ $nav['url'] }}"
@if($nav['opennew'] == 1)
 target="_blank"
@endif
>{{ $nav['name'] }}</a>

@if(!$loop->last)

				|

@endif


@endforeach

			</p>

@endif


@if($img_links  || $txt_links )

			<p class="footer-otherlink">

@foreach($img_links as $link)

				<a href="{{ $link['url'] }}" target="_blank" title="{{ $link['name'] }}"><img src="{{ $link['logo'] }}" alt="{{ $link['name'] }}" border="0" /></a>

@endforeach


@if($txt_links)


@foreach($txt_links as $link)

				<a href="{{ $link['url'] }}" target="_blank" title="{{ $link['name'] }}">{{ $link['name'] }}</a>

@if(!$loop->last)

				|

@endif


@endforeach


@endif

			</p>

@endif


@if(config('shop.icp_number'))

	<p class="copyright_info">{{ $lang['icp_number'] }}:<a href="http://beian.miit.gov.cn/" target="_blank">{{ config('shop.icp_number') }}</a> </p>

    <p>{!! $copyright ?? '' !!}</p>

    @if(config('shop.icp_file'))
	<p class="copyright_auth"><a href="http://beian.miit.gov.cn/" target="_blank"><img src="{{ config('shop.icp_file') }}" title="{{ config('shop.icp_number') }}" width="36" height="36"/></a></p>
	@endif

@endif

	@if(config('shop.stats_code', '')) <p style="display:none;"> {!! html_out(config('shop.stats_code', '')) !!} </p> @endif

		</div>
	</div>


    <div class="hidden">
        <input type="hidden" name="seller_kf_IM" value="{{ $shop_information['is_im'] }}" rev="" ru_id="{{ request()->get('merchant_id') }}" />
        <input type="hidden" name="seller_kf_qq" value="{{ $basic_info['kf_qq'] }}" />
        <input type="hidden" name="seller_kf_tel" value="{{ $basic_info['kf_tel'] }}" />
        <input type="hidden" name="user_id" value="{{ $user_id ?? 0 }}" />
    </div>
</div>


<script type="text/javascript" src="{{ asset('js/scroll_city.js') }}"></script>
