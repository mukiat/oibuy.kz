import Vue from 'vue'
import VueI18n from './i18n/vue-i18n'

Vue.use(VueI18n)

let configData = uni.getStorageSync('configData');

let lang = (configData && configData.lang) ? configData.lang : 'zh-CN';

const DEFAULT_LANG = lang

const locales = {
	'zh-CN': require('./i18n/zh-CN.js'),
	'en': require('./i18n/en.js'),
	'zh-TW': require('./i18n/zh-TW.js')
}

const i18n = new VueI18n({
	locale: DEFAULT_LANG,
	messages: locales
})

export default i18n