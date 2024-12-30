<template>
	<div class="user-detail comment_detail_content">
		<div class="goods_info">
			<img class="img" :src="commentInfo.goods_thumb" v-if="commentInfo.goods_thumb">
			<img class="img" src="../../../assets/img/no_image.jpg" v-else>
			<div class="rate_box">
				<p>Бағалау</p>
				<div class="rate" v-if="$route.params.type">
					<i :class="['iconfont', 'icon-wujiaoxing', 'size_16', commentInfo.comment_rank >= rate ? 'color_red' : '']"
						v-for="(rate, rIndex) in 5" :key="rIndex"></i>
				</div>
				<div class="rate" v-else>
					<i :class="['iconfont', 'icon-wujiaoxing', 'size_16', rank >= rate ? 'color_red' : '']"
						v-for="(rate, rIndex) in 5" :key="rIndex" @click="evaluation(rate)"></i>
				</div>
			</div>
		</div>

		<div class="comment_module">
			<div class="header">{{tips.title}}</div>
			<div class="tag_list u-border-bottom"
				v-if="$route.params.type == 0 && commentInfo.goods_product_tag && commentInfo.goods_product_tag.length">
				<div :class="['tag_item', tagList.includes(tag) ? 'active_tag' : '']"
					v-for="(tag, tagIndex) in commentInfo.goods_product_tag" :key="tagIndex" @click="selectTag(tag)">
					{{tag}}
				</div>
			</div>
			<div class="input_wrap">
				<textarea class="text_area" maxlength="500" v-model="textarea"
					:placeholder="tips.placeholder"></textarea>
				<div class="comment_length"><span>{{textareaLength}}</span> әріп жазылды</div>
			</div>
			<div class="add_img">
				<div class="img_box" v-for="(item,index) in materialList" :key="index">
					<img :src="item" />
					<i class="iconfont icon-delete" @click="deleteImg(index)"></i>
				</div>
				<van-uploader :class="['add_btn', materialList.length > 0 ? 'has_img' : '']" :after-read="onRead()"
					accept="image/jpg, image/jpeg, image/png, image/gif" multiple>
					<div class="upload_content">
						<i class="iconfont icon-jiahao"></i>
						<p class="btn_text">Фото жүктеу</p>
					</div>
				</van-uploader>
			</div>
		</div>

		<div class="satisfaction_module" v-if="commentInfo.degree_count == 0 && commentInfo.ru_id > 0 && $route.params.type == 0">
			<div class="header">
				<span>Бағалау</span>
				<span class="header_right">Ұнаса 5 жұлдыз беріңіз</span>
			</div>
			<div class="rate_list">
				<div class="rate_item" v-for="(item, index) in satisfactionList" :key="index">
					<div class="rate_label">{{item.title}}</div>
					<div class="rate_value">
						<i :class="['iconfont', 'icon-wujiaoxing', 'size_16', item.rank >= rate ? 'color_red' : '']"
							v-for="(rate, rIndex) in 5" :key="rIndex" @click="satisfaction(rate, index)"></i>
					</div>
				</div>
			</div>
		</div>

		<div class="floor_bar">
			<div class="submit_button" @click="btnSubmit">{{$t('lang.comment_submit')}}</div>
		</div>
	</div>
</template>

<script>
	import {
		mapState
	} from 'vuex'
	import qs from 'qs'

	import {
		Uploader,
		Toast,
		Dialog
	} from 'vant'
	import {
		Input
	} from 'element-ui'

	export default {
		data() {
			return {
				textarea: '',
				type: 0,
				rank: 0,
				server: 0,
				delivery: 0,
				tagList: [],
				satisfactionList: [{
						title: 'Тауар',
						rank: 0
					},
					{
						title: 'Қызмет',
						rank: 0
					},
					{
						title: 'Тасымал',
						rank: 0
					},
					{
						title: 'Жеткізуші',
						rank: 0
					}
				]
			}
		},
		components: {
			'EcInput': Input,
			[Uploader.name]: Uploader,
			[Toast.name]: Toast,
			[Dialog.name]: Dialog
		},
		created() {
			this.$store.dispatch('setAddcomment', {
				rec_id: this.$route.params.id,
				is_add_evaluate: this.$route.params.type
			})

			// this.$store.dispatch('setMaterial',{
			//   file:[]
			// })
		},
		destroyed() {
			this.$store.commit('clearMaterialImg')
		},
		computed: {
			...mapState({
				materialList: state => state.user.materialList,
				commentInfo: state => state.user.commentInfo
			}),
			textareaLength() {
				return this.textarea.length || 0
			},
			returnPictures() {
				return 9
			},
			tips() {
				return {
					title: this.$route.params.type ? 'Қолданғаннан кейінгі әсеріңізді жазыңыз' : 'Әсеріңізбен бөлісіңіз',
					placeholder: this.$route.params.type ? 'Бағалауға толықтырулар енгізіңіз,әділ және толықырақ~（500 әріп）' : 'Тауар сапасы қалай екен?бағаңызды көпшілікпен бөлісіңіз!（500 әріп）'
				}
			}
		},
		methods: {
			evaluation(val) {
				this.rank = val
			},
			selectTag(i) {
				if (this.tagList.includes(i)) this.tagList = this.tagList.filter(item => item != i)
				else this.tagList.push(i);
			},
			// 满意度打分
			satisfaction(rate, i) {
				this.satisfactionList.forEach((item, index) => {
					if (i == index) this.$set(this.satisfactionList[i], 'rank', rate)
					else this.$set(this.satisfactionList[index], 'rank', item.rank > rate ? item.rank : item.rank >	0 ? item.rank : 1)
				})
			},
			onRead() {
				return file => {
					let length = 0
					if (file.length == undefined) {
						length = this.materialList.length + 1
					} else {
						length = file.length + this.materialList.length
					}

					if (length > this.returnPictures) {
						Toast(this.$t('lang.return_nine_pic'));
					} else {
						this.$store.dispatch('setMaterial', {
							file: file
						})
					}
				}
			},
			btnSubmit() {
				if (this.$route.params.type) this.rank = this.commentInfo.comment_rank;
				if (this.rank == 0) {
					Toast(this.$t('lang.fill_in_comment_rank'))
					return false
				} else if (this.textarea == '') {
					Toast(this.$t('lang.comment_not_null'))
					return false
				} else {
					console.log(this.rank, this.textarea, this.materialList, this.tagList);
					this.$store.dispatch('setAddgoodscomment', {
						type: this.type,
						id: this.commentInfo.goods_id,
						content: this.textarea,
						rank: this.rank,
						server: this.server,
						tag: this.tagList,
						is_add_evaluate: this.$route.params.type,
						desc_rank: this.satisfactionList[0].rank,
						service_rank: this.satisfactionList[1].rank,
						delivery_rank: this.satisfactionList[2].rank,
						sender_rank: this.satisfactionList[3].rank,
						comment_id: this.commentInfo.comment_id,
						delivery: this.delivery,
						order_id: this.commentInfo.order_id,
						rec_id: this.commentInfo.rec_id,
						pic: this.materialList
					}).then(res => {
						if (res.status == 'success') {
							Toast.success({
								duration: 1000,
								forbidClick: true,
								loadingType: 'spinner',
								message: res.data.msg || this.$t('lang.comment_success')
							})

							setTimeout(() => {
								this.$router.replace({
									path: '/user/comment',
									query: {
										have: '1'
									}
								})
							}, 2000)
						} else {
							const err = res.errors.message || this.$t('lang.comment_fail');
							Toast(err);
						}
					})
				}
			},
			deleteImg(val) {
				Dialog.confirm({
					message: this.$t('lang.confirm_delete_pic'),
					className: 'text-center'
				}).then(() => {
					this.$store.dispatch('setDeleteImg', {
						index: val
					})
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.comment_detail_content {
		.goods_info {
			display: flex;
			padding: 1.2rem;
			margin: 1.2rem 0;
			border-radius: 1rem;
			background-color: #fff;

			.img {
				width: 7rem;
				height: 7rem;
			}

			.rate_box {
				margin-left: 1.2rem;
			}

			.rate {
				display: flex;
				align-items: center;
				height: 2rem;
				line-height: 2rem;
				margin-top: 1.2rem;

				.icon-wujiaoxing {
					transform: translateY(-0.2rem);
					margin-right: 0.5rem;
					color: #DDD;
				}

				.color_red {
					color: #E93B3D;
				}
			}
		}

		.comment_module {
			overflow: hidden;
			margin-bottom: 1.2rem;
			border-radius: 1rem;
			background-color: #fff;

			.header {
				font-size: 1.5rem;
				padding: 1.2rem;
				background-color: #FBFBFB;
			}

			.tag_list {
				display: flex;
				flex-wrap: wrap;
				padding: 2rem 1.2rem;
				font-size: 1.5rem;

				.tag_item {
					padding: 0 2rem;
					height: 3.2rem;
					line-height: 3.2rem;
					border-radius: 1.6rem;
					margin-right: 2rem;
					background-color: #f4f4f4;
				}

				.active_tag {
					color: #E93B3D;
					background-color: #FDF0EF;
				}
			}

			.input_wrap {
				display: flex;
				flex-direction: column;
				padding: 1.2rem;

				.text_area {
					flex: auto;
					height: 8rem;
				}

				.comment_length {
					color: #999;
					text-align: right;

					span {
						color: #E93B3D;
					}
				}
			}

			.add_img {
				display: flex;
				flex-wrap: wrap;
				padding: 0.5rem;

				.img_box {
					overflow: hidden;
					position: relative;
					width: 8rem;
					height: 8rem;
					border-radius: 0.6rem;
					margin: 0.5rem;

					img {
						width: 100%;
						height: 100%;
					}

					.iconfont {
						position: absolute;
						top: 0.5rem;
						right: 0.5rem;
						color: #E93B3D;
					}
				}

				.add_btn {
					flex: auto;
					border-radius: 0.6rem;
					margin: 0.5rem;
					box-shadow: 0 2px 8px #ddd;
				}

				.has_img {
					flex: none;
					width: 8rem;
				}

				.upload_content {
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
					height: 8rem;

					.iconfont {
						font-size: 2.4rem;
						color: #666;
					}

					.btn_text {
						margin-top: 0.8rem;
						color: #999;
					}
				}
			}
		}

		.satisfaction_module {
			overflow: hidden;
			border-radius: 1rem;
			margin-bottom: 1rem;
			background-color: #fff;

			.header {
				display: flex;
				justify-content: space-between;
				font-size: 1.5rem;
				padding: 1.2rem;

				.header_right {
					font-size: 1.4rem;
					color: #999;
				}
			}

			.rate_list {
				.rate_item {
					display: flex;
					align-items: center;
					padding: 1.2rem;
				}

				.rate_label {
					min-width: 12rem;
				}

				.rate_value {
					display: flex;
					align-items: center;

					.icon-wujiaoxing {
						transform: translateY(-0.2rem);
						margin-right: 0.5rem;
						color: #DDD;
					}

					.color_red {
						color: #E93B3D;
					}
				}
			}
		}

		.floor_bar {
			position: fixed;
			bottom: 0;
			left: 0;
			display: flex;
			justify-content: flex-end;
			align-items: center;
			width: 100%;
			height: 5rem;
			// padding: 0 30rpx;
			background-color: #fff;

			&::after {
				content: ' ';
				position: absolute;
				left: 0;
				top: 0;
				pointer-events: none;
				box-sizing: border-box;
				-webkit-transform-origin: 0 0;
				transform-origin: 0 0;
				// 多加0.1%，能解决有时候边框缺失的问题
				width: 199.8%;
				height: 199.7%;
				transform: scale(0.5, 0.5);
				border: 0 solid #e4e7ed;
				border-top-width: 1px;
				z-index: 2;
			}

			.submit_button {
				padding: 0 1.5rem;
				height: 3.2rem;
				line-height: 3.2rem;
				border-radius: 1.6rem;
				margin-right: 1.5rem;
				font-size: 1.5rem;
				color: #fff;
				background-color: #E93B3D;
			}
		}
	}
</style>
