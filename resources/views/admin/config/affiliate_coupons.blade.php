@include('admin.base.header', ['page_title' => $page_title])

<style>

</style>

<div class="warpper">
    <div class="title">{{ __('admin/affiliate_coupons.affiliate_coupons_menu') }}</div>
    <div class="content">

        <div class="tabs_info">
            <ul>
                <li role="presentation" class="curr"><a href="{{ route('admin.affiliate_coupons') }}">{{ __('admin/affiliate_coupons.affiliate_coupons_menu') }}</a>
                </li>
            </ul>
        </div>

        <div class="explanation" id="explanation">
            <div class="ex_tit">
                <i class="sc_icon"></i><h4>{{ __('admin/common.operating_hints') }}</h4><span id="explanationZoom" title="{{ __('admin/common.fold_tips') }}"></span>
            </div>
            <ul>
                @foreach(__('admin/affiliate_coupons.tips') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>

        <div class="flexilist">
            <div class="main-info">
                <form method="post" action="{{ route('admin.affiliate_coupons') }}" class="form-horizontal" role="form">
                    <div class="switch_info">

                        {{--上级是否获取优惠券--}}
                        <div class="item">
                            <div class="label-t">{{ __('admin/affiliate_coupons.give_parent_item') }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[give_parent]" class="ui-radio event_zhuangtai" id="value_give_parent_item_1" value="1"

                                               @if (isset($data['give_parent']) && $data['give_parent'] == 1)
                                               checked
                                                @endif
                                        />
                                        <label for="value_give_parent_item_1" class="ui-radio-label

                                            @if (isset($data['give_parent']) && $data['give_parent'] == 1)
                                                active
                                            @endif

                                                ">{{ __('admin/common.open') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[give_parent]" class="ui-radio event_zhuangtai" id="value_give_parent_item_0" value="0"
                                               @if (isset($data['give_parent']) && $data['give_parent'] == 0)
                                               checked
                                                @endif
                                        />
                                        <label for="value_give_parent_item_0" class="ui-radio-label
                                               @if (isset($data['give_parent']) && $data['give_parent'] == 0)
                                                       active
                                                @endif
                                                       ">{{ __('admin/common.close') }}</label>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{--注册人是否获取优惠券--}}
                        <div class="item ">
                            <div class="label-t">{{ __('admin/affiliate_coupons.give_register_item') }}：</div>
                            <div class="label_value">
                                <div class="checkbox_items">
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[give_register]" class="ui-radio event_zhuangtai" id="value_give_register_item_1" value="1"

                                               @if (isset($data['give_register']) && $data['give_register'] == 1)
                                               checked
                                                @endif
                                        />
                                        <label for="value_give_register_item_1" class="ui-radio-label

                                            @if (isset($data['give_register']) && $data['give_register'] == 1)
                                                active
                                            @endif

                                                ">{{ __('admin/common.open') }}</label>
                                    </div>
                                    <div class="checkbox_item">
                                        <input type="radio" name="data[give_register]" class="ui-radio event_zhuangtai" id="value_give_register_item_0" value="0"
                                               @if (isset($data['give_register']) && $data['give_register'] == 0)
                                               checked
                                                @endif
                                        />
                                        <label for="value_give_register_item_0" class="ui-radio-label
                                               @if (isset($data['give_register']) && $data['give_register'] == 0)
                                                active
@endif
                                                ">{{ __('admin/common.close') }}</label>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{--请选择赠送的优惠券--}}
                        <div class="item ">
                            <div class="label-t">{{ __('admin/affiliate_coupons.give_coupons_id_item') }}：</div>
                            {{--select选择框--}}
                            <div class="label_value">
                                <div class="w300">
                                    <select name="data[give_coupons_id]" class="form-control give_coupons_id_item">

                                        <option value="">{{ __('admin/common.please_select') }}</option>

                                        @foreach($select_coupons_list as $k => $options)

                                            <option value="{{ $options['cou_id'] }}" @if($data['give_coupons_id'] == $options['cou_id']) selected @endif >{{ $options['cou_type_name'] }} -- {{ $options['cou_name'] }}</option>

                                        @endforeach

                                    </select>
                                </div>


                            </div>
                        </div>

                        <div class="item">
                            <div class="label-t">&nbsp;</div>
                            <div class="lable_value info_btn">
                                @csrf
                                <input type="submit" name="submit" value="{{ __('admin/common.button_submit') }}" class="button btn-danger bg-red" />
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>
</div>


@include('admin.base.footer')
