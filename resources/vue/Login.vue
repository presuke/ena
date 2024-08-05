<script>
import logo from '../image/logo.png';

export default {
    data: function () {
        return {
            logo: logo
        }
    }
}
</script>
<template>
<div class="container">
    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">

                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
</div>

</template>
<style>
    #title {
        font-size: 50px;
        margin-left: 30px;
        float:left;
        font-family: 'Times New Roman', Times, serif;
    }
    .logo {
        margin-top: -50px;
        position: absolute;
        top:5;
        left:5;
        z-index:1;
    }
    .logo img{
        float:left;
        padding:10px;
        width:80px; 
        height:80px;
        filter: drop-shadow(0px 0px 15px sky-light);
    }

    x-sign {
        --interval: 1s;
        display: block;
        text-shadow: 
        0 0 10px var(--color1),
        0 0 20px var(--color2),
        0 0 40px var(--color3),
        0 0 80px var(--color4);
        will-change: filter, color;
        filter: saturate(60%);
        animation: flicker steps(100) var(--interval) 1s infinite;
    }
    x-sign:nth-of-type(1) {
        color: lightyellow;
        --color1: blue;
        --color2: blue;
        --color3: blue;
        --color4: lightblue;
        font-size: min(10vw, 40px);
        font-weight: 700;
        font-family: 'sans-serif', cursive;
    }
    @import url(//fonts.googleapis.com/css?family=Lato:300:400);

    .header_main {
        position:relative;
        text-align:center;
        height:100px;
        background: white;
    }

    .waves {
        position:relative;
        background-color: gray;
        width: 100%;
        height:100px;
        margin-bottom:-7px; /*Fix for safari gap*/
        min-height:30px;
        max-height:50px;
    }

    /* Animation */
    .parallax > use {
        animation: move-forever 25s cubic-bezier(.55,.5,.45,.5)     infinite;
    }
    .parallax > use:nth-child(1) {
        animation-delay: -2s;
        animation-duration: 7s;
    }
    .parallax > use:nth-child(2) {
        animation-delay: -3s;
        animation-duration: 10s;
    }
    .parallax > use:nth-child(3) {
        animation-delay: -4s;
        animation-duration: 13s;
    }
    .parallax > use:nth-child(4) {
        animation-delay: -5s;
        animation-duration: 20s;
    }
    @keyframes move-forever {
    0% {
        transform: translate3d(-90px,0,0);
    }
    100% { 
        transform: translate3d(85px,0,0);
    }
}
</style>