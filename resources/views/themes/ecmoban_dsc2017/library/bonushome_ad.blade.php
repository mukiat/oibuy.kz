
@if($ad_child)


@foreach($ad_child as $key => $ad)


@if($ad['ad_code'])

<div class="ejectAdv" ectype="ejectAdv">
    <div class="ejectAdvbg"></div>
    <div class="ejectAdvimg">
        <a href="{{ $ad['ad_link'] }}" target="_blank" ectype="ejectClose"><img src="{{ $ad['ad_code'] }}"></a>
        <a href="javascript:void(0);" class="ejectClose" ectype="ejectClose"></a>
    </div>
</div>

@endif


@endforeach


@endif
