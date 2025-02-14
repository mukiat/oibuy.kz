<div class="mc cou-seckill">
    <div class="ui-switchable-panel-main">
        <div class="ui-switchable-panel">
            <div class="seckill-list">

@foreach($seckill as $vo)

                <div class="quan-sk-item
@if($vo['cou_surplus'] == 0)
 quan-gray-sk-item
@endif
">
                    <div class="sk-img"><img width="130px" height="130px" src="{{ $vo['cou_goods_name']['0']['goods_thumb'] }}" alt="{{ $lang['pic_kill_goods'] }}"></div>
                    <div class="q-type">
                        <div class="q-price">
                            <em>{{ config('shop.currency_format', '¥') }}</em>
                            <strong class="num">{{ $vo['cou_money'] }}</strong>
                            <div class="txt"><div class="typ-txt">{{ $vo['cou_type_name'] }}</div></div>
                        </div>
                        <div class="limit"><span class="quota">{{ $lang['consumption_full'] }}{{ $vo['cou_man'] }}{{ $lang['available_full'] }}</span></div>
                        <div class="q-range">
                            <div class="range-item" title="{{ $vo['cou_title'] }}">
                                {{ $vo['cou_title'] }}
                            </div>
                            <div class="range-item">{{ $vo['store_name'] }}</div>
                        </div>
                    </div>
                    <div class="q-opbtns">
                        <b class="semi-circle"></b>

@if($vo['cou_surplus'] == 0)

                        <div class="btn-state btn-getend"></div>
                        <a href="javascript:void(0);" class="q-btn"><span class="txt">{{ $lang['Activities_end'] }}</span><b></b></a>

@else

                        <div class="canvas-qcode-box">
                            <div class="canvas-box">
                                <div class="canvas" data-per="{{ $vo['cou_surplus'] }}">

@if(!empty($user_id) && $vo['cou_is_receive'])

                                    <div class="btn-state btn-geted">{{ $lang['receive_hove'] }}</div>

@else

                                    <div class="canvas_wrap">
                                        <div class="circle">
                                            <div class="circle_item circle_left"></div>
                                            <div class="circle_item circle_right wth0"></div>
                                        </div>
                                        <div class="canvas_num"><span>{{ $lang['remaining'] }}<br /><i>{{ $vo['cou_surplus'] }}</i>%</span></div>
                                    </div>

@endif

                                </div>

                                <a href="javascript:void(0);" class="q-btn get-coupon" cou_id="{{ $vo['cou_id'] }}"><span class="txt">
@if($vo['cou_is_receive'] == 1)
已领取
@else
{{ $lang['receive_now'] }}
@endif
</span><b></b></a>
                                <a href="#none" class="qcode-btn"><b></b></a>
                            </div>
                        </div>

@endif

                    </div>
                </div>

@endforeach

            </div>
        </div>
    </div>
</div>
