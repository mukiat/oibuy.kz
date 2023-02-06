{{--bindgoods.blade.php--}}
<table id="list-table" class="ecsc-default-table" style="">
    <thead>
    <tr>
        <th width="3%" class="sign">
            <div class="tDiv">
                <input type="checkbox" class="checkbox" name="all_list" id="all_list" />
                <label for="all_list" class="checkbox_stars"></label>
            </div>
        </th>
        <th width="5%">
            <div class="tDiv">{{ __('admin/common.record_id') }}</div>
        </th>
        <th width="50%">
            <div class="tDiv">{{ __('admin/common.goods_name') }}</div>
        </th>
        <th>
            <div class="tDiv">{{ __('admin/order.shop_name') }}</div>
        </th>

        <th width="20%">
            <div class="tDiv">{{ __('admin/common.handler') }}</div>
        </th>
    </tr>
    </thead>

    @if(!empty($list))

    @foreach($list as $val)

    <tr>
        <td class="sign">
            <div class="tDiv">
                <input type="checkbox" class="checkbox" value="{{ $val['goods_id'] }}" id="checkbox_{{ $val['goods_id'] }}" name="goods_id[]">
                <label for="checkbox_{{ $val['goods_id'] }}" class="checkbox_stars "></label>
            </div>
        </td>
        <td>
            <div class="tDiv">{{ $val['goods_id'] }}</div>
        </td>
        <td>
            <div class="tDiv">
                <div class="img">
                    @if(!empty($val['goods_img']))
                        <img class="img-rounded" src="{{ $val['goods_img'] }}" width="68" height="68" />
                    @endif
                </div>
                <div class="goods-info-left">{{ $val['goods_name'] ?? '' }}</div>
            </div>
        </td>

        <td>
            <div class="tDiv">{{ $val['shop_name'] ?? '' }}</div>
        </td>

        <td class="handle">
            <div class="tDiv a2">
                <a href="javascript:;" data-href="{{ route('seller/goodsserviceslabel/unbind_goods', ['label_id' => $val['label_id'], 'goods_id'=> $val['goods_id']]) }}" class="btn_trash js-delete"><i class="fa fa-trash-o"></i>{{ __('admin/common.drop') }}</a>
            </div>
        </td>
    </tr>

    @endforeach

    @else

    <tbody>
    <tr>
        <td class="no-records" colspan="5">{{ __('admin/common.no_records') }}</td>
    </tr>
    </tbody>

    @endif

    <tfoot>
    <tr>
        <td colspan="20" class="td_border">
            <div class="shenhe">
                <input type="button" onclick="confirm_batch();" ectype="btnSubmit" value="{{ __('admin/common.batch_delete') }}" class="sc-btn btn_disabled"  disabled="true" />
            </div>
        </td>
    </tr>
    </tfoot>

</table>

@include('seller.base.seller_pageview')
