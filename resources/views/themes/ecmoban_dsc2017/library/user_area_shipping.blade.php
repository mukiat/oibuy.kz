
@if($shippingFee['is_shipping'] == 1)


@if($shippingFee['shipping_fee'] > 0)

<span class="gary" ectype="freight_info_span">[ {{ $lang['express'] }}：{{ $shippingFee['shipping_fee_formated'] }} ]&nbsp;&nbsp;
@if($shippingFee['shipping_title'])
{{ $shippingFee['shipping_title'] }}
@endif
</span>

@else

	
@if($is_shipping == 1)

    <span class="gary" ectype="freight_info_span">[ {{ $lang['Free_shipping'] }} ]</span>
    
@else

    	
@if($shippingFee['shipping_code'] == 'fpd')

            
@if($shippingFee['shipping_title'])
{{ $shippingFee['shipping_title'] }}
@endif

        
@else

            <span class="gary" ectype="freight_info_span">[ {{ $lang['free_freight'] }} ]</span>
        
@endif

	
@endif


@endif


@endif

<input name="is_shipping" id="is_shipping" type="hidden" value="{{ $shippingFee['is_shipping'] ?? 0 }}">