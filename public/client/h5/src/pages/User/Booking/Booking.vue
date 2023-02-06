<template>
  <div class="user-booking">
  	<van-cell-group>
	    <van-cell title="Haier/海尔 EG10014B39GU1 10公斤kg智能变频滚筒全自动洗衣机"></van-cell>
	    <van-field type="text" label="订购数量" value="1"></van-field>
	    <van-cell>
	    	<template solt="title">
	    		<label class="t-remark">订购描述（50字）</label>
	    		<div class="text-area1 m-top04">
						<textarea maxlength="50" name="desc" placeholder="选填"></textarea>
						<span>50</span>
					</div>
	    	</template>
	    </van-cell>
	    <van-field type="text" label="联系人" value="模板堂"></van-field>
	    <van-field type="text" label="邮箱地址" value="123456789@qq.com"></van-field>
	    <van-field type="text" label="联系电话" value="13855507894"></van-field>
	  </van-cell-group>
	  <div class="filter-btn">
	    <van-button type="primary" bottom-action>提交缺货登记</van-button>
	  </div>
  </div>
</template>
<script>
import {
  Cell,
  CellGroup,
  Button,
  Popup,
  Field,
} from 'vant'

export default{
	data(){
		return{}
	},
	components: {
	  [Cell.name]: Cell, 
	  [CellGroup.name]: CellGroup,
	  [Button.name]: Button,
	  [Popup.name]: Popup,
	  [Field.name]: Field,
  },
}
</script>
