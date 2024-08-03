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
<div class="header_main">

    <!--Waves Container-->
    <div>
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
viewBox="0 24 100 28" preserveAspectRatio="none" shape-rendering="auto">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
                </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(0,0,0,0.7" />
                <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(0,0,0,0.5)" />
                <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(0,0,0,0.3)" />
                <use xlink:href="#gentle-wave" x="48" y="7" fill="#000" />
            </g>
        </svg>
        <div class="logo">
            <img :src="logo" />
            <x-sign id="title">
                人狼
            </x-sign>
        </div>
    </div>
<!--Waves end-->
</div>
<!--Header ends-->

</template>
<style>
    #title {
        font-size: 60px;
        margin-left: 30px;
        float:left;
    }
    .logo {
        margin-top: 0px;
        position: absolute;
        top:5;
        left:5;
        z-index:1;
    }
    .logo img{
        float:left;
        width:80px; 
        height:80px;
        filter: drop-shadow(0px 0px 15px red);
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
        --color1: red;
        --color2: red;
        --color3: red;
        --color4: pink;
        font-size: min(10vw, 40px);
        font-weight: 700;
        font-family: 'Reggae One', cursive;
    }
    @import url(//fonts.googleapis.com/css?family=Lato:300:400);

    .header_main {
        position:relative;
        text-align:center;
        height:100px;
        background: black;
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