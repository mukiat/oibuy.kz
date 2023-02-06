<template>
  <div class="user-booking">
  	<van-cell-group>
	    <van-cell title="Haier EG10014B39GU1 10КГ Смарт кіржуғыш машинасы"></van-cell>
	    <van-field type="text" label="Брон саны" value="1"></van-field>
	    <van-cell>
	    	<template solt="title">
	    		<label class="t-remark">Брон сипаты(50 әріп)</label>
	    		<div class="text-area1 m-top04">
						<textarea maxlength="50" name="desc" placeholder="еркін"></textarea>
						<span>50</span>
					</div>
	    	</template>
	    </van-cell>
	    <van-field type="text" label="Аты-жөні" value="Паленше"></van-field>
	    <van-field type="text" label="E-mail" value="123456789@mysal.com"></van-field>
	    <van-field type="text" label="Телефон" value="7751234567"></van-field>
	  </van-cell-group>
	  <div class="filter-btn">
	    <van-button type="primary" bottom-action>Брон жолдау</van-button>
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
