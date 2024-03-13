<?php

use Illuminate\Support\Facades\Route;
?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ERS</title>
    <script src="{{asset('datepicker.js')}}"></script>    
    <link href="{{asset('datepicker.css')}}" rel="stylesheet">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
    <!-- Styles -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js" integrity="sha512-NQfB/bDaB8kaSXF8E77JjhHG5PM6XVRxvHzkZiwl3ddWCEPBa23T76MuWSwAJdMGJnmQqM0VeY9kFszsrBEFrQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>

    <script src="{{asset('chartjs.js')}}"></script>
    <style>
        /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */
        html {
            line-height: 1.15;
            -webkit-text-size-adjust: 100%
        }

        body {
            margin: 0
        }

        a {
            background-color: transparent
        }

        [hidden] {
            display: none
        }

        html {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
            line-height: 1.5
        }

        *,
        :after,
        :before {
            box-sizing: border-box;
            border: 0 solid #e2e8f0
        }

        a {
            color: inherit;
            text-decoration: inherit
        }

        svg,
        video {
            display: block;
            vertical-align: middle
        }

        video {
            max-width: 100%;
            height: auto
        }

        .bg-white {
            --tw-bg-opacity: 1;
            background-color: rgb(255 255 255 / var(--tw-bg-opacity))
        }

        .bg-gray-100 {
            --tw-bg-opacity: 1;
            background-color: rgb(243 244 246 / var(--tw-bg-opacity))
        }

        .border-gray-200 {
            --tw-border-opacity: 1;
            border-color: rgb(229 231 235 / var(--tw-border-opacity))
        }

        .border-t {
            border-top-width: 1px
        }

        .flex {
            display: flex
        }

        .grid {
            display: grid
        }

        .hidden {
            display: none
        }

        .items-center {
            align-items: center
        }

        .justify-center {
            justify-content: center
        }

        .font-semibold {
            font-weight: 600
        }

        .h-5 {
            height: 1.25rem
        }

        .h-8 {
            height: 2rem
        }

        .h-16 {
            height: 4rem
        }

        .text-sm {
            font-size: .875rem
        }

        .text-lg {
            font-size: 1.125rem
        }

        .leading-7 {
            line-height: 1.75rem
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto
        }

        .ml-1 {
            margin-left: .25rem
        }

        .mt-2 {
            margin-top: .5rem
        }

        .mr-2 {
            margin-right: .5rem
        }

        .ml-2 {
            margin-left: .5rem
        }

        .mt-4 {
            margin-top: 1rem
        }

        .ml-4 {
            margin-left: 1rem
        }

        .mt-8 {
            margin-top: 2rem
        }

        .ml-12 {
            margin-left: 3rem
        }

        .-mt-px {
            margin-top: -1px
        }

        .max-w-6xl {
            max-width: 72rem
        }

        .min-h-screen {
            min-height: 100vh
        }

        .overflow-hidden {
            overflow: hidden
        }

        .p-6 {
            padding: 1.5rem
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem
        }

        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem
        }

        .pt-8 {
            padding-top: 2rem
        }

        .fixed {
            position: fixed
        }

        .relative {
            position: relative
        }

        .top-0 {
            top: 0
        }

        .right-0 {
            right: 0
        }

        .shadow {
            --tw-shadow: 0 1px 3px 0 rgb(0 0 0 / .1), 0 1px 2px -1px rgb(0 0 0 / .1);
            --tw-shadow-colored: 0 1px 3px 0 var(--tw-shadow-color), 0 1px 2px -1px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .text-center {
            text-align: center
        }

        .text-gray-200 {
            --tw-text-opacity: 1;
            color: rgb(229 231 235 / var(--tw-text-opacity))
        }

        .text-gray-300 {
            --tw-text-opacity: 1;
            color: rgb(209 213 219 / var(--tw-text-opacity))
        }

        .text-gray-400 {
            --tw-text-opacity: 1;
            color: rgb(156 163 175 / var(--tw-text-opacity))
        }

        .text-gray-500 {
            --tw-text-opacity: 1;
            color: rgb(107 114 128 / var(--tw-text-opacity))
        }

        .text-gray-600 {
            --tw-text-opacity: 1;
            color: rgb(75 85 99 / var(--tw-text-opacity))
        }

        .text-gray-700 {
            --tw-text-opacity: 1;
            color: rgb(55 65 81 / var(--tw-text-opacity))
        }

        .text-gray-900 {
            --tw-text-opacity: 1;
            color: rgb(17 24 39 / var(--tw-text-opacity))
        }

        .underline {
            text-decoration: underline
        }

        .antialiased {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale
        }

        .w-5 {
            width: 1.25rem
        }

        .w-8 {
            width: 2rem
        }

        .w-auto {
            width: auto
        }

        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr))
        }

        @media (min-width:640px) {
            .sm\:rounded-lg {
                border-radius: .5rem
            }

            .sm\:block {
                display: block
            }

            .sm\:items-center {
                align-items: center
            }

            .sm\:justify-start {
                justify-content: flex-start
            }

            .sm\:justify-between {
                justify-content: space-between
            }

            .sm\:h-20 {
                height: 5rem
            }

            .sm\:ml-0 {
                margin-left: 0
            }

            .sm\:px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem
            }

            .sm\:pt-0 {
                padding-top: 0
            }

            .sm\:text-left {
                text-align: left
            }

            .sm\:text-right {
                text-align: right
            }
        }

        @media (min-width:768px) {
            .md\:border-t-0 {
                border-top-width: 0
            }

            .md\:border-l {
                border-left-width: 1px
            }

            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }
        }

        @media (min-width:1024px) {
            .lg\:px-8 {
                padding-left: 2rem;
                padding-right: 2rem
            }
        }

        @media (prefers-color-scheme:dark) {
            .dark\:bg-gray-800 {
                --tw-bg-opacity: 1;
                background-color: rgb(31 41 55 / var(--tw-bg-opacity))
            }

            .dark\:bg-gray-900 {
                --tw-bg-opacity: 1;
                background-color: rgb(17 24 39 / var(--tw-bg-opacity))
            }

            .dark\:border-gray-700 {
                --tw-border-opacity: 1;
                border-color: rgb(55 65 81 / var(--tw-border-opacity))
            }

            .dark\:text-white {
                --tw-text-opacity: 1;
                color: rgb(255 255 255 / var(--tw-text-opacity))
            }

            .dark\:text-gray-400 {
                --tw-text-opacity: 1;
                color: rgb(156 163 175 / var(--tw-text-opacity))
            }

            .dark\:text-gray-500 {
                --tw-text-opacity: 1;
                color: rgb(107 114 128 / var(--tw-text-opacity))
            }
        }
    </style>
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        .trax {
            transform: translateX(-310px) !important;
        }
        
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
  opacity: 0;
}
.loader {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  display: inline-block;
  position: relative;
  background: linear-gradient(0deg, rgba(45, 61, 180, 0.2) 33%, #fff 100%);
  box-sizing: border-box;
  animation: rotation 1s linear infinite;
}
.loader::after {
  content: '';  
  box-sizing: border-box;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #263238;
}
@keyframes rotation {
  0% { transform: rotate(0deg) }
  100% { transform: rotate(360deg)}
} 
    </style>
</head>

<body class="m-0 font-sans antialiased font-normal  text-base leading-default bg-[skyblue]/75 text-slate-500 overflow-hidden">
<canvas id="canvas" class="absolute z-[-1] w-full block"></canvas>
    <!-- <div class="absolute bg-border-radius  z-[-2] w-full bg-[skyblue]/75 dark:hidden" style="height: 300px;"></div> -->
    <main class="lg:ml-auto h-full max-h-screen transition-all duration-200 ease-in-out rounded-xl ps  xl:px-5 px-2  pl-2 py-5">

        <div id="app" class="sticky top-[1%]">
            <i class="top-0 left-0 fas fa-bars fixed text-gray-700   z-[100001] " @click="showMenu =false" v-if="showMenu"></i>
            <!-- Side Bar Begins -->
            <aside  :class="{'trax':showMenu}" class="fixed translate-x-0 inset-y-0 z-[100002] flex-wrap items-center justify-between block  w-2/3 md:w-1/2 lg:w-1/3 xl:w-[17vw] xl:w-[15vw] xl:left-0 p-0 my-4 overflow-y-auto antialiased transition-transform duration-200 -translate-x-full bg-white border-0 dark:shadow-none dark:bg-blue-850 ease-nav-brand z-990 xl:ml-6 rounded-2xl    shadow-xl" aria-expanded="false">
                <div class="h-[100%] relative">                    
                    <i @click="hideMenu()" class="absolute top-0 right-0 p-4 opacity-50 cursor-pointer fas fa-times dark:text-white text-slate-400" sidenav-close="" aria-hidden="true"></i>
                    <a class="block px-8 py-6 m-0 text-sm whitespace-nowrap dark:text-white text-slate-700" href="/">
                        <img src="{{asset('ers.webp')}}" class="inline h-full max-w-full transition-all duration-200 dark:hidden ease-nav-brand max-h-8" alt="main_logo">
                        <img src="../assets/img/logo-ct.png" class="hidden h-full max-w-full transition-all duration-200 dark:inline ease-nav-brand max-h-8" alt="main_logo">
                        <span class="ml-1 font-semibold transition-all duration-200 ease-nav-brand">ERS</span>
                    </a>
                    <hr class="gradient-hr">
                    <ul class="flex flex-col pl-0 mb-0 mt-3">
                        <li class="mt-0.5 w-full" >                            
                            <a href="/dashboard" :class="{'bg-blue-500/12':(currentRoute=='Dashboard')}" class="py-2 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" >
                                <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">                                
                                    <i class="relative fa fa-gauge top-0 leading-normal text-blue-500 ni ni-tv-2 text-sm"></i>
                                </div>
                                <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Dashboard</span>
                            </a>
                        </li>
                        <li class="mt-0.5 w-full" >                            
                            <a href="/" :class="{'bg-blue-500/12':(currentRoute=='Enrollee-Visits')}" class="py-2 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" >
                                <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">                                
                                    <i class="relative fa fa-gauge top-0 leading-normal text-blue-500 ni ni-tv-2 text-sm"></i>
                                </div>
                                <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Enrolee Visits</span>
                            </a>
                        </li>
                        <li class="mt-0.5 w-full" >                            
                            <a href="/medicals" :class="{'bg-blue-500/12':(currentRoute=='Medicals')}" class="py-2 dark:text-white dark:opacity-80 text-sm ease-nav-brand my-0 mx-2 flex items-center whitespace-nowrap rounded-lg px-4 font-semibold text-slate-700 transition-colors" >
                                <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-lg bg-center stroke-0 text-center xl:p-2.5">                                
                                    <i class="relative fa fa-gauge top-0 leading-normal text-blue-500 ni ni-tv-2 text-sm"></i>
                                </div>
                                <span class="ml-1 duration-300 opacity-100 pointer-events-none ease">Medicals Bill</span>
                            </a>
                        </li>               
                    </ul>
                    <div class="absolute bottom-[20px] inset-x-2">
                        <hr class="gradient-hr mb-5">
                        <div class="mx-auto flex justify-center items-center">
                            <img src="{{asset('nicare.jpg')}}" class="w-[50px] ml-2 inline" />
                            <img src="{{asset('niger_state.png')}}" class="w-[50px] ml-2 inline" />
                        </div>
                    </div>
                </div>
                
            </aside>
            <!-- Side Bar Ends -->
            <!-- Top Bar Begins -->
            <nav class=" flex flex-wrap items-center justify-between px-0 py-2 transition-all ease-in shadow-none duration-250 rounded-2xl lg:flex-nowrap lg:justify-start  backdrop-saturate-200 backdrop-blur-sm  dark:bg-slate-850/80 dark:shadow-dark-blur bg-[hsla(0,0%,100%,0.8)] shadow-blur z-[1000]">
                <div class="block md:flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
                    <nav>
                        <ol class="flex flex-wrap pt-1 mr-12 bg-transparent rounded-lg sm:mr-16">
                            <li class="leading-normal text-sm">
                                <a class="text-dark opacity-50" href="javascript:;">Pages</a>
                            </li>
                            <li class="text-sm pl-2 capitalize leading-normal text-dark before:float-left before:pr-2 before:text-white before:content-['/']" aria-current="page">{{ Route::currentRouteName() }}</li>
                        </ol>
                        <!-- <h6 class="mb-0 font-bold text-white capitalize"></h6> -->
                    </nav>
                    <div class="flex items-center mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 l g:flex lg:basis-auto">
                        <div class="flex items-center md:ml-auto md:pr-4 mr-2">
                            <div class="relative flex flex-wrap items-stretch w-full transition-all rounded-lg ease">
                                <span class="text-sm ease leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all">
                                    <i class="fas fa-calendar" aria-hidden="true"></i>
                                </span>
                                <input id="input" v-model="dateRange" class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 dark:bg-slate-850 dark:text-white bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow" autocomplete="off">                                
                            </div>
                            <div class="relative flex flex-wrap items-stretch w-full transition-all rounded-lg ease mx-2">
                                <span class="text-sm ease leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <input type="text" @input="sendWindowEvent($event)" class="pl-9 text-sm focus:shadow-primary-outline ease w-1/100 leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 dark:bg-slate-850 dark:text-white bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-blue-500 focus:outline-none focus:transition-shadow" placeholder="Type here...">
                            </div>
                        </div>
                        <ul class="flex flex-row justify-end pl-0 mb-0 list-none md-max:w-full">
                            <li class="flex items-center">
                                <a href="../pages/sign-in.html" class="flex px-0 py-2 font-semibold text-dark transition-all ease-nav-brand text-sm">
                                    <img src="{{asset('/logout.svg')}}" style="width: 14px; height:25px;" class="mr-1" />
                                    <span class="hidden sm:inline">Sign Out</span>
                                </a>
                            </li>
                            <li class="flex items-center pl-4 xl:hidden">
                                <a @click="showMenu = !showMenu" class="block p-0 transition-all ease-nav-brand text-sm dark:text-white" sidenav-trigger="">
                                    <div class="w-6 h-6 flex flex-col justify-between" style="height:15px;">
                                        <span class="h-0.5 w-full bg-slate-500 rounded-sm"></span>
                                        <span class="h-0.5 w-full bg-slate-500 rounded-sm"></span>
                                        <span class="h-0.5 w-full bg-slate-500 rounded-sm"></span>
                                    </div>
                                </a>
                            </li>


                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Top Bar Ends -->
        </div>
        <div class="w-full">
            @yield('content')
        </div>
    </main>
    <script>
        window.onload = function() {
            const laravelCurrentRouteName = '<?= Route::currentRouteName(); ?>'; 

            const {createApp} = Vue
            createApp({
                data() {
                    return {
                        dateRange2:[],
                        test:true,
                        showMenu: true, 
                        currentRoute: laravelCurrentRouteName,
                    };
                },
                methods:{
                    hideMenu(){                                              
                        this.showMenu =true
                    },
                    sendWindowEvent(event){
                        // Create a new custom event with the input's value
                        const customEvent = new CustomEvent('custom-input-event', {
                            detail: { value: event.target.value },
                        });
                        // Dispatch the event on the window object
                        window.dispatchEvent(customEvent);
                    },
                },
                watch: {
                    dateRange2(newVal, oldVal) {
                    // Your logic when dateRange2 changes
                    const customEvent = new CustomEvent('custom-date-event', {
                        detail: { value: newVal }, // Use newVal to access the updated value
                    });
                    // Dispatch the event on the window object
                    window.dispatchEvent(customEvent);
                    }
                },
                mounted(){
                    let localeEn = {
                    days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                    daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    today: 'Today',
                    clear: 'Clear',
                    dateFormat: 'yyyy-MM-dd',
                    timeFormat: 'hh:ii aa',
                    firstDay: 0
                };
                let datepicker = new AirDatepicker('#input', {
                    locale: localeEn,        
                    range:true,
                    multipleDatesSeparator: ' - ',
                    view: 'months',
                    minView: 'months',
                    dateFormat: 'dd-MM-yyyy',
                    onSelect:(formattedDate, date, inst) =>{
                        if(formattedDate.formattedDate.length >1){
                            
                            const customEvent = new CustomEvent('custom-date-event', {
                                detail: formattedDate.formattedDate, // Use newVal to access the updated value
                            });
                            // Dispatch the event on the window object
                            window.dispatchEvent(customEvent);
                            this.dateRange2 = formattedDate.formattedDate                        
                        }
                    }
                });
                }
            }).mount('#app')
        }
    </script>
    
    <style>
        .ps {
            overflow-y: scroll !important;
            overflow-anchor: none;
            -ms-overflow-style: none;
            touch-action: auto;
            -ms-touch-action: auto;
        }

        /* For WebKit browsers (like Chrome, Safari) */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 4px;
        }

        /* For Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        /* For Edge and IE */
        *::-ms-scrollbar {
            width: 8px;
            height: 8px;
        }

        *::-ms-scrollbar-track {
            background: #f1f1f1;
        }

        *::-ms-scrollbar-thumb {
            background-color: #888;
            border-radius: 4px;
        }

        .gradient-hr {
            border: none;
            height: 1px;
            /* Adjust the height of the hr */
            background: linear-gradient(to right, #fff, #eee, #fff);
            /* Change colors as needed */
        }

        .bg-slate-500 {
            --tw-bg-opacity: 1;
            background-color: rgb(103 116 142 / var(--tw-bg-opacity));
        }

        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.25, 0.1, 0.25, 1);
            transition-duration: 150ms;
        }       
    </style>
    <script src="{{asset('/grained.js')}}"></script>
</body>


</html>