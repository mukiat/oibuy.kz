<template>
	<view class="container">
		<view class="header-search">
			<view class="input-view">
				<uni-icons type="search" size="22" color="#666666"></uni-icons>
				<input confirm-type="search" v-model="keyword" class="input" type="text" :placeholder="$t('lang.enter_search_keywords')" />
			</view>
			<button type="warn" size="mini" @click="onSearch" class="button">{{$t('lang.search')}}</button>
		</view>
		<view class="page-body">
			<scroll-view class="nav-left" scroll-y :style="'height:'+height+'px'">
				<view class="nav-left-item" @click="bindChangeFirstCate(index,item.cat_id)" :key="index" :class="index==currentFirstIndex?'active':''"
				    v-for="(item,index) in cateListAll">
					<text>{{item.cat_name}}</text>
				</view>
			</scroll-view>
			<scroll-view class="nav-right" scroll-y :scroll-top="scrollTop" @scroll="scroll" :style="'height:'+height+'px'" scroll-with-animation>
				<view class="adv" v-if="touch_catads">
					<image :src="touch_catads" @click="$outerHref(touch_catads_url)" v-if="touch_catads_url"></image>
					<image :src="touch_catads" v-else></image>
				</view>
				<view class="item" v-for="(item,index) in cateListSecond" :key="index">
					<view class="tit">
						<label class="custom-checkbox" @click="fcatCheck(item.cat_id,item.drp_type)" v-if="drptype == 1">
							<checkbox :value="item.cat_id" :checked="item.drp_type" color="#f92028" />{{ item.cat_name }}
						</label>
						<text v-else>{{ item.cat_name }}</text>
					</view>
					<view class="nav-right-item" v-for="(third,itemIndex) in item.child" :key="itemIndex" @click="catCheck(item.cat_id,third.cat_id)">
						<view v-if="drptype == 1">
							<image :src="third.touch_icon" />
							<view class="uni-ellipsis">{{third.cat_name}}</view>
							<checkbox :value="third.cat_id" :checked="third.drp_type" color="#f92028"></checkbox>
						</view>
						<navigator :url="'/pagesA/drp/goodsList/goodsList?id='+third.cat_id" hover-class="none" v-else>
							<image :src="third.touch_icon" />
							<view class="uni-ellipsis">{{third.cat_name}}</view>
						</navigator>
					</view>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	
	export default {
		components: {
			uniIcons,
		},
		data() {
			return {
				currentFirstIndex: 0,
				touch_catads:'',
				touch_catads_url:'',
				cat_id:0,
				height: 0,
				scrollTop: 0,
				scrollHeight: 0,
				keyword:''
			}
		},
		computed:{
			...mapState({
				cateListAll: state => state.drp.cateListAll
			}),
			cateListSecond:{
				get(){
					return this.$store.state.drp.cateListSecond
				},
				set(val){
					this.$store.state.drp.cateListSecond = val
				}
			},
			drptype(){
				return this.$store.state.drp.drptype
			}
		},
		methods: {
			scroll(e) {
				this.scrollHeight = e.detail.scrollHeight;
			},
			bindChangeFirstCate(index,cat_id) {
				this.cat_id = cat_id;
				this.currentFirstIndex = index;
				this.scrollTop = -this.scrollHeight * index;
				
				this.cateListSecond = []
				this.$store.dispatch('setDrpCategoryLists',{
					index:index,
					id:cat_id
				})
			},
			handelTouchCatads(){
				this.cateListAll.forEach(v=>{
					if(v.cat_id == this.cat_id){
						this.touch_catads = v.touch_catads;
						this.touch_catads_url = v.touch_catads_url
					}
				})
			},
			catCheck(f_cat_id,cat_id){
				this.$store.dispatch('setDrpCategoryAdd',{
					id:cat_id,
					f_id:f_cat_id,
					type:0
				})
			},
			fcatCheck(cat_id,drptype){
				let arr = [];
				let type = drptype == true ? 2 : 1;
	
				this.cateListSecond.forEach(res=>{
					if(res.cat_id == cat_id){
						res.child.forEach(result=>{
							arr.push(result.cat_id)
						})
					}
				})
				
				//if(arr.length > 0){
					this.$store.dispatch('setDrpCategoryAdd',{
						id:arr,
						cur_id:cat_id,
						type:type
					})
				//}
			},
			onSearch(){
				if(this.drptype == 2){
					uni.navigateTo({
						url:'/pagesA/drp/goodsList/goodsList?keyword='+this.keyword
					})
				}else{
					uni.showToast({ title:'只有商品模式可以搜索',icon:'none'})
				}
			},
		},
		onLoad() {
			this.$store.dispatch('setDrpCategoryLists',{
				index:this.currentFirstIndex
			})
			
			let difHeight = 50
			
			//#ifdef APP-PLUS
			difHeight = 100
			//#endif
			
			if(uni.getSystemInfoSync().model == 'Redmi Note 7'){
				difHeight = 26
			}
			this.height = uni.getSystemInfoSync().windowHeight - difHeight;
		},
		watch:{
			cateListAll(){
				this.cat_id = this.cateListAll[this.currentFirstIndex].cat_id;

				this.$store.dispatch('setDrpCategoryLists',{
					id:this.cat_id
				})

				this.handelTouchCatads()
			},
			cat_id(){
				this.handelTouchCatads()
			}
		}
	}
</script>

<style>
.container{ overflow: hidden;}
.header-search{ display: flex; width: calc(100% - 40upx); padding:0 20upx; background: #FFFFFF; border-bottom: solid 1px #E0E0E0; position: fixed; top: 0; height: 50px; align-items: center; z-index: 99;}
.header-search .input-view{ background-color: #FFFFFF; border:1px solid #e6e6e6; margin: 9px 9px 9px 0; line-height: 30px;}
.header-search .button{ width: 120rpx; padding: 0; height: 30px; margin: 9px 0;}

.page-body { display: flex; padding-top: 50px; background: #FFFFFF;}
.nav {display: flex;width: 100%;}

.nav-left {width: 28%;border-right: solid 1px #E0E0E0;}
.nav-left-item {height: 100upx;display: flex;align-items: center;justify-content: center;}
.nav-left-item text{ display: block; width: 100%; text-align: center; }
.nav-left-item.active { color: #f23030; }
.nav-left-item.active text{ border-left: 5upx solid #f23030; }

.nav-right { width: 72%;}
.nav-right .adv{ width: 100%; height: 240upx; padding: 0 20upx; margin-top: 20upx; box-sizing: border-box;}
.nav-right .adv image{ width: 100%; height: 100%;}
.nav-right .item{ padding: 0 20upx; overflow: hidden; margin-bottom: -20upx;}
.nav-right .item .tit{ display: flex; justify-content: center; margin: 15upx 0 30upx; position: relative;}
.nav-right .item .tit text{ background: #FFFFFF;position: relative; z-index: 2; padding: 0 10upx;}
.nav-right .item .tit:after{content: " "; height: 1upx; width: 45%; background: #E0E0E0; position: absolute; top: 26upx; z-index: 1;}
.nav-right .item .nav-right-item { width: 33.3%; height: 220upx;float: left;text-align: center;font-size: 28upx; position: relative;}
.nav-right .item .nav-right-item image { width: 100upx;height: 100upx;}

.custom-checkbox{ z-index: 2; background: #FFFFFF; display: flex; flex-direction: row; justify-content: center; align-items: center;}

.nav-right-item checkbox{ position: absolute; top: 0; left: 0; z-index: 2;}
checkbox {
  transform:scale(0.7);
}
</style>
