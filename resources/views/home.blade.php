@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div id="app">
            <div class="col-md-3" style="float:left; margin-right:10px;">
                <div class="card">
                    <div class="card-header">ハイブリッドインバータ</div>
                    <div v-if="hybridInverters == -1">
                        loading...
                    </div>
                    <div v-else-if="hybridInverters == 0">
                        <img src="/image/hi_icon.png" style="filter: grayscale(100%);" />
                        ハイブリッドインバータのデータがありません。
                    </div>
                    <div v-else style="padding:10px;">
                        <div class="card" v-for="hybridInverter in hybridInverters" :key="hybridInverter.no">
                            <div :calss="[selectedHybridInverter==hybridInverter.no ? 'selectedHivridInvertor' : 'selectedHivridInvertor']">
                                <div class="card-header">@{{ hybridInverter.no }}</div>
                                <div class="btn" @click="this.selectInverter(hybridInverter.no);">
                                    <img src="/image/hi_icon.png" />
                                </div>
                                <div>@{{Math.floor( (new Date() - new Date(hybridInverter.create_at)) / 1000 / 60)}}分前データ</div>
                                <div style="height:240px;width: 300px;">
                                    <ul class="slider">
                                        <li>
                                            <h2>バッテリー</h2>
                                            <v-progress-circular :model-value="hybridInverter.battery_soc" :color="[hybridInverter.battery_soc<30?'red':hybridInverter.battery_soc<50?'orange':hybridInverter.battery_soc<70?'yellow':'green']" :size="50" :width="8">
                                                <div style="color:black;">@{{ Math.ceil(hybridInverter.battery_soc)}}%</div>
                                            </v-progress-circular>
                                            <div>電圧：@{{hybridInverter.battery_voltage}}v</div>
                                            <div>電流：@{{hybridInverter.battery_current}}a</div>
                                            <div>入力電力：@{{hybridInverter.battery_charge_power}}v</div>
                                            <div>最大入力電流：@{{hybridInverter.battery_max_charge_current}}a</div>
                                        </li>
                                        <li>
                                            <h2>PV</h2>
                                            <div>電圧：@{{hybridInverter.pv_voltage}}v</div>
                                            <div>電流：@{{hybridInverter.pv_current}}v</div>
                                            <div>発電量：@{{hybridInverter.pv_power}}v</div>
                                            <div>バッテリー流入電流：@{{hybridInverter.pv_battery_charge_current}}v</div>
                                        </li>
                                        <li>
                                            <h2>商用電源</h2>
                                            <div>電圧：@{{hybridInverter.grid_voltage}}v</div>
                                            <div>電流：@{{hybridInverter.grid_input_current}}v</div>
                                            <div>バッテリー流入電流：@{{hybridInverter.grid_battery_charge_current}}a</div>
                                            <div>最大バッテリー流入電流：@{{hybridInverter.grid_battery_charge_max_current}}a</div>
                                            <div>周波数：@{{hybridInverter.grid_frequency}}Hz</div>
                                        </li>
                                        <ii>
                                            <h2>インバータ</h2>
                                            <div>電圧：@{{hybridInverter.inverter_voltage}}v</div>
                                            <div>電流：@{{hybridInverter.inverter_current}}v</div>
                                            <div>消費電力量：@{{hybridInverter.inverter_power}}w</div>
                                            <div>周波数：@{{hybridInverter.inverter_frequency}}Hz</div>
                                            <div>使用系：@{{this.get_output_priority(hybridInverter.inverter_output_priority)}}</div>
                                            <div>蓄電系：@{{this.get_chargepriority(hybridInverter.inverter_charger_priority)}}</div>
                                            <div>DC温度：@{{Math.round(hybridInverter.temp_dc * 10 / 10)}}℃</div>
                                            <div>AC温度：@{{Math.round(hybridInverter.temp_ac * 10 / 10)}}℃</div>
                                            <div>TR温度：@{{Math.round(hybridInverter.temp_tr * 10 / 10)}}℃</div>
                                        </ii>
                                        <li>
                                            <textarea style="width:200px; height:300px;">
                                            @{{hybridInverter}}
                                            </textarea>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div v-if="showControllBox" class="card">
                    <div class="card-header">リモート設定</div>
                    ハイブリッドインバータの設定を手動で変更します。
                    <button @click="this.selectInverter(0);">close</button>
                </div>
                <div v-if="showControllBox" class="card">
                    <div class="card-header">インテリジェンス設定</div>
                    Looop電気の料金が
                    <button @click="this.selectInverter(0);">close</button>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card" style="height:600px;">
                    <div class="card-header">デイリーデータ</div>
                    <p style="border-bottom: solid thin gray;width:240px;">日付: <input type="text" id="datepicker"></p>
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
                            <div class="chartGudance">aa日付とハイブリッドインバータを選択すると運用状況がグラフで表示されます。</div>
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
<style>
    div.btn {
        transition: all 0.5s;
    }

    div.btn:hover {
        background-color: lightgray;
    }

    .selectedHivridInvertor {
        background-color: #ffe;
    }
</style>
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
            },
            serverTime: 'xxx',
            hybridInverters: -1,
            selectedHybridInverter: 0,
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
                this.token = window.localStorage.getItem('token');
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
                this.showControllBox = this.selectedHybridInverter != 0;
                if (this.selectedHybridInverter != 0) {
                    this.getInverterData();
                }
            },
            getInverterData() {
                if (this.selectedHybridInverter == 0) {
                    return;
                }

                const date = $("#datepicker").val();
                axios
                    .get('/api/v1/log/getHybridInverterDatas?token=' + this.token + '&no=' + this.selectedHybridInverter + '&date=' + date, {})
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
                    const ctxA = $('#chartA');
                    const ctxB = $('#chartB');
                    const ctxC = $('#chartC');

                    const keyTotal = {
                        PowerPV: 'pvPower',
                        PowerInverter: 'inverterPoser',
                        PowerBatt: 'batteryPower',
                        PowerGridCharge: 'gridPowerCharge',
                        PowerGridUse: 'gridPowerUse',
                        PowerGridUseTotal: 'gridPowerUseTotal',
                        PoolBatt: 'batteryPoolPower',
                    };

                    const intervalPerHour = dataOrgn.interval / 60;
                    let labels = [];
                    let datas = [];
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
                        num = row['battery_charge_power'] * intervalPerHour;
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PowerGridCharge;
                        num = row['grid_battery_charge_current'] * row['grid_voltage'] * intervalPerHour;
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PowerGridUseTotal;
                        num = row['grid_input_current'] * row['grid_voltage'] * intervalPerHour;
                        totals[keyTotal.PowerGridUse].push(num);
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PoolBatt;
                        num = row['battery_current'] * row['battery_voltage'] * intervalPerHour;
                        totals[key2].push(num);

                        labels.push(key);
                    });


                    // JSONデータ
                    var jsonDataCommonSOC = {
                        "label": "バッテリー残量（SOC）",
                        "data": datas['battery_soc'],
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
                            jsonDataCommonSOC,
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
                                "label": "消費量",
                                "data": totals[keyTotal.PowerInverter],
                                "borderColor": "rgba(75, 192, 192, 1)",
                                "backgroundColor": "rgba(99, 132, 255, 0.2)",
                                yAxisID: 'y1'
                            },
                            {
                                "label": "買電量",
                                "data": totals[keyTotal.PowerGridUse],
                                "borderColor": "rgba(255, 132, 99, 1)",
                                "backgroundColor": "rgba(255, 132, 99, 0.2)",
                                yAxisID: 'y1'
                            },
                            jsonDataCommonSOC,
                        ]
                    };

                    var jsonDataC = {
                        "labels": labels,
                        "datasets": [{
                                "label": "バッテリー電圧",
                                "data": datas['battery_voltage'],
                                "borderColor": "rgba(255, 99, 132, 1)",
                                "backgroundColor": "rgba(255, 99, 132, 0.2)",
                                yAxisID: 'y1'
                            },
                            jsonDataCommonSOC,
                        ]
                    };


                    $(".chartGudance").slideUp();

                    //共通
                    const ICON_PV_CHARGE = '⚡';
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
                    const common_y_soc = {
                        type: 'linear',
                        position: 'right',
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
                                            return value + ' w'; // Y軸のラベルに単位を追加
                                        }
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '(w)' // Y軸全体のラベルに単位を追加
                                    }
                                },
                                y2: common_y_soc,
                            }
                        }
                    });

                    //chartB*
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
                                            return value + ' wh'; // Y軸のラベルに単位を追加
                                        }
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '(wh)' // Y軸全体のラベルに単位を追加
                                    }
                                },
                                y2: common_y_soc,
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
                                y2: common_y_soc,
                            }
                        },
                    });
                } catch (err) {
                    this.error = err;
                    console.log(err);
                }
            },
            getBackgroundColor(hour) {
                return (hour >= 8 && hour < 12) ? 'rgba(255, 255, 0, 0.1)' : 'rgba(0, 0, 255, 0.1)';
            }
        }
    }
    const objApp = Vue.createApp(objVue);
    objApp.use(vuetify);
    objApp.mount('#app');
</script>
@endsection