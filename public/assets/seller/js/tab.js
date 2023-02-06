/*
* * 上海商创网络科技有限公司
* * team:made by https://www.dscmall.cn
* * Author:made by zhuofuxi
* * Date:2018-04-06 09:30:00
*/

$(".tabs").find("li").click(function(){
	var attr = $(this).data('tab');
	var info = $(this).parent(".tabs").nextAll(".info_warp");
	var list = info.find(".table_item")
	var buttonInfo = info.find("#button-info");
	$(this).addClass("current").siblings().removeClass("current");
	for(var i=0;i<list.length;i++){
		list.eq(i).hide();
		var cate = list.eq(i).data('table');

		if(attr == cate){
			list.eq(i).show();
			buttonInfo.removeClass().addClass("button-info-item"+i);
		}
	}
});
