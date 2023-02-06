/*
* * 上海商创网络科技有限公司
* * team:made by https://www.dscmall.cn
* * Author:made by zhuofuxi
* * Date:2018-04-06 09:30:00
*/

//数量选择
function quantity(){
	var quantity = Number($("#quantity").val());
	var perNumber = Number($("#perNumber").val());
	var perMinNumber = Number($("#perMinNumber").val());

	$(".btn-reduce").click(function(){
		if(quantity>perMinNumber){
			quantity-=1;
			$("#quantity").val(quantity);
		}else{
			$("#quantity").val(perMinNumber);
		}
	});

	$(".btn-add").click(function(){
		if(quantity<perNumber){
			quantity+=1;
			$("#quantity").val(quantity);
		}else{
			$("#quantity").val(perNumber);
		}
	})
}
quantity();
