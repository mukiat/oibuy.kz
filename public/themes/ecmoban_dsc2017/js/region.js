/*
* * 上海商创网络科技有限公司
* * team:made by https://www.dscmall.cn
* * Author:made by zhuofuxi
* * Date:2018-04-06 09:30:00
*/

jQuery.levelLink = function (type){
	var regionLinkage = "*[ectype='regionLinkage']",
		opt = "*[ectype='layer']",
		Item = "*[ectype='ragionItem']",
		txt = "*[ectype='txt']",
		input = 'input[type="hidden"]',
		dropdown = "*[ectype='smartdropdown']";

	if(type == ""){
		type = 0;
	}

	$(opt).hover(function(){
		$(this).perfectScrollbar("destroy");
		$(this).perfectScrollbar();
	});

    //select下拉默认值赋值
	if($(dropdown).parents(regionLinkage).length>0){
		$(regionLinkage).each(function(){
			var T = $(this);
			T.find(dropdown).each(function(k,v){
				//默认加载国家、省份、市区3级
				if(k > 4){
					return false;
				}else{
					var t = $(this);
					var val = 0;

					//type == 1 表示新增，type==0 表示编辑
					if(type == 1){
						//新增默认选中中国，并且默认第一个是国家
						if(k == 0 && $(v).find("input[type='hidden']").attr("name") == "country"){
							val = t.find(Item).eq(0).data("value");
						}
					}else{
						//编辑获取当前地区
						val = t.find(input).val();

						if(t.find(input).val() == 0){
							t.hide();
						}else{
							t.show();
						}
					}

					Ajax.call('region.php?act=consigne', 'type='+(k+1)+'&parent=' + val, function (data) {
						t.next().find(opt).html(data.content);

						t.next().find(opt).perfectScrollbar("destroy");
						t.next().find(opt).perfectScrollbar();

						if(data.region_name != ''){
							t.find(txt).html(data.region_name);
							t.find(input).val(val);
						}
					}, 'POST', 'JSON');

					t.find(Item).each(function(){
						if($(this).data('value') == val){
							t.find(txt).html($(this).data("text"));
						}
					});
				}
			});
		});
	}

	$(document).find(txt).on('click',$(dropdown),function(){
		var t = $(this);
		$(dropdown).removeClass("visible");
		if(t.parent(dropdown).hasClass("visible")){
			t.parents(dropdown).removeClass("visible");
			t.nextAll(opt).hide();
		}else{
			t.parents(dropdown).addClass("visible");
			t.parents(dropdown).find(opt).show();
			t.parents(dropdown).siblings().removeClass("visible");
			t.parents(dropdown).siblings().find(opt).hide();
		}
	});

	$(document).on('click',Item,function(){
		var t = $(this),
			text = t.data("text"),
			value = t.data("value"),
			type = t.data("type"),
			parents = t.parents(dropdown),
			index = parents.index(),
			old_val = parents.find(input).attr("name");

		if(old_val != value){
			var nextAll = parents.nextAll(dropdown);


			//初始化后面地区的值
			nextAll.find("input").val(0);

			//初始化后面地区名称
			nextAll.each(function(k,v){
				var name = $(this).find(input).attr("name");
				if(name == "province"){
					$(this).find(txt).html(json_languages.select_province);
				}else if(name == "city"){
					$(this).find(txt).html(json_languages.select_city);
				}else if(name == "district"){
					$(this).find(txt).html(json_languages.select_district);
					$(this).hide();
				}else if(name == "street"){
					$(this).find(txt).html(json_languages.select_street);
					$(this).hide();
				}
			});
		}

		//前台筛选门店 门店订单使用
		if(type == 4){
			if(parents.data("store") == 1){
				var done_cart_value = $("input[name='done_cart_value']").val();
				get_store_list(value,"flow",done_cart_value); //筛选出门店方法
			}else if(parents.data("store") == 2){
				get_store_list_goods(value);
			}
		}

		Ajax.call('region.php?act=consigne', 'type='+type+'&parent=' + value, function (data) {
			//判断当前地区是否有下一级地区
			if(data.regions.length>0){
				parents.next().find(opt).html(data.content);

				if(parents.next().length > 0){
					parents.next().show();
					parents.next().find(opt).show();
					parents.next().find(input).removeClass("ignore");
				}else{
					//验证赋值 在没有使用5级地区时
					if(t.parents(regionLinkage).next("input[name='validate_region']")){
						t.parents(regionLinkage).next("input[name='validate_region']").val(1);
					}
				}

				parents.next().find(opt).perfectScrollbar("destroy");
				parents.next().find(opt).perfectScrollbar();
			}else{
				if(type == 4 && parents.data("store") == 1){
					//门店
					parents.next(dropdown).show();
				}else{
					parents.next(dropdown).hide();
				}

				//验证赋值 在使用到5级地区时
				if(t.parents(regionLinkage).next("input[name='validate_region']")){
					t.parents(regionLinkage).next("input[name='validate_region']").val(1);
				}
				parents.next().find(input).addClass("ignore");
			}

		}, 'POST', 'JSON');

		parents.find(input).val(value);
		parents.find(txt).html(text);
		t.parents(opt).hide();
		parents.removeClass("visible");
	});

	$(document).click(function(e){
		if(e.target.className !='txt'){
			$(opt).hide();
			//$(dropdown).removeClass("visible");
		}
	});

	/*var i = 100;
	$(".smartdropdown").each(function(index, element) {
        $(this).css({"z-index":i--});
    });*/
}
