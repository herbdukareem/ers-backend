<?php

use App\Models\Facility;
use App\Models\LGA;
use App\Models\Ward;

$lgas  = LGA::all();
$wards = Ward::all();
$facilities = Facility::all();
?>
@extends('layouts.app')

@section('content')

<div class="w-full mt-4 bg-[black] p-5 text-white" style="border-radius: 25px;" id="appRoot2">
    <div class="grid grid-cols-4 w-full gap-5 mb-5">
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1">
            <span class="">Total Enrolee Visit</span>
            <h4 class=" font-bold text-xl">@{{encountersAnalytics?.total_visits}}</h4>
        </div>
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1" @click="visible = true">
            <span class="">Total Disinct Enrolee Visit</span>
            <h4 class=" font-bold text-xl">@{{encountersAnalytics?.total_distinct_visits}}</h4>
        </div>
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1">
            <span class="">Total Referrals</span>
            <h4 class=" font-bold text-xl">@{{encountersAnalytics?.total_referrals}}</h4>
        </div>
        <div class="chart-container p-3 grid md:grid-cols-2 grid-cols-1">
            <span class="">Total Non Referrals</span>
            <h4 class=" font-bold text-xl">@{{encountersAnalytics?.total_visits - encountersAnalytics?.total_referrals}}</h4>
        </div>
    </div>
    <div :key="chartRefresh" class="grid grid-cols-3 w-full gap-5">
        <div id="topAccessedChart" class="chart-container col-span-1  h-[250px] bg-[transparent]"></div>
        <div id="visitsBySexChart" class="chart-container col-span-1 h-[250px]"></div>
        <div id="visitsByModeOfEnrolmentChart" class="chart-container col-span-1 h-[250px]"></div>
        <div id="topfacilityAccessed" class="chart-container col-span-1 h-[250px]"></div>
        <div id="topWardAccessed" class="chart-container col-span-1 h-[250px]"></div>        
    </div>    

    <transition name="fade">
    <div v-if="visible" class="fixed inset-0 z-[9999999999999] overflow-y-auto h-full w-full">        
        <div class="fixed inset-0 z-[9999999999998] bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"  @click="visible=false"></div>
      <div class="relative top-20 z-[99999999999991] mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
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
  <div v-if="loading" class="bg-sky-500/70 inset-0 fixed z-[999999999999999999999] flex flex-col justify-center items-center   overflow-y-auto h-full w-ful" >
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
                const response = await axios.post('/reports/analytics', this.filters);
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
                    const response = await axios.post('reports/medical/analytics', this.filters);
                    this.medicalAnalytics = response.data
                    this.loading = false
                } catch (e) {

                }
            },
            /*      async fetchTotalEnrollees() {
                     const response = await fetch('/reports/total-enrollees');
                     this.totalEnrollees = await response.json();
                 }, */
            /*    async fetchTotalReferrals() {
                   const response = await fetch('/reports/total-referrals');
                //   this.totalReferrals = await response.json();
               },
               async fetchEncountersLastMonth() {
                   const response = await fetch('/reports/encounters-last-month');
                  // this.totalEncountersLastMonth = await response.json();
               },
               async encountersByQuarter(){
                   const response = await fetch('reports/encounters-by-quarter');
                  // this.totalEncountersThisYearByQuarter = response.json();
               },
               async top10Services(){
                   const response = await fetch('reports/top-10-services');
                   //this.top10ServicesThisYear = response.json();
               } */
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
        /* Optional: adds a subtle border */

        /* Glass effect */
        /* background-color: rgba(255, 255, 255, 0.25); 
            backdrop-filter: blur(5px); 
            -webkit-backdrop-filter: blur(5px);  */

        /* Additional styling */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        /* Optional: adds a subtle shadow */
    }
    .fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
</style>
@endsection
