<template>
  <div id="profile">
    <van-cell-group>
      <van-cell>
        <template slot="title">
          <div class="s-user-top">
            <div class="user-bg-box-1"><img src="../../../assets/img/user-1.png" class="img"></div>
            <div class="user-bg2-box-1"><img src="../../../assets/img/user-2.png" class="img"></div>
            <div class="user_profile_box">
              <div class="user-img">
                <van-uploader :after-read="onRead" accept="image/jpg, image/jpeg, image/png, image/gif" class="user-img-box" multiple>
                  <img :src="userInfo.avatar" alt="" class="img-height" v-if="userInfo.avatar">
                  <img src="../../../assets/img/user_default.png" alt="" class="img-height" v-else>
                </van-uploader>
              </div>
              <div class="profile-index-top">
                <h3 v-if="userInfo.name != ''">{{ userInfo.name }}</h3>
                <h3 v-else>{{ userInfo.username }}</h3>
                <p><span>{{$t('lang.username')}} </span>{{ userInfo.username }}</p>
              </div>
            </div>
          </div>
        </template>
      </van-cell>
      <van-cell class="title_flex_inherit" :title="$t('lang.nickname')" @click="isShow('name')" v-model="userInfo.name" is-link />
      <van-cell :title="$t('lang.sex')" @click="isShow('isSex')" :value="isSex" is-link />
      <van-cell :title="$t('lang.birthday')" @click="isShow('birthday')" v-model="userInfo.birthday" is-link />
      <van-cell :title="$t('lang.address')" :to="{ name: 'address' }" is-link />
    </van-cell-group>
    <van-cell-group class="m-top10">
      <van-cell :title="$t('lang.real_name')" :to="{ name: 'realname' }" is-link />
      <van-cell :title="$t('lang.account_security')" :to="{ name: 'accountsafe' }" is-link />
    </van-cell-group>
    <van-cell-group class="m-top10">
      <van-cell :title="$t('lang.use_help')" :to="{ name: 'help' }" is-link />
    </van-cell-group>
    <div class="ect-button-more padding-all">
      <van-button size="large" tag="a" @click="handelLogout">{{$t('lang.drop_out')}}</van-button>
    </div>
    <div class="demo-mask mask" :class="{'active': show == true}" @click="onClickMask"></div>
    <section class="demo-popup" :class="{'active': show == true}">
      <div class="my-box">
        <template v-if="type == 'name'">
        <div class="my-box-item">
          <div class="text-all dis-box" style="border-bottom: 0">
            <label>{{$t('lang.nickname')}}</label>
            <div class="input-text box-flex">
              <input class="j-input-text inputcard" type="text" name="name" :placeholder="$t('lang.nickname')" autocomplete="off" v-model="nickName" />
            </div>
          </div>
          <p style="font-size: 12px; color: #999; padding-left: 5px;">{{$t('lang.nickname_tishi')}}</p>
        </div>
        </template>
        <template v-else-if="type == 'isSex'">
        <div class="my-box-item">
          <ul class="user-sex dis-box">
            <li class="box-flex">
              <label for="sex_1">
                <input type="radio" name="sex" value="1" id="sex_1" v-model="isSexNum">
                <i class="iconfont icon-nan my-sex-size"></i>
                <h4>{{$t('lang.male')}}</h4>
              </label>
            </li>
            <li class="box-flex">
              <label for="sex_2">
                <input type="radio" name="sex" value="2" id="sex_2" v-model="isSexNum">
                <i class="iconfont icon-nv my-sex-size"></i>
                <h4>{{$t('lang.woman')}}</h4>
              </label>
            </li>
          </ul>
        </div>
        </template>
        <template v-else>
        <div class="my-box-item">
          <div class="text-all dis-box">
            <label>{{$t('lang.birthday')}}</label>
            <div class="input-text box-flex">
              <input class="j-input-text inputcard" type="date" name="birthday" placeholder="1970-01-01" autocomplete="off" v-model="birthday" />
            </div>
          </div>
        </div>
        </template>
        <div class="ect-button-more">
          <button class="btn btn-submit" :class="[nicknameDisabled > 0 ? 'btn-disabled' : 'btn-submit']" @click="updateInfo">{{$t('lang.confirm')}}</button>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import qs from 'qs'

import { mapState } from 'vuex'
import {
  Cell,
  CellGroup,
  Button,
  Popup,
  Field,
  RadioGroup,
  Radio,
  Toast,
  Dialog,
  Uploader
} from 'vant'

import isApp from '@/mixins/is-app'
import commonGet from '@/mixins/common-get'

export default{
	name:'profile',
  mixins: [isApp],
	data(){
		return{
      show:false,
      type:'name',
      avatarUploadImage:'',
      nicknameDisabled:0
    }
	},
  components: {
      [Cell.name]: Cell, 
      [CellGroup.name]: CellGroup,
      [Button.name]: Button,
      [Popup.name]: Popup,
      [Field.name]: Field,
      [RadioGroup.name]: RadioGroup,
      [Radio.name]: Radio,
      [Toast.name]: Toast,
      [Dialog.name]: Dialog,
      [Uploader.name]: Uploader,
  },
  created(){
    this.$store.dispatch('userProfile');
  },
  computed:{
    ...mapState({
      userInfo: state => state.user.userInfo
    }),
    nickName:{
      get(){
        return this.userInfo.name;
      },
      set(val){
        this.$store.dispatch('userUpdateText',{
          type:'name',
          val:val
        });
      }
    },
    isSexNum:{
      get(){
        return this.userInfo.sex;
      },
      set(val){
        this.$store.dispatch('userUpdateText',{
          type:'sex',
          val:val
        });
      }
    },
    birthday:{
      get(){
        return this.userInfo.birthday;
      },
      set(val){
        this.$store.dispatch('userUpdateText',{
          type:'birthday',
          val:val
        });
      }
    },
    isSex(){
      let sexArr = [this.$t('lang.secrecy'), this.$t('lang.male'), this.$t('lang.woman')];
      return sexArr[this.isSexNum];
    },
    isWeiXin(){
      return isApp.isWeixinBrowser()
    }
  },
  methods:{
    getStringLen(str){
      let i,len,code;
      if(str == null || str == '') return 0
      len = str.length;
      for(i = 0; i < len; i++){
        code = str.charCodeAt(i);
        if(code > 255){
          len++;
        }
      }
      return len;
    },
    isShow(val){
      this.show = true;

      if(val == 'name'){
        this.nickName = this.userInfo.name
      }

      this.type = val;
    },
    onClickMask(){
      this.show = false
      this.nicknameDisabled = 0;
      this.nickName = this.userInfo.name
    },
    handelLogout(){
      Dialog.confirm({
        message:this.$t('lang.user_logout'),
        className:'text-center'
      }).then(()=>{
        this.$store.dispatch('userLogout');

        // 清除商家入驻申请信息
        window.localStorage.removeItem('merchantInfo');
        window.localStorage.removeItem('merchantsData');

        if(this.release){
          this.$http.get(`${window.ROOT_URL}user.php?act=logout`);
        }

        Toast.loading({
          mask: true,
          message: this.$t('lang.loading'),
          duration:1000
        },this.isWeiXin ? this.$router.push({path: '/index'}) : this.$router.push({path: '/login'}));
      })
    },
    updateInfo(){
      if(this.nicknameDisabled == 1){
        Toast(this.$t('lang.up_to_2_characters_can_be_entered'));
        return
      }else if(this.nicknameDisabled == 2){
        Toast(this.$t('lang.up_to_20_characters_can_be_entered'));
        return
      }

      let o = {
        sex:this.isSexNum,
        name:this.nickName,
        birthday:this.birthday
      }
      this.$store.dispatch('updateProfile',o);

      this.show = false;
    },
    onRead(file){
      this.imgPreview(file.file);
    },
    updataAvatar(){
    	Toast.loading({ message:'上传中...',duration:0 });
    	this.$store.dispatch('setMaterial',{
        file:{
        	content:this.avatarUploadImage
        },
        type:'avatar'
      }).then(res=>{
        this.$store.dispatch('userUpdateAvatar',{
            pic:res.data[0]
        })
      })
    },
    imgPreview(file) {
      let that = this;
      let orientation;
      //去获取拍照时的信息，解决拍出来的照片旋转问题
      that.Exif.getData(file, function () {
        orientation = that.Exif.getTag(this, "orientation");
      });

      // 看支持不支持FileReader
      if (!file || !window.FileReader) return;
      if (/^image/.test(file.type)) {
        // 创建一个reader
        let reader = new FileReader();
        // 将图片2将转成 base64 格式
        reader.readAsDataURL(file);
        // 读取成功后的回调
        reader.onloadend = function () {
          let result = this.result;
          let img = new Image();
          img.src = result;
          //判断图片是否大于500K,是就直接上传，反之压缩图片
          if (this.result.length <= 500 * 1024) {
            that.avatarUploadImage = this.result;
            that.updataAvatar();
          } else {
            img.onload = function () {
              let data = commonGet.compress(img, orientation);
              console.log(data)
              that.avatarUploadImage = data;
              that.updataAvatar();
            };
          }
        };
      }
    },
  },
  watch:{
    nickName(){
      let length = this.getStringLen(this.nickName);
      if(length < 2){
        this.nicknameDisabled = 1;
      }else if(length > 20){
        this.nicknameDisabled = 2;
      }else{
        this.nicknameDisabled = 0;
      }
    }
  }
}
</script>
