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
      id:'',
      password:'',
      error:'',
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
		login(){
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
      .post(this.rootPath + '/api/login', this.form)
      .then((response) => {
        try {
          if(response.data.player != undefined){
            this.form.player = response.data.player;
            this.form.player.roomid = 0;
            this.form.player.step = 3;
          }else if(response.data.code !=undefined){
            response.data.avators.forEach((avator) =>{
              this.form.selection.selected.push(avator.sex + '_' + avator.img);
            });
            if(response.data.code == 1){
              this.form.player.error = '同じ名前のプレイヤーが存在します。別の名前にしてください。';
              this.se.Error.play();
            }
            else if(response.data.code == 2){
              this.form.player.error = '同じアバターのプレイヤーが存在します。別のアバターを選択してください。';
              this.se.Error.play();
            }
          }
          else if(response.data.error != undefined){
            this.form.player.error = response.data.error.errorInfo;
          }else{
            this.form.player.error = '特定できないエラー';
          }
        } catch (e) {
          this.errors = e;
        }
      })
      .catch((err) => {
        console.log(err);
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
    <v-text-field
        label="ID"
        v-model="this.form.id"
        placeholder="IDを入力してください。"
      >
		</v-text-field>
    <v-text-field
      label="パスワード"
      type="password"
      v-model="this.form.password"
      hint="パスワードを入力してください"
      >
    </v-text-field>
    <v-btn
     @click="this.login();"
     >
      login
    </v-btn>
    <v-card v-model="this.form.error" >
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
