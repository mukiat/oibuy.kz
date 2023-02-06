@if($user_coupons)

    <div class="panl-top">
        <div class="panl-items">
            @foreach($user_coupons as $vo)
                <div class="panl-item panlItem_{{ $vo['ru_id'] }} @if($loop->iteration % 4 == 0) panl-item-last @endif @if($vo['uc_id'] == $order['uc_id']) selected @endif"
                     ectype="panlItem" data-ucid="{{ $vo['uc_id'] }}"
                     data-ru_id="{{ $vo['ru_id'] }}" data-type="coupons">
                    <i class="i-left"></i>
                    <div class="panl-item-warp">
                        <div class="panl-item-top">
                            @if($vo['cou_type'] != 5)
                                <strong>
                                    @if($vo['uc_money'] > 0)
                                        {{ $vo['uc_money_formated'] }}
                                    @else
                                        {{ $vo['cou_money_formated'] }}
                                    @endif
                                </strong>
                            @else
                                <em class="icon-my"></em>
                            @endif
                            <span>{{ $lang['full'] }}{{ $vo['cou_man'] }}</span>
                        </div>
                        <div class="panl-item-bot">
                            <p>{{ $lang['range_bonus'] }}：[{{ $vo['cou_type_name'] }}][{{ $vo['cou_goods_name'] }}
                                ]</p>
                            <p>{{ $lang['seller'] }}：{{ $vo['shop_name'] }}</p>
                            <p>{{ $lang['overdue_time'] }}{{ $vo['cou_end_time'] }}</p>
                        </div>
                    </div>
                    <i class="i-right"></i>
                    <b></b>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if($coupons_list)
    <div class="panl-bot">
        <div class="panl-items">
            @foreach($coupons_list as $vo)
                <div class="panl-item panl-item-disabled
@if($loop->iteration % 4 == 0)
                        panl-item-last
@endif
                        ">
                    <i class="i-left"></i>
                    <div class="panl-item-warp">
                        <div class="panl-item-top">

                            @if($vo['cou_type'] != 5)
                                <strong>
                                    @if($vo['uc_money'] > 0)
                                        {{ $vo['uc_money_formated'] }}
                                    @else
                                        {{ $vo['cou_money_formated'] }}
                                    @endif
                                </strong>
                            @else
                                <em class="icon-my"></em>
                            @endif
                            <span>{{ $lang['full'] }}{{ $vo['cou_man'] }}</span>
                        </div>
                        <div class="panl-item-bot">
                            <p>{{ $lang['range_bonus'] }}：[{{ $vo['cou_type_name'] }}][{{ $vo['cou_goods_name'] }}]</p>
                            <p>{{ $lang['seller'] }}：{{ $vo['shop_name'] }}</p>
                            <p>{{ $lang['overdue_time'] }}：{{ $vo['cou_end_time'] }}</p>
                        </div>
                    </div>
                    <i class="i-right"></i>
                </div>
            @endforeach
        </div>
    </div>
@endif

