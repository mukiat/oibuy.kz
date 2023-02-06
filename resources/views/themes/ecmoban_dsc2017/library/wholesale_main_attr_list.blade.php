
@foreach($main_attr_list as $key => $attr)
 
<div class="lie
@if($loop->last)
 last-child
@endif
" ectype="attr_group" data-attr_group="{{ $attr['attr_group'] }}">
    <div class="row1"><strong class="ftx-06">{{ $attr['attr_value'] }}</strong></div>
    <div class="row2"><div class="price">{{ $goods['goods_price_formatted'] }}</div></div>
    <div class="row3">
       <div class="b-stock">{{ $attr['product_number'] }}件可售</div> 
    </div>
    <div class="row4">
    	<div class="number">
            <a href="javascript:void(0)" class="decrement btn-reduce">-</a>
            <input name="goods_number[{{ $attr['goods_attr_id'] }}][]" type="text" id="quantity" class="itxt buy-num" value="1" size="10" data-inventory="{{ $attr['product_number'] }}">
            <a href="javascript:void(0)" class="increment btn-add">+</a>
        </div>
    </div>
</div>

@endforeach
