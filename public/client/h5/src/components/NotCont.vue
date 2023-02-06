<template>
    <div class="ectouch-notcont">
        <div class="img">
            <img class="img" src="../assets/img/no_content.png" />
        </div>
        <template v-if="isSpan">
            <span class="cont">{{$t('lang.not_cont_prompt')}}</span>
        </template>
        <template v-else>
            <slot name="spanCon"></slot>
        </template>
    </div>
</template>

<script>
    export default {
        props:{
            isSpan:{
                type:Boolean,
                default:true
            }
        },
        name: "NotCont",
        data() {
            return {
              
            }
        }
    }
</script>