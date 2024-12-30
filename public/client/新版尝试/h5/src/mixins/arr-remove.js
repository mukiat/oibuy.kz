/**
 * Array数组原型添加删除方法
 */
function remove(val,number) {
    let arrIndex = this.indexOf(val),
        num =  Number(number)
    if(num > this.length) num = this.length
    if(arrIndex != -1 && num > 0){
        this.splice(arrIndex, num)
    }
}

function trimSpace(array){
	//去除数组中的空值
	for(var i = 0; i<array.length; i++)
	{
		if(array[i] == "" || typeof(array[i]) == "undefined")
		{
			array.splice(i,1)
			i= i - 1
		}
	}
	return array
}


export default {
    remove,
    trimSpace
}