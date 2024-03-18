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
        <div id="visitsBySexChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'visitsBySexChart')"></div>
        <div id="visitsByModeOfEnrolmentChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'visitsByModeOfEnrolmentChart')"></div>
        <div id="topfacilityAccessed" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'topfacilityAccessed')"></div>
        <div id="topWardAccessed" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'topWardAccessed')"></div>      
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
            plotChart(chartId, chartType, titleText, categories, seriesName, data, isDoughnut = false) {
                try {
                    Highcharts.chart(chartId, {
                        chart: {
                            type: chartType,
                            plotBackgroundColor: null,
                            backgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false
                        },
                        title: {
                            text: titleText,
                            style: {
                                color: '#FFFFFF'
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
       
                this.filters.dateRange = e.detail.value
                this.fetchTotalEncounters()
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
                const response = await axios.post(this.prefix+'/reports/analytics', this.filters);
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
