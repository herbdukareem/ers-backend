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
    <div class="grid grid-cols-4 gap-4 mb-4">
        <div>
            <label class="w-full">Programme:</label>
            <select v-model="selected_scheme" @change="handlefundCategory"  class="w-full text-[black]">
                <option v-for="sch in accountAnalytics?.scheme_income" :value="sch">@{{sch.name}}</option>
            </select>
        </div>
        <div>
            <label class="w-full" style="white-space:nowrap;">Fund Category:</label>
            <select v-model="selected_fund_category" @change="handlefundCategory" class="w-full text-[black]">
                <option v-for="fnd in fund_categories?.[selected_scheme?.id]" :value="fnd">@{{ fnd }}</option>
            </select>
        </div>
        <div class="flex col-span-2 justify-end items-center">
            <i @click="reload()"  class=" fa fa-refresh mx-3 place-self-center mb-3 cursor-pointer hover:text-[#1BFFFF]" title="refresh"></i>
            <i @click="toggleFullscreen('appRoot2')" :class="isFullScreen? 'fa-solid' : ' fa-regular'" class=" fa-window-maximize place-self-center mb-3 cursor-pointer hover:text-[#1BFFFF]" :title="isFullScreen?'Minimize':'Maximize'"></i>
        </div>
    </div>
    <div class="w-full overflow-auto mb-4 grid md:grid-cols-2 grid-cols-1 gap-4">
        <div class="w-full  mb-5 flex">
            <v-skeleton v-if="loading"  width="100%" height="250px"></v-skeleton>
            <div :key="chartRefresh" v-else :id="selected_scheme?.id+'_specs'"  class="chart-container col-span-1 h-[250px]  w-full bg-[transparent] transition-all duration-300 ease-in-out "></div>
        </div>
        <div id="latestFacilityReport" >
            <div v-if="loading">
                <v-skeleton width="100%" class="mb-2"  height="78px"></v-skeleton>
                <v-skeleton width="100%" class="mb-2"  height="78px"></v-skeleton>
                <v-skeleton width="100%"  height="78px"></v-skeleton>
            </div>
            <div v-else  class="chart-container col-span-1 h-[250px] px-3 pt-1 pb-10  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand($event, 'latestFacilityReport')">
                <h3 class="text-white text-center text-md"><b>Approvals Data: Newest Entries</b></h3>

            <ul class="h-[100%] overflow-y-auto">
                <li class="flex justify-between bgradient cursor-pointer hover:bg-white/20" v-for="record in  accountAnalytics?.transactions">
                    <span>
                        <p>@{{record.title}}</p>
                        <p>@{{record.approval_date}}</p>
                    </span>
                    <span class="p-1">@{{formatCurrency(record.amount)}}</span>
                </li>
            </ul>
        </div>
        </div>
        <div class=" h-[250px] chart-container px-3 pt-1" >
            <h3 class="text-white text-center text-md bg-[black] w-full p-2"><b>Expenditures</b></h3>
            <div class="h-[220px]" style="overflow-y: auto">
            <table class="border-collapse w-full">
                <thead class="bg-gray-100">
                  <tr>
                    <th  class="text-gray-500 text-left w-[20%] bgradient">S/N</th>
                    <th  class="text-gray-500 text-left w-[40%] bgradient">Appropriation</th>
                    <th  class="text-gray-500 text-left w-[40%] bgradient">Amount (<span>&#8358;</span>)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(appr, i) in expenditures" :key="i">
                    <td class="text-white bgradient">@{{ i + 1 }}</td>
                    <td class="text-white bgradient">@{{ appr.name }}</td>
                    <td class="text-white bgradient">@{{ formatCurrency(appr.expenditure_total_amount) }}</td>
                  </tr>
                </tbody>
              </table>
              <p class="p-4">
                <b>Total</b>: <span>&#8358;</span>
                <span v-if="getCategoryIncomeBalance == 0">0.00</span>
                <span v-else>@{{ formatNumber(total_expenditure) }}</span>
              </p>
            </div>
        </div>
    </div>


  {{-- <transition name="fade">
    <div v-if="loading" class="bg-sky-500/70 inset-0 fixed z-[100001] flex flex-col justify-center items-center   overflow-y-auto h-full w-full" >
        <span class="loader"></span>
        <span class="text-2xl">loading</span>
    </div>
  </transition>
 --}}
</div>

<script>
    const {
        createApp
    } = Vue

    const app = createApp({
        components:{
            'v-skeleton': primevue.skeleton
        },
        data() {
            return {
                isFullScreen:false,
                selected_fund_category:{},
                selected_scheme:{},
                prefix: '<?= config('constant.prefix') ?>',
                loading:true,
                visible:false,
                dateRange: [],
                fund_categories:{},
                total_expenditure:0,
                expenditures:[],
                // Define data properties to store the fetched data
                accountAnalytics: null,
                chartRefresh: 0,
                filters: {
                    dateRange: '',
                    search: ''
                }
                // Additional data properties...
            }
        },
        created() {
            // Call the API endpoints when the component is created
            this.fetchAccountAnalytics();
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
                            type: 'column',
                            zoomType: 'xy',
                            plotBackgroundColor: null,
                            backgroundColor: null,
                            plotBorderWidth: null,
                            height:250,
                            plotShadow: false
                        },
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
                                  style:{color:'#fff'},
                                  formatter:function(){

                                        return '₦'+ new Intl.NumberFormat('es-US', {}).format(this.value);
                                    },
                                },
                                title:null
                             /*    title:{
                                    text: capTotalAmountName, style:{color:'#fff'}
                                }, */
                            }
                        ],
                        plotOptions: {
                            column: {
                                borderRadius: '30%',
                                groupPadding: 0.1,
                                dataLabels: {
                                    enabled: true,
                                    rotation: rotate,
                                    distance: -40,

                                    style: { fontSize: '1.2em', textOutline: 'none', opacity: 0.7 },
                                    filter: { operator: '>', property: 'percentage', value: 10 }

                                 },

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
                            type: 'column',
                            data: totalAmountData,
                            tooltip: {
                                valuePrefix: '₦'
                            }
                        }]
                    });
            },
            searchedDate(e) {
                this.filters.dateRange = e.detail.value
                this.fetchAccountAnalytics()
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
                    this.fetchAccountAnalytics()
                }, 2500)
            },

            calculateTotalExpenditureAmount() {
                this.total_expenditure = this.expenditures.reduce((total, appr) => total + appr.expenditure_total_amount, 0);
            },
            async loadExpenditures(){
                const response = await axios.post(this.prefix+'/accounts/request', {
                    route:'fetch_expenditures',
                    method:'post',
                    scheme_id:this.selected_scheme?.id,
                    fund_category:this.selected_fund_category
                });

                this.expenditures = response.data
                this.calculateTotalExpenditureAmount()
            },
            handlefundCategory(){
                const scheme = this.selected_scheme.categories[this.selected_fund_category];
                const incomes = scheme.map(item => parseInt(item.income));
                const balances = scheme.map(item => item.balance);
                const categories = scheme.map(item => item.name);
                this.chartRefresh += 1
                this.loadExpenditures()
                setTimeout(()=>{
                    this.plotLineChart(this.selected_scheme.id+'_specs', categories, 'Head Budget', incomes, 'Balance', balances, "","Overall Balance:" + this.formatCurrency(this.selected_scheme.balance?.toFixed(2)),90);
                },2000)
            },
            async fetchAccountAnalytics() {
                this.chartRefresh += 1
                this.loading = true
                const response = await axios.post(this.prefix+'/accounts/analytics', this.filters);
                this.accountAnalytics = response.data
                this.loading = false
                try {
                    //

                   /*  this.plotChart('topWardAccessed', 'bar', 'Top Visits By Ward', Object.keys(this.accountAnalytics.top_wards), 'Services', Object.values(this.accountAnalytics.top_wards));
                    this.plotChart('topfacilityAccessed', 'bar', 'Top Visits By Facility', Object.keys(this.accountAnalytics.top_facility), 'Services', Object.values(this.accountAnalytics.top_facility));
                    this.plotChart('topAccessedChart', 'bar', 'Top Accessed Services', Object.keys(this.accountAnalytics.top_accessed), 'Services', Object.values(this.accountAnalytics.top_accessed));
                    this.plotChart('visitsBySexChart', 'pie', 'Visits by Sex', [], 'Sex', Object.entries(this.accountAnalytics.visits_by_sex).map(([value, name]) => ({
                        name,
                        y: parseInt(value)
                    })));
                    this.plotChart('visitsByModeOfEnrolmentChart', 'pie', 'Visits by Enrollee Programme', [], 'Enrollee Programme', this.accountAnalytics.visits_by_mode_of_enrolment.map(item => ({
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
                    */
                    this.selected_scheme = this.accountAnalytics.scheme_income[0];
                    for (let index = 0; index < this.accountAnalytics.scheme_income.length; index++) {
                        const scheme = this.accountAnalytics.scheme_income[index];
                        this.fund_categories[scheme.id] = Object.keys(scheme.categories)
                        if(index  == 0){
                            const lastIndex = this.fund_categories[scheme.id].length
                            this.selected_fund_category = this.fund_categories[scheme.id]?.[lastIndex-1]
                        }
                    }
                    this.handlefundCategory()
                } catch (e) {

                }
            },
            reload(){
                this.fetchAccountAnalytics()
            }
        }
    })
    app.use(primevue.config.default);
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
