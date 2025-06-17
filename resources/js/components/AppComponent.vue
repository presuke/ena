<template>

    
    <v-app class="rounded rounded-md">
  
      <!-- 上段バー -->
      <v-app-bar>
        <v-toolbar-title>Ena-Save</v-toolbar-title>
        <v-btn icon="mdi-menu" @click="drawer = !drawer">
        </v-btn>
      </v-app-bar>
      <v-navigation-drawer
        v-model="drawer"
        location="right"
        fixed
        temporary
      >
        <v-list
          nav
          dense
        >
          <v-list-item-group>
            <v-list-item>
              <v-list-item-title>{{ this.dialog.login.user.email }}</v-list-item-title>
            </v-list-item>
          </v-list-item-group>
          <v-list-item-group>
            <v-list-item>
              <v-btn 
              color="primary" 
              variant="outlined"
              block 
              @click="openSetting()"
              >
                設定
              </v-btn>
            </v-list-item>
          </v-list-item-group>
          <v-list-item-group v-if="hybridInverters != -1 && this.hybridInverters != 0">
            <v-list-item
            v-for="hybridInverter in hybridInverters" :key="hybridInverter.no"
            >
              <v-btn 
              color="primary" 
              variant="outlined"
              block 
              @click="selectInverter(hybridInverter)"
              >
                {{ hybridInverter.no }}号機
              </v-btn>
            </v-list-item>
          </v-list-item-group>
          <v-list-item-group>
            <v-list-item>
                <v-btn 
                variant="outlined"
                color="primary" 
                block 
                @click="dialog.logout.show = true"
                >
                ログアウト
                </v-btn>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-navigation-drawer>

      <!-- メインコンテンツ -->
      <v-main> 
          <div  class="text-center">
            <div v-if="hybridInverters == -1" style="padding:10px; text-align:center;">
                <!--
                <v-progress-circular color="primary" indeterminate></v-progress-circular>loading...
                -->
            </div>
            <div v-else-if="hybridInverters == 0">
                <img src="image/img_hi.png" style="filter: grayscale(100%);" />
                このアカウントはハイブリッドインバータを保有していません。
            </div>
            <v-card 
            v-else
            elevation="16"
            id="contents"
            >
                <v-card-title 
                class="d-flex justify-space-between align-center">
                    <div class="text-h5 text-medium-emphasis ps-2">
                        {{selectedHybridInverter.no}}号機
                    </div>
                </v-card-title>
                <v-card-item>
                    <!--current data-->
                    <div 
                    v-for="hybridInverter in hybridInverters" :key="hybridInverter.no"
                    style="float:left;">
                        <v-card 
                        color="#333"
                        elevation="8"
                        id="current"
                        :calss="[selectedHybridInverter.no==hybridInverter.no ? 'selectedHivridInvertor' : 'selectedHivridInvertor']"
                        >
                            <v-card-item 
                            style="max-width:800px; color:#fff;">
                                <div style="width:min(12vw, 120px); float:left;" :style="[hybridInverter.pv_power > 0 ? '' : 'filter:brightness(50%);']">
                                    <img src="/image/icon_solor.png" class="iconModule">
                                    <div class="fontMain">{{Math.floor(hybridInverter.pv_power).toLocaleString()}}w</div>
                                    <div class="fontSub">{{hybridInverter.pv_voltage.toLocaleString()}}V</div>
                                    <div class="fontSub">{{hybridInverter.pv_current.toLocaleString()}}A</div>
                                </div>
                                <div style="width:min(18vw, 180px); height: min(16vw, 160px); float:left;">
                                    <div style="width:100%; height:min(7.5vw, 75px);"><br /></div>
                                    <div class="box" style="width:100%; height:100%;" v-if="hybridInverter.pv_power > 0">
                                        <span class="box__line"></span>
                                        <span class="box__line"></span>
                                    </div>
                                </div>
                                <div style="width:min(12vw, 120px); clear:left; float:left;" :style="[hybridInverter.grid_input_current > 0 ? '' : 'filter:brightness(50%);']">
                                    <img src="/image/icon_grid.png" class="iconModule">
                                    <div class="fontMain">{{(hybridInverter.grid_voltage * hybridInverter.grid_input_current).toLocaleString(undefined, { maximumFractionDigits: 0 })}}VA</div>
                                    <div class="fontSub">{{hybridInverter.grid_voltage.toLocaleString()}}V</div>
                                    <div class="fontSub">{{hybridInverter.grid_input_current.toLocaleString()}}A</div>
                                    <div class="fontSub">({{hybridInverter.grid_frequency}}Hz)</div>
                                </div>
                                <div style="width:min(12vw, 120px); float:left;">
                                    <div style="width:100%; height:min(7.5vw, 75px);"><br /></div>
                                    <div class="box">
                                        <span class="box__line" v-if="hybridInverter.grid_input_current > 0"></span>
                                        <br />
                                    </div>
                                </div>
                                <div style="width:min(12vw, 120px); float:left;">
                                    <!-- hi-->
                                    <div>
                                        <img src="/image/icon_hi.png" class="iconModule">
                                    </div>
                                    <!--line-->
                                    <div style="float:left;">
                                        <div style="margin-left:min(7vw, 70px); height:min(5vw, 50px);">
                                            <div class="box" style="float: left; width: 1vw; height:100%;" v-if="hybridInverter.battery_current < 0">
                                                <span class="box__line" style="display: none;"></span>
                                                <span class="box__line"></span>
                                            </div>
                                            <div class="box" style="float: left; width: 1vw; height:100%; transform: scaleY(-1);" v-if="hybridInverter.battery_current > 0">
                                                <span class="box__line" style="display: none;"></span>
                                                <span class="box__line"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- batt -->
                                    <div style="width:min(30vw, 300px); clear:left;">
                                        <div class="fontMain" style="width:min(18vw, 180px);" v-if="hybridInverter.battery_current < 0">
                                            <div v-if="hybridInverter.grid_battery_charge_current > 0">
                                                🔌{{Math.floor(hybridInverter.grid_battery_charge_current * hybridInverter.grid_voltage).toLocaleString()}}VA
                                            </div>
                                            <div v-if="hybridInverter.pv_battery_charge_current > 0">
                                                🌞{{Math.floor(hybridInverter.pv_battery_charge_current * hybridInverter.battery_voltage).toLocaleString()}}VA
                                            </div>
                                            <div style="clear:left;"></div>
                                        </div>
                                        <div class="fontMain" style="width:min(15vw, 150px);" v-if="hybridInverter.battery_current > 0">
                                            {{(hybridInverter.battery_voltage * hybridInverter.battery_current).toLocaleString(undefined, { maximumFractionDigits: 0 })}}VA
                                        </div>
                                        <div style="margin-top:min(3vw, 30px); width:min(15vw, 150px); float:left;">
                                            <img src="/image/icon_battery.png" class="iconModule">
                                            <div class="fontMain" style="z-index:10;">{{hybridInverter.battery_voltage.toLocaleString()}}V</div>
                                            <v-progress-linear
                                            v-model="hybridInverter.battery_soc"
                                            :color="getColorBattSoc(hybridInverter.battery_soc)"
                                            height="20"
                                            >
                                                <strong >{{ hybridInverter.battery_soc }}%</strong>
                                            </v-progress-linear>
                                            <!--
                                            <v-progress-circular
                                                :model-value="hybridInverter.battery_soc"
                                                :color="[hybridInverter.battery_soc<30?'#f33':hybridInverter.battery_soc<50?'#f93':hybridInverter.battery_soc<70?'#cf3':'#6f6']"
                                                style="margin-left:-10px; margin-top:-10px; clear:left; width:min(5vw, 50px); height:min(5vw, 50px);">
                                                <div style="color:white; z-index:1; text-shadow:0 0 4px #fff, 0 0 8px #ccc, 0 0 12px #999;">{{ Math.ceil(hybridInverter.battery_soc)}}%</div>
                                            </v-progress-circular>
                                            -->
                                        </div>
                                    </div>
                                </div>
                                <div style="width:min(12vw, 120px); height:min(12vw, 120px); float:left;">
                                    <div style="width:100%; height:min(7.5vw, 75px);"><br /></div>
                                    <div class="box" style="width:100%; height:100%;">
                                        <span class="box__line" v-if="hybridInverter.inverter_power > 0"></span>
                                        <br />
                                    </div>
                                </div>
                                <div style="width:min(12vw, 120px); height:min(16vw, 160px); float:left;">
                                    <img src="/image/icon_use.png" class="iconModule">
                                    <div class="fontMain">{{Math.floor(hybridInverter.inverter_power).toLocaleString(undefined, { maximumFractionDigits: 0 })}}w</div>
                                    <div style="height:min(3vw, 50px);"></div>
                                    <div class="fontSub">{{(hybridInverter.inverter_voltage * hybridInverter.inverter_current).toLocaleString(undefined, { maximumFractionDigits: 0 })}}VA</div>
                                    <div class="fontSub">{{hybridInverter.inverter_voltage.toLocaleString()}}v</div>
                                    <div class="fontSub">{{hybridInverter.inverter_current.toLocaleString()}}A</div>
                                    <div class="fontSub">({{hybridInverter.inverter_frequency}}Hz)</div>
                                </div>
                                <div style="color:#666; position: absolute; right: 60; bottom: 0;">{{hybridInverter.create_at}}</div>
                            </v-card-item>
                        </v-card>
                    </div>
                    <!--chart data-->
                    <v-card 
                    elevation="16"
                    id="chart"
                    variant="outlined"
                    >
                        <v-card-item>
                            <v-row align="center">
                                <v-col cols="auto">
                                <v-btn icon @click="moveDate(-1)">
                                    <v-icon>mdi-arrow-left</v-icon>
                                </v-btn>
                                </v-col>
                                <v-col>
                                <v-text-field
                                    v-model="dialog.datepicker.dateFormatted"
                                    label="日付を選択"
                                    @click="dialog.datepicker.show = true"
                                    readonly
                                    style="height: 50px;"
                                ></v-text-field>
                                </v-col>
                                <v-col cols="auto">
                                <v-btn icon @click="moveDate(1)">
                                    <v-icon>mdi-arrow-right</v-icon>
                                </v-btn>
                                </v-col>
                            </v-row>
                            <v-dialog v-model="dialog.datepicker.show">
                                <v-date-picker v-model="dialog.datepicker.date" @change="setDate"></v-date-picker>
                            </v-dialog>
                            <v-tabs
                                center-active
                                v-model="chart.tabIndex"
                                >
                                <v-tab value="0" @click="viewChart()">推移</v-tab>
                                <v-tab value="1" @click="viewChart()">累積</v-tab>
                                <v-tab value="2" @click="viewChart()">バッテリー</v-tab>
                            </v-tabs>
                            <v-card class="chartArea" style="overflow-y: hidden;">
                                <div id="chart0">
                                    <div class="chartArea">
                                        <canvas id="chartA"></canvas>
                                    </div>
                                </div>
                                <div id="chart1">
                                    <div class="chartArea">
                                        <canvas id="chartB"></canvas>
                                    </div>
                                </div>
                                <div id="chart2">
                                    <div class="chartArea">
                                        <canvas id="chartC"></canvas>
                                    </div>
                                </div>
                                <div id="chart3">
                                    <div class="chartArea">
                                        Loading...
                                    </div>
                                </div>
                            </v-card>
                        </v-card-item>
                    </v-card>
                </v-card-item>
            </v-card>
        </div>
        <!---Login-->
        <v-dialog 
        persistent
        v-model="dialog.login.show" 
        width="500">
            <v-card>
                <v-card-title>ログイン</v-card-title>
                <v-card-text>
                    <v-text-field label="メールアドレス" v-model="dialog.login.email" type="email" required></v-text-field>
                    <v-text-field label="パスワード" v-model="dialog.login.password" type="password" required></v-text-field>
                    <v-card-text style="color:red;">{{this.dialog.login.message}}</v-card-text>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="login">ログイン</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!---LoginContinue-->
        <v-dialog 
        v-model="dialog.loginContinue.show"
        width="500">
            <v-card>
                <v-card-text>
                    <div>ログインの有効期限はあと{{ dialog.loginContinue.limit }}分です。</div>
                    <div>ログインを継続しますか？</div>
                    <v-card-text style="color:red;">{{this.dialog.loginContinue.message}}</v-card-text>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="loginContinue()">はい</v-btn>
                    <v-btn color="primary" @click="logout()">ログアウトする</v-btn>
                    <v-btn color="primary" @click="dialog.loginContinue.show = false">閉じる</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!---Logout-->
        <v-dialog 
        persistent
        v-model="dialog.logout.show"
        width="300">
            <v-card>
                <v-card-title>ログアウト</v-card-title>
                <v-card-text>
                    ログアウトしますか？
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="logout()">はい</v-btn>
                    <v-btn color="primary" @click="dialog.logout.show = false">いいえ</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!--Setting-->
        <v-dialog v-model="dialog.setting.show">
            <v-card>
                <v-card-title>ハイブリッドインバータ設定</v-card-title>
                <v-card-text>
                <div class="accordion">
                <h3>リモート設定</h3>
                <div>
                    <fieldset>
                        <div style="border-bottom: solid thin gray;">蓄電のための電源</div>
                        <div class="cp_ipradio02">
                            <label><input type="radio" class="option-input radio" name="setting_once_chargerPriority" v-model="dialog.setting.once.chargerPriority" value="0" />PV優先</label>
                            <label><input type="radio" class="option-input radio" name="setting_once_chargerPriority" v-model="dialog.setting.once.chargerPriority" value="1" />Grid優先</label>
                            <label><input type="radio" class="option-input radio" name="setting_once_chargerPriority" v-model="dialog.setting.once.chargerPriority" value="2" />ハイブリッド</label>
                            <label><input type="radio" class="option-input radio" name="setting_once_chargerPriority" v-model="dialog.setting.once.chargerPriority" value="3" />PVのみ</label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <div style="border-bottom: solid thin gray;">消費のための電源</div>
                        <div class="cp_ipradio02">
                            <label><input type="radio" class="option-input radio" name="setting_once_outputPriority" v-model="dialog.setting.once.outputPriority" value="0" />PV優先</label>
                            <label><input type="radio" class="option-input radio" name="setting_once_outputPriority" v-model="dialog.setting.once.outputPriority" value="1" />Grid優先</label>
                            <label><input type="radio" class="option-input radio" name="setting_once_outputPriority" v-model="dialog.setting.once.outputPriority" value="2" />バッテリー優先</label>
                        </div>
                    </fieldset>
                    <div v-if="this.dialog.setting.once.message != ''" style="margin-top:10px; font-size:smaller; color:gray;">
                        {{this.dialog.setting.once.message}}
                    </div>
                    <v-btn @click="settingOnce(0)">設定</v-btn>
                    <v-btn @click="settingOnce(1)">設定取消</v-btn>
                </div>
                <h3>深夜電力利用設定</h3>
                <div>
                    <div style="border-bottom:solid thin gray;">
                        深夜電力
                    </div>
                    <div style="margin:10px;">
                        <select v-model="dialog.setting.ever.midnightSt">
                            <option v-for="n in 6 " :key="n">{{n+18}}</option>
                        </select>
                        ～
                        <select v-model="dialog.setting.ever.midnightEd">
                            <option v-for="n in 9 " :key="n">{{n}}</option>
                        </select>時
                    </div>
                    <div style="border-bottom:solid thin gray;">
                        充電条件
                    </div>
                    <div style="margin:5px 0px 10px 10px;">
                        <div style="background-color:#eee;">
                            🔌充電開始
                        </div>
                        <div style="margin: 5px 0px 5px 10px;">
                            <div>
                                バッテリ電圧が
                                <select v-model="dialog.setting.ever.voltageGridingSt">
                                    <option v-for="n in 100 " :key="n">{{(n/10)+48}}</option>
                                </select>V未満
                            </div>
                            <div>
                                または深夜時間開始時
                                <input class="toggle-input" type='checkbox' v-model="dialog.setting.ever.forceSt" />
                            </div>
                        </div>
                        <div style="background-color:#eee;">
                            🚫充電終了
                        </div>
                        <div style="margin:5px 0px 5px 10px;">
                            <div>
                                バッテリ電圧が
                                <select v-model="dialog.setting.ever.voltageGridingEd">
                                    <option v-for="n in 100 " :key="n">{{(n/10)+48}}</option>
                                </select>V以上
                            </div>
                            <div>
                                または深夜時間終了時
                                <input class="toggle-input" type='checkbox' v-model="dialog.setting.ever.forceEd" />
                            </div>
                        </div>
                    </div>
                    <div v-if="this.dialog.setting.ever.message != ''" style="margin-top:10px; font-size:smaller; color:gray;">
                        {{this.dialog.setting.ever.message}}
                    </div>

                    <v-btn @click="settingEver(0)">設定</v-btn>
                    <v-btn @click="settingEver(1)">設定取消</v-btn>
                </div>
                <!--
                <h3>安く使う設定</h3>
                <div>
                    <div>
                        エリア：<select id="area1">
                            <option value="1">東京</option>
                            <option value="2">東北</option>
                            <option value="3">中部</option>
                            <option value="4">東海</option>
                            <option value="5">関西</option>
                        </select>
                        　<a href="javascript:openPriceList('area1');">料金表</a>
                    </div>
                    <div>
                        料金：
                        <select>
                            <option v-for="value in form.select.prices" :key="value" :value="value">
                                {{ value }}
                            </option>
                        </select>
                        円/kwh以下
                    </div>
                    <div>
                        バッテリー充電度：
                        <select>
                            <option v-for="value in form.select.socs" :key="value" :value="value">
                                {{ value }}
                            </option>
                        </select>

                        ％以下
                    </div>
                    <v-btn>商用電力を使う</v-btn>
                </div>
                -->
            </div>
                </v-card-text>
                <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="this.dialog.setting.show = false">閉じる</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!--PriceList-->
        <v-dialog id="dialogPriceList" style="display: none;">
        <div style="border-bottom: solid thin gray;width:180px;"> <input type="text" id="datepickerGridPrice" class="datepicker" onSlelect="vueObj.getGridPriceData();"></div>
        <div v-if="gridPrices.length == 0" style="padding:10px;">
            <v-progress-circular color="primary" indeterminate></v-progress-circular>loading...
        </div>
        <div v-else>
            <div style="height:300px; overflow:scroll;">
                <div v-for="gridPrice in gridPrices" :key="gridPrice.idx">
                    <div style="width: 100px; clear: left; float: left;" :style="gridPrice.idx % 2 === 1 ? { backgroundColor: '#eeeeff' } : {}">{{ gridPrice.label }}</div>
                    <div style="float: left;" :style="gridPrice.idx % 2 === 1 ? { backgroundColor: '#eeeeff' } : {}">{{ gridPrice.price }}円/kwh</div>
                </div>
            </div>
        </div>
        </v-dialog>
      </v-main>  
      <!-- 下段ナビゲーション -->
      <v-bottom-navigation>
        <v-btn 
        href="https://github.com/presuke/ena/" 
        target="_blank"
        >
            presented by presuke.
        </v-btn>
      </v-bottom-navigation>
    </v-app>
  </template>
  
  <style scoped></style>
  

<script>
    var chartA = null
    var chartB = null
    var chartC = null

    $(function() {
    })

    export default {
      data() {
        return {
          drawer: null,
          dialog: {
            login : {
              show: false,
              email: '',
              password: '',
              message: '',
              accessToken:'',
              localStorageKey: 'accessToken',
              user: {
                name: '',
                email: '',
              },
            },
            loginContinue:{
                show: false,
                expiration : null,
                limit: null,
                limitMunits: 10,
                message: '',
            },
            logout:{
                show: false,
            },
            datepicker:{
              show: false,
              date: '',
              dateOld: '',
              dateFormatted: '',
            },
            setting:{
                show: false,
                once: {
                    outputPriority: -1,
                    chargerPriority: -1,
                    message: '',
                },
                ever: {
                    midnightSt: 22,
                    midnightEd: 8,
                    voltageGridingSt: 52.5,
                    voltageGridingEd: 56.5,
                    forceSt: false,
                    forceEd: false,
                    message: '',
                },
            },
          },
          chart:{
            A: null,
            B: null,
            C: null,
            tabIndex: 0,
            hybridInverterData: null,
          },
          form: {
              name: '',
              select: {
                  prices: [],
                  socs: [],
              },
          },
          serverTime: 'xxx',
          screen: {
              width: 0,
              height: 0,
          },
          gridPrices: [],
          hybridInverters: -1,
          selectedHybridInverter: {},
          showControllBox: false,
          hybridInverterData: [],
          outputPriority: {
              0: 'PV優先',
              1: 'Grid優先',
              2: 'Batt優先',
          },
          chargerPriority: {
              0: 'CSO?',
              1: 'Grid',
              2: 'PV',
              3: 'OSO?',
          },
        }
      },
      mounted() {
        window.onload = () => {
            const today = new Date()
            const year = today.getFullYear()
            const month = String(today.getMonth() + 1).padStart(2, '0') // 月は0から始まるため+1が必要
            const day = String(today.getDate()).padStart(2, '0')
            this.dialog.datepicker.dateFormatted = `${year}/${month}/${day}`
            this.screen.height = document.body.clientHeight
            this.screen.width = document.body.clientWidth
            this.form.select.prices = this.generateValues(8, 16, 0.2)
            this.form.select.socs = this.generateValues(30, 80, 1)
            this.confirmAuth()
            this.handleResize()
        }
        window.addEventListener('resize', this.handleResize)
      },
      computed: {
        dateChanged() {
          if (this.dialog.datepicker.date !== this.dialog.datepicker.dateOld) {
            this.dialog.datepicker.dateOld = this.dialog.datepicker.date
            this.dialog.datepicker.show = false
            const date = new Date(this.dialog.datepicker.date)
            const year = date.getFullYear()
            const month = ('0' + (date.getMonth() + 1)).slice(-2)
            const day = ('0' + date.getDate()).slice(-2)
            this.dialog.datepicker.dateFormatted = `${year}/${month}/${day}`
            this.getInverterData()
            return true
          }

          this.dialog.datepicker.show = false
          return false
        },
      },
      watch: {
        dateChanged(newValue) {
          if (newValue) {
            console.log('日付が変更されました:', this.dialog.datepicker.date)
            // ここで必要な処理を実行します
            this.dialog.datepicker.show = false
          }
        },
      },
      methods:{
        handleResize(){
            //document.getElementById("chart").style.height = document.getElementById("current").offsetHeight
        },
        confirmAuth(){
          if(this.dialog.login.accessToken == ''){
            this.dialog.login.accessToken = localStorage.getItem(this.dialog.login.localStorageKey)
          }
          if(this.dialog.login.accessToken != ''){
            axios.request({
              method: 'get',
              maxBodyLength: Infinity,
              url: '/api/auth/confirm',
              headers: { 
                'Authorization': 'Bearer ' + this.dialog.login.accessToken
              }
            })
            .then((response) => {
              this.dialog.login.user = response.data
              this.dialog.login.show = false
              this.getMyHybridInverters()
              setInterval(this.getMyHybridInverters, 60000)
              setInterval(this.confirmLoginContinue, 60000)
            })
            .catch((error) => {
              this.dialog.login.message = ''
              this.dialog.login.show = true
            })

          }else{
            this.dialog.login.message = ''
            this.dialog.login.show = true
          }
        },
        login(){
          this.dialog.login.message = 'loading...'
          axios
            .post('/api/login', { 
            email: this.dialog.login.email, 
            password: this.dialog.login.password, 
            })
            .then((response) => {
                this.setAccessToken(response.data.authorisation.token)
                this.confirmAuth()
            })
            .catch((error) => {
            this.dialog.login.message = error.response.data.message
          })
        },
        setAccessToken(token){
            this.dialog.login.accessToken = token
            localStorage.setItem(this.dialog.login.localStorageKey, this.dialog.login.accessToken)
            this.confirmLoginContinue()
        },
        confirmLoginContinue(){
            const payload = JSON.parse(atob(this.dialog.login.accessToken.split('.')[1]));
            this.dialog.loginContinue.expiration = new Date(payload.exp * 1000)
            const limit = this.dialog.loginContinue.expiration - new Date()
            this.dialog.loginContinue.limit = Math.ceil(limit / 60 / 1000 )
            if(this.dialog.loginContinue.limit <= 0 ||
               this.dialog.loginContinue.limit > this.dialog.loginContinue.limitMunits
            ){
                this.dialog.loginContinue.show = false
            }else{
                this.dialog.loginContinue.show = true
            }
        },
        loginContinue(){
            axios.request({
              method: 'post',
              maxBodyLength: Infinity,
              url: '/api/refresh',
              headers: { 
                'Authorization': 'Bearer ' + this.dialog.login.accessToken
              }
            })
            .then((response) => {
                this.setAccessToken(response.data.authorisation.token)
            })
            .catch((error) => {
            this.dialog.loginContinue.message = error.response.data.message
          })
        },
        logout(){
            this.drawer = false
            this.dialog.login.accessToken = ''
            localStorage.setItem(this.dialog.login.localStorageKey, '')
            this.confirmAuth()
        },
        get_outputpriority(priority) {
            return this.outputPriority[priority]
        },
        get_chargepriority(priority) {
            return this.chargerPriority[priority]
        },
        getMyHybridInverters() {
            axios.request({
              method: 'get',
              maxBodyLength: Infinity,
              url: '/api/v1/log/getMyHybridInverters',
              headers: { 
                'Authorization': 'Bearer ' + this.dialog.login.accessToken
              }
            })
            .then((response) => {
                try {
                    if (response.data.code == 0) {
                        this.hybridInverters = response.data.data
                        if (this.hybridInverters.length > 0){
                            let selectedInverterNo = 0
                            if(this.selectedHybridInverter.no != undefined) {
                                selectedInverterNo = this.selectedHybridInverter.no
                            }
                            this.selectInverter(this.hybridInverters[selectedInverterNo])
                        }
                    } else {
                        this.error = '特定できないエラー'
                        console.log(response.data)
                    }
                } catch (err) {
                    this.error = err
                    console.log(err)
                }
            })
            .catch((err) => {
                if(err.status == 401){
                    this.dialog.login.accessToken = ''
                    this.confirmAuth()
                }
                this.error = err
                console.log(err)
            })
        },
        selectInverter(inverter) {
            this.drawer = false
            this.selectedHybridInverter = inverter
            this.getInverterData()
        },
        getInverterData() {
            this.chart.tabIndex = 3
            this.viewChart()
            axios.request({
                method: 'get',
                maxBodyLength: Infinity,
                url: '/api/v1/log/getHybridInverterDatas?no=' + this.selectedHybridInverter.no + '&date=' + this.dialog.datepicker.dateFormatted,
                headers: { 
                    'Authorization': 'Bearer ' + this.dialog.login.accessToken
                }
            })
            .then((response) => {
                try {
                    if (response.data.code == 0) {
                        this.chart.tabIndex = 0
                        this.viewChart()
                        this.chart.hybridInverterData = response.data.data;
                        this.makeChartDaily()
                    } else {
                        this.error = '特定できないエラー'
                        console.log(response.data)
                    }
                } catch (err) {
                    this.error = err
                    console.log(err)
                }
            })
            .catch((err) => {
                this.error = err
                console.log(err)
            })
        },
        makeChartDaily() {
        this.$nextTick(() => {
            try {
                const dataOrgn = this.chart.hybridInverterData
                const kw = 1000
                const ctxA = $('#chartA')
                const ctxB = $('#chartB')
                const ctxC = $('#chartC')

                const keyTotal = {
                    PowerPV: 'pvPower',
                    PowerInverter: 'inverterPower',
                    PowerBatt: 'batteryPower',
                    PowerGridCharge: 'gridPowerCharge',
                    PowerGridUse: 'gridPowerUse',
                    PowerGridUseTotal: 'gridPowerUseTotal',
                    PoolBatt: 'batteryPoolPower',
                }

                const intervalPerHour = dataOrgn.interval / 60
                let labels = []
                let labelsExistsData = []
                let datas = []
                let datasExistsData = []
                let totals = []
                Object.keys(keyTotal).forEach((key) => {
                    totals[keyTotal[key]] = []
                })

                Object.keys(dataOrgn.datas).forEach((key) => {
                    let row = dataOrgn.datas[key]
                    Object.keys(row).forEach((clm) => {
                        if (datas[clm] == undefined) {
                            datas[clm] = []
                        }
                        datas[clm].push(row[clm])
                    })
                    var key2 = ''
                    var num = 0

                    key2 = keyTotal.PowerPV
                    num = row['pv_power'] * intervalPerHour
                    if (totals[key2].length > 0) {
                        num += totals[key2][totals[key2].length - 1]
                    }
                    totals[key2].push(num)

                    key2 = keyTotal.PowerInverter
                    num = row['inverter_power'] * intervalPerHour
                    if (totals[key2].length > 0) {
                        num += totals[key2][totals[key2].length - 1]
                    }
                    totals[key2].push(num)

                    key2 = keyTotal.PowerBatt
                    num = row['battery_charge_power'] * intervalPerHour / kw
                    if (totals[key2].length > 0) {
                        num += totals[key2][totals[key2].length - 1]
                    }
                    totals[key2].push(num)

                    key2 = keyTotal.PowerGridCharge
                    num = row['grid_battery_charge_current'] * row['grid_voltage'] * intervalPerHour / kw
                    if (totals[key2].length > 0) {
                        num += totals[key2][totals[key2].length - 1]
                    }
                    totals[key2].push(num)

                    key2 = keyTotal.PowerGridUseTotal
                    num = row['grid_input_current'] * row['grid_voltage'] / kw
                    totals[keyTotal.PowerGridUse].push(num)
                    num *= intervalPerHour
                    if (totals[key2].length > 0) {
                        num += totals[key2][totals[key2].length - 1]
                    }
                    totals[key2].push(num)

                    key2 = keyTotal.PoolBatt
                    num = row['battery_current'] * row['battery_voltage'] * intervalPerHour * -1 / kw
                    totals[key2].push(num)

                    //データが有るときだけプッシュ
                    if (row['battery_voltage'] > 0) {
                        labelsExistsData.push(key)
                        Object.keys(row).forEach(function(key2) {
                            if (datasExistsData[key2] == undefined) {
                                datasExistsData[key2] = []
                            }
                            datasExistsData[key2].push(row[key2])
                        })
                    }

                    labels.push(key)
                })


                // JSONデータ
                var jsonDataCommonBAT = {
                    "label": "バッテリ電圧",
                    "data": datas['battery_voltage'],
                    "borderColor": "rgba(235, 235, 102, 0.5)",
                    "backgroundColor": "rgba(235, 235, 102, 0.2)",
                    fill: true, // 塗りつぶしを有効にする
                    pointRadius: 0, // 点を非表示にする
                    yAxisID: 'y2'
                }
                var jsonDataA = {
                    "labels": labels,
                    "datasets": [{
                            "label": "発電量",
                            "data": datas['pv_power'],
                            "borderColor": "rgba(99, 255, 132, 1)",
                            "backgroundColor": "rgba(54, 162, 235, 0.2)",
                            yAxisID: 'y1'
                        },
                        {
                            "label": "消費量",
                            "data": datas['inverter_power'],
                            "borderColor": "rgba(75, 192, 192, 1)",
                            "backgroundColor": "rgba(75, 192, 192, 0.2)",
                            yAxisID: 'y1'
                        },
                        {
                            "label": "買電量",
                            "data": totals[keyTotal.PowerGridUse],
                            "borderColor": "rgba(255, 132, 99, 1)",
                            "backgroundColor": "rgba(255, 132, 99, 0.2)",
                            yAxisID: 'y1'
                        },
                        jsonDataCommonBAT,
                    ]
                }

                var jsonDataB = {
                    "labels": labels,
                    "datasets": [{
                            "label": "発電量",
                            "data": totals[keyTotal.PowerPV],
                            "borderColor": "rgba(99, 255, 132, 1)",
                            "backgroundColor": "rgba(99, 255, 132, 0.2)",
                            yAxisID: 'y1'
                        },
                        {
                            "label": "商用蓄電量",
                            "data": totals[keyTotal.PowerGridCharge],
                            "borderColor": "rgb(153, 102, 255, 1)",
                            "backgroundColor": "rgba(99, 132, 255, 0.2)",
                            yAxisID: 'y1'
                        },
                        {
                            "label": "消費量",
                            "data": totals[keyTotal.PowerInverter],
                            "borderColor": "rgba(75, 192, 192, 1)",
                            "backgroundColor": "rgba(99, 132, 255, 0.2)",
                            yAxisID: 'y1'
                        },
                        {
                            "label": "買電量",
                            "data": totals[keyTotal.PowerGridUseTotal],
                            "borderColor": "rgba(255, 132, 99, 1)",
                            "backgroundColor": "rgba(255, 132, 99, 0.2)",
                            yAxisID: 'y1'
                        },
                        jsonDataCommonBAT,
                    ]
                }

                var jsonDataC = {
                    "labels": labelsExistsData,
                    "datasets": [{
                            "label": "バッテリ電圧",
                            "data": datasExistsData['battery_voltage'],
                            "borderColor": jsonDataCommonBAT.borderColor,
                            "backgroundColor": jsonDataCommonBAT.backgroundColor,
                            yAxisID: 'y1'
                        },
                        {
                            "label": "バッテリ残量（SOC）",
                            "data": datasExistsData['battery_soc'],
                            "borderColor": "rgba(168, 255, 168, 0.5)",
                            "backgroundColor": "rgba(168, 255, 168, 0.2)",
                            fill: true, // 塗りつぶしを有効にする
                            pointRadius: 0, // 点を非表示にする
                            yAxisID: 'y2'
                        }
                    ]
                }

                //$(".chartGudance").slideUp()

                //共通
                const ICON_PV_CHARGE = '🌞'
                const ICON_GRID_CHARGE = '🔌'
                const common_x = {
                    ticks: {
                        callback: function(value, index, values) {
                            var label = jsonDataB.labels[index]
                            var buf = dataOrgn.datas[label]
                            try {
                                if (buf['grid_battery_charge_current'] > 0) {
                                    return ICON_GRID_CHARGE + label
                                } else if (buf['pv_battery_charge_current'] > 0) {
                                    return ICON_PV_CHARGE + label
                                }
                                return label
                            } catch (err) {
                                console.log(err)
                            }
                        },
                        color: function(context) {
                            var label = context.tick.label
                            try {
                                if (label.indexOf(ICON_GRID_CHARGE) != -1) {
                                    return 'rgba(128, 0, 0, 1)'
                                } else if (label.indexOf(ICON_PV_CHARGE) != -1) {
                                    return 'rgba(0, 168, 1)'
                                }
                                return 'rgba(0, 0, 0, 1)'
                            } catch (err) {
                                console.log(err)
                            }
                        },
                    }
                }
                const common_y_battery_voltage = {
                    type: 'linear',
                    position: 'right',
                    min: 46,
                    max: 60,
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return value + ' v' // Y軸のラベルに単位を追加
                        }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: '(v)' // Y軸全体のラベルに単位を追加
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }

                //chartA
                if (chartA != null) {
                    chartA.destroy()
                }
                chartA = new Chart(ctxA, {
                    type: 'line',
                    data: jsonDataA,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: common_x,
                            y1: {
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        return value + ' kW' // Y軸のラベルに単位を追加
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: '(kW)' // Y軸全体のラベルに単位を追加
                                }
                            },
                            y2: common_y_battery_voltage,
                        }
                    }
                })

                //chartB
                if (chartB != null) {
                    chartB.destroy()
                }
                chartB = new Chart(ctxB, {
                    type: 'line',
                    data: jsonDataB,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: common_x,
                            y1: {
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        return value + ' kWh' // Y軸のラベルに単位を追加
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: '(kWh)' // Y軸全体のラベルに単位を追加
                                }
                            },
                            y2: common_y_battery_voltage,
                        }
                    },
                })

                //chartC
                if (chartC != null) {
                    chartC.destroy()
                }
                chartC = new Chart(ctxC, {
                    type: 'line',
                    data: jsonDataC,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: common_x,
                            y1: {
                                type: 'linear',
                                position: 'left',
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        return value + ' v' // Y軸のラベルに単位を追加
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: '(v)' // Y軸全体のラベルに単位を追加
                                }
                            },
                            y2: {
                                type: 'linear',
                                position: 'right',
                                min: 0,
                                max: 100,
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        return value + ' %' // Y軸のラベルに単位を追加
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: '(%)' // Y軸全体のラベルに単位を追加
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                        }
                    },
                })
            } catch (err) {
                this.error = err
                console.log(err)
            }
        })
        },
        getGridPriceData(areaId) {
            this.gridPrices = []
            axios
                .get('/api/v1/regist/getGridPrice?token=' + this.token + '&date=' + $("#datepickerGridPrice").val() + '&area=' + $("#" + areaId).val(), {})
                .then((response) => {
                    try {
                        if (response.data.code == 0) {
                            this.gridPrices = []
                            let idx = 0
                            let startTime = new Date()
                            startTime.setHours(0, 0, 0, 0)
                            response.data.data.forEach(row => {
                                let hFrom = String(startTime.getHours()).padStart(2, '0')
                                let mFrom = String(startTime.getMinutes()).padStart(2, '0')
                                startTime.setMinutes(startTime.getMinutes() + 30)
                                let hTo = String(startTime.getHours()).padStart(2, '0')
                                let mTo = String(startTime.getMinutes()).padStart(2, '0')
                                this.gridPrices.push({
                                    idx: idx,
                                    label: `${hFrom}:${mFrom}～${hTo}:${mTo}`,
                                    price: row.price,
                                })
                                idx++
                            })

                        } else {
                            this.error = '特定できないエラー'
                            console.log(response.data)
                        }
                    } catch (err) {
                        this.error = err
                        console.log(err)
                    }
                })
                .catch((err) => {
                    this.error = err
                    console.log(err)
                })
        },
        getRegistSetting() {
            this.dialog.setting.once.message = 'loading..'
            this.dialog.setting.ever.message = 'loading..'
            axios.request({
              method: 'get',
              maxBodyLength: Infinity,
              params:{
                no: this.selectedHybridInverter.no,
                action: 'get',
              },
              url: '/api/v1/regist/readRegistSetting',
              headers: { 
                'Authorization': 'Bearer ' + this.dialog.login.accessToken
              }
            })
            .then((response) => {
                try {
                    this.dialog.setting.once.message = ''
                    this.dialog.setting.ever.message = ''
                    if (response.data.code == 0) {
                        for (let idx = 0; idx < response.data.regists.length; idx++) {
                            let item = response.data.regists[idx]
                            let regist = JSON.parse(item.regist)
                            if (item.mode == 0) {
                                if (item.result == '') {
                                    this.dialog.setting.once.message = '設定予約済です。1分以内に切替設定処理をします。'
                                }
                            } else if (item.mode == 1) {
                                this.dialog.setting.ever = regist
                                this.dialog.setting.ever.message = '設定済です。変更する場合は改めて設定してください。'
                            }
                        }
                    } else {
                        this.dialog.setting.ever.message = 'error[' + response.data.code + ']:' + response.data.error
                    }
                } catch (err) {
                    this.error = err
                    console.log(err)
                }
            })
            .catch((err) => {
                this.error = err
                console.log(err)
            })
        },
        openSetting() {
            this.drawer = false
            console.log('this.selectedHybridInverter')
            console.log(this.selectedHybridInverter)
            this.dialog.setting.once.chargerPriority = this.selectedHybridInverter.inverter_charger_priority
            this.dialog.setting.once.outputPriority = this.selectedHybridInverter.inverter_output_priority
            this.dialog.setting.show = true
            this.getRegistSetting()
        },
        generateValues(start, end, step) {
            const values = []
            for (let i = start; i <= end; i = parseFloat((i + step).toFixed(2))) {
                values.push(i)
            }
            return values
        },
        settingOnce(flgDel) {
            axios.request({
              method: 'post',
              maxBodyLength: Infinity,
              data:{
                no: this.selectedHybridInverter.no,
                action: 'set',
                mode: 0,
                flgDel: flgDel,
                regist: {
                    inverter_output_priority_write: this.dialog.setting.once.outputPriority,
                    inverter_charger_priority_write: this.dialog.setting.once.chargerPriority,
                },
              },
              url: '/api/v1/regist/recordSettingHybridInverter',
              headers: { 
                'Authorization': 'Bearer ' + this.dialog.login.accessToken
              }
            })
            .then((response) => {
                try {
                    if (response.data.code == 0) {
                        this.dialog.setting.once.message = response.data.message
                    } else {
                        this.dialog.setting.once.message = '⚠️error[' + response.data.code + ']:' + response.data.error
                        console.log(response.data)
                    }
                } catch (err) {
                    this.error = err
                    console.log(err)
                }
            })
            .catch((err) => {
                this.error = err
                console.log(err)
            })
        },
        settingEver(flgDel) {
            this.dialog.setting.ever.message = ''
            axios.request({
              method: 'post',
              maxBodyLength: Infinity,
              data:{
                no: this.selectedHybridInverter.no,
                action: 'set',
                mode: 1,
                flgDel: flgDel,
                regist: this.dialog.setting.ever,
              },
              url: '/api/v1/regist/recordSettingHybridInverter',
              headers: { 
                'Authorization': 'Bearer ' + this.dialog.login.accessToken
              }
            })
            .then((response) => {
                try {
                    if (response.data.code == 0) {
                        this.dialog.setting.ever.message = response.data.message
                    } else {
                        this.dialog.setting.ever.message = '⚠️error[' + response.data.code + ']:' + response.data.error
                        console.log(response.data)
                    }
                } catch (err) {
                    this.error = err
                    console.log(err)
                }
            })
            .catch((err) => {
                this.error = err
                console.log(err)
            })
        },
        moveDate(add){
            const date = new Date(this.dialog.datepicker.dateFormatted)
            date.setDate(date.getDate() + add)
            const year = date.getFullYear()
            const month = ('0' + (date.getMonth() + 1)).slice(-2)
            const day = ('0' + date.getDate()).slice(-2)
            this.dialog.datepicker.date = date
            this.dialog.datepicker.dateFormatted = `${year}/${month}/${day}`
            this.getInverterData()
        },
        setDate(date) {
            if (!date) return null
            const [year, month, day] = date.split("-")
            this.text = `${year}${month}${day}`
            this.menu = false
            return
        },
        viewChart() {
            const element = document.getElementById(`chart${this.chart.tabIndex}`)
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' })
            }
        },
        getColorBattSoc(value) {
            if (value < 30) {
                return 'red'; // 30%未満の場合は赤
            } else if (value < 60) {
                return 'orange'; // 30%以上70%未満の場合は黄色
            } else {
                return 'green'; // 70%以上の場合は緑
            }
        },
      }
    }
</script>