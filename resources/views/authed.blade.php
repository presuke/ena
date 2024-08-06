@extends('layouts.app')

@section('content')
<div class="container">
    <div>
        yourtoken:<span id="token"></span>
    </div>
    <div>
        <a href="/home">home</a>
    </div>
    <div>
        <a href="/logout">logout</a>
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
    $(function() {
        if (location.href.indexOf('?')) {
            const token = location.href.split('?')[1];
            window.localStorage.setItem('token', token);
            $("#token").html(window.localStorage.getItem('token'));
        }
    });
</script>
@endsection