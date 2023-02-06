<template>
	<div class="container">
		<div class="store_info" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
			<template v-if="list.length>0">
				<ul style="overflow: hidden;">
					<li class="list-item " v-for="(item,index) in list " data-type="0" :key="index">
						<div class="list-box" @touchstart.capture="touchStart" @touchend.capture="touchEnd" @click="onChats(item.goods_id,item.shop_id,item.uuid)">
							<div class="list-img-on">
								<i v-if="item.unread"></i>
								<img class="list-img" :src="item.shop_logo" alt="">
							</div>
							<div class="list-content">
								<p class="title">{{item.shop_name}}</p>
								<p class="tips" v-html="item.last_message"></p>
								<p class="time">{{item.last_time}}</p>
							</div>
						</div>
						<div class="delete" @click="deleteItem(item.id)" :data-index="index">删除</div>
					</li>
				</ul>
			</template>
			<template v-else-if="!loading">
				<NotCont></NotCont>
			</template>
			<CommonNav></CommonNav>
		</div>
	</div>
</template>

<script>
	import qs from 'qs'
	import {
		List,
		Cell,
		Toast,
		Waterfall,
	} from 'vant'
	//mixins
	import formProcessing from '@/mixins/form-processing.js'
	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		mixins: [formProcessing],
		components: {
			[List.name]: List,
			[Cell.name]: Cell,
			NotCont,
			CommonNav
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				startX: 0,
				endX: 0,
				page: 1,
				size: 10,
				loading: false,
				list: []
			}
		},
		created() {
			this.default()
		},
		methods: {

			onChats(goods_is, shops_id, uuid) {
				this.$http.post(`${window.ROOT_URL}api/chat/session/mark`, {
					uuid: uuid
				}).then(res => {
					if (res.data.status == 'success') {
						this.onChat(goods_is, shops_id)

					}
				})

			},
			default (page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				let o = {
					page: 1,
					size: 10
				}
				this.$http.get(`${window.ROOT_URL}api/chat/sessions`, {
					params: o
				}).then(res => {
					if (res.data.status == 'success') {
						if (this.page == 1) {
							this.list = res.data.data
						} else {
							this.list = this.list.concat(res.data.data); //将数据拼接在一起
						}

					}
				})
			},
			// 跳转
			skip() {
				// this.$router.push({
				// 	name: 'order',
				// 	query: {
				// 		tab: val
				// 	}
			},
			// 滑动开始
			touchStart(e) {
				console.log(e, 565656556)
				// 记录初始位置
				this.startX = e.touches[0].clientX
			},
			// 滑动结束
			touchEnd(e) {
				console.log(e, "ssssss")
				// 当前滑动的父级元素
				let parentElement = e.currentTarget.parentElement
				// 记录结束位置
				this.endX = e.changedTouches[0].clientX
				// 左滑
				if (parentElement.dataset.type == 0 && this.startX - this.endX > 30) {
					this.restSlide()
					parentElement.dataset.type = 1
				}
				// 右滑
				if (parentElement.dataset.type == 1 && this.startX - this.endX < -30) {
					this.restSlide()
					parentElement.dataset.type = 0
				}
				this.startX = 0
				this.endX = 0
			},
			// 判断当前是否有滑块处于滑动状态
			checkSlide() {
				let listItems = document.querySelectorAll('.list-item')
				for (let i = 0; i < listItems.length; i++) {
					if (listItems[i].dataset.type == 1) {
						return true
					}
				}
				return false
			},
			// 复位滑动状态
			restSlide() {
				let listItems = document.querySelectorAll('.list-item')
				// 复位
				for (let i = 0; i < listItems.length; i++) {
					listItems[i].dataset.type = 0
				}
			},
			// 删除
			deleteItem(id) {
				this.$http.post(`${window.ROOT_URL}api/chat/session/destroy`, {
					id: id
				}).then(res => {
					if (res.data.status == 'success') {
						Toast("Сәтті жойылды")
						this.default()

					}
				})
			},
			loadMore() {
				setTimeout(() => {
					this.disabled = true
					if (this.page * this.size == this.list.length) {
						this.page++
						this.default()
					}
				}, 200);
			},
		},
		watch: {
			list() {
				if (this.page * this.size == this.list.length) {
					this.disabled = false
					this.loading = true
				} else {
					this.loading = false
				}
				this.list = arrRemove.trimSpace(this.list)
			}
		}
	}
</script>
<style scoped lang="scss">
	.list-item {
		position: relative;
		height: 8rem;
		-webkit-transition: all 0.2s;
		transition: all 0.2s;
	}

	.list-item[data-type="0"] {
		transform: translate3d(0, 0, 0);
	}

	.list-item[data-type="1"] {
		transform: translate3d(-10rem, 0, 0);
	}

	// .list-item:after{
	//   content: " ";
	//   position: absolute;
	//   left: 0.2rem;
	//   bottom: 0;
	//   right: 0;
	//   height: 1px;
	//   border-bottom: 1px solid #ccc;
	//   color: #ccc;
	//   -webkit-transform-origin: 0 100%;
	//   transform-origin: 0 100%;
	//   -webkit-transform: scaleY(0.5);
	//   transform: scaleY(0.5);
	//   z-index: 2;
	// }
	.list-box {
		padding: 1rem;
		background: #fff;
		display: flex;
		align-items: center;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
		justify-content: flex-end;
		border-radius: 5px;
		position: absolute;
		top: 1rem;
		right: 1rem;
		bottom: 0;
		left: 1rem;
		font-size: 0;
	}

	.list-item .list-img-on {
		position: relative;
		width: 4rem;
		height: 4rem;
		margin-right: 0.3rem;
	}

	.list-item .list-img-on i {
		position: absolute;
		width: 0.5rem;
		height: 0.5rem;
		background-color: red;
		border-radius: 50%;
		top: -0.3rem;
		right: -0.4rem;
		z-index: 999999;
	}

	.list-item .list-img-on .list-img {
		display: block;
		width: 4rem;
		height: 4rem;
		border-radius: 0.8rem;
	}

	.list-item .list-content {
		padding: 0.1rem 0 0.1rem 0.2rem;
		position: relative;
		flex: 1;
		flex-direction: column;
		align-items: flex-start;
		justify-content: center;
		overflow: hidden;
	}

	.list-item .title {
		display: block;
		color: #333;
		overflow: hidden;
		font-size: 15px;
		font-weight: bold;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.list-item .tips {
		display: block;
		overflow: hidden;
		font-size: 14px;
		color: #999;
		line-height: 20px;
		text-overflow: ellipsis;
		white-space: nowrap;
		padding-right: 9.1rem;
	}

	.list-item .time {
		display: block;
		font-size: 12px;
		position: absolute;
		right: 0;
		top: 0.1rem;
		color: #999;
	}

	.list-item .delete {
		width: 10rem;
		height: 7rem;
		background: #ff4949;
		font-size: 17px;
		color: #fff;
		text-align: center;
		line-height: 7rem;
		position: absolute;
		top: 1rem;
		right: -10rem;
	}
</style>
