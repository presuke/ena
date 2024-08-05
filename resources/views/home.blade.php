@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div id="app">
            <div class="col-md-3" style="float:left; margin-right:10px;">
                <div class="card">
                    <div class="card-header">ハイブリッドインバータ</div>
                    <div v-if="hybridInverters == 0">
                        loading...
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
                <div class="card">
                    <div class="card-header">デイリーデータ</div>
                    <p>Date: <input type="text" id="datepicker"></p>
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
<script>
    var vueObj;
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
            serverTime: 'oooo',
            hybridInverters: 0,
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
                rootPath = url.split('/home')[0];
                this.token = url.split('?')[1];
                this.loadNumbers();
            };
        },
        methods: {
            loadNumbers() {
                axios
                    .get(this.rootPath + '/api/v1/log/getMyHybridInverterNumbers?token=' + this.token, {})
                    .then((response) => {
                        try {
                            if (response.data.code == 0) {
                                this.hybridInverters = response.data.data;
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
                date = $("#datepicker").val();
                axios
                    .get(this.rootPath + '/api/v1/log/getHybridInverterDatas?token=' + this.token + '&no=' + this.selectedHybridInverter + '&date=' + date, {})
                    .then((response) => {
                        try {
                            if (response.data.code == 0) {
                                this.hybridInverterData = response.data.data;
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
            }
        }
    }).mount('#app')
</script>
@endsection