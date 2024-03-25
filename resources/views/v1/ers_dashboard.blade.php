<?php

use App\Models\Facility;
use App\Models\Lga;
use App\Models\Ward;

$lgas  = Lga::all();
$wards = Ward::all();
$facilities = Facility::all();
?>
@extends('../layouts.app')

@section('content')

<div class="w-full mt-4 bg-[black] p-5 text-white" style="border-radius: 25px;" id="appRoot2">
    <div class="grid md:grid-cols-4 grid-cols-1 w-full gap-5 mb-5">
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1">
            <span class="place-self-center">Total Visit</span>
            <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.total_visits}}</h4>
        </div>
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1" @click="visible = true">
            <span class="place-self-center">Enrollees with Encounter Visit</span>
            <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.total_distinct_visits}}</h4>
        </div>
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1">
            <span class="place-self-center">Total Referrals</span>
            <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.total_referrals}}</h4>
        </div>
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1">
            <span class="place-self-center">Total Non Referrals</span>
            <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.total_visits - encountersAnalytics?.total_referrals}}</h4>
        </div>
    </div>
    <div :key="chartRefresh" class="grid md:grid-cols-3 grid-cols-1 w-full gap-5">
        <div id="topAccessedChart" class="chart-container col-span-1  h-[250px] bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'topAccessedChart')"></div>
        <div class="md:col-span-2 col-span-1 grid md:grid-cols-3 grid-cols-1 w-full gap-5">
            <div id="visitsBySexChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'visitsBySexChart')"></div>
            <div id="visitsByModeOfEnrolmentChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'visitsByModeOfEnrolmentChart')"></div>
            <div id="latestFacilityReport" class="chart-container col-span-1 h-[250px] px-3 pt-1 pb-10  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'latestFacilityReport')">
                <h3 class="text-white text-center text-md"><b>Facility Data: Newest Entries</b></h3>
                <ul class="h-[100%] overflow-y-auto">
                    <li class="flex justify-between bgradient cursor-pointer hover:bg-white/20" v-for="record in  encountersAnalytics?.newest_facility_entries">
                        <span>
                            <p>@{{record.facility}}</p>
                            <p>@{{record.since_added}}</p>
                        </span>
                        <span class="p-1">@{{record.total}}</span>
                    </li>                            
                </ul>
            </div>
        </div>
        <div id="topfacilityAccessed" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'topfacilityAccessed')"></div>
        <div id="topWardAccessed" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'topWardAccessed')"></div>      
        <div id="medicalsChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'medicalsChart')"></div>  
    </div>    

    <transition name="fade">
    <div v-if="visible">        
        <v-chartfilter @on-selected="subChartReolver" :lgas="lgas" :wards="wards" @close="visible=false"></v-chartfilter>
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
                prefix: '<?= config('constant.prefix') ?>',
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
                    dateRange: '',
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
            plotChart(chartId, chartType, titleText, categories, seriesName, data, isDoughnut = false, rotate=0) {
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
                            series:this.dataLabels(chartType,chartId, rotate),                            
                          
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
            plotLineChart(chartId, categories, capTotalAmountName, capTotalAmountData, totalAmountName, totalAmountData, titleG,subtitle, rotate =0) {
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
                            },
                            series: {
                                rotation: rotate,
                                enabled: true,                                
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
                this.filters.dateRange = e.detail.value
                this.fetchTotalEncounters()
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
            dataLabels(type, chartId="default", rotate=-90){
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
                                rotation: rotate,
                                enabled: true,                                
                            }
                        }
                }else{
                    return {
                        events: {
                            /*     click: (event) =>{
                                    this.visible = true;
                                    this.subchartvalue = event.target.point.category  
                                    this.selected_chartID = chartId;                                                                      
                                } */
                            },
                            dataLabels: {
                                rotation: rotate,
                                enabled: true,                                
                            }
                    }
                }
            }, 
            async subChartReolver(filters){    
                this.visible = false                            
                const filter = {
                        value :this.subchartvalue,
                        dateRange:this.dateRange,
                        type:this.filters.type,
                        ...filters
                        /* dateType:this.dateType */

                }
                let chartName = this.subchartvalue;

                if (filters.location && filters.location.lga && filters.location.lga.lga) {
                    chartName += ' in <span class="capitalize">' + filters.location.lga.lga.toLowerCase()+'</span>';

                    if (filters.location.ward && filters.location.ward.ward) {
                        chartName += ' of <span class="capitalize">' + filters.location.ward.ward.toLowerCase()+'</span>';
                    }
                } else if (filters && filters.zone) {
                    chartName += ' by Zone';
                }

                if(this.selected_chartID == 'topAccessedService'){
                    const response = await this.customFetch('top_accessed_services',filter)                                        
                    const categories = response.map(item => item.name);
                    const data = response.map(item => item.total);                    
                    this.plotChart(this.selected_chartID+'_sub', 'area',chartName, categories, 'Total Cases', data);                                        
                    this.dataCharts[this.selected_chartID] = true
                }
                chartName = "Number of "+ chartName
                if(this.selected_chartID == 'enrolleeByVulnerableGroup'){
                    const response = await this.customFetch('enrollee_by_category',filter)                                        
                    const categories = response.map(item => item.name);
                    const data = response.map(item => item.total);                    
                    this.plotChart(this.selected_chartID+'_sub', 'area',chartName, categories, 'Values', data);                                        
                    this.dataCharts[this.selected_chartID] = true
                }
            },
            handleOverlayHide(value, type) {
                //this.dateType = dateType
                this.filter[type] = value
                this.subChartReolver(type)
                this.visible=false
            },
            formatCurrency(number, locale = 'en-US', options = {}) {
                return '₦'+ this.formatNumber(number)
            },
            formatNumber(number, locale = 'en-US', options = {}) {
                return new Intl.NumberFormat(locale, options).format(number);
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
            },            
            computePerc(val, total){
                const output = (val/total)* 100
                if(isNaN(output)){
                    return '0%'
                }
                return output?.toFixed(1) + '%';
            },   
            searchedInput(e) {
                setTimeout(() => {
                    this.filters.search = e.details.value
                    this.fetchTotalEncounters()
                }, 2500)
            },
            async fetchMedical(){                               
            },
            async fetchTotalEncounters() {
                this.chartRefresh += 1
                this.loading = true
                const response = await axios.post(this.prefix+'/ers/reports/analytics', this.filters);
                this.encountersAnalytics = response.data
                this.loading = false
                try {
                    //
                    
                    this.plotChart('topWardAccessed', 'bar', 'Top Visits By Ward', Object.keys(this.encountersAnalytics.top_wards), 'Services', Object.values(this.encountersAnalytics.top_wards));
                    this.plotChart('topfacilityAccessed', 'bar', 'Top Visits By Facility', Object.keys(this.encountersAnalytics.top_facility), 'Services', Object.values(this.encountersAnalytics.top_facility));
                    this.plotChart('topAccessedChart', 'bar', 'Top Accessed Services', Object.keys(this.encountersAnalytics.top_accessed), 'Services', Object.values(this.encountersAnalytics.top_accessed));
                    this.plotChart('visitsBySexChart', 'pie', 'Visits by Sex', [], 'Sex', Object.entries(this.encountersAnalytics.visits_by_sex).map(([value, name]) => ({
                        name,
                        y: parseInt(value)
                    })));
                    this.plotChart('visitsByModeOfEnrolmentChart', 'pie', 'Visits by Enrollee Programme', [], 'Enrollee Programme', this.encountersAnalytics.visits_by_mode_of_enrolment.map(item => ({
                        name: item.mode_of_enrolment,
                        y: item.total_visits
                    })), true);


                     this.loading = true
                    const response = await axios.post(this.prefix+'/reports/medical/analytics', this.filters);
                    this.medicalAnalytics = response.data
                    this.loading = false
                    const categories = this.medicalAnalytics.medicals.map(item => item.date);
                    const capTotalAmountData = this.medicalAnalytics.medicals.map(item => parseInt(item.cap_total_amount));
                    const totalAmountData = this.medicalAnalytics.medicals.map(item => item.total_medicalbill_amount);

                    this.plotLineChart('medicalsChart', categories, 'CAP Total Amount', capTotalAmountData, 'Total Medical Bill', totalAmountData, "Medical Bills By Months","Prosit: ₦"+this.medicalAnalytics.prosit);
                } catch (e) {

                }
            },       
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
</style>
@endsection
