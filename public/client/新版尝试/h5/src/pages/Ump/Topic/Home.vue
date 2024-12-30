<template>
    <div class='home'>
        <component v-for="(item,index) in modules" :key="index" :is="'ec-'+item.module" :data="item.data" :preview="false"
         :modules-index="index" :shop-id="bStore" v-if="item.module != 'live'">
            {{ item }}
        </component>
        <ec-tab-down></ec-tab-down>
    </div>
</template>
<script>
    //node library
    import url from 'url'
    import qs from 'qs'

    // mapActions mapState
    import {
        mapActions,
        mapState
    } from 'vuex'

    // custom components
    import EcSlide from '@/components/visualization/slide/Frontend'
    import EcTitle from '@/components/visualization/title/Frontend'
    import EcAnnouncement from '@/components/visualization/announcement/Frontend'
    import EcNav from '@/components/visualization/nav/Frontend'
    import EcLine from '@/components/visualization/line/Frontend'
    import EcBlank from '@/components/visualization/blank/Frontend'
    import EcJigsaw from '@/components/visualization/jigsaw/Frontend'
    import EcProduct from '@/components/visualization/product/Frontend'
    import EcCoupon from '@/components/visualization/coupon/Frontend'
    import EcCountDown from '@/components/visualization/count-down/Frontend'
    import EcButton from '@/components/visualization/button/Frontend'
    import EcSearch from '@/components/visualization/search/Frontend'
    import EcStore from '@/components/visualization/store/Frontend'
    import EcShopSigns from '@/components/visualization/shop-signs/Frontend'
    import EcShopMenu from '@/components/visualization/shop-menu/Frontend'
    import EcTabDown from '@/components/visualization/tab-down/Frontend'
    import EcFilterTop from '@/components/visualization/element/FilterTop'

    export default {
        name: 'home',
        components: {
            EcSlide,
            EcTitle,
            EcAnnouncement,
            EcNav,
            EcLine,
            EcBlank,
            EcJigsaw,
            EcProduct,
            EcCoupon,
            EcCountDown,
            EcButton,
            EcSearch,
            EcStore,
            EcShopSigns,
            EcShopMenu,
            EcTabDown,
            EcFilterTop,
        },
        data() {
            return {
                fromId: '',
                topic_id:this.$route.params.id,
                scrollTop: 0,
                docTitle: ''
            }
        },
        created() {
            this.init(this.topic_id)
        },
        activated () {
            if (this.docTitle) {
              document.title = this.docTitle
            }

            if (this.scrollTop > 0) {
              window.scrollTo({
                top: this.scrollTop,
                behavior: "instant"
              })
            }
            var passiveSupported = false;

            try {
                var options = Object.defineProperty({}, "passive", {
                    get: function() {
                     passiveSupported = true;
                    }
                });

                window.addEventListener('scroll', this.handleScroll, passiveSupported ? { passive: true } : false);
            } catch(err) {
                window.addEventListener('scroll', this.handleScroll);
            }
        },
        deactivated () {
            window.removeEventListener('scroll', this.handleScroll);
        },
        beforeRouteLeave (to, from, next) {
            this.docTitle = document.title;
            next()
        },
        methods: {
            handleScroll(e) {
                this.scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
            },
            init(id) {
                this.modules = []

                this.$store.dispatch('setModuleInfo', {
                    id: id,
                    type: 'topic',
                    device: window.shopInfo.device  // device 设备  h5 app wxapp
                })
            },
        },
        computed: {
            ...mapState({
                modulesTitle: state => state.pageSetting.title,
            }),
            bStore() {
                return this.$route.params.id ? this.$route.params.id : 0
            },
            modules: {
                get() {
                    return this.$store.state.modulesTopic
                },
                set(val) {
                    this.$store.state.modulesTopic = val
                }
            }
        },
        watch: {
            '$route'(to, from) {
                if(to.name == 'topicHome' && to.params.id != this.topic_id){
                    this.init(to.params.id)
                    this.topic_id = to.params.id
                }
            },
            modulesTitle() {
                document.title = this.modulesTitle ? this.modulesTitle : this.$t('lang.topicHome');
                let configData = JSON.parse(sessionStorage.getItem('configData'));
                
                this.$wxShare.share({
                    title: document.title,
                    desc: configData.shop_desc,
                    link: window.location.href,
                    imgUrl: configData.wap_logo ? configData.wap_logo : ''
                })
            }
        }
    }
</script>