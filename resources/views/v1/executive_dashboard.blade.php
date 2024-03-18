<?php

use App\Models\Facility;
use App\Models\LGA;
use App\Models\Ward;

$lgas  = LGA::all();
$wards = Ward::all();
$facilities = Facility::all();
?>
@extends('../layouts.app')

@section('content')

<div class="w-full mt-4 bg-[black] p-5 text-white" style="border-radius: 25px;" id="appRoot2">
    
    <div class="gradient w-50 p-[2px] mb-1 text-center grid md:grid-cols-3 grid-cols-5 mb-5">
        <span></span>
        <span class="text-center md:col-span-1 col-span-3">
            Data from @{{title}}
        </span>
        <i @click="toggleFullscreen('appRoot2')" :class="isFullScreen? 'fa-solid' : ' fa-regular'" class=" fa-window-maximize place-self-end cursor-pointer hover:text-[#1BFFFF]" :title="isFullScreen?'Minimize':'Maximize'"></i>
    </div>
    <div class="grid md:grid-cols-3 grid-cols-1 w-full gap-5">      
        <div class="chart-container px-3 py-2 grid grid-cols-1  place-content-center gap-3">
            <div class="grid sm:grid-cols-2 grid-cols-1">
                <span class="place-self-center">Total Enrollee:</span>
                <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.totalEnrolleesAll}}</h4>
            </div>
            <div class="cgradient" ></div>            
            <div class="grid grid-cols-1" :class="`sm:grid-cols-${encountersAnalytics?.enrollee_by_scheme?.length}`">
                <div class="text-center" v-for="(scheme,i) in encountersAnalytics?.enrollee_by_scheme">
                    <span class="place-self-left text-sm">Total @{{scheme.mode_of_enrolment}}</span>
                    <h4>@{{formatNumber(scheme?.total)}}</h4>
                </div>               
            </div>
        </div>  
        <div class="chart-container px-3 py-2 grid grid-cols-1  place-content-center gap-3">
            <div class="grid sm:grid-cols-2 grid-cols-1">
                <span class="place-self-center">Total Capitation:</span>
                <h4 class=" font-bold text-xl place-self-center">@{{formatCurrency(encountersAnalytics?.capitation)}}</h4>
            </div>
            <div class="cgradient" ></div>
            <div class="grid sm:grid-cols-2 grid-cols-1">
                <div class="place-self-center">
                    <span class="place-self-left text-sm">Medical Bills</span>
                    <h4>@{{formatCurrency(medicalAnalytics?.medical_bill_amount)}}
                    <span class="ml-2">(@{{computePerc(medicalAnalytics?.medical_bill_amount, encountersAnalytics?.capitation)}})</span>
                    </h4>
                </div>                
                <div  class="place-self-center">
                    <span class="place-self-left text-sm text-right">Prosit</span>
                    <h4 class="text-right">@{{ formatCurrency(encountersAnalytics?.capitation - medicalAnalytics?.medical_bill_amount)}}
                    <span class="ml-2">(@{{computePerc(encountersAnalytics?.capitation - medicalAnalytics?.medical_bill_amount, encountersAnalytics?.capitation)}})</span>
                    </h4>
                </div>
            </div>
        </div>
           
        <div class="chart-container px-3 py-2 grid grid-cols-1  place-content-center gap-3">
            <div class="grid sm:grid-cols-2 grid-cols-1">
                <span class="place-self-center">Total Encounter Visits:</span>
                <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.total_visits}}</h4>
            </div>
            <div class="cgradient" ></div>
            <div  class="text-center">
                    <span class="place-self-left text-sm text-right">Enrollees with Encounter Visit</span>
                    <h4 class="place-self-center">@{{encountersAnalytics?.total_distinct_visits}}        
                    <span class="ml-2">(@{{computePerc(encountersAnalytics?.total_distinct_visits,Number(encountersAnalytics?.totalEnrolleesAll.replaceAll(',','')))}})</span>
                    </h4>
                </div>
        </div>   
    </div>
    <div class="ggradient my-5"></div>
    <div :key="chartRefresh" class="grid md:grid-cols-3 grid-cols-1 w-full gap-5">
        <div id="enrolleeByVulnerableGroup" class="chart-container col-span-1  h-[250px] bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'enrolleeByVulnerableGroup')"></div>
        <div id="EnrolleeBySex" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'EnrolleeBySex')"></div>
        <div id="enrolleeByZone" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'enrolleeByZone')"></div>
        <div id="topAccessedService" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'topAccessedService')"></div>
        <div id="EnrolleeByOccupations" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'EnrolleeByOccupations')"></div>      
        <div id="medicalsChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'medicalsChart')"></div>  
    </div>    

    <transition name="fade">
    <div v-if="visible" class="fixed inset-0 z-[100] overflow-y-auto h-full w-full">        
        <div class="fixed inset-0 z-[108] bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"  @click="visible=false"></div>
      <div class="relative top-20 z-[109] mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center text-gray-700">
          <h3 class="text-lg leading-6 font-medium text-gray-700" v-if="title">tailwindcss</h3>
          <div class="mt-2 px-7 py-3">
            ls jeowjweiojweoie woiwjoiwejoeiweoijio
          </div>
          <div class="items-center px-4 py-3">
            <slot name="actions"></slot>
          </div>
        </div>
      </div>
    </div>
  </transition>
  <transition name="fade">
  <div v-if="loading" class="bg-sky-500/70 inset-0 fixed z-[100001] flex flex-col justify-center items-center   overflow-y-auto h-full w-ful" >
      <span class="loader"></span>
      <span class="text-2xl">loading</span>
  </div>
  </transition>

</div>

<script>
    const {
        createApp
    } = Vue

    const app = createApp({
        data() {
            return {
                barsLength:100,
                requestResolver:0,
                requestResolved:true,
                isFullScreen:false,
                prefix: '<?= env('PREFIX') ?>',
                loading:true,
                visible:false,
                dateRange: [],
                // Define data properties to store the fetched data
                encountersAnalytics: null,
                totalEnrollees: null,
                totalReferrals: null,
                totalEncountersLastMonth: null,
                totalEncountersLastYearByQuarter: {},
                totalEncountersThisYearByQuarter: {},

                servicesBySex: [],
                top10ServicesThisYear: [],
                top10ServicesLastYear: [],
                chartRefresh: 0,
                medicalAnalytics:{},
                filters: {
                    dateRange: 'Inception',
                    search: ''
                }
                // Additional data properties...
            }
        },
        created() {
            // Call the API endpoints when the component is created
            this.fetchTotalEncounters();
            /*    this.fetchTotalEnrollees();
               this.fetchTotalReferrals();
               this.fetchEncountersLastMonth();
               this.top10Services();
               this.encountersByQuarter();         */
        },
        mounted() {
            window.addEventListener('custom-input-event', this.searchedInput);
            window.addEventListener('custom-date-event', this.searchedDate);
        },
        methods: {
            toggleExpand(event, chartId) {                
                if (this.expandedChartId === chartId) {
                    this.expandedChartId = null;
                } else {
                    this.expandedChartId = chartId;
                }
                
                const chartContainer = event.currentTarget;                
                if (this.expandedChartId) {
                    chartContainer.style.position = 'fixed';
                    chartContainer.style.left = '0';
                    chartContainer.style.top = '0';
                    chartContainer.style.width = '100vw';
                    chartContainer.style.height = '100vh';
                    chartContainer.style.zIndex = '9999';
                    chartContainer.style.backgroundColor = '#000';
                } else {                    
                    chartContainer.style.position = '';
                    chartContainer.style.left = '';
                    chartContainer.style.top = '';
                    chartContainer.style.width = '';
                    chartContainer.style.height = '';
                    chartContainer.style.zIndex = '';
                    chartContainer.style.backgroundColor = '#000';
                }                          
            },
            plotChart(chartId, chartType, titleText, categories, seriesName, data, isDoughnut = false) {
                try {
                    Highcharts.chart(chartId, {
                        chart: {
                            type: chartType,
                            plotBackgroundColor: null,
                            backgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            options3d:this.options3d(chartType)
                        },
                        title: {
                            text: titleText,
                            style: {
                                color: '#FFFFFF',
                                fontSize:'1em'
                            }
                        },
                        xAxis: {
                            categories: categories, // Correctly assign categories array
                            labels: {
                                style: {
                                    color: '#FFFFFF'
                                }
                            }
                        },
                        yAxis: {
                            labels: {
                                style: {
                                    color: '#FFFFFF'
                                }
                            },
                            title: {
                                style: {
                                    color: '#FFFFFF'
                                }
                            }
                        },
                        plotOptions: {
                            type: chartType,
                            dataLabels: {
                                enabled: true
                            },
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true
                            },
                            showInLegend: true,
                            innerSize: isDoughnut ? '50%' : undefined,
                            series:this.dataLabels(chartType)
                        },
                        series: [{
                            name: seriesName,
                            colorByPoint: chartType === 'pie',
                            data,
                            color: {
                                linearGradient: {
                                    x1: 0,
                                    x2: 0,
                                    y1: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, '#1BFFFF'], // Darker at the bottom
                                    [1, '#2E3192'] // Lighter at the top
                                ]
                            }
                        }]
                    });
                } catch (e) {

                }
            },
            plotLineChart(chartId, categories, capTotalAmountName, capTotalAmountData, totalAmountName, totalAmountData, titleG,subtitle) {
                    Highcharts.chart(chartId, {
                        chart: {
                            zoomType: 'xy',
                            plotBackgroundColor: null,
                            backgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false},
                        title: { text: titleG, style:{color:'#fff'} },
                        xAxis: {
                            categories: categories,
                            crosshair: true,
                            labels:{
                                style:{color:'#fff'}

                            }
                        },
                        yAxis: [
                            {
                                labels:{
                                    format: '₦{value}', style:{color:'#fff'} 
                                },
                                title:null
                             /*    title:{                                
                                    text: capTotalAmountName, style:{color:'#fff'} 
                                }, */
                            },
                            {
                                labels:{
                                    format: '₦{value}', style:{color:'#fff'} 
                                },
                                title:null
                             /*    title:{                                
                                    text: totalAmountName, style:{color:'#fff'} 
                                }, */
                            },
                        ],
                        plotOptions: {
                            line: {
                                dataLabels: { enabled: true },
                                enableMouseTracking: true
                            }
                        },
                        tooltip: {
                            shared: true
                        },
                        subtitle: {
                            text: subtitle,
                            align: 'center',
                            style:{color:'#fff', fontWeight:'bolder'}
                        },
                        legend: {
                            align: 'center',
                            x: 0,
                            verticalAlign: 'bottom',
                            y: 20,
                            floating: true,
                            alignColumns:false,
                            itemHoverStyle:{color:'#fff', fontSize: "0.55em"},
                            itemStyle:{color:'#fff', fontSize: "0.55em"},
                            backgroundColor:null
                        },
                        series: [{
                            name: capTotalAmountName,
                            type: 'column',
                            data: capTotalAmountData,
                            tooltip: {
                                valuePrefix: '₦'
                            }
                        }, {
                            name: totalAmountName,
                            type: 'spline',
                            data: totalAmountData,
                            tooltip: {
                                valuePrefix: '₦'
                            }
                        }]
                    });
                },
            searchedDate(e) {                
                if(e.detail.value){
                    this.filters.dateRange = e.detail.value
                    this.fetchTotalEncounters(this.filters)            
                }
            },
            searchedInput(e) {
                setTimeout(() => {
                    this.filters.search = e.details.value
                    this.fetchTotalEncounters(this.filters)
                }, 2500)
            },
            async fetchMedical(){
                
               
            }, 
            options3d(type){
                if(type == 'pie'){
                    return { 
                        enabled: true,
                        alpha: 45,
                        beta: 0
                    }
                }
                return undefined
            },        
            dataLabels(type){
                if(type == 'pie'){
                    return {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            depth: 35,
                            dataLabels: 
                            [
                                { enabled: true, distance: 20}, 
                                { enabled: true, distance: -40, format: '{point.percentage:.1f}%', 
                                    style: { fontSize: '1.2em', textOutline: 'none', opacity: 0.7 },
                                    filter: { operator: '>', property: 'percentage', value: 10 }
                                }
                            ]
                        }
                }else if(type=='column'){
                    return {
                            borderWidth: 0,
                            dataLabels: {
                                rotation: -90,
                                enabled: true,                                
                            }
                        }
                }
                else{
                    return undefined
                }
            },
            async fetchTotalEncounters(filters={}) {
                this.chartRefresh += 1
                this.loading = true
                const response = await axios.post(this.prefix+'/executive/analytics',filters);
                this.encountersAnalytics = response.data
                this.loading = false
                try {
                    //                    
                    this.replotBars()
                    this.plotChart('EnrolleeBySex', 'pie', 'Enrollee by Sex', [], 'Sex', Object.entries(this.encountersAnalytics.sex).map(([value, name]) => ({
                        name,
                        y: parseInt(value)
                    })));                    
                    this.plotChart('enrolleeByZone', 'pie', 'Enrollee By Zone', [], 'Enrollee By Zone', this.encountersAnalytics.enrollee_by_zone.map(item => ({
                        name: item.zone,
                        y: item.total
                    })), true);


                    this.loading = true
                    const response = await axios.post(this.prefix+'/reports/medical/analytics', filters);
                    this.medicalAnalytics = response.data
                    this.loading = false
                    const categories = this.medicalAnalytics.medicals.map(item => item.date);
                    const capTotalAmountData = this.medicalAnalytics.medicals.map(item => parseInt(item.cap_total_amount));
                    const totalAmountData = this.medicalAnalytics.medicals.map(item => item.total_medicalbill_amount);

                    this.plotLineChart('medicalsChart', categories, 'CAP Total Amount', capTotalAmountData, 'Total Medical Bill', totalAmountData, "Medical Bills By Months","Prosit: ₦"+  (this.encountersAnalytics.capitation - this.medicalAnalytics.medical_bill_amount));
                } catch (e) {
                    console.log(e,4444)
                }
            },  
            replotBars(){
                    const Okeys = Object.keys(this.encountersAnalytics.occupations).slice(0, this.barsLength);
                    const Ovalues = Object.values(this.encountersAnalytics.occupations).slice(0,this.barsLength);
                    this.plotChart('EnrolleeByOccupations', 'column', 'Enrollee By Occupations', Okeys, 'Occupations', Ovalues);                    
                    this.plotChart('topAccessedService', 'bar', 'Top Accessed Services', Object.keys(this.encountersAnalytics.top_accessed), 'Services', Object.values(this.encountersAnalytics.top_accessed));
                    this.plotChart('enrolleeByVulnerableGroup', 'bar', 'Enrollee Vulnerable Group', Object.keys(this.encountersAnalytics.vulnerability_status), 'Vunerables', Object.values(this.encountersAnalytics.vulnerability_status));
            },
            formatCurrency(number, locale = 'en-US', options = {}) {
                return '₦'+ this.formatNumber(number)
            },
            formatNumber(number, locale = 'en-US', options = {}) {
                return new Intl.NumberFormat(locale, options).format(number);
            },
            computePerc(val, total){
                const output = (val/total)* 100
                    return output?.toFixed(1) + '%';
            },
            toggleFullscreen(elementId) {
                const elem = document.getElementById(elementId);
                const isFullscreen = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
                this.isFullScreen = !this.isFullScreen
                if (!isFullscreen) { // Enter fullscreen
                    if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                    } else if (elem.mozRequestFullScreen) {
                    elem.mozRequestFullScreen();
                    } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                    }
                } else { // Exit fullscreen
                    if (document.exitFullscreen) {
                    document.exitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                    }
                }
                }

        },
        watch:{
            requestResolver:(newV,oldV)=>{
                setTimeout(()=>{
                    this.requestResolved = true
                },2000)
            }
        },
        computed: {
            title() {
                if (Array.isArray(this.filters.dateRange)) {
                    return this.filters.dateRange.map(date => {
                        const formattedDate = new Date(date).toLocaleDateString('en-US', {
                        month: 'short', 
                        year: 'numeric'
                        });
                        return formattedDate;
                    }).join(' to ');
                }
                return this.filters.dateRange;
            }
        }

        
    })
 
    app.mount('#appRoot2')
</script>
<style>
    .pagi div nav div span {
        display: flex;
    }

    .chart-container {
        border-radius: 25px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.18);     

        /* Additional styling */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        /* Optional: adds a subtle shadow */
    }
    .fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.cgradient{
  height: 0.5px;
  width: 100%;
  background-color: #fff; /* For browsers that do not support gradients */
  background-image: linear-gradient(to right, #000, #fff, #000);
}
.ggradient{
  height: 1px;
  width: 100%;
  background-color: red; /* For browsers that do not support gradients */
  background-image: linear-gradient(to right, #1BFFFF, #2E3192, #1BFFFF);
}
.gradient{
    background-color: red; /* For browsers that do not support gradients */
    background-image: linear-gradient(to right, #000, #2E3192, #000);
}
</style>
@endsection
