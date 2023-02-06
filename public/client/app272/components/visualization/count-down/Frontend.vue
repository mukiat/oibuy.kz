<template>
	<view class="page-section-seckill" :class="{'new-style':styleSel == 2}">
		<block v-if="styleSel != 2">
			<block v-if="productList && productList.length > 0">
				<view class="seckill-section">
					<view class="header uni-flex">
						<view class="right uni-flex">
							<text class="h3">限时秒杀</text>
							<view class="data">
								<uni-countdown fontColor="#FFFFFF" bgrColor="#000000" :timer="timer" v-if="timer"></uni-countdown>
							</view>
						</view>
						<view class="more-link">
							<view class="txt" @click="moreLink">更多</view>
							<uni-icons type="arrowright" size="20"></uni-icons>
						</view>
					</view>
					<view class="content">
						<scroll-view class="scroll-view scroll-view-product" scroll-x="true" scroll-left="0">
							<view class="scroll-view-item" v-for="(item,index) in productList" :key="index" @click="linkHref(item.id)">
								<image :src="item.goods_thumb" mode="widthFix"></image>
								<view class="name uni-ellipsis"><image class="country_icon" :src="item.country_icon" v-if="item.country_icon"></image>{{ item.title }}</view>
								<view class="price uni-flex">
									<currency-price :price="item.price" :delPrice="item.market_price" style="margin-top: 2px;"></currency-price>
								</view>
							</view>
						</scroll-view>
					</view>
				</view>
			</block>
		</block>
		<block v-else>
			<view class="seckill-new-style">
				<view class="seckill-header">
					<view class="header-top">
						<view class="tit-img">
							<image :src="module.allValue.titleImg" class="img" mode="widthFix" v-if="module.allValue.titleImg"></image>
						</view>
						<view class="data" v-if="bEndTime">
							<view class="data-txt">距结束还剩</view>
							<uni-countdown fontColor="#FFFFFF" splitorColor="#f20d23" borderColor="rgba(249,31,40,1)" bgrColor="linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1))" :timer="bEndTime" v-if="bEndTime"></uni-countdown>
						</view>
					</view>
					<view class="header-time-slot">
						<view class="item"
						:class="{'active':firstId == item.id}"
						v-for="(item,index) in seckillTime"
						:key="index"
						@click="seckillClick(item)"
						>
							<view class="tit">{{item.title}}</view>
							<text class="text" v-if="item.status && !item.soon && !item.is_end">抢购中</text>
							<text class="text" v-if="!item.status && item.soon && !item.is_end">{{$t('lang.begin_minute')}}</text>
							<text class="text" v-if="!item.status && !item.soon && item.is_end">{{$t('lang.has_ended')}}</text>
						</view>
					</view>
				</view>
				<view class="seckill-goods-list">
					<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
						<view class="scroll-view-item" v-for="(item,index) in seckillGoodsList" :key="index" @click="linkHref(item.id)">
							<view class="goods-top">
								<view class="img-box"><image :src="item.goods_thumb" class="img" mode="widthFix"></image></view>
								<view class="tagicon"><image :src="imagePath.seckillTag" mode="widthFix" class="img"></image></view>
							</view>
							<view class="goods-info">
								<view class="name uni-ellipsis"><image :src="item.country_icon" class="country_icon" v-if="item.country_icon"></image>{{ item.title || item.goods_name }}</view>
								<currency-price :price="item.sec_price" style="display: block; margin-top: 5px;"></currency-price>
								<currency-price :price="item.market_price" :del="true"></currency-price>
							</view>
						</view>
					</scroll-view>
				</view>
				<view class="more" @click="$outerHref('/pagesA/seckill/seckill','app')">
					<text class="tit">查看更多秒杀商品</text>
					<uni-icons type="arrowright" size="16" color="#000"></uni-icons>
				</view>
			</view>
		</block>
	</view>
</template>

<script>
import uniIcons from '@/components/uni-icons/uni-icons.vue';
import uniCountdown from "@/components/uni-countdown.vue"

export default{
	props: ['module', 'preview'],
	data(){
		return {
			timer: '',
			endTime:'',
			productList: [],
			type:0,
			seckillTime:[],
			seckillGoodsList:[],
			seckillTimeActive:[],
			firstId:0,
			status:0,
			html:"",
			tomorrow:0
		}
	},
	components:{
		uniIcons,
		uniCountdown,
	},
	created() {
		if(this.styleSel != 2){
			uni.request({
				url: this.websiteUrl + '/api/visual/seckill',
				method: 'POST',
				data: {
					num: this.nNumber
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					let data = res.data.data

					if(data.type){
						data.type == 0 ? this.endTime = data.begin_time : this.endTime = data.end_time
						if (data.goods) this.productList = data.goods
						this.type = data.type
					}else{
						this.type = 0
					}

					this.timer = this.formatDateTime(this.endTime * 1000);
				},
				fail: (err) => {
					console.error(err)
				}
			})
		}else{
			this.getSeckillData('load');
		}
    },
	computed:{
		nNumber() {
            return this.module.allValue.number
        },
		styleSel() {
		    return this.module.isStyleSel
		},
		bEndTime(){
			let time = this.seckillTimeActive.frist_end_time
			return time
		}
	},
	methods:{
		formatDateTime(inputTime) { //时间戳 转 YY-mm-dd HH:ii:ss
			var date = new Date(inputTime);
			var y = date.getFullYear();
			var m = date.getMonth() + 1;
			m = m < 10 ? ('0' + m) : m;
			var d = date.getDate();
			d = d < 10 ? ('0' + d) : d;
			var h = date.getHours();
			h = h < 10 ? ('0' + h) : h;
			var minute = date.getMinutes();
			var second = date.getSeconds();
			minute = minute < 10 ? ('0' + minute) : minute;
			second = second < 10 ? ('0' + second) : second;
			return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
		},
		moreLink(){
			uni.navigateTo({
				url:'/pagesA/seckill/seckill'
			})
		},
		linkHref(id){
			uni.navigateTo({
				url:'/pagesA/seckill/detail/detail?id='+id+'&tomorrow=' + this.tomorrow
			})
		},
		seckillClick(item) {
			this.tomorrow = item.tomorrow;

			let o = {
				id:item.id,
				tomorrow:item.tomorrow || 0
			}
			this.firstId = item.id;
			this.getSeckillData('list',o)
		},
		getSeckillData(type,o){
			uni.request({
				url: this.websiteUrl + '/api/visual/visual_seckill',
				method: 'GET',
				data: o,
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					let data = res.data.data
					if(type == 'load'){
						this.seckillTime = data.time_list
						this.seckillTimeActive = this.seckillTime[0]
						this.firstId = this.seckillTime[0].id
						this.status = this.seckillTime[0].status
					}

					this.seckillGoodsList = data.seckill_list
				},
				fail: (err) => {
					console.error(err)
				}
			})
		}
	}
}
</script>

<style>
.page-section-seckill{ margin: 0 0 15upx; padding: 0 20upx 0 30upx; background: #FFFFFF;}
.seckill-section{ overflow: hidden;}
.seckill-section .header{ justify-content: space-between; padding: 20upx 0;}
.seckill-section .header .right{ align-items: center; }
.seckill-section .header .h3{ font-size: 36upx; color: #000000; margin-right: 20upx;}
.seckill-section .header .data{  }
.uni-countdown{ display: flex; flex-wrap: nowrap;}

/*新版秒杀样式*/
.new-style{
    background: transparent;
	padding: 0;
	margin: 0;
}
.seckill-new-style{
    margin: 20upx 20upx 0;
    background-color: #fff;
    border-radius: 20upx;
}
.seckill-new-style .seckill-header{
    padding: 0;
}
.seckill-new-style .header-top{
    display: flex;
    justify-content: space-between;
    flex-direction: row;
    align-items: center;
    padding: 40upx 20upx 20upx;
}
.seckill-new-style .header-top .tit-img,.seckill-new-style .header-top .tit-img img{
    width: 200upx;
	line-height: 0;
}

.seckill-new-style .header-top .data{
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
}

.seckill-new-style .header-top .data-txt{
	font-size: 25upx;
	margin-right: 10upx;
}

.seckill-new-style .header-top /deep/ .data-time{
    font-size: 1.4rem;
}
.seckill-new-style .header-top /deep/ .data-time span{
    display: inline-block;
    padding: .5rem;
    background:linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1));
    color: #fff;
    font-size: 1.2rem;
    border-radius: .5rem;
    min-width: 1.5rem;
}
.seckill-new-style .header-top /deep/ .data-time i{
    font-size: 1.2rem;
    color: #F20D23;
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 .4rem;
}
.seckill-new-style .header-top /deep/ .data-time strong{
    font-size: 1.2rem;
    color: #333;
    font-weight: 400;
    margin-right: 1.2rem;
}
.seckill-new-style .header-time-slot{
    display: flex;
    padding: 20upx 0;
}
.seckill-new-style .header-time-slot .item{
    display: flex;
    flex: 1;
    flex-direction: column;
    color: #999999;
    justify-content: center;
    align-items: center;
    position: relative;
    padding-bottom: 20upx;
	line-height: 1.5;
}
.seckill-new-style .header-time-slot .item:after{
    content: ' ';
    position: absolute;
    height: 1px;
    background-color: #EEEEEE;
    left: 0;
    right: 0;
    bottom: 0;
}
.seckill-new-style .header-time-slot .item:first-child:after{
    left: 20upx;
}
.seckill-new-style .header-time-slot .item:last-child:after{
    right: 20upx;
}

.seckill-new-style .header-time-slot .item .tit{
	line-height: 1.5;
    font-weight: 700;
}
.seckill-new-style .header-time-slot .item .text{
    font-size: 25upx;
    font-weight: 400;
    margin-top: 3px;
}
.seckill-new-style .header-time-slot .item.active{
    color: #F20D28;
}
.seckill-new-style .header-time-slot .item.active .tit{
    font-size: 32upx;
}
.seckill-new-style .header-time-slot .item.active .text{
    font-size: 28upx;
    margin-top: 0;
}
.seckill-new-style .header-time-slot .item.active:after{
    background-color: #F20D28;
    height: 2px;
    left: 20upx;
    right: 20upx;
}

.seckill-goods-list{ padding: 0 20upx 0;}
.seckill-goods-list .scroll-view{}
.seckill-goods-list .scroll-view-item{
	width: 30%;
	padding: 10upx 6upx;
	box-sizing: border-box;
	display: inline-block;
	position: relative;
}
.seckill-goods-list .scroll-view-item .goods-top{
	width: 100%;
	position: relative;
}
.seckill-goods-list .scroll-view-item .goods-top .img-box{ line-height: 1;}
.seckill-goods-list .scroll-view-item .goods-top .tagicon{
	position: absolute;
	top: 0;
	left: 0;
	width: 120upx;
	z-index:2;
}
.seckill-goods-list .scroll-view-item .goods-info{
	padding: 20upx 10upx;
	text-align: center;
	line-height: 1.5;
}
.seckill-goods-list .scroll-view-item .goods-info .name{
	font-size: 26upx;
	color: 000;
	width: 100%;
	display: block;
}
.seckill-goods-list .scroll-view-item .goods-info .price{
	color: #F20D28;
	font-weight: 500;
	font-size: 28upx;
	line-height: 1.5;
	margin-top: 10upx;
}
.seckill-goods-list .scroll-view-item .goods-info .price .em{
	font-size: 25upx;
	margin-right: 5upx;
}
.seckill-goods-list .scroll-view-item .goods-info .price-del{
	text-decoration: line-through;
	font-size: 24upx;
	color: #888;
	line-height: 1.5;
}
.seckill-goods-list .scroll-view-item .goods-info .price-del .em{
	font-size: 20upx;
	font-style: normal;
}


.seckill-new-style .more{
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    padding-bottom: 40upx;
}
.seckill-new-style .more .tit{
	font-weight: 700;
	color: 000;
}

.country_icon{
	width: 43rpx;
	height: 30rpx;
	padding-right: 7rpx;
	position: relative;
	top: 5rpx;
}
</style>
