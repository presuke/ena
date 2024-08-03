<script>
import axios from 'axios';
import VueQrcode from '@chenfengyuan/vue-qrcode';
import CopyRight from '../CopyRight.vue';
import Header from '../Header.vue';
import Footer from '../Footer.vue';
import Const from '../../js/const.js';

export default {
	components: {
		CopyRight,
		Header,
		Footer,
		VueQrcode,
	},
	data: () => ({
		url: location.href,
		rootPath: '',
		isLoading: false,
		rooms: [],
		room:{},
		playersOnRoom: [],
		error: '',
		form: {
			selection: {
				sex: ['male', 'fmale'],
				imgs: ['00','01','02','03','04','05','06','07','08','09','10','11','12'],
				selected: [],
			},
			player: {
				step: 0,
				id: 0,
				roomid: 0,
				sex: 'male',
				img: 0,
				name: '',
				pass: '',
				error:'',
			},
			room: {
				step: 0,
				controll:{
					btnAdd: Const.data.btnAdd,
					btnRemove: Const.data.btnRemove,
				},
				name: '',
				key: '',
				url:'',
				roles:Const.data.roles,
				role: {
					name: '',
					description: '',
				},
				playerNum: 0,
				playerNumMax: 20,
				error: '',
			}, //カンマを追加
		},
		dialog:{
			copyright: {
				show: false,
			},
		},
		se: Const.data.se,
	}),
	created: function () {
		if(this.url.indexOf('/room') != -1){
			this.rootPath = this.url.split('/room')[0];
			let key = this.url.split('/room')[1];
			if(key.indexOf('/') != -1 && key != '/'){
				this.form.room.key = key.split('/')[1];
				this.loadRoom();
			}
		}
	},
	mounted() {
		window.onload = () => {
			//this.loadRooms();
		};
	},
	methods: {
		loadRooms() {
			this.isLoading = true;
			this.form.player.step = 0;
			this.form.player.roomid = 0;
			this.error = '';
			axios
				.get(this.rootPath + '/api/v1/room/getAll', this.param)
				.then((response) => {
					this.isLoading = false;
					try {
						if(response.data.error != undefined){
							this.error = response.data.error.errorInfo;
						}else if(response.data.rooms  != undefined){
						//app.rooms = response.data.rooms;
							this.rooms = response.data.rooms;
						}else{
							this.error = '特定できないエラー';
							console.log(response.data);
						}
					} catch (err) {
						this.error = err;
						console.log(err);
					}
				})
				.catch((err) => {
					this.error = err;
					console.log(err);
				});
		},
		loadRoom() {
			this.isLoading = true;
			this.form.player.step = 0;
			this.form.player.roomid = 0;
			this.error = '';
			axios
				.get(this.rootPath + '/api/v1/room/get', {
					params: this.form.room,
				})
				.then((response) => {
					this.isLoading = false;
					try {
						if(response.data.error != undefined){
							if(response.data.error.errorInfo != undefined) {
								this.error = response.data.error.errorInfo;
								alert(response.data.error.errorInfo);
							}else{
								this.error = response.data.error;
								alert(response.data.error);
							}
						}else if(response.data.room  != undefined){
							response.data.room.players.forEach((player) =>{
								if(player.sex != ''){
									this.form.selection.selected.push(player.sex + '_' + player.img);
								}
							});
							this.room = response.data.room;
							this.entryRoom(this.room);
						}else{
							this.error = '特定できないエラー';
							console.log(response.data);
						}
					} catch (err) {
						this.error = err;
						console.log(err);
					}
				})
				.catch((err) => {
					this.error = err;
					console.log(err);
				});
		},
		entryRoom(room){
			this.form.player.step = 0;
			setTimeout(()=>{
				this.playersOnRoom = room.players;
				this.form.player.roomid = room.room.id;
				this.form.player.step = 1;
			},100);
		},
		playGame(player){
			if(player.name == ''){
				player.sex = 'male';
				player.img = 0;
				player.pass = '';
				this.form.player = player;
				this.form.player.step = 2;
			}else{
				location.href = this.rootPath + '/play/' + player.id;
			}
		},
		createPlayer(){
			this.form.player.error = '';

			if(this.form.player.name == ''){
				this.form.player.error ='名前を入力してください。';
			}
			else if(this.form.player.name.length > 5){
				this.form.player.error ='お名前は5文字以内にしてください。';
			}
			else if(this.form.player.pass == ''){
				this.form.player.error = 'パスワードを入力してください。';
			}
			if(this.form.player.error != ''){
				this.se.Error.play();
			}else{
				axios
				.post(this.rootPath + '/api/v1/player/create', this.form.player)
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
			}
		},
		checkCreateRoom() {
			this.changeNum(this.form.room.roles[0], 0);
			if(this.form.room.error == ''){
				if (this.form.room.name == '') {
					this.form.room.error = '部屋名を入力してください。';
				} else if (this.form.room.name > 15) {
					this.form.room.error = '15文字以内で入力ください';
				}

				this.form.room.roles.forEach(role => {
					if(role.id == 1 && role.num == 0){
						this.form.room.error = '人狼は1人以上設定してください';
					}
				});

				if(this.form.room.playerNum > this.form.room.playerNumMax){
					this.form.room.error = 'プレイヤー数は' + this.form.room.playerNumMax + '人以下にしてください';
				}
			}
			if (this.form.room.error == '') {
				this.form.room.step = 2;
			}else{
				this.se.Error.play();
			}
		},
		createRoom(){
			this.form.room.step = 3;
			axios
			.post(this.rootPath + '/api/v1/room/create', {
				params: this.form.room,
			})
			.then((response) => {
				if(response.data.code == 0){
					this.form.room.step = 4;
					this.form.room.url = this.rootPath + '/room/' + response.data.room.key;
					this.se.Success.play();
				}else{
					this.form.room.step = (response.data.code == 8) ? 1 : 2;
					this.form.room.error = response.data.error;
					this.se.Error.play();
				}
			})
			.catch((err) => {
				this.form.room.error = err.message;
				this.form.room.step = 2;
				this.se.Error.play();
			});
		},
		restart(room){
			if(!confirm("部屋「" + room.room.name + "」でカードを配り直して再プレイしますか？")){
				return false;
			}
			axios
			.post(this.rootPath + '/api/v1/room/restart', {
				params: room.room,
			})
			.then((response) => {
				if(response.data.code == 0){
					this.se.Success.play();
					this.loadRoom();
				}else{
					this.form.room.error = response.data.error;
					this.se.Error.play();
				}
			})
			.catch((err) => {
				this.form.room.error = err.message;
				this.se.Error.play();
			});
		},
		removeRoom(room){
			if(!confirm("部屋「" + room.room.name + "」を削除しますか？")){
				return false;
			}
			axios
			.post(this.rootPath + '/api/v1/room/remove', {
				params: room.room,
			})
			.then((response) => {
				if(response.data.code == 0){
					this.se.Success.play();
					this.loadRooms();
				}else{
					this.form.room.error = response.data.error;
					this.se.Error.play();
				}
			})
			.catch((err) => {
				this.form.room.error = err.message;
				this.form.room.step = 2;
				this.se.Error.play();
			});
		},
		showDescription(role){
			this.form.room.role.name = role.name;
			this.form.room.role.description = role.description;
			document.getElementById('description').classList.remove('scaleHide');
			document.getElementById('description').classList.add('scaleShow');
			setTimeout(() => {
				document.getElementById('description').classList.remove('scaleShow');
				document.getElementById('description').classList.add('scaleHide');
			},3000);
		},
		changeNum(role, num){
			this.se.Push.play();
			if(num > 0 || role.num != 0){
				role.num += num;
			}

			const team = this.countNum();
			if(team.peaple <= team.jinro){
				this.form.room.error = '村人側は人狼側より多くする必要があります。';
			}else{
				this.form.room.error = '';
			}
		},
		countNum(){
			let team = {};
			team.peaple = 0;
			team.jinro = 0;
			this.form.room.roles.forEach(role => {
				if(role.id == 1 || role.id == 5 || role.id == 7
				){
					team.jinro += role.num;
				}else{
					team.peaple += role.num;
				}
			});
			this.form.room.playerNum = team.jinro + team.peaple;
			return team;
		},
		checkString(inputdata) {
			var regExp = /^[a-zA-Z0-9_]*$/;
			return regExp.test(inputdata);
		},
	},
};
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Caveat&family=Reggae+One&display=swap');
</style>
<style lang="scss" scoped>
@import '../../scss/app.scss';
</style>
<style>
#header{
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  z-index: 9;
}
.step{
	width: 100%;
	height:calc(100vh - 150px);
	overflow-y: auto;
}
.inRoom{
	border-radius:5px;
	padding:5px;
	background-color:rgb(230, 253, 230); 
	text-shadow: 
        0 0 10px green,
        0 0 20px lightgreen;
	box-shadow: 
        0 0 10px lightgreen,
        0 0 20px yellowgreen,
        0 0 40px yellow,
        0 0 80px gold;
	color:rgb(0, 187, 0);
}
.role{
	position: relative;
	width:90px;
	height:150px;
	border-radius: 10px;
	background-size: contain;
	margin:3px;
	.num {
		position:absolute;
		right:0;
		width:25px;
		height:25px;
		background-color: rgba(255, 0, 0, 0.5);
		border-radius: 50%;
	}
}
.name{
	position:absolute;
	left:0;
	right:0;
	bottom:50px;
	text-align:center;
	background-color: rgba(0,0,0,0.5);;
}
.num{
	position:absolute;
	left:0;
	right:0;
	bottom:30px;
	text-align:center;
}
.btnAdd{
	position:absolute;
	right:5px;
	bottom:30px;
	width:25px;
	height: 25px;
	z-index:2;
}
.btnRemove{
	position:absolute;
	left:5px;
	bottom:30px;
	width:25px;
	height: 25px;
	z-index:2;
}
#description{
	position: absolute; 
	top: 0; 
	left:0;
	border-radius: 5px; 
	border: solid 1px yellow; 
	background-color: rgba(0,0,0,0.75);
	padding:5px; 
	margin:10px; 
	z-index:9;
}
.scaleShow{
	transition: all 0.5s 0s ease;
	zoom:1.0;
}
.scaleHide{
	transition: all 0.5s 0s ease;
	zoom:1.0;
	transform:scale(0.0, 1.0);
	transform-origin: 0px 0px;
	position: absolute;
	transform-origin-y: 0px;
}
.error {
	font-size: smaller;
	color: red;
}
</style>
<template>
	<div style="  max-width: 1024px; height: 100%; background-color: black; margin: 0 auto; font-family: 'Reggae One', cursive;">
		<div id="header">
			<Header></Header>
		</div>
		<v-card 
		style="text-align: center; margin-bottom:20px; background-color: black;"
		>
			<div 
			class="step" 
			:class="[form.room.step==0 ? 'scaleShow' : 'scaleHide']">
				<div style="margin-top:10px;">
					<!--
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); loadRooms();"
					>
					部屋再読み込み
					</v-btn>
					-->
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); form.room.step=1;"
					>
						部屋を作成する
					</v-btn>
					<v-btn
					v-if="form.room.key != ''"
					color="purple darken-4"
					@click="se.Push.play(); this.loadRoom();"
					>
						プレイヤー一覧
					</v-btn>
					<v-btn
					v-if="form.room.key != ''"
					color="purple darken-4"
					@click="se.Push.play(); this.restart(this.room);"
					>
						再プレイ
					</v-btn>
				</div>
				<v-container>
					<!-- 部屋一覧-->
					<v-row v-for="room in rooms" :key="id" v-if="!isLoading">
						<v-col
							style="
								padding: 10px;
								border-bottom: solid thin gray;
							"
						>
							<div style="float: left; width:calc(100% - 200px);">
								{{ room.room.name }}
							</div>
							<div style="float: left">
								{{ room.players.length }}名
							</div>
							<div 
							v-if="room.room.win == 0"
							style="float: right;"
							>
								<span
								v-if="this.form.player.roomid == room.room.id"
								class="inRoom"
								>
									入室中
								</span>
								<v-btn
								color="purple darken-4"
								v-else @click="entryRoom(room)"
								>
									入室
								</v-btn>
							</div>
							<div v-else
							style="float: right;">
								<v-btn
								color="purple darken-4"
								@click="restart(room)"
								>
									replay
								</v-btn>
								<v-btn
								color="purple darken-4"
								@click="removeRoom(room)"
								>
									削除
								</v-btn>
							</div>
							<br style="clear: left" />
						</v-col>
					</v-row>
					<div v-if="isLoading">
						<v-progress-circular color="blue-lighten-3" model-value="20" :size="77"></v-progress-circular>
						loading data...
					</div>
				</v-container>

				<!-- Entry Room -->
				<div v-if="this.room.room != undefined">部屋名：{{ this.room.room.name }}のQR</div>
				<VueQrcode 
				v-if="this.url" 
				:value="this.url" 
				:options="{ width: 100 }" />
				<div 
				:class="[form.player.step==1 ? 'scaleShow' : 'scaleHide']"
				style="margin:10px;"
				>
					<v-container>
						<v-row style="margin: 0 10;">
							プレイするプレイヤーを選んでください。
						</v-row>
						<v-row 
						v-for="(player, index) in playersOnRoom" 
						:key="index">
							<v-col
								style="
									border-bottom:solid thin lightgray;
									padding: 10px;
								"
							>
								<div style="float: left;">
									{{ index + 1 }}&nbsp;
								</div>
								<div v-if="player.name != ''">
									<div style="float: left;">
										<img 
										:src="rootPath + '/image/avatar/' + player.sex + '/icon' + ( '00' + player.img ).slice( -2 ) + '.png'" 
										class="rounded-circle"
										Width="30"
										Height="30"
										/>
									</div>
									<div style="float: left;">
										{{ player.name }}&nbsp;
									</div>
								</div>
								<div v-else>
									<div style="float: left;">
										<img 
										:src ="rootPath + '/image/avatar/random.png'" 
										class="rounded-circle"
										Width="30"
										Height="30"
										/>
									</div>
									<div style="float: left;">
										プレイヤー未作成
									</div>
								</div>
								<div style="float: right;">
									<v-btn
										@click="playGame(player)"
										color="purple darken-4"
										>
										<span v-if="player.name != ''">
											参加
										</span>
										<span v-else>
											プレイヤー作成
										</span>
									</v-btn>
								</div>
								<br style="clear: left" />
							</v-col>
						</v-row>
					</v-container>
				</div>

				<!-- create player -->
				<div 
				:class="[form.player.step==2 ? 'scaleShow' : 'scaleHide']" 
				style="margin:10px;"
				>
					<div>
						プレイヤーの設定をしてください。
					</div>
					<v-text-field
						label="お名前"
						v-model="this.form.player.name"
						v-on:change="checkName"
						placeholder="お名前を入力してください。"
					>
					</v-text-field>
					<v-text-field
						label="パスワード"
						type="password"
						v-model="this.form.player.pass"
						hint="Enter your password to access this website"
					></v-text-field>
					<div v-for="n in 10" style="float:left; margin:5px;">
						<v-btn 
						color="purple darken-4"
						@click="this.form.player.pass += (n%10) + ''"
						>
						{{ n%10 }}
						</v-btn>
					</div>
					<v-spacer></v-spacer>
					<div style="float:left; margin:5px;">
						<v-btn 
						color="purple darken-4"
						@click="this.form.player.pass = ''"
						>
						Clear
						</v-btn>
					</div>
					<br style="clear: both;" />
					<v-select
						label="性別"
						v-model="form.player.sex"
						:items="this.form.selection.sex"
					></v-select>
					<div style="margin: 10px;">
						アバターアイコンを選択してください。
					</div>
					<div style="width:100%; height:50%; max-height:300px; text-align:center;">
						<v-carousel 
						:continuous="false"
						v-model="form.player.img"
						width="200"
						height="200"
						class="rounded-circle"
						hide-delimiters
						>
							<v-carousel-item
								v-for="img in form.selection.imgs"
								:key="img"
								contain
							>
								<img 
								:style="[(this.form.selection.selected.indexOf(form.player.sex + '_' + Number(img))) == -1 ? '' : 'filter:grayscale(1);']"
								:src="rootPath + '/image/avatar/' + form.player.sex + '/icon' + img + '.png'" 
								class="rounded-circle"
								/>
							</v-carousel-item>
						</v-carousel>
					</div>
					<div style="maring-top:10px;">
						<div class="error">
							{{ form.player.error }}
						</div>
					</div>
					<div style="text-align:center; margin:auto;">
						<v-btn
						class="text-h4"
						elevation="30"
						height="60"
						rounded
						color="deep-purple darken-1"
						@click="this.createPlayer()">これで決まり！！</v-btn>
					</div>
				</div>

				<!-- created Player -->
				<div 
				:class="[form.player.step==3 ? 'scaleShow' : 'scaleHide']"
				style="margin-top:10px;"
				>
					<v-card>
						<v-card-title
						color="primary"
						dark>
						プレイヤー作成完了
						</v-card-title>
						<v-card-text>
							<img 
								:src="rootPath + '/image/avatar/' + form.player.sex + '/icon' + ( '00' + form.player.img ).slice( -2 ) + '.png'" 
								class="rounded-circle"
								/>
							<div>
								{{this.form.player.name}}さん、ようこそ！
							</div>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn @click="playGame(this.form.player)">
								ゲームに参加
							</v-btn>
						</v-card-actions>
					</v-card>
				</div>
			</div>
			<!--create room-->
			<div 
			class="step" 
			:class="[form.room.step==1 ? 'scaleShow' : 'scaleHide']">
				ゲームルームの設定をしてください。
				<div>
					<v-text-field
						id="roomName"
						label="部屋名"
						v-model="form.room.name"
						placeholder="部屋名を入力してください"
					>
					</v-text-field>
				</div>
				<div style="text-align: center;">
					各役割の人数を設定してください。
					<div>
						現在のプレイヤー数：{{ this.form.room.playerNum }}
					</div>

					<div 
					v-for="role in form.room.roles" 
					:key="role"
					style="margin:0 auto; width:min(80vw, 600px);"
					>
						<div style="float:left;">
							<div 
							style="position:relative;"
							>
								<div 
								class="role"
								:style="{ backgroundImage: `url('${role.img}')` }"
								@click="this.showDescription(role)"
								>
								</div>
								<div class="num">
									{{ role.num }}
								</div>
								<div class="name">
									<span v-if="role.team == 1" style="color:red;">
										{{ role.name }}
									</span>
									<span v-else style="color:yellow;">
										{{ role.name }}
									</span>
								</div>
								<img 
								class="btnRemove"
								:src="form.room.controll.btnRemove" 
								@click="changeNum(role, -1)" />
								<img 
								class="btnAdd"
								:src="form.room.controll.btnAdd" 
								@click="changeNum(role, 1)" />
							</div>
						</div>
					</div>
				</div>
				<div style="clear:left;">
					<div>
						<span style="color:red;">赤文字表示</span>
						<span>　人狼チーム</span>
					</div>
					<div>
						<span style="color:yellow;">黄文字表示</span>
						<span>　村人チーム</span>
					</div>
				</div>
				<div 
				id="description"
				:class="[form.room.role.name!='' ? 'scaleShow' : 'scaleHide']"
				>
					<div>
						役割説明　【{{ this.form.room.role.name }}】
					</div>
					<div style="margin: 10px;">
						{{ this.form.room.role.description }}
					</div>
				</div>
				<br  style="clear:left;" />
				<div class="error">
					{{ form.room.error }}
				</div>
				<div>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); form.room.step = 0;"
					>
					戻る
					</v-btn>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); this.checkCreateRoom()"
					>
					次へ
					</v-btn>
				</div>
			</div>
			<div class="step" v-bind:class="[form.room.step==2 ? 'scaleShow' : 'scaleHide']">
				<div>
					これでいいですか？
				</div>
				<div>部屋名:{{ form.room.name }}</div>
				<div style="text-align: center;">
					<div 
					v-for="role in form.room.roles" 
					:key="role"
					style="margin:0 auto; width:min(80vw, 600px);"
					>
						<div style="float:left;">
							<div 
							class="role"
							:style="{ backgroundImage: `url('${role.img}')` }"
							>
								<div class="name">
									{{ role.name }}
								</div>
								<div class="num" style="top:0;right:0;">
									{{ role.num }}
								</div>
							</div>
						</div>
					</div>
				</div>
				<br  style="clear:left;" />
				<div class="error">
					{{ form.room.error }}
				</div>
				<div>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); form.room.step=1;"
					>
					考え直す
					</v-btn>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); createRoom();"
					>
					部屋を作成
					</v-btn>
				</div>
			</div>
			<div class="step" v-bind:class="[form.room.step==3 ? 'scaleShow' : 'scaleHide']">
				部屋作ってます。ちょっと待ってね。。
			</div>
			<div class="step" v-bind:class="[form.room.step==4 ? 'scaleShow' : 'scaleHide']">
				<div>部屋ができました！</div>
				<div>以下のURLを全プレイヤーとシェアして、各プレイヤーの設定をしてください。</div>
				<div style="font-size:smaller; color:aquamarine;">
					<a :href="form.room.url" style="">{{ form.room.url }}</a>
				</div>
				<VueQrcode 
				v-if="form.room.url" 
				:value="form.room.url" 
				:options="{ width: 200 }" />
			</div>
			<div class="error">
				{{ this.error }}
			</div>
		</v-card>

		<!--Notifyダイアログ-->
		<v-dialog 
		v-model="this.dialog.copyright.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card maxWidth="400" height="400">
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
	</div>
</template>
