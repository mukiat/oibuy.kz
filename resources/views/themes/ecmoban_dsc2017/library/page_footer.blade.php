<div class="footer-new">
    <div class="footer-new-top">
    	<div class="w w1200">
            <div class="service-list">
                <div class="service-item">
                    <i class="f-icon f-icon-qi"></i>
                    <span>{{ $lang['7_days_return'] }}</span>
                </div>
                <div class="service-item">
                    <i class="f-icon f-icon-zheng"></i>
                    <span>{{ $lang['Authentic_guarantee'] }}</span>
                </div>
                <div class="service-item">
                    <i class="f-icon f-icon-hao"></i>
                    <span>{{ $lang['Rave_reviews'] }}</span>
                </div>
                <div class="service-item">
                    <i class="f-icon f-icon-shan"></i>
                    <span>{{ $lang['Lightning_delivery'] }}</span>
                </div>
                <div class="service-item">
                    <i class="f-icon f-icon-quan"></i>
                    <span>{{ $lang['Authority_of_honor'] }}</span>
                </div>
            </div>
            <div class="contact">
                <div class="contact-item contact-item-first"><i class="f-icon f-icon-tel"></i><span>{{ $service_phone }}</span></div>
                <div class="contact-item">
                	 <a id="IM" IM_type="dsc" onclick="openWin(this)" href="javascript:void(0);"  class="btn-ctn"><i class="f-icon f-icon-kefu"></i><span>{{ $lang['consulting_service'] }}</span></a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-new-con">
    	<div class="fnc-warp">
            <div class="help-list">

@foreach($helps as $help_cat)


@if($loop->iteration < 6)

                <div class="help-item">
                    <h3>{{ $help_cat['cat_name'] }}</h3>
                    <ul>

@foreach($help_cat['article'] as $item)


@if($loop->iteration < 4)

                    <li><a href="{{ $item['url'] }}"  title="{{ $item['title'] }}" target="_blank">{{ $item['short_title'] }}</a></li>

@endif


@endforeach

                    </ul>
                </dl>
                </div>

@endif


@endforeach

            </div>
            <div class="qr-code">
                <div class="qr-item qr-item-first">
                    <div class="code_img"><img src="{{ $ecjia_qrcode }}"></div>
                    <div class="code_txt">关注微信</div>
                </div>
                <div class="qr-item">
                    <div class="code_img"><img src="{{ $ectouch_qrcode }}"></div>
                    <div class="code_txt">下载APP</div>
                </div>
            </div>
    	</div>
    </div>
    <div class="footer-new-bot">
    	<div class="w w1200">

@if($navigator_list['bottom'])

            <p class="copyright_links">

@foreach($navigator_list['bottom'] as $nav)

                <a href="{{ $nav['url'] }}"
@if($nav['opennew'] == 1)
 target="_blank"
@endif
>{{ $nav['name'] }}</a>

@if(!$loop->last)

                <span class="spacer"></span>

@endif


@endforeach

            </p>

@endif



@if($img_links  || $txt_links )

            <p class="copyright_links">

@foreach($img_links as $link)

                    <a href="{{ $link['url'] }}" target="_blank" title="{{ $link['name'] }}"><img src="{{ $link['logo'] }}" alt="{{ $link['name'] }}" border="0" /></a>

@endforeach



@if($txt_links)


@foreach($txt_links as $link)

                    <a href="{{ $link['url'] }}" target="_blank" title="{{ $link['name'] }}">{{ $link['name'] }}</a>

@if(!$loop->last)

                    <span class="spacer"></span>

@endif


@endforeach


@endif

            </p>

@endif



@if(config('shop.icp_number'))

        <p>
        <span>{{ $lang['icp_number'] }}:</span><a href="http://beian.miit.gov.cn/" target="_blank">{{ config('shop.icp_number') }}</a>
        </p>
        <p>{!! $copyright ?? '' !!}</p>

        @if(config('shop.icp_file') && config('shop.icp_number'))
            <p class="copyright_auth"><a href="http://beian.miit.gov.cn/" target="_blank"><img src="{{ $icp_file }}" title="{{ config('shop.icp_number') }}" width="36" height="36"/></a></p>
        @endif

@endif



@if($partner_img_links  || $partner_txt_links )

            <p class="copyright_auth">

@foreach($partner_img_links as $link)

                <a href="{{ $link['url'] }}" target="_blank" title="{{ $link['name'] }}"><img src="{{ $link['logo'] }}" alt="{{ $link['name'] }}" border="0" /></a>

@endforeach


@if($txt_links)


@foreach($partner_txt_links as $link)

                <a href="{{ $link['url'] }}" target="_blank" title="{{ $link['name'] }}" class="mr0">{{ $link['name'] }}</a>

@if(!$loop->last)

                |

@endif


@endforeach


@endif

            </p>

@else

            <p class="copyright_auth">&nbsp;</p>

@endif


    @if(config('shop.stats_code', '')) <p style="display:none;"> {!! html_out(config('shop.stats_code', '')) !!} </p> @endif

        </div>
    </div>


    <div class="hide" id="pd_coupons">
        <span class="success-icon m-icon"></span>
        <div class="item-fore">
            <h3>{{ $lang['Coupon_redemption_succeed'] }}</h3>
            <div class="txt ftx-03">{{ $lang['coupons_prompt'] }}</div>
        </div>
    </div>


    <div class="hidden">
        <input type="hidden" value="{{ $is_jsonp ?? 0 }}" id="is_jsonp" name="is_jsonp">
        <input type="hidden" value="{{ url('/') }}" id="asset" name="asset">
        <input type="hidden" name="seller_kf_IM" value="{{ $shop_information['is_im'] }}" rev="" ru_id="{{ $shop_information['ru_id'] ?? 0 }}" />
        <input type="hidden" name="seller_kf_qq" value="{{ $basic_info['kf_qq'] }}" />
        <input type="hidden" name="seller_kf_tel" value="{{ $basic_info['kf_tel'] }}" />
        <input type="hidden" name="user_id" ectype="user_id" value="{{ $user_id ?? 0 }}" />
    </div>
</div>


<script type="text/javascript" src="{{ asset('js/suggest.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/scroll_city.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>


@if($is_jsonp)

    @if($area_htmlType != 'goods' && $area_htmlType != 'exchange')

        <script type="text/javascript" src="{{ asset('js/warehouse_area.js') }}"></script>

    @else

        <script type="text/javascript" src="{{ asset('js/warehouse.js') }}"></script>

    @endif

@else

    <script type="text/javascript" src="{{ asset('js/warehouse.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/warehouse_area.js') }}"></script>

@endif



@if($suspension_two)

<script>var seller_qrcode='<img src="{{ $seller_qrcode_img }}" alt="{{ $seller_qrcode_text }}" width="164" height="164">'; //by wu</script>
{{ $suspension_two }}

@endif


<script type="text/javascript">
$(function () {
	var len = $("form[name='searchForm'] :input[name='_token']").size();
	if(len > 1){
		$("form[name='searchForm'] :input[name='_token']:first").nextAll().remove()
	}
});
</script>
