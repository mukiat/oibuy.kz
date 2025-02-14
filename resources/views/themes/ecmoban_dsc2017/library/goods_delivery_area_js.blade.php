<script type="text/javascript">
$(function(){
	//配送区域
	goods_delivery_area();
});

/* 获取配送区域 start*/
function goods_delivery_area(){
	var area = new Object();
	var goods_attr_id = '';
    if($(".summary [ectype='is-attr']")){
        $(".summary [ectype='is-attr']").each(function(item){
            var val = $(this).find(".selected").data("name");
            goods_attr_id += val + ',';
        })

        goods_attr_id = goods_attr_id.slice(0,goods_attr_id.length - 1);
    }

	area.province_id = '{{ $area['province_id'] ?? 0 }}';
	area.city_id = '{{ $area['city_id'] ?? 0 }}';
	area.district_id = '{{ $area['district_id'] ?? 0 }}';
	area.street_id = '{{ $area['street_id'] ?? 0 }}';
	area.street_list = '{{ $area['street_list'] ?? 0 }}';
	area.goods_id = '{{ $area['goods_id'] ?? 0 }}';
    area.goods_attr_id = goods_attr_id;
	area.user_id = '{{ $area['user_id'] ?? 0 }}';
	area.region_id = '{{ $area['region_id'] ?? 0 }}';
	area.area_id = '{{ $area['area_id'] ?? 0 }}';
	area.area_city = '{{ $area['area_city'] ?? 0 }}';
	area.merchant_id = '{{ $area['merchant_id'] ?? 0 }}';

	Ajax.call('ajax_dialog.php?act=goods_delivery_area', 'area=' + $.toJSON(area), goods_delivery_areaResponse, 'POST', 'JSON');
}

function goods_delivery_areaResponse(result){
	$("#area_address").html(result.content);
	$(".store-warehouse-info").html(result.warehouse_content);

	if(result.is_theme == 1){
		get_user_area_shipping(result.goods_id, result.goods_attr_id, result.area.region_id, result.area.area_id, result.area.area_city, result.area.province_id, result.area.city_id, result.area.district_id, result.area.street_id, result.area.street_list);
	}
}
/* 获取配送区域 end*/

/* 查询用户所在地区是否支持配送 */
function get_user_area_shipping(goods_id, goods_attr_id, region_id, area_id, area_city, province_id, city_id, district_id, street_id, street_list){

	var area = new Object();

	area.goods_id = goods_id;
    area.goods_attr_id = goods_attr_id;
	area.region_id = region_id;
	area.area_id = area_id;
	area.area_city = area_city;
	area.province_id = province_id;
	area.city_id = city_id;
	area.district_id = district_id;
	area.street_id = street_id;
	area.street_list = street_list;

	Ajax.call('ajax_dialog.php?act=user_area_shipping', 'area=' + $.toJSON(area), user_area_shippingResponse, 'POST', 'JSON');
}

function user_area_shippingResponse(result){
	$("#user_area_shipping").html(result.content);

	changePrice(1);
}

/* 配送地区 常用地址选择 start*/
function get_region_change(goods_id, province_id, city_id, district_id, street_id){
	Ajax.call("ajax_dialog.php", 'id=' + goods_id + '&act=in_stock' + '&province=' + province_id + "&city=" + city_id + "&district=" + district_id+ "&street=" + street_id, ajax_is_inStock, "GET", "JSON");
}

function ajax_is_inStock(res){
	var t = '&t=' + parseInt(Math.random()*1000);
	var str_new = window.location.href.replace(/\&t\=\d+/g,t);
	location.href = str_new;
}
/* 配送地区 常用地址选择 end*/
</script>
