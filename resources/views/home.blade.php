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
                                <div style="text-align:center;">
                                    <img src="/image/hi_icon.png" />
                                </div>
                            </div>
                        </div>
                    </div>
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
                    test
                    <p>Date: <input type="text" id="datepicker"></p>
                    <canvas id="myChart" width="400" height="400"></canvas>

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
    });
    const {
        createApp,
        ref
    } = Vue

    createApp({
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
                this.loadNumbers();
            };
        },
        methods: {
            loadNumbers() {
                const accessToken = this.token;
                axios
                    .get('/api/v1/log/getMyHybridInverterNumbers?token=' + accessToken, {})
                    .then((response) => {
                        try {
                            if (response.data.code == 0) {
                                this.hybridInverters = response.data.data;
                                if (response.data.data.length == 0) {
                                    this.hybridInverters = 0;
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
                                this.makeChart(response.data.datas);
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
            makeChart(data) {
                const ctx = $('#myChart');

                let labels = [];
                let datas = [];
                Object.keys(data).forEach((key) => {
                    let row = data[key];
                    Object.keys(row).forEach((clm) => {
                        if (datas[clm] == undefined) {
                            datas[clm] = [];
                        }
                        datas[clm].push(row[clm]);
                    });

                    labels.push(key);
                });

                // JSONデータ
                var jsonData = {
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

                if (chartA != null) {
                    chartA.destory();
                }

                chartA = new Chart(ctx, {
                    type: 'line',
                    data: jsonData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                ticks: {
                                    callback: function(value, index, values) {
                                        return jsonData.labels[index];
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

            },
            getBackgroundColor(hour) {
                return (hour >= 8 && hour < 12) ? 'rgba(255, 255, 0, 0.1)' : 'rgba(0, 0, 255, 0.1)';
            }
        }
    }).mount('#app');
</script>
@endsection