@extends('layouts.app')

@section('content')
<div class="container">
    authed
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
        alert(location.href);
    });
</script>
@endsection