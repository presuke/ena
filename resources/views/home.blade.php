@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div id="app">
            <div style="width:calc(100vw - 10);">
                <div class="card">
                    <div class="card-header">ハイブリッドインバータ</div>
                    <div v-if="hybridInverters == -1" style="padding:10px;">
                        <v-progress-circular color="primary" indeterminate></v-progress-circular>loading...
                    </div>
                    <div v-else-if="hybridInverters == 0">
                        <img src="image/img_hi.png" style="filter: grayscale(100%);" />
                        ハイブリッドインバータのデータがありません。
                    </div>
                    <div v-else style="padding:10px;">
                        <div class="card" v-for="hybridInverter in hybridInverters" :key="hybridInverter.no">
                            <div :calss="[selectedHybridInverter.no==hybridInverter.no ? 'selectedHivridInvertor' : 'selectedHivridInvertor']">
                                <div class="card-header" style="text-align: right;">
                                    <img src="/image/icon_setting.png" class="iconBtn" @click="openSetting(hybridInverter);" />　
                                    <img src="/image/icon_graph.png" class="iconBtn" @click="selectInverter(hybridInverter);" />
                                </div>
                                <div style="background-color:#333;">
                                    <div style="max-width:1000px; padding: 5px; margin:10px auto; text-align:center; color:white;">
                                        <div style="width:min(15vw, 150px); float:left;" :style="[hybridInverter.pv_power > 0 ? '' : 'filter:brightness(50%);']">
                                            <img src="/image/icon_solor.png" class="iconModule">
                                            <div class="fontMain">@{{Math.floor(hybridInverter.pv_power).toLocaleString()}}w</div>
                                            <div class="fontSub">@{{hybridInverter.pv_voltage.toLocaleString()}}V</div>
                                            <div class="fontSub">@{{hybridInverter.pv_current.toLocaleString()}}A</div>
                                        </div>
                                        <div style="width:min(22.5vw, 225px); height: min(20vw, 200px); float:left;">
                                            <div style="width:100%; height:min(7.5vw, 75px);"><br /></div>
                                            <div class="box" style="width:100%; height:100%;" v-if="hybridInverter.pv_power > 0">
                                                <span class="box__line"></span>
                                                <span class="box__line"></span>
                                            </div>
                                        </div>
                                        <div style="width:min(15vw, 150px); clear:left; float:left;" :style="[hybridInverter.grid_input_current > 0 ? '' : 'filter:brightness(50%);']">
                                            <img src="/image/icon_grid.png" class="iconModule">
                                            <div class="fontMain">@{{(hybridInverter.grid_voltage * hybridInverter.grid_input_current).toLocaleString(undefined, { maximumFractionDigits: 0 })}}VA</div>
                                            <div class="fontSub">@{{hybridInverter.grid_voltage.toLocaleString()}}V</div>
                                            <div class="fontSub">@{{hybridInverter.grid_input_current.toLocaleString()}}A</div>
                                            <div class="fontSub">(@{{hybridInverter.grid_frequency}}Hz)</div>
                                        </div>
                                        <div style="width:min(15vw, 150px); float:left;">
                                            <div style="width:100%; height:min(7.5vw, 75px);"><br /></div>
                                            <div class="box">
                                                <span class="box__line" v-if="hybridInverter.grid_input_current > 0"></span>
                                                <br />
                                            </div>
                                        </div>
                                        <div style="width:min(15vw, 150px); float:left;">
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
                                                        🔌@{{Math.floor(hybridInverter.grid_battery_charge_current * hybridInverter.grid_voltage).toLocaleString()}}VA
                                                    </div>
                                                    <div v-if="hybridInverter.pv_battery_charge_current > 0">
                                                        🌞@{{Math.floor(hybridInverter.pv_battery_charge_current * hybridInverter.battery_voltage).toLocaleString()}}VA
                                                    </div>
                                                    <div style="clear:left;"></div>
                                                </div>
                                                <div class="fontMain" style="width:min(15vw, 150px);" v-if="hybridInverter.battery_current > 0">
                                                    @{{(hybridInverter.battery_voltage * hybridInverter.battery_current).toLocaleString(undefined, { maximumFractionDigits: 0 })}}VA
                                                </div>
                                                <div style="width:min(15vw, 150px); float:left;">
                                                    <img src="/image/icon_battery.png" class="iconModule">
                                                    <div style="width:min(10vw, 100px); margin-top:max(-15vw, -150px); margin-left:min(7.5vw, 75px);">
                                                        <v-progress-circular
                                                            :model-value="hybridInverter.battery_soc"
                                                            :color="[hybridInverter.battery_soc<30?'#f33':hybridInverter.battery_soc<50?'#f93':hybridInverter.battery_soc<70?'#cf3':'#6f6']"
                                                            style="clear:left; width:min(5vw, 50px); height:min(5vw, 50px);">
                                                            <div style="color:white; z-index:1; text-shadow:0 0 4px #fff, 0 0 8px #ccc, 0 0 12px #999;">@{{ Math.ceil(hybridInverter.battery_soc)}}%</div>
                                                        </v-progress-circular>
                                                    </div>
                                                    <div class="fontMain" style="margin-top:min(7.5vw, 75px); z-index:1;">@{{hybridInverter.battery_voltage.toLocaleString()}}V</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="width:min(15vw, 150px); height:min(15vw, 150px); float:left;">
                                            <div style="width:100%; height:min(7.5vw, 75px);"><br /></div>
                                            <div class="box" style="width:100%; height:100%;">
                                                <span class="box__line" v-if="hybridInverter.inverter_power > 0"></span>
                                                <br />
                                            </div>
                                        </div>
                                        <div style="width:min(15vw, 150px); height:min(20vw,200px); float:left;">
                                            <img src="/image/icon_use.png" class="iconModule">
                                            <div class="fontMain">@{{Math.floor(hybridInverter.inverter_power).toLocaleString(undefined, { maximumFractionDigits: 0 })}}w</div>
                                            <div style="height:min(3vw, 50px);"></div>
                                            <div class="fontMain">@{{(hybridInverter.inverter_voltage * hybridInverter.inverter_current).toLocaleString(undefined, { maximumFractionDigits: 0 })}}VA</div>
                                            <div class="fontSub">@{{hybridInverter.inverter_voltage.toLocaleString()}}v</div>
                                            <div class="fontSub">@{{hybridInverter.inverter_current.toLocaleString()}}A</div>
                                            <div class="fontSub">(@{{hybridInverter.inverter_frequency}}Hz)</div>
                                        </div>

                                        <div style="clear:left; background-color:black;">@{{hybridInverter.create_at}}</div>
                                    </div>
                                </div>
                                <textarea style="width:60vw; height:300px;">
                                            @{{hybridInverter}}
                                            </textarea>

                            </div>

                        </div>
                    </div>
                </div>
                <div id="dialogSetting" style="display:none;">
                    <div class="accordion">
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
                                        @{{ value }}
                                    </option>
                                </select>
                                円/kwh以下
                            </div>
                            <div>
                                バッテリー充電度：
                                <select>
                                    <option v-for="value in form.select.socs" :key="value" :value="value">
                                        @{{ value }}
                                    </option>
                                </select>

                                ％以下
                            </div>
                            <v-btn>商用電力を使う</v-btn>
                        </div>
                        <h3>安く溜める設定</h3>
                        <div>
                            <div>
                                エリア：<select id="area2">
                                    <option value="1">東京</option>
                                    <option value="2">東北</option>
                                    <option value="3">中部</option>
                                    <option value="4">東海</option>
                                    <option value="5">関西</option>
                                </select>
                                　<a href="javascript:openPriceList('area2');">料金表</a>
                            </div>
                            <div>
                                料金：
                                <select>
                                    <option v-for="value in form.select.prices" :key="value" :value="value">
                                        @{{ value }}
                                    </option>
                                </select>
                                円/kwh以下
                            </div>
                            <div>
                                バッテリー充電度：
                                <select>
                                    <option v-for="value in form.select.socs" :key="value" :value="value">
                                        @{{ value }}
                                    </option>
                                </select>

                                ％以下
                            </div>
                            <v-btn>商用電力で溜める</v-btn>
                        </div>
                        <h3>リモート設定</h3>
                        <div>
                            <h4>現在の設定</h4>
                            <div
                                @click="modeChange(0)"
                                style="margin-bottom: 10px; border-radius: 10px; padding:10px; background-color: black; cursor: pointer;">
                                <div style="float:left; width:40px; height:40px; text-align: center;">
                                    <img src="/image/icon_solor.png" class="iconModule" style="width:100%; height:100%;">
                                    <div class="fontMain" style="font-size:min(2.5vw, 14px); color:white;">1st</div>
                                </div>
                                <div style="float:left; width:40px; height:40px; text-align: center;">
                                    <img src="/image/icon_grid.png" class="iconModule" style="width:100%; height:100%;">
                                    <div class="fontMain" style="font-size:min(2.5vw, 14px); color:white;">2nd</div>
                                </div>
                                <div class="fontMain" style="clear:left; font-size:min(3vw, 16px); color:white;">PV優先モード</div>
                            </div>
                            <div
                                @click="modeChange(1)"
                                style="margin-bottom: 10px; border-radius: 10px; padding:10px; background-color: black; cursor: pointer;">
                                <div style="float:left; width:40px; height:40px; text-align: center;">
                                    <img src="/image/icon_grid.png" class="iconModule" style="width:100%; height:100%;">
                                    <div class="fontMain" style="font-size:min(2.5vw, 14px); color:white;">1st</div>
                                </div>
                                <div style="float:left; width:40px; height:40px; text-align: center;">
                                    <img src="/image/icon_solor.png" class="iconModule" style="width:100%; height:100%;">
                                    <div class="fontMain" style="font-size:min(2.5vw, 14px); color:white;">2nd</div>
                                </div>
                                <div class="fontMain" style="clear:left; font-size:min(3vw, 16px); color:white;">Grid優先モード</div>
                            </div>
                            <div
                                @click="modeChange(2)"
                                style="margin-bottom: 10px; border-radius: 10px; padding:10px; background-color: black; cursor: pointer;">
                                <div style="float:left; width:50px; height:50px; text-align: center;">
                                    <img src="/image/icon_solor.png" class="iconModule" style="width:100%; height:100%;">
                                </div>
                                <div style="float:left; width:50px; height:50px; text-align: center;">
                                    <img src="/image/icon_grid.png" class="iconModule" style="width:100%; height:100%;">
                                </div>
                                <div class="fontMain" style="clear:left; font-size:min(3vw, 16px); color:white;">ハイブリッドモード</div>
                            </div>
                            <div
                                @click="modeChange(3)"
                                style="margin-bottom: 10px; border-radius: 10px; padding:10px; background-color: black; cursor: pointer;">
                                <div style="float:left; width:50px; height:50px; text-align: center;">
                                    <img src="/image/icon_solor.png" class="iconModule" style="width:100%; height:100%;">
                                    <div class="fontMain" style="font-size:min(2.5vw, 14px); color:white;">only</div>
                                </div>
                                <div style="float:left; width:50px; height:50px; text-align: center; filter:brightness(50%);">
                                    <img src="/image/icon_grid.png" class="iconModule" style="width:100%; height:100%;">
                                </div>
                                <div class="fontMain" style="clear:left; font-size:min(3vw, 16px); color:white;">PVのみモード</div>
                            </div>
                            <div>
                                <div>消費電力源：@{{this.get_output_priority(this.selectedHybridInverter.inverter_output_priority)}}</div>
                                <div>蓄電元電源：@{{this.get_chargepriority(selectedHybridInverter.inverter_charger_priority)}}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="dialogPriceList" style="display: none;">
                    <div style="border-bottom: solid thin gray;width:180px;"> <input type="text" id="datepickerGridPrice" class="datepicker" onSlelect="vueObj.getGridPriceData();"></div>
                    <div v-if="gridPrices.length == 0" style="padding:10px;">
                        <v-progress-circular color="primary" indeterminate></v-progress-circular>loading...
                    </div>
                    <div v-else>
                        <div style="height:300px; overflow:scroll;">
                            <div v-for="gridPrice in gridPrices" :key="gridPrice.idx">
                                <div style="width: 100px; clear: left; float: left;" :style="gridPrice.idx % 2 === 1 ? { backgroundColor: '#eeeeff' } : {}">@{{ gridPrice.label }}</div>
                                <div style="float: left;" :style="gridPrice.idx % 2 === 1 ? { backgroundColor: '#eeeeff' } : {}">@{{ gridPrice.price }}円/kwh</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card" style="height:600px;">
                        <div class="card-header">デイリーデータ</div>
                        <p style="border-bottom: solid thin gray;width:240px;">日付: <input type="text" id="datepicker" class="datepicker"></p>
                        <div id="tabs">
                            <ul>
                                <li><a href="#tabs-1">推移</a></li>
                                <li><a href="#tabs-2">累積</a></li>
                                <li><a href="#tabs-3">バッテリー</a></li>
                            </ul>
                            <div id="tabs-1">
                                <div style="height:250px">
                                    <canvas id="chartA" width="100"></canvas>
                                </div>
                                <div class="chartGudance">日付とハイブリッドインバータを選択すると運用状況がグラフで表示されます。</div>
                            </div>
                            <div id="tabs-2">
                                <div style="height:250px">
                                    <canvas id="chartB"></canvas>
                                </div>
                                <div class="chartGudance">日付とハイブリッドインバータを選択すると運用状況がグラフで表示されます。</div>
                            </div>
                            <div id="tabs-3">
                                <div style="height:250px">
                                    <canvas id="chartC"></canvas>
                                </div>
                                <div class="chartGudance">日付とハイブリッドインバータを選択すると運用状況がグラフで表示されます。</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var vueObj;
        var chartA = null;
        var chartB = null;
        var chartC = null;

        $(function() {
            $("#datepicker").datepicker({
                dateFormat: 'yy/mm/dd',
                onSelect: function(dateText, inst) {
                    vueObj.getInverterData();
                }
            });

            $("#datepicker").val(new Date().toLocaleDateString("ja-JP", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit"
            }));
            $("#tabs").tabs();
        });

        function openPriceList(areaId) {
            var selectedArea = $("#" + areaId).find('option:selected').text();
            $("#dialogPriceList").dialog({
                title: selectedArea + 'の料金表',
                modal: true,
                height: 500,
                buttons: [{
                    text: "閉じる",
                    click: function() {
                        $(this).dialog("close");
                    }
                }],
                open: function() {
                    $("#datepickerGridPrice").datepicker({
                        dateFormat: 'yy/mm/dd',
                        onSelect: function(dateText, inst) {
                            vueObj.getGridPriceData(areaId);
                        }
                    });

                    $("#datepickerGridPrice").val(new Date().toLocaleDateString("ja-JP", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit"
                    }));
                    vueObj.getGridPriceData(areaId);
                },
            });
        }

        const {
            createApp,
            ref
        } = Vue

        vuetify = Vuetify.createVuetify({
            theme: {
                defaultTheme: 'light'
            }
        });

        const objVue = {
            data: () => ({
                token: '',
                rootPath: '',
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
                output_priority: {
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
            }),
            setup() {},
            created() {},
            mounted() {
                vueObj = this;
                window.onload = () => {
                    this.screen.height = document.body.clientHeight;
                    this.screen.width = document.body.clientWidth;
                    this.token = window.localStorage.getItem('token');
                    this.form.select.prices = this.generateValues(8, 16, 0.2);
                    this.form.select.socs = this.generateValues(30, 80, 1);
                    this.getMyHybridInverters()
                    setInterval(this.getMyHybridInverters, 60000);
                };
            },
            methods: {
                get_output_priority(priority) {
                    return this.output_priority[priority];
                },
                get_chargepriority(priority) {
                    return this.chargerPriority[priority];
                },
                getMyHybridInverters() {
                    const accessToken = this.token;
                    axios
                        .get('/api/v1/log/getMyHybridInverters?token=' + accessToken, {})
                        .then((response) => {
                            try {
                                if (response.data.code == 0) {
                                    this.hybridInverters = response.data.data;
                                    if (!$('.slider').hasClass('slick-initialized')) {
                                        setTimeout(function() {
                                            $('.slider').slick({
                                                autoplay: true, // 自動再生
                                                autoplaySpeed: 4000, // 再生速度（ミリ秒設定） 1000ミリ秒=1秒
                                                infinite: true, // 無限スライド
                                            });
                                        }, 500);
                                    }
                                    if (response.data.length == 0) {
                                        this.hybridInverters = response.data;
                                    }
                                } else {
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
                selectInverter(inverter) {
                    this.selectedHybridInverter = inverter;
                    this.getInverterData();
                },
                getInverterData() {
                    const date = $("#datepicker").val();
                    axios
                        .get('/api/v1/log/getHybridInverterDatas?token=' + this.token + '&no=' + this.selectedHybridInverter.no + '&date=' + date, {})
                        .then((response) => {
                            try {
                                if (response.data.code == 0) {
                                    this.hybridInverterData = response.data.data;
                                    this.makeChartDaily(response.data.data);
                                } else {
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
                makeChartDaily(dataOrgn) {
                    try {
                        const kw = 1000;
                        console.log("makechart3");
                        const ctxA = $('#chartA');
                        const ctxB = $('#chartB');
                        const ctxC = $('#chartC');

                        const keyTotal = {
                            PowerPV: 'pvPower',
                            PowerInverter: 'inverterPower',
                            PowerBatt: 'batteryPower',
                            PowerGridCharge: 'gridPowerCharge',
                            PowerGridUse: 'gridPowerUse',
                            PowerGridUseTotal: 'gridPowerUseTotal',
                            PoolBatt: 'batteryPoolPower',
                        };

                        const intervalPerHour = dataOrgn.interval / 60;
                        let labels = [];
                        let labelsExistsData = [];
                        let datas = [];
                        let datasExistsData = [];
                        let totals = [];
                        Object.keys(keyTotal).forEach((key) => {
                            totals[keyTotal[key]] = [];
                        });

                        Object.keys(dataOrgn.datas).forEach((key) => {
                            let row = dataOrgn.datas[key];
                            Object.keys(row).forEach((clm) => {
                                if (datas[clm] == undefined) {
                                    datas[clm] = [];
                                }
                                datas[clm].push(row[clm]);
                            });
                            var key2 = ''
                            var num = 0;

                            key2 = keyTotal.PowerPV;
                            num = row['pv_power'] * intervalPerHour;
                            if (totals[key2].length > 0) {
                                num += totals[key2][totals[key2].length - 1];
                            }
                            totals[key2].push(num);

                            key2 = keyTotal.PowerInverter;
                            num = row['inverter_power'] * intervalPerHour;
                            if (totals[key2].length > 0) {
                                num += totals[key2][totals[key2].length - 1];
                            }
                            totals[key2].push(num);

                            key2 = keyTotal.PowerBatt;
                            num = row['battery_charge_power'] * intervalPerHour / kw;
                            if (totals[key2].length > 0) {
                                num += totals[key2][totals[key2].length - 1];
                            }
                            totals[key2].push(num);

                            key2 = keyTotal.PowerGridCharge;
                            num = row['grid_battery_charge_current'] * row['grid_voltage'] * intervalPerHour / kw;
                            if (totals[key2].length > 0) {
                                num += totals[key2][totals[key2].length - 1];
                            }
                            totals[key2].push(num);

                            key2 = keyTotal.PowerGridUseTotal;
                            num = row['grid_input_current'] * row['grid_voltage'] * intervalPerHour / kw;
                            totals[keyTotal.PowerGridUse].push(num);
                            if (totals[key2].length > 0) {
                                num += totals[key2][totals[key2].length - 1];
                            }
                            totals[key2].push(num);

                            key2 = keyTotal.PoolBatt;
                            num = row['battery_current'] * row['battery_voltage'] * intervalPerHour * -1 / kw;
                            totals[key2].push(num);

                            //データが有るときだけプッシュ
                            if (row['battery_voltage'] > 0) {
                                labelsExistsData.push(key);
                                Object.keys(row).forEach(function(key2) {
                                    if (datasExistsData[key2] == undefined) {
                                        datasExistsData[key2] = [];
                                    }
                                    datasExistsData[key2].push(row[key2]);
                                });
                            }

                            labels.push(key);
                        });


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
                        };

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
                        };

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
                        };

                        $(".chartGudance").slideUp();

                        //共通
                        const ICON_PV_CHARGE = '🌞';
                        const ICON_GRID_CHARGE = '🔌';
                        const common_x = {
                            ticks: {
                                callback: function(value, index, values) {
                                    var label = jsonDataB.labels[index];
                                    var buf = dataOrgn.datas[label];
                                    try {
                                        if (buf['grid_battery_charge_current'] > 0) {
                                            return ICON_GRID_CHARGE + label;
                                        } else if (buf['pv_battery_charge_current'] > 0) {
                                            return ICON_PV_CHARGE + label;
                                        }
                                        return label;
                                    } catch (err) {
                                        console.log(err);
                                    }
                                },
                                color: function(context) {
                                    var label = context.tick.label;
                                    try {
                                        if (label.indexOf(ICON_GRID_CHARGE) != -1) {
                                            return 'rgba(128, 0, 0, 1)';
                                        } else if (label.indexOf(ICON_PV_CHARGE) != -1) {
                                            return 'rgba(0, 168, 1)';
                                        }
                                        return 'rgba(0, 0, 0, 1)';
                                    } catch (err) {
                                        console.log(err);
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
                                    return value + ' v'; // Y軸のラベルに単位を追加
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
                            chartA.destroy();
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
                                                return value + ' kW'; // Y軸のラベルに単位を追加
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
                        });

                        //chartB
                        if (chartB != null) {
                            chartB.destroy();
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
                                                return value + ' kWh'; // Y軸のラベルに単位を追加
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
                        });

                        //chartC
                        if (chartC != null) {
                            chartC.destroy();
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
                                                return value + ' v'; // Y軸のラベルに単位を追加
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
                                                return value + ' %'; // Y軸のラベルに単位を追加
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
                        });
                    } catch (err) {
                        this.error = err;
                        console.log(err);
                    }
                },
                getGridPriceData(areaId) {
                    this.gridPrices = [];
                    axios
                        .get('/api/v1/regist/getGridPrice?token=' + this.token + '&date=' + $("#datepickerGridPrice").val() + '&area=' + $("#" + areaId).val(), {})
                        .then((response) => {
                            try {
                                if (response.data.code == 0) {
                                    this.gridPrices = [];
                                    let idx = 0;
                                    let startTime = new Date();
                                    startTime.setHours(0, 0, 0, 0);
                                    response.data.data.forEach(row => {
                                        let hFrom = String(startTime.getHours()).padStart(2, '0');
                                        let mFrom = String(startTime.getMinutes()).padStart(2, '0');
                                        startTime.setMinutes(startTime.getMinutes() + 30);
                                        let hTo = String(startTime.getHours()).padStart(2, '0');
                                        let mTo = String(startTime.getMinutes()).padStart(2, '0');
                                        this.gridPrices.push({
                                            idx: idx,
                                            label: `${hFrom}:${mFrom}～${hTo}:${mTo}`,
                                            price: row.price,
                                        });
                                        idx++;
                                    });

                                } else {
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
                openSetting(hybridInverter) {
                    this.selectedHybridInverter = hybridInverter;
                    $("#dialogSetting").dialog({
                        title: '設定',
                        modal: true,
                        height: 500,
                        buttons: [{
                            text: "閉じる",
                            click: function() {
                                $(this).dialog("close");
                            }
                        }],
                        open: function() {
                            $(".accordion").accordion();
                            $("#priceList").accordion({
                                collapsible: true
                            });

                            $("#area").change(function() {
                                vueObj.getGridPriceData()
                            });

                            vueObj.getGridPriceData();
                        },
                    });
                },
                generateValues(start, end, step) {
                    const values = [];
                    for (let i = start; i <= end; i = parseFloat((i + step).toFixed(2))) {
                        values.push(i);
                    }
                    return values;
                },
                modeChange(value) {
                    axios
                        .post('api/v1/regist/recordSettingHybridInverter', {
                            token: this.token,
                            no: this.selectedHybridInverter.no,
                            mode: 0,
                            regist: {
                                value: value
                            },
                        })
                        .then((response) => {
                            try {
                                if (response.data.code == 0) {} else {
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
                }
            }
        }
        const objApp = Vue.createApp(objVue);
        objApp.use(vuetify);
        objApp.mount('#app');
    </script>
    @endsection