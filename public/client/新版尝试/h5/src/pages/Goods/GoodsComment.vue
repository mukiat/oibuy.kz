<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<header class="header-nav-content">
			<van-nav-bar :title="$t('lang.goods_comments')" :left-arrow="leftArrow" @click-left="onClickLeft"></van-nav-bar>
		</header>

		<section class="comment-content">
			<section class="goods_module_wrap m-top10">
				<div class="title_box">
					<div class="title_text">
						<span>{{commentTotal.total > 0 ? $t('lang.comment') : $t('lang.no_comment')}}</span>
					</div>
					<span class="drgree_of_praise" v-if="commentTotal.total > 0">{{$t('lang.high_praise')}}{{commentTotal.good}}</span>
				</div>
				<div class="nav_wrap">
					<ul class="nav_list">
						<li :class="[currNav == index ? 'curr_nav' : '', item.tag_name ? 'nav_li' : '']" v-for="(item, index) in commentTabs" :key="index" @click="toggleType(index)">{{item.title}} {{item.count}}</li>
					</ul>
				</div>
			</section>
			
			<section class="goods_module_wrap comment_main m-top10">
				<template v-for="(list, listIndex) in goodsCommentList">
					<div class="comment-items" v-show="listIndex == currNav" :key="listIndex">
						<div class="comitem" v-for="(item, index) in list" :key="index">
							<div class="item_header">
								<img :src="item.user_picture" class="head_l" v-if="item.user_picture">
								<img src="../../assets/img/get_avatar.png" class="head_l" v-else>
								<div class="head_r">
									<div class="com_name">{{item.user_name}}</div>
									<div class="com_time">
										<div class="rate_wrap"><i :class="['iconfont', 'icon-wujiaoxing', 'size_12', rate <= item.rank ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></i></div>
										<span class="comment_time">{{ item.add_time }}</span>
									</div>
								</div>
							</div>
							<div class="item_body">
								<div class="comment_con">{{ item.content }}</div>
								<div class="imgs_scroll" v-if="item.comment_img">
									<div class="com_img" :style="{height: `${windowWidth}px`}" v-for="(val, ind) in item.comment_img" :key="ind" >
										<img :src="val" @click="previewImgs(ind, item.comment_img)" v-if="val" />
										<img src="../../assets/img/no_image.jpg" v-else />
									</div>
								</div>
							</div>
							<div class="item_footer" v-if="item.goods_attr">{{item.goods_attr}}</div>
							<div class="item_body add_comment" v-if="item.add_comment.comment_id">
								<div class="title">Клент {{item.add_comment.add_time_humans}} жауабы</div>
								<div class="comment_con">{{ item.add_comment.content }}</div>
								<div class="imgs_scroll" v-if="item.add_comment.get_comment_img && item.add_comment.get_comment_img.length > 0">
									<div class="com_img" :style="{height: `${windowWidth}px`}" v-for="(img, imgIndex) in item.add_comment.get_comment_img" :key="imgIndex" >
										<img :src="img" @click="previewImgs(imgIndex, item.add_comment.get_comment_img)" v-if="img" />
										<img src="../../assets/img/no_image.jpg" v-else />
									</div>
								</div>
							</div>
							<div class="reply_content" v-if="item.re_content"><div class="re_label">{{$t('lang.admin_reply')}}：</div><div class="re_content">{{item.re_content}}</div></div>
						</div>
						<!-- <div class="footer-cont" v-if="list.length >= size">{{paginated[listIndex] == 1 ? $t('lang.loading') : $t('lang.no_more')}}</div> -->
					</div>
				</template>
				<NotCont v-if="shopEmpty"></NotCont>
			</section>
		</section>

		<!--初始化loading-->
		<DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>

<script>
	import Vue from 'vue';
	import qs from 'qs'
	import {
		mapState
	} from 'vuex'

	import {
		NavBar,
		Tab,
		Tabs,
		Waterfall,
		Loading,
		Toast,
		ImagePreview
	} from 'vant'

	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'
	import DscLoading from '@/components/DscLoading'
	Vue.use(ImagePreview).use(Toast);
	let vantToast = null;
	export default {
		data() {
			return {
				commentTabs: [
					{
						title: this.$t('lang.all'),
						type: 'all',
						count: 0
					},
					{
						title: this.$t('lang.issue_img'),
						type: 'img',
						count: 0
					},
					{
						title: this.$t('lang.good_comment'),
						type: 'good',
						count: 0
					},
					{
						title: this.$t('lang.medium_comment'),
						type: 'in',
						count: 0
					},
					{
						title: this.$t('lang.negative_comment'),
						type: 'rotten',
						count: 0
					}
				],
				number: Object,
				goods_id: this.$route.params.id,
				leftArrow: true,
				size: 10,
				footerCont: false,
				dscLoading: true,
				shopEmpty: false,
				commentTotal: {},
				goodsCommentList: [],
				paginated: [],
				currNav: 0,
				flag: false,
				windowWidth: 'auto'
			}
		},
		components: {
			[NavBar.name]: NavBar,
			[Tab.name]: Tab,
			[Tabs.name]: Tabs,
			[Loading.name]: Loading,
			NotCont,
			DscLoading
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		created() {
			let that = this
			this.windowWidth = (document.body.clientWidth - 30) / 3;
			setTimeout(() => {
				uni.getEnv(function(res) {
					if (res.plus || res.miniprogram) {
						that.leftArrow = false
					}
				})
			}, 100)
			//评论接口
			this.onNumber();
		},
		methods: {
			onClickLeft() {
				this.$router.go(-1);
			},
			toggleType(i) {
				this.shopEmpty = false;
				if (this.currNav == i) return;
				this.currNav = i;
				
				if (this.goodsCommentList[i].length > 0) return;
				Toast.loading({
				  duration: 0,       // 持续展示 toast
				  forbidClick: true, // 禁用背景点击
				  loadingType: 'spinner',
				  message: this.$t('lang.loading') + '...'
				});
				this.onGoodsComment()
			},
			// 图片预览
			previewImgs(i = 0, imgs = []) {
				if (imgs.length == 0) return;
				ImagePreview({
				  images: imgs,
				  startPosition: i
				});
			},
			async onGoodsComment() {
				let i = this.currNav;
				
				if (this.goodsCommentList.length == 0) this.goodsCommentList = this.commentTabs.map(() => []);
				if (this.paginated.length == 0) this.paginated = this.commentTabs.map(() => 1);
				
				let page = this.goodsCommentList[i].length / this.size;
				
				page = Math.ceil(page) + 1;
				
				this.flag = false;

				//商品评论接口
				const { data } = await this.$store.dispatch('getGoodsCommentById', {
					goods_id: this.$route.params.id,
					rank: this.commentTabs[i].type,
					page: page,
					size: this.size,
					goods_tag: this.commentTabs[i].tag_name || ''
				});
				
				this.flag = true;

				if (!this.dscLoading) Toast.clear();
				
				this.dscLoading = false;
				const list = arrRemove.trimSpace(data);
				if (Array.isArray(list)) {
					this.$set(this.goodsCommentList, i, [...this.goodsCommentList[i], ...list]);
				};

				if (this.goodsCommentList[i].length < this.size) this.$set(this.paginated, i, 0);
				this.shopEmpty = this.goodsCommentList[i].length == 0
			},
			onNumber() {
				this.$http.post(`${window.ROOT_URL}api/comment/title`, qs.stringify({
					goods_id: this.$route.params.id
				})).then(({
					data: {
						data
					}
				}) => {
					this.commentTotal = {
						total: data.all || 0,
						good: parseInt(data.good / data.all * 100) + '%'
					};
					this.number = data;
					this.commentTabs = this.commentTabs.map(item => {
						item.count = data[item.type];
						return item;
					})
					if (data.comment) {
						const arr = data.comment.map(item => {
							item.type = 'all';
							item.title = item.tag_name;
							return item;
						})
						this.commentTabs = [...arr, ...this.commentTabs];
						this.currNav = arr.length;
					}
					this.onGoodsComment();
				})
			},
			loadMore() {
				const listData = this.goodsCommentList[this.currNav];
				if (listData && listData.length > 0) {
					if((listData.length/this.size) > 0 && (listData.length/this.size) %1 === 0){
						this.onGoodsComment()
					}
				}
			}
		}
	}
</script>
<style lang="scss" scoped>
	.admin-con {
		color: #999;
		padding: 5px 10px;
		font-size: 12px;
		background: #f4f4f4;
		border-radius: 5px;
		margin: 10px;
	}

	.comment-content {
		padding: 5rem 1rem 1rem;

		.goods_module_wrap {
			overflow: hidden;
			padding: 1.1rem;
			font-size: 1.4rem;
			border-radius: 1rem;
			background-color: #fff;
			.title_box {
				display: flex;
				justify-content: space-between;
				align-items: center;
				&:nth-child(n + 2) {
					border-top: 0.1rem solid #F9F9F9;
				}
				.title_text {
					position: relative;
					font-size: 1.4rem;
					font-weight: 700;
					padding-left: 1rem;
					&:before {
						position: absolute;
						top: 50%;
						left: 0;
						transform: translateY(-50%);
						content: '';
						width: 0.3rem;
						height: 1.5rem;
						background: linear-gradient(180deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
					}
					.comment_count {
						margin-left: 1.5rem;
						font-weight: normal;
					}
				}
				.drgree_of_praise {
					color: #999;
				}
			}
			.nav_list {
				display: flex;
				flex-wrap: wrap;
				li {
					padding: 0 1rem;
					margin: 1.5rem 0.9rem 0 0;
					height: 2.8rem;
					line-height: 2.6rem;
					text-align: center;
					border-radius: 1.4rem;
					border: 0.1rem solid #FDF0EF;
					color: #000;
					background-color: #FDF0EF;
					width: calc(33.3% - .6rem);
				}

				li:nth-child(3n){
					margin-right: 0;
				}

				.nav_li {
					border-color: #e6e6e6;
					background-color: #fff;
				}
				.curr_nav {
					color: #F91F28;
					border-color: #FDF0EF;
					background-color: #FDF0EF;
				}
			}
		}
		.comment_main {
			padding: 0;
			background-color: transparent;
			.comment-items {
				.comitem {
					padding: 1.4rem 0;
					border-radius: 1rem;
					background-color: #fff;
					&:nth-child(n + 2) {
						// border-top: 0.1rem solid #F9F9F9;
						margin-top: 1rem;
					}
				}
				.item_header {
					display: flex;
					align-items: center;
					padding: 0 1.1rem;
					.head_l {
						flex: none;
						width: 3.4rem;
						height: 3.4rem;
						border-radius: 50%;
						background-color: #ccc;
					}
					.head_r {
						flex: 1;
						display: flex;
						flex-direction: column;
						justify-content: space-between;
						height: 3.4rem;
						margin-left: 1rem;
					}
					.com_time {
						display: flex;
						justify-content: space-between;
						.comment_time {
							color: #999;
						}
					}
					.rate_wrap {
						.icon-wujiaoxing {
							margin-right: 0.5rem;
							color: #DDD;
						}
						.color_red {
							color: #E93B3D;
						}
					}
				}
				.item_body {
					.comment_con {
						padding: 1.3rem 1.1rem 0.5rem;
					}
					.imgs_scroll {
						display: flex;
						flex-wrap: wrap;
						padding: 0 0.5rem;
						.com_img {
							overflow: hidden;
							width: 33.33%;
							padding: 0.5rem;
							img {
								width: 100%;
								height: 100%;
								object-fit: cover;
								border-radius: 0.5rem;
							}
						}
					}
				}
				.item_footer {
					margin: 0.5rem 1.1rem 0;
					color: #999;
				}
				.reply_content {
					display: flex;
					align-items: baseline;
					padding: 1.1rem;
					margin: 1.1rem 1.1rem 0;
					border-radius: 0.5rem;
					background-color: #F2F2F2;
					.re_label {
						flex: none;
					}
					.re_content {
						flex: auto;
					}
				}
			}
		}
	}
	.add_comment {
		.title {
			margin: 0.8rem 1.1rem 0;
			font-weight: bold;
		}
		.comment_con {
			padding-top: 0.8rem!important;
		}
	}
</style>
