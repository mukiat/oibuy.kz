/*
* * 上海商创网络科技有限公司
* * team:made by https://www.dscmall.cn
* * Author:made by zhuofuxi
* * Date:2018-04-06 09:30:00
*/

function getBankName(bankCard){
	$.getJSON("./bankData.json", {}, function (data) {
		var bankBin = 0;
		var isFind = false;
		for (var key = 10; key >= 2; key--) {
			bankCard = bankCard.toString();
			bankBin = bankCard.substring(0, key);
			$.each(data, function (i, item) {
				if (item.bin == bankBin) {
					isFind = true;
					return item.bankName;
				}
			});
			if (isFind) {
				break;
			}
		}
		if (!isFind) {
			return "未知发卡银行";
		}
	});
}

