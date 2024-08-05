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
                            <div class="btn" @click="this.selectInverter(hybridInverter.no);" :style="[selectedHybridInverter==hybridInverter.no ? 'background-color:#ffe;' : 'background-color:#fefefe;']">
                                <div class="card-header">@{{ hybridInverter.no }}</div>
                                <div style="text-align:center;background-image:url('/image/hi_icon.png');height:200px;">
                                    <img src="" />
                                </div>
                                @{{ hybridInverter}}
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
                            <li><a href="#tabs-1">発蓄電状況</a></li>
                            <li><a href="#tabs-2">バッテリー状況</a></li>
                            <li><a href="#tabs-3">発電VS消費</a></li>
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
<style>
    div.btn {
        transition: all 0.5s;
    }

    div.btn:hover {
        background-color: lightgray;
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
        }),
        setup() {},
        created() {},
        mounted() {
            vueObj = this;
            window.onload = () => {
                url = window.location.href;
                if (url.indexOf('?') != -1) {
                    this.token = url.split('?')[1];
                    this.token = this.token.split('=')[0];
                    window.localStorage.setItem('token', this.token);
                } else {
                    this.token = window.localStorage.getItem('token');
                }
                this.getMyHybridInverters();
            };
        },
        methods: {
            getMyHybridInverters() {
                const accessToken = this.token;
                axios
                    .get('/api/v1/log/getMyHybridInverters?token=' + accessToken, {})
                    .then((response) => {
                        try {
                            if (response.data.code == 0) {
                                this.hybridInverters = response.data.data;
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
                                this.makeChartDaily(response.data.datas);
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
                        PoolBatt: 'batteryPoolPower',
                    };

                    let labels = [];
                    let datas = [];
                    let totals = [];
                    Object.keys(keyTotal).forEach((key) => {
                        totals[keyTotal[key]] = [];
                    });

                    Object.keys(dataOrgn).forEach((key) => {
                        let row = dataOrgn[key];
                        Object.keys(row).forEach((clm) => {
                            if (datas[clm] == undefined) {
                                datas[clm] = [];
                            }
                            datas[clm].push(row[clm]);
                        });
                        var key2 = ''
                        var num = 0;

                        key2 = keyTotal.PowerPV;
                        num = row['pv_power'];
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PowerInverter;
                        num = row['inverter_power'];
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PowerInverter;
                        num = row['battery_charge_power'];
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PowerGridCharge;
                        num = row['grid_battery_charge_current'] * row['grid_voltage'];
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PowerGridUse;
                        num = row['grid_input_current'] * row['grid_voltage'];
                        if (totals[key2].length > 0) {
                            num += totals[key2][totals[key2].length - 1];
                        }
                        totals[key2].push(num);

                        key2 = keyTotal.PoolBatt;
                        num = row['battery_current'] * row['battery_voltage'];
                        totals[key2].push(num);

                        labels.push(key);
                    });


                    // JSONデータ
                    var jsonDataA = {
                        "labels": labels,
                        "datasets": [{
                                "label": "battery_charge_power",
                                "data": datas['battery_charge_power'],
                                "borderColor": "rgba(255, 99, 132, 1)",
                                "backgroundColor": "rgba(255, 99, 132, 0.2)"
                            },
                            {
                                "label": "pv_power",
                                "data": datas['pv_power'],
                                "borderColor": "rgba(54, 162, 235, 1)",
                                "backgroundColor": "rgba(54, 162, 235, 0.2)"
                            },
                            {
                                "label": "inverter_power",
                                "data": datas['inverter_power'],
                                "borderColor": "rgba(75, 192, 192, 1)",
                                "backgroundColor": "rgba(75, 192, 192, 0.2)"
                            }
                        ]
                    };

                    var jsonDataB = {
                        "labels": labels,
                        "datasets": [{
                                "label": "battery_voltage",
                                "data": datas['battery_voltage'],
                                "borderColor": "rgba(255, 99, 132, 1)",
                                "backgroundColor": "rgba(255, 99, 132, 0.2)",
                                yAxisID: 'y1'
                            },
                            {
                                "label": "battery_soc",
                                "data": datas['battery_soc'],
                                "borderColor": "rgba(54, 162, 235, 1)",
                                "backgroundColor": "rgba(54, 162, 235, 0.2)",
                                yAxisID: 'y1'
                            },
                            {
                                "label": "battery_cap",
                                "data": totals[keyTotal.PoolBatt],
                                "borderColor": "rgba(235, 162, 235, 1)",
                                "backgroundColor": "rgba(54, 162, 235, 0.2)",
                                yAxisID: 'y2'
                            },
                        ]
                    };

                    var jsonDataC = {
                        "labels": labels,
                        "datasets": [{
                                "label": "PowerBatt",
                                "data": totals[keyTotal.PowerBatt],
                                "borderColor": "rgba(255, 99, 132, 1)",
                                "backgroundColor": "rgba(255, 99, 132, 0.2)"
                            },
                            {
                                "label": "PowerPV",
                                "data": totals[keyTotal.PowerPV],
                                "borderColor": "rgba(99, 255, 132, 1)",
                                "backgroundColor": "rgba(99, 255, 132, 0.2)"
                            },
                            {
                                "label": "PowerInverter",
                                "data": totals[keyTotal.PowerInverter],
                                "borderColor": "rgba(99, 132, 255, 1)",
                                "backgroundColor": "rgba(99, 132, 255, 0.2)"
                            },
                            {
                                "label": "PowerGridUse",
                                "data": totals[keyTotal.PowerGridUse],
                                "borderColor": "rgba(255, 132, 99, 1)",
                                "backgroundColor": "rgba(255, 132, 99, 0.2)"
                            },
                            {
                                "label": "PowerGridCharge",
                                "data": totals[keyTotal.PowerGridCharge],
                                "borderColor": "rgba(132, 99, 255, 1)",
                                "backgroundColor": "rgba(132, 99, 255, 0.2)"
                            },
                        ]
                    };

                    $(".chartGudance").slideUp();

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
                                x: {
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return jsonDataA.labels[index];
                                        },
                                        color: function(context) {
                                            var label = context.tick.label;
                                            var hour = parseInt(label.split(':')[0]);
                                            return 'rgba(0, 0, 0, 1)'; // 黒色
                                        },
                                        backgroundColor: function(context) {
                                            var label = context.tick.label;
                                            var hour = parseInt(label.split(':')[0]);
                                            if (hour >= 11 && hour < 12) {
                                                return 'rgba(0, 0, 0, 0.5)'; // 半透明の黒色
                                            }
                                            return 'rgba(255, 0, 255, 0.5)'; // 透明
                                        }
                                    }
                                }
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
                                x: {
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return jsonDataB.labels[index];
                                        },
                                        color: function(context) {
                                            var label = context.tick.label;
                                            var buf = dataOrgn[label];
                                            if (buf['grid_battery_charge_current'] > 0) {
                                                return 'rgba(255, 128, 128, 1)'; // 緑色
                                            } else if (dataOrgn[label]['pv_battery_charge_current'] > 0) {
                                                return 'rgba(128, 255, 128, 1)'; // 緑色
                                            }
                                            return 'rgba(0, 0, 0, 1)'; // 黒色
                                        },
                                        backgroundColor: function(context) {
                                            var label = context.tick.label;
                                            var hour = parseInt(label.split(':')[0]);
                                            if (hour >= 11 && hour < 12) {
                                                return 'rgba(0, 0, 0, 0.5)'; // 半透明の黒色
                                            }
                                            return 'rgba(255, 0, 255, 0.5)'; // 透明
                                        }
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'left',
                                    ticks: {
                                        beginAtZero: true
                                    }
                                },
                                y2: {
                                    type: 'linear',
                                    position: 'right',
                                    ticks: {
                                        beginAtZero: true
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
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
                                x: {
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return jsonDataB.labels[index];
                                        },
                                        color: function(context) {
                                            var label = context.tick.label;
                                            var buf = dataOrgn[label];
                                            if (buf['grid_battery_charge_current'] > 0) {
                                                return 'rgba(255, 128, 128, 1)'; // 緑色
                                            } else if (dataOrgn[label]['pv_battery_charge_current'] > 0) {
                                                return 'rgba(128, 255, 128, 1)'; // 緑色
                                            }
                                            return 'rgba(0, 0, 0, 1)'; // 黒色
                                        },
                                        backgroundColor: function(context) {
                                            var label = context.tick.label;
                                            var hour = parseInt(label.split(':')[0]);
                                            if (hour >= 11 && hour < 12) {
                                                return 'rgba(0, 0, 0, 0.5)'; // 半透明の黒色
                                            }
                                            return 'rgba(255, 0, 255, 0.5)'; // 透明
                                        }
                                    }
                                }
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