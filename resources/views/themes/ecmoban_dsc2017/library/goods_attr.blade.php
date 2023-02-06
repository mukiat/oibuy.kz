
<form action="javascript:;" method="post" name="ECS_FORMBUY" id="ECS_FORMBUY" >
<div class="addCartAttr">
	<div class="attr_list">

@foreach($spe_array as $spe_list)

    	<div class="item goods_info_attr">
            <div class="dt">{{ $spe_list['name'] }}：</div>
            <div class="dd">

@if($spe_list['is_checked'] > 0)

                <ul>

@foreach($spe_list['values'] as $val)

                    <li @if($val['checked'] == 1) class="selected" @endif >
                        <b></b>
                        <a href="javascript:void(0)">
                        @if($val['img_flie'])
                        <img src="{{ $val['img_flie'] }}" width="25" height="25" style="margin-right:3px;" />
                        @endif
                        {{ $val['label'] }}
                        </a>
                        <input class="hide" id="spec_value_{{ $val['id'] }}"
@if($spe_list['attr_type'] == 2)
type="checkbox"
@else
type="radio"
@endif

@if($val['checked'] == 1) checked="checked" @endif name="spec_{{ $spe_list['attr_id'] }}" value="{{ $val['id'] }}" />
                    </li>

@endforeach

                </ul>

@else

                <ul>

@foreach($spe_list['values'] as $key => $val)

                    <li @if($key == 0) class="selected" @endif >
                        <b></b>
                        <a href="javascript:void(0)">{{ $val['label'] }}</a>
                        <input class="hide" id="spec_value_{{ $val['id'] }}" onClick="cat_changePrice();"
@if($spe_list['attr_type'] == 2)
type="checkbox"
@else
type="radio"
@endif

@if($key == 0) checked="checked" @endif name="spec_{{ $spe_list['attr_id'] }}" value="{{ $val['id'] }}" />
                    </li>

@endforeach

                </ul>

@endif

            </div>
        </div>
        <input type="hidden" name="spec_list" value="{{ $spe_list['attr_id'] }}" />

@endforeach


@if($goods['is_minimum'] == 1)

        <div class="item">
        	<div class="dt">{{ $lang['minimum'] }}：</div>
            <div class="dd">{{ $goods['minimum'] }}</div>
        </div>

@endif

        <div class="item">
        	<div class="dt">{{ $lang['selling_price_alt'] }}：</div>
            <div class="dd"><span class="p-price" id="ECS_SHOPPRICE">{{ $shop_price ?? 0 }}</span></div>
        </div>
        <div class="item choose-num">
        	<div class="dt">{{ $lang['number_to'] }}：</div>
            <div class="dd is-number">
            	<div class="amount-warp">
                    <input class="text buy-num" id="quantity_{{ $goods_id }}" onblur="cat_changePrice()" value="
@if($goods['is_minimum'] == 1)
                    {{ $goods['minimum'] }}
@else
1
@endif

" name="number" defaultnumber="1">
                    <div class="a-btn">
                    	<a class="btn-add" href="javascript:void(0);" data-id="{{ $goods_id }}"><i class="iconfont icon-up"></i></a>
                        <a class="btn-reduce btn-disabled" href="javascript:void(0);" data-id="{{ $goods_id }}"><i class="iconfont icon-down"></i></a>
                    </div>
                    <input type="hidden" id="perNumber" value="1000">
                    <input type="hidden" id="perMinNumber" value="
@if($goods['is_minimum'] == 1)
                    {{ $goods['minimum'] }}
@else
1
@endif
">
                </div>

@if($cfg['show_goodsnumber'])

                <span>库存：<em id="goods_attr_num"></em>&nbsp;
@if($goods['goods_unit'])
{{ $goods['goods_unit'] }}
@else
{{ $goods['measure_unit'] }}
@endif
</span>

@endif

            </div>
        </div>
    </div>
</div>
@csrf </form>

<script type="text/javascript">
$(function(){
	cat_changePrice(1);
});

/**
 * 点选可选属性或改变数量时修改商品价格的函数
 */
function cat_changePrice(type)
{
  var qty = document.forms['ECS_FORMBUY'].elements['number'].value;

  //ecmoban模板堂 --zhuo start 限购
  if(type != 1){

	  var goods_attr_id = getSelectedAttributes(document.forms['ECS_FORMBUY']);


@if($xiangou == 1)


@if($goods['is_xiangou']==1 && $goods['xiangou_num'] > 0)

			var xuangou_num = {{ $goods['xiangou_num'] }};
			var xiangou = {{ $xiangou }};
			if(qty > xuangou_num && xuangou_num > 0 && xiangou == 1){
				pbDialog(json_languages.Purchase_quantity,"",0);
				qty = 1;
			}

@endif


@endif


	   var goods_attr = '';

@if($cfg['add_shop_price'] == 0)

	   		var attr_id = goods_attr = getSelectedAttributesGroup(document.forms['ECS_FORMBUY']);
			goods_attr = '&goods_attr=' + attr_id;

@endif


	   Ajax.call('goods.php', 'act=price&id=' + {{ $goods_id ?? 0 }} + '&attr=' + goods_attr_id + goods_attr + '&number=' + qty + '&warehouse_id=' + {{ $region_id ?? 0 }} + '&area_id=' + {{ $area_id ?? 0 }}, changePriceResponse, 'GET', 'JSON');
  }else{

	  var attr = getSelectedAttributes(document.forms['ECS_FORMBUY']);
	  attr = '&attr=' + attr;

	  Ajax.call('goods.php', 'act=price&id=' + {{ $goods_id ?? 0 }} + attr + '&number=' + qty + '&warehouse_id=' + {{ $region_id ?? 0 }} + '&area_id=' + {{ $area_id ?? 0 }} + '&type=' + type, changePriceResponse, 'GET', 'JSON');
  }
   //ecmoban模板堂 --zhuo end 限购
}

/**
 * 接收返回的信息
 */
function changePriceResponse(res)
{
	if (res.err_msg.length > 0)
	{
		pbDialog(res.err_msg,"",0);
	}else{
		if (document.getElementById('goods_attr_num')){
		  document.getElementById('goods_attr_num').innerHTML = res.attr_number;
		  document.getElementById('perNumber').value = res.attr_number;
		}

		if(res.type == 1){
			document.getElementById('ECS_SHOPPRICE').innerHTML = res.result;
		}else{
			if(res.show_goods == 1){
				document.getElementById('ECS_SHOPPRICE').innerHTML = res.spec_price;
			}else{
				document.getElementById('ECS_SHOPPRICE').innerHTML = res.result;
			}
		}
	}
}

//数量选择
function quantity(){
	$(".btn-reduce").click(function(){
		var goods_id = Number($(this).data("id"));
		var quantity = Number($("#quantity_" + goods_id).val());
		var perNumber = Number($("#perNumber").val());
		var perMinNumber = Number($("#perMinNumber").val());

		if(quantity>perMinNumber){
			quantity-=1;
			$("#quantity_" + goods_id).val(quantity);
            cat_changePrice();//@author bylu 数量减少后获取白条分期新价格;

			if(quantity == 1)
			$(".btn-reduce").addClass("btn-disabled");
		}else{
			$("#quantity_" + goods_id).val(perMinNumber);
		}
	});

	$(".btn-add").click(function(){
		var goods_id = Number($(this).data("id"));
		var quantity = Number($("#quantity_" + goods_id).val());
		var perNumber = Number($("#perNumber").val());
		var perMinNumber = Number($("#perMinNumber").val());
		var err = 0;

		if(quantity < perNumber){

			quantity+=1;

			//限购

@if($xiangou == 1)


@if($goods['is_xiangou']==1 && $goods['xiangou_num'] > 0)

					var xuangou_num = {{ $goods['xiangou_num'] }};
					var xiangou = {{ $xiangou }};

					if ({{ $orderG_number }} >= xuangou_num){
						  err = 1;
						  var message = json_languages.Already_buy+'{{ $orderG_number }}' + json_languages.Already_buy_two;
						  quantity = 1;
					}else if(quantity > xuangou_num && xuangou_num > 0 && xiangou == 1){
						  err = 1;
						  var message = json_languages.Purchase_quantity;
						  quantity = 1;
					}

					if(err == 1){
						pbDialog(message,"",0);
					}

@endif


@endif


			if(quantity == 1){
				err = 0;
			}
			$("#quantity_" + goods_id).val(quantity);
            cat_changePrice();//@author bylu 数量增加后获取白条分期新价格;

			$(".btn-reduce").removeClass("btn-disabled");
		}else{
			$("#quantity_" + goods_id).val(perNumber);
		}

	})
}
quantity();
</script>
