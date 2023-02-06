<template>
	<div>
		<slot v-if="!nodes.length" />
		<div id="_top" :style="showAm+(selectable?';user-select:text;-webkit-user-select:text':'')">
			<div :id="'rtf'+uid"></div>
		</div>
	</div>
</template>

<script>
	var windowWidth = document.body.clientWidth;
	
	var	cfg = require('./libs/config.js');

	import Vue from 'vue';
	import { ImagePreview } from 'vant';
    Vue.use(ImagePreview);

	/**
	 * Parser 富文本组件
	 * @tutorial https://github.com/jin-yufeng/Parser
	 * @property {String} html 富文本数据
	 * @property {Boolean} autopause 是否在播放一个视频时自动暂停其他视频
	 * @property {Boolean} autoscroll 是否自动给所有表格添加一个滚动层
	 * @property {Boolean} autosetTitle 是否自动将 title 标签中的内容设置到页面标题
	 * @property {Number} compress 压缩等级
	 * @property {String} domain 图片、视频等链接的主域名
	 * @property {Boolean} lazyLoad 是否开启图片懒加载
	 * @property {String} loadingImg 图片加载完成前的占位图
	 * @property {Boolean} selectable 是否开启长按复制
	 * @property {Object} tagStyle 标签的默认样式
	 * @property {Boolean} showWithAnimation 是否使用渐显动画
	 * @property {Boolean} useAnchor 是否使用锚点
	 * @property {Boolean} useCache 是否缓存解析结果
	 * @event {Function} parse 解析完成事件
	 * @event {Function} load dom 加载完成事件
	 * @event {Function} ready 所有图片加载完毕事件
	 * @event {Function} error 错误事件
	 * @event {Function} imgtap 图片点击事件
	 * @event {Function} linkpress 链接点击事件
	 * @author JinYufeng
	 * @version 20201014
	 * @listens MIT
	 */
	export default {
		name: 'parser',
		data() {
			return {
				uid: this._uid,
				showAm: '',
				nodes: []
			}
		},
		props: {
			html: String,
			autopause: {
				type: Boolean,
				default: true
			},
			autoscroll: Boolean,
			autosetTitle: {
				type: Boolean,
				default: true
			},
			domain: String,
			lazyLoad: Boolean,
			selectable: Boolean,
			tagStyle: Object,
			showWithAnimation: Boolean,
			useAnchor: Boolean
		},
		watch: {
			html(html) {
				this.setContent(html);
			}
		},
		created() {
			// 图片数组
			this.imgList = [];
			this.imgList.each = function(f) {
				for (var i = 0, len = this.length; i < len; i++)
					this.setItem(i, f(this[i], i, this));
			}
			this.imgList.setItem = function(i, src) {
				if (i == void 0 || !src) return;
				
				// 去重
				if (src.indexOf('http') == 0 && this.includes(src)) {
					var newSrc = src.split('://')[0];
					for (var j = newSrc.length, c; c = src[j]; j++) {
						if (c == '/' && src[j - 1] != '/' && src[j + 1] != '/') break;
						newSrc += Math.random() > 0.5 ? c.toUpperCase() : c;
					}
					newSrc += src.substr(j);
					return this[i] = newSrc;
				}
			
				this[i] = src;
				// 暂存 data src
				if (src.includes('data:image')) {
					var filePath, info = src.match(/data:image\/(\S+?);(\S+?),(.+)/);
					if (!info) return;
				}
			}
		},
		mounted() {
		
			this.document = document.getElementById('rtf' + this._uid);
			
			if (this.html) this.setContent(this.html);
		},
		beforeDestroy() {
			
			if (this._observer) this._observer.disconnect();
		
			clearInterval(this._timer);
		},
		methods: {
			// 设置富文本内容
			setContent(html, append) {
			
				if (!html) {
					if (this.rtf && !append) this.rtf.parentNode.removeChild(this.rtf);
					return;
				}
				var div = document.createElement('div');
				if (!append) {
					if (this.rtf) this.rtf.parentNode.removeChild(this.rtf);
					this.rtf = div;
				} else {
					if (!this.rtf) this.rtf = div;
					else this.rtf.appendChild(div);
				}
				div.innerHTML = this._handleHtml(html, append);
				for (var styles = this.rtf.getElementsByTagName('style'), i = 0, style; style = styles[i++];) {
					style.innerHTML = style.innerHTML.replace(/body/g, '#rtf' + this._uid);
					style.setAttribute('scoped', 'true');
				}
				// 懒加载
				if (!this._observer && this.lazyLoad && IntersectionObserver) {
					this._observer = new IntersectionObserver(changes => {
						for (let item, i = 0; item = changes[i++];) {
							if (item.isIntersecting) {
								item.target.src = item.target.getAttribute('data-src');
								item.target.removeAttribute('data-src');
								this._observer.unobserve(item.target);
							}
						}
					}, {
						rootMargin: '500px 0px 500px 0px'
					})
				}
				var _ts = this;
				// 获取标题
				var title = this.rtf.getElementsByTagName('title');
				if (title.length && this.autosetTitle)
					uni.setNavigationBarTitle({
						title: title[0].innerText
					})
				// 图片处理
				this.imgList.length = 0;
				var imgs = this.rtf.getElementsByTagName('img');
				for (let i = 0, j = 0, img; img = imgs[i]; i++) {
					if (parseInt(img.style.width || img.getAttribute('width')) > windowWidth)
						img.style.height = 'auto';
					var src = img.getAttribute('src');
					if (this.domain && src) {
						if (src[0] == '/') {
							if (src[1] == '/')
								img.src = (this.domain.includes('://') ? this.domain.split('://')[0] : '') + ':' + src;
							else img.src = this.domain + src;
						} else if (!src.includes('://')) img.src = this.domain + '/' + src;
					}
					if (!img.hasAttribute('ignore') && img.parentElement.nodeName != 'A') {
						img.i = j++;
						_ts.imgList.push(img.getAttribute('original-src') || img.src || img.getAttribute('data-src'));
						img.onclick = function() {
							var preview = true;
							this.ignore = () => preview = false;
							_ts.$emit('imgtap', this);
							if (preview) {
								ImagePreview({
									images: _ts.imgList,
									startPosition: this.i
								});
							}
						}
					}
					img.onerror = function() {
						if (cfg.errorImg)
							_ts.imgList[this.i] = this.src = cfg.errorImg;
						_ts.$emit('error', {
							source: 'img',
							target: this
						});
					}
					if (_ts.lazyLoad && this._observer && img.src && img.i != 0) {
						img.setAttribute('data-src', img.src);
						img.removeAttribute('src');
						this._observer.observe(img);
					}
				}
				// 链接处理
				var links = this.rtf.getElementsByTagName('a');
				for (var link of links) {
					link.onclick = function() {
						var jump = true,
							href = this.getAttribute('href');
						_ts.$emit('linkpress', {
							href,
							ignore: () => jump = false
						});
						if (jump && href) {
							if (href[0] == '#') {
								if (_ts.useAnchor) {
									_ts.navigateTo({
										id: href.substr(1)
									})
								}
							} else if (href.indexOf('http') == 0 || href.indexOf('//') == 0)
								return true;
							else
								uni.navigateTo({
									url: href
								})
						}
						return false;
					}
				}
				// 视频处理
				var videos = this.rtf.getElementsByTagName('video');
				_ts.videoContexts = videos;
				for (let video, i = 0; video = videos[i++];) {
					video.style.maxWidth = '100%';
					video.onerror = function() {
						_ts.$emit('error', {
							source: 'video',
							target: this
						});
					}
					video.onplay = function() {
						if (_ts.autopause)
							for (let item, i = 0; item = _ts.videoContexts[i++];)
								if (item != this) item.pause();
					}
				}
				// 音频处理
				var audios = this.rtf.getElementsByTagName('audio');
				for (var audio of audios)
					audio.onerror = function() {
						_ts.$emit('error', {
							source: 'audio',
							target: this
						});
					}
				// 表格处理
				if (this.autoscroll) {
					var tables = this.rtf.getElementsByTagName('table');
					for (var table of tables) {
						let div = document.createElement('div');
						div.style.overflow = 'scroll';
						table.parentNode.replaceChild(div, table);
						div.appendChild(table);
					}
				}
				if (!append) this.document.appendChild(this.rtf);
				this.$nextTick(() => {
					this.nodes = [1];
					this.$emit('load');
				});
				setTimeout(() => this.showAm = '', 500);
				
				var height;
				clearInterval(this._timer);
				this._timer = setInterval(() => {
					this.rect = this.rtf.getBoundingClientRect();
				}, 350);
				if (this.showWithAnimation && !append) this.showAm = 'animation:_show .5s';
			},
			// 获取文本内容
			getText(ns = this.nodes) {
				var txt = '';
				txt = this.rtf.innerText;
				return txt;
			},
			// 锚点跳转
			in (obj) {
				if (obj.page && obj.selector && obj.scrollTop) this._in = obj;
			},
			navigateTo(obj) {
				if (!this.useAnchor) return obj.fail && obj.fail('Anchor is disabled');
				var d = ' ';
				var selector = uni.createSelectorQuery().in(this._in ? this._in.page : this).select((this._in ? this._in.selector :
					'#_top') + (obj.id ? `${d}#${obj.id},${this._in?this._in.selector:'#_top'}${d}.${obj.id}` : '')).boundingClientRect();
				if (this._in) selector.select(this._in.selector).scrollOffset().select(this._in.selector).boundingClientRect();
				else selector.selectViewport().scrollOffset();
				selector.exec(res => {
					if (!res[0]) return obj.fail && obj.fail('Label not found')
					var scrollTop = res[1].scrollTop + res[0].top - (res[2] ? res[2].top : 0) + (obj.offset || 0);
					if (this._in) this._in.page[this._in.scrollTop] = scrollTop;
					else uni.pageScrollTo({
						scrollTop,
						duration: 300
					})
					obj.success && obj.success();
				})
			},
			// 获取视频对象
			getVideoContext(id) {
				if (!id) return this.videoContexts;
				else
					for (var i = this.videoContexts.length; i--;)
						if (this.videoContexts[i].id == id) return this.videoContexts[i];
			},
			_handleHtml(html, append) {
				if (!append) {
					// 处理 tag-style 和 userAgentStyles
					var style = '<style>@keyframes _show{0%{opacity:0}100%{opacity:1}}img{max-width:100%}';
					for (var item in cfg.userAgentStyles)
						style += `${item}{${cfg.userAgentStyles[item]}}`;
					for (item in this.tagStyle)
						style += `${item}{${this.tagStyle[item]}}`;
					style += '</style>';
					html = style + html;
				}
				// 处理 rpx
				if (html.includes('rpx'))
					html = html.replace(/[0-9.]+\s*rpx/g, $ => (parseFloat($) * windowWidth / 750) + 'px');
				return html;
			},
		}
	}
</script>

<style>
	@keyframes _show {
		0% {
			opacity: 0;
		}

		100% {
			opacity: 1;
		}
	}

	/* #ifdef MP-WEIXIN */
	:host {
		display: block;
		overflow: scroll;
		-webkit-overflow-scrolling: touch;
	}

	/* #endif */
</style>
