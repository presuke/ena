<script>
import axios from 'axios';
import CopyRight from '../CopyRight.vue';
import Header from '../Header.vue';
import Footer from '../Footer.vue';

export default {
	components: {
		CopyRight,
		Header,
		Footer,

	},
	data: () => ({
		rootPath: '',
    form:{
      name:'',
      email:'',
      password:'',
      password_confirm:'',
      error:'',
    },
    done:{
      display: false,
    },
    dialog:{
			copyright:{
				show: false,
			},
		},
	}),
	created() {
    this.rootPath = 'http://localhost:8001'
  },
  methods: {
		regist(){
			this.form.error = '';

      /*
			if(this.form.player.name == ''){
				this.form.player.error ='名前を入力してください。';
			}
			else if(this.form.player.name.length > 5){
				this.form.player.error ='お名前は5文字以内にしてください。';
			}
			else if(this.form.player.pass == ''){
				this.form.player.error = 'パスワードを入力してください。';
			}
      */
      axios
      .post(this.rootPath + '/api/auth/regist', this.form)
      .then((response) => {
        try {
          if(response.data.code == 0){
            this.done.display = true;
          }else{
            this.form.error = '特定できないエラー';
          }
        } catch (e) {
          this.errors = e;
        }
      })
      .catch((err) => {
        this.form.error = err.response.data.message;
      });
		},
  },
};
</script>
<style>
</style>
<style lang="scss" scoped>
@import '../../scss/app.scss';
</style>
<style lang="scss" scoped>
@import '../../scss/play.scss';
</style>
<style>
.btn,
a.btn,
button.btn {
  font-size: 1.6rem;
  font-weight: 700;
  line-height: 1.5;
  position: relative;
  display: inline-block;
  padding: 1rem 4rem;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-transition: all 0.3s;
  transition: all 0.3s;
  text-align: center;
  vertical-align: middle;
  text-decoration: none;
  letter-spacing: 0.1em;
  color: #212529;
  border-radius: 0.5rem;
}

.btn-border-gradient-wrap {
  display: inline-block;

  padding: 0.2rem;

  border-radius: 0.5rem;
}

.btn-border-gradient-wrap--gold {
  background-image: -webkit-linear-gradient(
    315deg,
    #700843 0%,
    #ff08ce 37%,
    #feb2fe 47%,
    #fad6fa 50%,
    #feb2fe 53%,
    #e108ce 63%,
    #700843 100%
  );
  background-image: linear-gradient(
    135deg,
    #700843 0%,
    #ff08ce 37%,
    #feb2fe 47%,
    #fadafa 50%,
    #feb2fe 53%,
    #e108ce 63%,
    #700843 100%
  );
}

.btn-border-gradient-wrap--gold:hover a.btn {
  text-shadow: 0 0 15px rgba(250, 250, 214, 0.5),
    0 0 15px rgba(250, 250, 214, 0.5), 0 0 15px rgba(250, 250, 214, 0.5),
    0 0 15px rgba(250, 250, 214, 0.5);
}

a.btn-border-gradient {
  font-size: 2rem;
  background: #fff;
}

.btn-text-gradient--gold {
  font-family: 'Shrikhand', cursive;

  background: -webkit-gradient(
    linear,
    left bottom,
    left top,
    from(#ffdbff),
    to(#a12264)
  );

  background: -webkit-linear-gradient(bottom, #ffdbff, #a12264);

  background: linear-gradient(to top, #ffdbff, #a12264);
  -webkit-background-clip: text;

  -webkit-text-fill-color: transparent;
}

</style>

<template>
	<header>
		<Header></Header>
	</header>
	<div style="text-align: center; height:calc(100vh - 100px);">
    <div 
			:class="[this.done.display ? 'scaleShow' : 'scaleHide']">
      ユーザ登録が完了しました。
    </div>
    <v-card
    :class="[this.done.display ? 'scaleHide' : 'scaleShow']"
    style="padding:10px;"
    >
      <v-cart-header>
        会員登録するには以下を入力して下さい。
      </v-cart-header>
      <v-card-content
      >
    <v-text-field
        label="お名前"
        v-model="this.form.name"
        placeholder="お名前を入力してください。"
      >
		</v-text-field>
    <v-text-field
        label="E-mail"
        v-model="this.form.email"
        placeholder="E-mailを入力してください。"
      >
		</v-text-field>
    <v-text-field
      label="パスワード"
      type="password"
      v-model="this.form.password"
      hint="パスワードを入力してください"
      >
    </v-text-field>
    <v-text-field
      label="パスワード（再入力）"
      type="password"
      v-model="this.form.password_confirmation"
      hint="パスワードを入力してください"
      >
    </v-text-field>
    <div class="error">{{this.form.error}}</div>
    <v-btn
     @click="this.regist();"
     >
      登録
    </v-btn>
  </v-card-content>
    </v-card>

  </div>
	<!--Notifyダイアログ-->
	<v-dialog 
	v-model="this.dialog.copyright.show"
	transition="dialog-top-transition"
	max-width="400"
	>
		<v-card width="320" height="400">
			<v-card-text style="overflow-y: auto;">
				<CopyRight></CopyRight>
			</v-card-text>
			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn
				color="blue-darken-1"
				variant="text"
				@click="this.dialog.copyright.show = false;"
				>
					閉じる
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
	<footer @click="this.dialog.copyright.show = true;">
		<Footer></Footer>
	</footer>
</template>
