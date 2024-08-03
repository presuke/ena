<script>
import axios from 'axios';
import CopyRight from '../CopyRight.vue';
import Header from '../Header2.vue';
import Footer from '../Footer.vue';
import Const from '../../js/const.js';

export default {
	components: {
		CopyRight,
		Header,
		Footer,
	},
	data: () => ({
		url: location.href,
		rootPath: '',
		authtoken: '',
		isProcessing: false,
		isUsingPower: false,
		timeZone: -1,
		room: {},
		players: [],
		playerSelected:{},
		me: { 
			name: '',
			roleid: 0,
			role: {
				id:0,
				name:'',
				img:'',
				color:'',
				command:[],
			},
		},
		info:{
			message: '',
		},
		action:{
			message:'',
			error:'',
		},
		error: '',
		room:{
			time: 0,
			roles:[],
		},
		players:[],
		const:{
			authTokenName: 'CF_AUTH_TOKEN',
			roles: Const.data.roles,
			times: Const.data.times,
			actions: Const.data.actions,
		},
		reflesh:{
			const:{
				interval: 1000,
				countMax: 10,
			},
			timer: '',
			count: 0,
			countValue: 40,
			total: 0,
			message: '',
		},
		countdown:{
			const:{
				interval: 1000,
			},
			timer: '',
			action: '',
			sec: 0,
		},
		dialog:{
			login:{
				show: false,
				pass: '',
				error: '',
				success: false,
			},
			result:{
				vote:{
					show:false,
				},
				action:{
					show:false,
					attackedPlayers:[],
					freedomPlayers:[],
					message : [],
					confirmed: false,
				},
			},
			confirm:{
				show:false,
				message:'',
				ret: false,
			},
			win:{
				show:false,
			},
			predict:{
				show:false,
			},
			expose:{
				show:false,
			},
			roomInfo:{
				show: false,
				role:{
					name:'',
					img:'',
					command: [],
					description: '',
					defaultdescription: 'カードをタップすると説明が表示されます。',
				}
			},
			myCard:{
				show: false,
			},
			copyright:{
				show: false,
			},
		},
		se: Const.data.se,
		ret:{}
	}),
	created() {
		if(this.url.indexOf('/play/') != -1){
			this.rootPath = this.url.split('/play/')[0];
			this.playerid = this.url.split('/play/')[1];
		}

		this.authtoken = localStorage.getItem(this.const.authTokenName);
		if(this.authtoken == null)
			this.authtoken = '';

		this.dialog.roomInfo.role.description = this.dialog.roomInfo.role.defaultdescription;

		//役割ごとのコマンドを設定
		this.const.roles.forEach((role) => {
			switch(role.id){
				case 0:{
					role.command = [{name:'眠る', action:this.const.actions.sleep,}]
					break;
				}
				case 1:{
					role.command = [{name:'襲撃', action:this.const.actions.attack,}]
					break;
				}
				case 2:{
					role.command = [{name:'守る', action:this.const.actions.save,}]
					break;
				}
				case 3:{
					role.command = [{name:'占う', action:this.const.actions.predict,}]
					break;
				}
				case 4:{
					role.command = [{name:'霊媒', action:this.const.actions.expose,}]
					break;
				}
				case 5:{
					role.command = [{name:'眠る', action:this.const.actions.sleep,}]
					break;
				}
				case 6:{
					role.command = [{name:'眠る', action:this.const.actions.sleep,}]
					break;
				}
				case 7:{
					role.command = [{name:'襲撃', action:this.const.actions.change,},{name:'眠る', action:this.const.actions.sleep,}]
					break;
				}
				case 8:{
					role.command = [{name:'検討', action:this.const.actions.consider,},{name:'眠る', action:this.const.actions.sleep,}]
					break;
				}
				case 9:{
					role.command = [{name:'救出', action:this.const.actions.freedom,},{name:'眠る', action:this.const.actions.sleep,}]
					break;
				}
			}
		});

		this.refleshStatus();
	},
	methods: {
		startRefleshTimer(){
			clearInterval(this.reflesh.timer);
			this.reflesh.count = 0;
			this.reflesh.timer = null;
			this.reflesh.timer = setInterval(()=>{
				this.reflesh.count++;
				this.reflesh.countValue = this.reflesh.count * 100 / this.reflesh.const.countMax;
				if(this.reflesh.count == this.reflesh.const.countMax){
					clearInterval(this.reflesh.timer);
					this.reflesh.timer = null;
					this.refleshStatus();
				}
			}, this.reflesh.const.interval);
		},
		startCountDownTimer(){
			clearInterval(this.countdown.timer);
			this.countdown.timer = null;
			this.countdown.timer = setInterval(()=>{
				if(this.countdown.sec == 0){
					clearInterval(this.countdown.timer);
					this.countdown.timer = null;
					this.info.message = this.countdown.action + 'の締め切り時間が来ました。他のプレイヤーが' + this.countdown.action + 'を変更しなければ結果発表に移ります。';
				}else{
					this.info.message = 'あと'+ this.countdown.sec + '秒で' + this.countdown.action + 'を締め切ります。変更があるならお早めに';
					this.se.Clock.play();
				}
				this.countdown.sec--;
			}, this.countdown.const.interval);
		},
		refleshStatus() {
			//部屋の状況を取得
			this.authtoken = localStorage.getItem(this.const.authTokenName);
			axios
			.get(this.rootPath + '/api/v1/play/getRoomStatus', {
				params: {
					playerid: this.playerid,
					authtoken: this.authtoken,
				}
			})
			.then((response) => {
				try {
					if(response.data.code == 0){
						this.startRefleshTimer();
						this.room = response.data.room;
						for(let i=0; i<this.room.roles.length; i++){
							this.const.roles.forEach((role) => {
								if(role.id == this.room.roles[i].id){
									this.room.roles[i] = role;
								}
							});
						}
						this.players = response.data.players;
						this.me = response.data.me;
						this.timeZone = response.data.timeZone;
						this.info = response.data.info;

						//役割
						this.me.role = this.getRole(this.me.roleid);
						//他の人の役割（分かっている人のための）
						this.players.forEach((player) => {
							player.role = this.getRole(player.roleid);
						});
						//パワー有効
						this.isUsingPower = this.me.role.power;

						//時間帯による処理
						switch(this.info.time){
							//投票中
							case  0:{
								const votes = response.data.votes;
								if(votes != undefined){
									try{
										for(let idx = 0; idx < this.players.length; idx++){
											let player = this.players[idx];
											votes.forEach((vote) => {
												if(vote.playerid == player.id){
													player.done = 1;
												}
											});
											this.players[idx] = player;
										}
									}catch(error){
										console.log(error);
									}
								}
								//投票終了までのカウントダウン
								if(this.info.countdown != undefined){
									this.countdown.sec = response.data.info.countdown.sec;
									this.countdown.action = response.data.info.countdown.action;
									this.startCountDownTimer();
								}
								break;
							}
							//投票結果
							case 1:{
								const confirms = response.data.confirms;
								for(let idx = 0; idx < this.players.length; idx++){
									let player = this.players[idx];
									confirms.forEach((confirm) => {
										if(confirm.playerid == player.id){
											player.done = 1;
										}
									});
									this.players[idx] = player;
								}
								if(this.info.result.vote != undefined) {
									this.players.forEach((player) => {
										if(player.id == this.info.result.vote.targetid){
											this.dialog.result.vote.player = player;
										}
									});
									if(!this.info.result.vote.confirmed){
										if(!this.dialog.result.vote.show){
											this.dialog.result.vote.show = true;
											this.se.Jail.play();
										}
									}
									//選択中プレイヤーのクリア
									this.playerSelected = {};
								}
								break;
							}
							//役割ごとのアクション
							case 2:{
								try{
									const acted = response.data.acted;
									for(let idx = 0; idx < this.players.length; idx++){
										let player = this.players[idx];
										acted.forEach((act) => {
											if(act.playerid == player.id){
												player.done = 1;
											}
										});
										this.players[idx] = player;
									}
								}catch(error){
									console.log(error);
								}
								const message = (this.me.flgDead == 0) ? 
												'あなたは' + this.me.role.name + 'です。さぁ、あなたがやるべきことをやってください。':
												'';
								if(this.info == undefined){
									this.info.message = message;
								}else if(this.info.countdown != undefined){
									//行動終了までのカウントダウン
									this.countdown.sec = response.data.info.countdown.sec;
									this.countdown.action = response.data.info.countdown.action;
									this.startCountDownTimer();
								}else if(this.info.message == undefined){
									this.info.message = message;
								}
								break;
							}
							//襲撃結果
							case 3:{
								if(this.info.result != undefined) {
									const action = this.info.result.action;
									const confirms = this.info.result.confirmed;
									for(let idx = 0; idx < this.players.length; idx++){
										let player = this.players[idx];
										confirms.forEach((confirm) => {
											if(confirm.playerid == player.id){
												player.done = 1;
											}
										});
										this.players[idx] = player;
									}
									this.dialog.result.action.json = this.info.result;
									this.dialog.result.action.confirmed = this.info.result.confirmed;
									this.dialog.result.action.attackedPlayers = [];
									this.dialog.result.action.freedomPlayers = [];
									this.dialog.result.action.message = [];
									this.players.forEach((player) => {
										action.attacked.forEach((attackedPlayerid) => {
											if(player.id == attackedPlayerid){
												this.dialog.result.action.attackedPlayers.push(player);
											}
										});
										action.freedom.forEach((freedomPlayerid) => {
											if(player.id == freedomPlayerid){
												this.dialog.result.action.freedomPlayers.push(player);
											}
										});
									});
									let flgAttacked = this.dialog.result.action.attackedPlayers.length > 0;
									let flgFreedom = this.dialog.result.action.freedomPlayers.length > 0;
									let flgSaved = false;
									let flgChanged = false;
									if(action.cntSave != undefined){
										if(action.cntSave > 0){
											this.dialog.result.action.message.push(action.cntSave + '人が狩人に守られました。');
											flgSaved = true;
										}
									}
									if(action.cntConsider != undefined){
										if(action.cntConsider > 0){
											this.dialog.result.action.message.push(action.cntConsider + '人が検討して難を逃れました。');
											flgSaved = true;
										}
									}
									if(action.cntChange != undefined){
										if(action.cntChange > 0){
											this.dialog.result.action.message.push(action.cntChange + '人が吸血鬼に襲われました。');
											flgChanged = true;
										}
									}
									if(!this.info.result.myconfirmed){
										if(!this.dialog.result.action.show){
											this.dialog.result.action.show = true;
											//se
											if(flgAttacked && flgFreedom){
												this.se.JailAndFreedom.play();
											}else if(flgAttacked && !flgFreedom){
												this.se.Jail.play();
											}else if(!flgAttacked && flgFreedom){
												this.se.Freedom.play();
											}else if(!flgAttacked && flgSaved){
												this.se.Safe.play();
											}
										}
									}
									if(!flgAttacked &&
									   !flgFreedom &&
									   !flgSaved &&
									   !flgChanged){
										this.dialog.result.action.message.push('昨夜は何も起こりませんでした。');
									}
									//選択中プレイヤーのクリア
									this.playerSelected = {};
								}
								break;
							}
							//勝敗
							case 4:{
								let lstJinro = [];
								let lstPeaple = [];
								this.players.forEach((player) => {
									if(player.flgDead == 0){
										if(player.roleid == 1 || player.roleid ==5 || player.roleid == 7){
											lstJinro.push(player);
										}else if(player.role != undefined){
											lstPeaple.push(player);
										}
									}
								});
								if(response.data.win == 2){
									this.dialog.win.team = '村人';
									this.dialog.win.players = lstPeaple;
								}else if(response.data.win == 1){
									this.dialog.win.team = '人狼';
									this.dialog.win.players = lstJinro;
								}else{
									this.info.message = 'システムエラー';
									return;
								}
								if(!this.dialog.win.show){
									clearInterval(this.reflesh.timer);
									this.reflesh.count = 0;
									this.reflesh.timer = null;
									this.se.Win.play();
									this.dialog.win.show = true;
								}
								break;
							}
							//?
							default:{
								if(response.data.errors != undefined){
									this.error = JSON.stringfy(response.data.errors);
								}
								//this.error = JSON.stringify(response.data);
								break;
							}
						}

						this.reflesh.total++;
					}else if(response.data.code == 7){
						//名無しエラー
						this.problem = this.const.problems.namelessOtherPlayer;
					}else if(response.data.code == 8){
						//名無しエラー
						this.problem = this.const.problems.namelessMyself;
					}else if(response.data.code == 1 || response.data.code == 9){
						this.dialog.login.show = true;
					}else if(response.data.room  != undefined){
						this.rooms = response.data.room;
					}else if(response.data.errors.length > 0){
						this.error = JSON.stringfy(response.data.errors);
						console.log(this.error);
					}else{
						this.error = '特定できないエラーが発生しました。';
					}
				} catch (e) {
					this.error = e;
				}
			})
			.catch((err) => {
				console.log(err);
				setTimeout(() =>this.refleshStatus(), 500);
			});
		},
		act(action){
			const retCheck = this.checkAction(action);
			if(!retCheck){
				return ;
			}
			if(this.action.error != ''){
				this.action.message = '';
				return;
			}
			this.action.error = '';
			this.action.message = '';
			this.isProcessing = true;
			this.reflesh.count = 0;
			this.ret = '';
			this.authtoken = localStorage.getItem(this.const.authTokenName);
			let params = {};
			params.myid = this.me.id;
			params.targetid = this.playerSelected.id;
			params.roles = this.const.roles;
			params.action = action;
			params.authtoken = this.authtoken;

			axios
			.post(this.rootPath + '/api/v1/play/action', {
				params,
			})
			.then((response) => {
				try {
					this.isProcessing = false;
					//this.action.event = actionEventBackup;
					this.reflesh.count = this.reflesh.const.countMax - 1;

					if(response.data.error != undefined) {
						this.action.error = response.data.error;
						this.refleshStatus();
					}else if(response.data.message != undefined){
						this.action.message = response.data.message;
						this.refleshStatus();
					}

					if(response.data.result != undefined){
						const result = response.data.result;
						if(result.predict != undefined){
							const target = result.predict.target;
							//役割
							this.const.roles.forEach((role) => {
								if(target.role == role.id){
									target.role = role;
								}
							});
							this.dialog.predict.target = target;
							this.dialog.predict.show = true;
						}else if(result.expose != undefined){
							const target = result.expose.target;
							//役割
							this.const.roles.forEach((role) => {
								if(target.role == role.id){
									target.role = role;
								}
							});
							this.dialog.expose.target = target;
							this.dialog.expose.show = true;
						}else if(result.consider != undefined){
							if(result.consider == 'voice'){
								this.se.Consider.play();
							}
						}
					}
				} catch (e) {
					this.error = e;
				}
			})
			.catch((err) => {
				this.ret = [];
				this.ret.push(err);
			});
		},
		checkAction(action) {
			this.action.error = '';
			if(this.me.flgDead == 1 && action != 'confirmActionResult'){
				this.action.error = 'あなたは投獄されています。他のプレイヤーの行動を静観しましょう。';
				return false;
			}
			switch(action){
				case this.const.actions.vote:{
					if(this.playerSelected.id == undefined){
						this.action.error = '投票する人を指定してください。';
					}else if(this.playerSelected.flgDead == 1){
						this.action.error = '投獄されていない人を指定してください。';
					}
					break;
				}
				case this.const.actions.sleep:{
					//人前行動キャンセル確認
					if(this.action.message.indexOf('救出') != -1){
						if(!confirm('救出をキャンセルして就寝しますか？')){
							return false;
						}
					}else if(this.action.message.indexOf('襲撃') != -1){
						if(!confirm('襲撃をキャンセルして就寝しますか？')){
							return false;
						}
					}else if(this.action.message.indexOf('検討') != -1){
						if(!confirm('検討をキャンセルして就寝しますか？')){
							return false;
						}
					}
					if(this.playerSelected.id == undefined){
						this.action.error = '誰かを指定してください。その人の無事を祈りながら眠りましょう。';
					}
					break;
				}
				case this.const.actions.consider:{
					if(this.playerSelected.id == undefined){
						this.action.error = '誰かを指定してください。その人について国会で検討しましょう。';
					}
					break;
				}
				case this.const.actions.attack:{
					if(this.playerSelected.id == undefined){
						this.action.error = '襲撃する人を指定してください。';
					}else if(this.playerSelected.role.id == 1){
						this.action.error = '襲撃対象に人狼を指定することはできません。';
					}else if(this.playerSelected.flgDead == 1){
						this.action.error = '投獄されていない人を指定してください。';
					}
					break;
				}
				case this.const.actions.save:{
					if(this.playerSelected.id == undefined){
						this.action.error = '守る対象を指定してください。';
					}else if(this.playerSelected.flgDead == 1){
						this.action.error = '投獄されていない人を指定してください。';
					}
					break;
				}
				case this.const.actions.expose:{
					if(this.playerSelected.id == undefined){
						this.action.error = '霊媒する対象を指定してください。';
					}else if(this.playerSelected.flgDead == 0){
						this.action.error = '投獄されている人を指定してください。';
					}
					break;
				}
				case this.const.actions.predict:{
					if(this.playerSelected.id == undefined){
						this.action.error = '占う対象を指定してください。';
					}else if(this.playerSelected.flgDead == 1){
						this.action.error = '投獄されていない人を指定してください。';
					}
					break;
				}
				case this.const.actions.freedom:{
					if(this.playerSelected.id == undefined){
						this.action.error = '救う対象を指定してください。';
					}else if(this.playerSelected.flgDead == 0){
						this.action.error = '投獄されている人を指定してください。';
					}
					break;
				}
				default:{
					break;
				}
			}
			return true;
		},
		showConfirm(message){
			this.dialog.confirm.message = message;
			this.dialog.confirm.show = true;
			this.dialog.confirm.ret = undefined;
			return new Promise((resolve) => {
				if(this.dialog.confirm.ret != undefined){
					alert('a');
				}
			});
		},
		showRoleInfo(role){
			this.dialog.roomInfo.role = JSON.parse(JSON.stringify(role));
			document.getElementById('roledescription').classList.remove('scaleHide');
			document.getElementById('roledescription').classList.add('scaleShow');
		},
		clearRoleInfo(){
			this.dialog.roomInfo.role.name = '';
			this.dialog.roomInfo.role.img = '';
			this.dialog.roomInfo.role.command = [];
			this.dialog.roomInfo.role.description = this.dialog.roomInfo.role.defaultdescription;
			document.getElementById('roledescription').classList.remove('scaleShow');
			document.getElementById('roledescription').classList.add('scaleHide');
		},
		login(){
			this.dialog.login.error = '';
			if(this.dialog.login.pass == ''){
				this.dialog.login.error = 'パスワードを入力してください';
				this.se.Error.play();
				return false;
			}

			axios
			.post(this.rootPath + '/api/v1/play/login', {
				playerid: this.playerid,
				pass: this.dialog.login.pass,
			})
			.then((response) => {
				try {
					if(response.data.code == 0 && response.data.token != ''){
						this.authtoken = response.data.token;
						localStorage.setItem(this.const.authTokenName, response.data.token);
						this.dialog.login.success = true;
						this.dialog.login.show = false;
						this.refleshStatus();
						this.se.Success.play();
						this.dialog.myCard.show = true;
					}
					else if(response.data.error != undefined){
						this.dialog.login.show = true;
						this.dialog.login.error = response.data.error;
						this.se.Error.play();
					}else{
						this.error = '特定できないエラーが発生しました。';
						this.se.Error.play();
					}
				} catch (e) {
					this.error = e;
				}
			})
			.catch((err) => {
				console.log(err);
			});
		},
		logout(){
			localStorage.setItem(this.const.authTokenName, '');
			this.refleshStatus();
			this.se.Action.play();
		},
		getRole(roleid){
			let ret = undefined;
			this.const.roles.forEach((role) => {
				if(roleid == role.id){
					ret = role;
				}
			});
			return ret;
		},
		selectPlayer(player){
			if(this.me.id == player.id) { 
				if(this.me.role.power){
					this.isUsingPower = !this.isUsingPower; 
				}else{
					this.error = '他のプレイヤーを見破る力を持っていません。';
					setTimeout(()=>{
						this.error = '';
					},2000);
				}
				this.playerSelected ={}; 
			}else{ 
				this.playerSelected = player; 
			}
		},
		checkString: function (inputdata) {
			var regExp = /^[a-zA-Z0-9_]*$/;
			return regExp.test(inputdata);
		},
		checkMaxLength: function (inputdata) {
			var booleanLength = false;
			inputdata.length <= 15
				? (booleanLength = true)
				: (booleanLength = false);
			return booleanLength;
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
<style type="scss" scoped>
@import '../../scss/play.scss';
</style>
<template>
	<div 
	id="app" 
	style="background-size: cover;"
	:style="{ backgroundImage: `url('${this.rootPath}/image/bg${this.room.time}.jpg')` }">
		<header>
			<Header></Header>
		</header>

		<v-progress-linear
		v-model="this.reflesh.countValue"
		color="purple">
		</v-progress-linear>
		<div>
			{{ this.room.day }}日目 {{ this.const.times[this.room.time] }}
		</div>
		<div id="players">
			<div
			v-for="player in this.players"
			:key="player"
			style="float:left;"
			>
				<div
				v-if="player.sex == ''"
				class="player"
				>
					<img 
					class="icon" 
					:src="rootPath + '/image/avatar/random.png'"
					/>
					<div style="font-size:smaller;">
						?
					</div>
				</div>
				<div
				v-else
				class="player"
				v-bind:class="[this.playerSelected.id == player.id ? 'selectedOn' : 'selectedOff']"
				@click="selectPlayer(player)"
				>
					<div 
					class="votenum"
					:class="[info.time == 0 ? 'show' : 'hide']"
					:style="{ display: [ player.flgDead == 1 ? `none`: `block` ]}"
					>
						{{ player.votenum }}
					</div>
					<div 
					v-if="isUsingPower"
					class="roleShortName">
						<span v-if="player != undefined">
							<span v-if="player.role != undefined">
								{{ player.role.shortName }}
							</span>
						</span>
					</div>
					<div 
					class="attacked"
					:class="[player.attacked == 9 ? 'show' : 'hide', ]">
						<img 
						:src="rootPath + '/image/attacked.png'" style="width:50%; height:50%;" />
					</div>
					<img 
					class="icon"
					:src="rootPath + '/image/avatar/' + player.sex + '/icon' + player.img.toString().padStart( 2, '0') + '.png'"
					:class="[player.flgDead == 1 ? 'dead' : '', player.done == 1 ? 'done' : '']"
					:style="{ boxShadow: [ isUsingPower ? `0px 5px 10px ${player.role.color}`: `` ]}"
					/>
					<div 
					style="font-size:smaller;"
					:style="{ color: [ this.me.id == player.id ? `yellow`: `white` ]}"
					>
						{{ player.name }}
					</div>
				</div>
			</div>
			<br style="clear:both;" />
		</div>

		<!--勝敗表示-->
		<div
		v-bind:class="[this.dialog.win.show ? 'scaleShow' : 'scaleHide']"
		style="margin:10px: padding:10px;"
		>
			<div style="font-size:30px;">
				<div>{{ this.dialog.win.team }}チームの勝利です！</div>
			</div>
			<div class="win">
				<div 
				v-if="this.dialog.win.players != undefined"
				style="clear:left;">
					<div 
					style="float:left; margin:10px; text-align:center;"
					v-for="n in this.dialog.win.players.length" :key="n"
					>
						<img :src="rootPath + '/image/avatar/' + this.dialog.win.players[n-1].sex + '/icon' + this.dialog.win.players[n-1].img.toString().padStart( 2, '0') + '.png'"
						class="icon"
						:style="{ boxShadow: `0px 5px 10px ${this.dialog.win.players[n-1].role.color}` }"
						/>
						<div>
							{{ this.dialog.win.players[n-1].name }}[{{ this.dialog.win.players[n-1].role.name }}]
						</div>
					</div>
				</div>
			</div>
		</div>
		<br style="clear:left;" />
		<div id="information">
			{{ this.info.message }}
		</div>

		<!-- ログインダイアログ-->
		<v-dialog
		v-model="this.dialog.login.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card>
				<v-card-title
				color="primary"
				dark
				>
				<span class="text-h5">認証</span>
				</v-card-title>
				<v-card-text>
					<v-container>
						<v-row>
							<v-col cols="12">
								<v-text-field
								label="Password*"
								type="password"
								v-model="this.dialog.login.pass"
								required
								></v-text-field>
							</v-col>
						</v-row>
						<v-row>
							<div v-for="n in 10" style="float:left; margin:5px;">
								<v-btn @click="this.dialog.login.pass += (n%10) + ''">{{ n%10 }}</v-btn>
							</div>
							<div style="float:left; margin:5px;">
								<v-btn @click="this.dialog.login.pass = ''">Clear</v-btn>
							</div>
						</v-row>
					</v-container>
					<div class="error">
						{{this.dialog.login.error}}
					</div>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="location.href='../room'"
					>
						戻る
					</v-btn>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="login()"
					>
						ログイン
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

		<!--部屋情報-->
		<v-dialog 
		v-model="this.dialog.roomInfo.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card width="320" height="700">
				<v-card-title>
					部屋名：{{ this.room.name }}
				</v-card-title>
				<v-card-text style="overflow-y: auto;">
					<div id="roles">
						<div
						v-for="role in this.room.roles"
						:key="role"
						style="float:left;"
						>
							<div 
							class="role"
							:style="{ backgroundImage: `url('${role.img}')` }"
							@click="showRoleInfo(role);"
							>
								<div class="num">
									{{ role.num }}
								</div>
								<div 
								class="name" 
								:style="{ boxShadow: `0px 5px 10px ${role.color}` }">
									<span v-if="role.team == 1" style="color:red;">
										{{ role.name }}
									</span>
									<span v-else style="color:yellow;">
										{{ role.name }}
									</span>
								</div>
							</div>
						</div>
					</div>
					<div id="roledescription">
						<div v-if="this.dialog.roomInfo.role.name != ''">
							<div>
								{{ this.dialog.roomInfo.role.name }}
							</div>
							<img
							style="width:280px; height: auto;"
							:src="this.dialog.roomInfo.role.img" 
							/>
							<div>
								{{ this.dialog.roomInfo.role.description }}
							</div>
							深夜に選択可能な行動：
							<span
							v-for="command in this.dialog.roomInfo.role.command"
							:key="command"
							>
								{{ command.name }} &nbsp;
							</span>
							<div style="text-align:right;">
								<v-btn
								color="blue-darken-1"
								variant="text"
								@click="this.clearRoleInfo();"
								>
									閉じる
								</v-btn>
							</div>
						</div>
					</div>
					<div style="clear:left;">
						<div>
							<span style="color:red;">赤文字表示</span>
							<span>人狼チーム</span>
						</div>
						<div>
							<span style="color:yellow;">黄文字表示</span>
							<span>村人チーム</span>
						</div>
					</div>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.dialog.roomInfo.show = false; this.clearRoleInfo();"
					>
						閉じる
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

		<!--自カード情報-->
		<v-dialog 
		v-model="this.dialog.myCard.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card width="320" height="580">
				<v-card-title>
					あなたの役割
				</v-card-title>
				<v-card-text style="overflow-y: auto;">
					<div 
					class="role"
					style="width:260px; height:260px;"
					:style="{ backgroundImage: `url('${this.me.role.img}')` }"
					>
						<div style="background-color:rgba(0, 0, 0, 0.5);">
							{{ this.me.role.name }}
						</div>
					</div>
					<div style="width:100%; text-align:cener; background-color:rgba(0, 0, 0, 0.5);">
						{{ this.me.role.description }}
					</div>
					深夜に選択可能な行動：
					<span
					v-for="command in this.me.role.command"
					:key="command"
					>
						{{ command.name }} &nbsp;
					</span>
					<div style="border:solid thin gray; border-radius: 5px; padding:5px;">
						勝利条件
						<div v-if="this.me.role.team == 1">
							人狼チームです。<br>
							投獄されていないプレイヤーの数が、村人チームと同数以上になったら勝利します。
						</div>
						<div v-else>
							村人チームです。<br>
							役割が人狼のプレイヤーを全て投獄すると勝利します。
						</div>
					</div>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.dialog.myCard.show = false;"
					>
						閉じる
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

		<!-- 投票結果-->
		<v-dialog
		v-model="this.dialog.result.vote.show"
		transition="dialog-top-transition"
		max-width="400"
		>
			<v-card width="320" height="400">
				<v-card-title>
					投票結果
				</v-card-title>
				<v-card-text 
				:style="{ backgroundImage: 'url(' + rootPath + '/image/jail.jpg)' }"
				class="jail">
					{{ this.dialog.result.vote.player.name }}さんが投獄されました。
					<img :src="rootPath + '/image/avatar/' + this.dialog.result.vote.player.sex + '/icon' + this.dialog.result.vote.player.img.toString().padStart( 2, '0') + '.png'"
					class="icon"
					/>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.act(this.const.actions.confirmVoteResult); this.dialog.result.vote.show = false;"
					>
						確認
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

		<!-- 襲撃結果-->
		<v-dialog
		v-model="this.dialog.result.action.show"
		transition="dialog-top-transition"
		max-width="500"
		>
			<v-card maxWidth="500" height="500">
				<v-card-title>
					結果
				</v-card-title>
				<v-card-text>
					<div v-if="this.dialog.result.action.attackedPlayers.length > 0"
					:style="{ backgroundImage: 'url(' + rootPath + '/image/jail.jpg)' }"
					class="confine">
						以下のプレイヤーが人狼に拉致されて投獄されました。
						<div style="clear:left;">
							<div
							style="float:left;text-align:center;"
							v-for="player in this.dialog.result.action.attackedPlayers"
							:key="player"
							>
								<img :src="rootPath + '/image/avatar/' + player.sex + '/icon' + player.img.toString().padStart( 2, '0') + '.png'"
								class="icon"
								/>
								<div>
									{{ player.name }}
								</div>
							</div>
						</div>
					</div>
					<div v-if="this.dialog.result.action.freedomPlayers.length > 0"
					:style="{ backgroundImage: 'url(' + rootPath + '/image/freedom.jpg)' }"
					class="freedom">
						以下のプレイヤーが天使により救われました。
						<div style="clear:left;">
							<div 
							style="float:left;text-align:center;"
							v-for="player in this.dialog.result.action.freedomPlayers"
							:key="player"
							>
								<img :src="rootPath + '/image/avatar/' + player.sex + '/icon' + player.img.toString().padStart( 2, '0') + '.png'"
								class="icon"
								/>
								<div>
									{{ player.name }}
								</div>
							</div>
						</div>
					</div>
					<ul 
					style="margin-left:10px;list-style-type:none;"
					v-for="message in this.dialog.result.action.message"
					:key="message"
					>
						<li>
							{{ message }}
						</li>
					</ul>
				<!--
				-->
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.act(this.const.actions.confirmActionResult); this.dialog.result.action.show = false;"
					>
						確認
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

		<!-- 占い結果-->
		<v-dialog
		v-model="this.dialog.predict.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card maxWidth="400" height="400">
				<v-card-title>
					占い結果
				</v-card-title>
				<v-card-text
				style="background-size: contain; background-position: center; text-shadow: 0 0 5px black;"
				:style="{ backgroundImage: `url('${this.dialog.predict.target.role.img}')` }">
					<div>
						<img
						style="width:100px; height:100px; border-radius: 50%;"
						:src="rootPath + '/image/avatar/' + this.dialog.predict.target.sex + '/icon' + this.dialog.predict.target.img.toString().padStart( 2, '0') + '.png'"
						/>
					</div>
					{{ this.dialog.predict.target.name }}さんは、{{ this.dialog.predict.target.role.name }}です。
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.dialog.predict.show = false"
					>
						確認
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

		<!-- 霊媒結果-->
		<v-dialog
		v-model="this.dialog.expose.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card maxWidth="400" height="400">
				<v-card-title>
					霊媒結果
				</v-card-title>
				<v-card-text
				style="background-size: contain; background-position: center; text-shadow: 0 0 5px black;"
				:style="{ backgroundImage: `url('${this.dialog.expose.target.role.img}')` }">
				<div>
					<img
					style="width:100px; height:100px; border-radius: 50%;"
					:src="rootPath + '/image/avatar/' + this.dialog.expose.target.sex + '/icon' + this.dialog.expose.target.img.toString().padStart( 2, '0') + '.png'"
					/>
				</div>
					{{ this.dialog.expose.target.name }}さんは、{{ this.dialog.expose.target.role.name }}です。
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.dialog.expose.show = false"
					>
						確認
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>

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
					@click="this.logout();"
					>
						ログアウト
					</v-btn>
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

		<!--確認ダイアログ-->
		<v-dialog
		v-model="this.dialog.confirm.show"
		transition="dialog-top-transition"
		max-width="400"
		class="dialog"
		>
			<v-card maxWidth="400" height="400">
				<v-card-text style="overflow-y: auto;">
					{{ this.dialog.confirm.message }}
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.dialog.confirm.ret = true; this.dialog.confirm.show = false;"
					>
						はい
					</v-btn>
					<v-btn
					color="blue-darken-1"
					variant="text"
					@click="this.dialog.confirm.ret = false; this.dialog.confirm.show = false;"
					>
						いいえ
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>


		<div id="controll">
			<div v-if="this.me.flgDead == 1">
				あなたは投獄されています。他のプレイヤーの動向を見守りましょう
			</div>
			<div v-else>
				<!--未入室-->
				<v-row :class="[this.info.time==-1 ? 'scaleShow': 'scaleHide']">
					<v-col>
						<v-btn
						@click="this.dialog.roomInfo.show = true;"
						>
							部屋情報
						</v-btn>
					</v-col>
					<v-col>
						<v-btn
						@click="this.dialog.myCard.show = true;"
						>
							自カード
						</v-btn>
					</v-col>
				</v-row>
				<!--夕刻-->
				<v-row :class="[this.info.time==0 ? 'scaleShow': 'scaleHide']">
					<v-col>
						<v-btn
						@click="this.act(this.const.actions.vote);"
						>
							投票
						</v-btn>
					</v-col>
					<v-col>
						<v-btn
						@click="this.dialog.roomInfo.show = true;"
						>
							部屋情報
						</v-btn>
					</v-col>
					<v-col>
						<v-btn
						@click="this.dialog.myCard.show = true;"
						>
							自カード
						</v-btn>
					</v-col>
				</v-row>
				<!--夜-->
				<v-row :class="[this.info.time==1 ? 'scaleShow': 'scaleHide']">
					<v-col>
						<v-btn
						@click="this.dialog.result.vote.show = true;"
						>
							投票結果確認
						</v-btn>
					</v-col>
				</v-row>
				<!--深夜-->
				<v-row :class="[this.info.time==2 ? 'scaleShow': 'scaleHide']">
					<v-col 
					v-for="command in this.me.role.command"
					:key="command">
						<v-btn
						@click="this.act(command.action);"
						>
							{{ command.name }}
						</v-btn>
					</v-col>
					<v-col>
						<v-btn
						@click="this.dialog.roomInfo.show = true;"
						>
							部屋情報
						</v-btn>
					</v-col>
					<v-col>
						<v-btn
						@click="this.dialog.myCard.show = true;"
						>
							自カード
						</v-btn>
					</v-col>
				</v-row>
				<!--朝-->
				<v-row :class="[this.info.time==3 ? 'scaleShow': 'scaleHide']">
					<v-col>
						<v-btn
						@click="this.dialog.result.action.show = true;"
						>
							襲撃結果確認
						</v-btn>
					</v-col>
				</v-row>
				<!--昼-->
				<v-row :class="[this.info.time==4 ? 'scaleShow': 'scaleHide']">
					<v-col>
						<!--
						<v-btn
						@click="this.dialog.win.show = true;"
						>
							勝敗結果確認
						</v-btn>
						-->
					</v-col>
				</v-row>
				<div class="actionResult">
					<div class="message">
						{{ this.action.message }}
					</div>
					<div class="error">
						{{ this.action.error }}
					</div>
				</div>
			</div>
			<div class="error">
				{{ this.error }}
			</div>
		</div>
		<footer @click="this.dialog.copyright.show = true;">
			<Footer></Footer>
		</footer>
	</div>
</template>
