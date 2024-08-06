@extends('layouts.app')

@section('content')
<div class="container">
</div>
<script>
    $(function() {
        if (location.href.indexOf('?')) {
            const tokenFromUrl = location.href.split('?')[1];
            window.localStorage.setItem('token', tokenFromUrl);
            const token = window.localStorage.getItem('token');
            $.get('/api/v1/log/getMyHybridInverters?token=' + token, {})
                .then((response) => {
                    try {
                        if (response.code == 0) {
                            location.href = '/home';
                        } else {
                            alert('api error[' + response.code + ']');
                        }
                    } catch (err) {
                        alert('system error[1]: ' + err.message);
                    }
                })
                .catch((err) => {
                    alert('system error[2]: ' + err.message);
                });
        }
    });
</script>
@endsection