<template>
    <div class="tabs_item" :id="`dsc-tab-${dscKey}`">
        <div class="tab_scroll_box">
            <div :class="[f_div, 'first_div']"></div>
            <template v-if="init">
                <slot></slot>
            </template>
            <div v-if="loadingText != ''" :class="[l_div, 'last_div']">
                {{ loadingText }}
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        dscKey: {
            required: true
        },
        dscModel: {
            required: true
        },
        loadingText: {
            type: String,
            default: '加载中...'
        }
    },
    data() {
        return {
            tabObserver: null,
            init: false
        }
    },
    computed: {
        f_div: function () {
            return `dsc-first-div-${this.dscKey}`;
        },
        l_div: function () {
            return `dsc-last-div-${this.dscKey}`;
        }
    },
    watch: {
        dscModel: 'startObserver'
    },
    destroyed() {
        if (this.dscModel == this.dscKey && this.tabObserver != null) this.tabObserver.disconnect();
    },
    mounted() {
        // console.log(this.dscModel)
        this.startObserver(this.dscModel);
    },
    methods: {
        observeCallback(entries) {
            entries.forEach((entry) => {
                if (entry.target.className.indexOf(this.f_div) != -1) {
                    // 上拉刷新 
                } else if (entry.target.className.indexOf(this.l_div) != -1) {
                    if (entry.intersectionRatio <= 0) return;
                    this.$emit('load');
                }
            });
        },
        startObserver(val) {
            if (this.tabObserver == null && val == this.dscKey) {
                if (!this.init) this.init = true;
                let id = `#dsc-tab-${this.dscKey}`;
        
                var opts = { 
                    root: document.querySelector(id),
                    rootMargin: "-50px 0px 0px 0px"
                };
                this.tabObserver = new IntersectionObserver(this.observeCallback, opts);

                this.tabObserver.observe(document.querySelector(`.${this.f_div}`));
                this.tabObserver.observe(document.querySelector(`.${this.l_div}`));
            }
        }
  }
}
</script>

<style scoped>
.tabs_item {
	flex: none;
	width: 100%;
    height: auto;
    overflow-y: auto;
	box-sizing: border-box;
    -webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
    
}
.tab_scroll_box {
    position: relative;
}
.first_div,
.last_div {
    width: 100%;
    height: 5rem;
    font-size: 1.4rem;
    line-height: 5rem;
    text-align: center;
    background-color: transparent!important;
}
.first_div {
    position: absolute;
    left: 0;
    top: -50px;
}
.tabs_item_hied {
	height: 0;
    overflow: visible;
}
</style>

