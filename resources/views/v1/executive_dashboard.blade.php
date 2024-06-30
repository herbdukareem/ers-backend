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
<script src="{{asset('components/chartFilter.js')}}"></script>
<div class="w-full mt-4 bg-[black] p-5 text-white" style="border-radius: 25px;" id="appRoot2">

    <div class="gradient w-50 p-[2px] mb-1 text-center grid md:grid-cols-3 grid-cols-5 mb-5">
        <span></span>
        <span class="text-center md:col-span-1 col-span-3">
            Data from @{{title}}
        </span>
        <i @click="reload()"  class=" fa fa-refresh mx-3 place-self-center mb-3 cursor-pointer hover:text-[#1BFFFF]" title="refresh"></i>
        <i @click="toggleFullscreen('appRoot2')" :class="isFullScreen? 'fa-solid' : ' fa-regular'" class=" fa-window-maximize place-self-end cursor-pointer hover:text-[#1BFFFF]" :title="isFullScreen?'Minimize':'Maximize'"></i>
    </div>
    <div class="grid md:grid-cols-4 grid-cols-1 w-full gap-5">
        <div class="chart-container md:col-span-4 px-3 py-2 grid grid-cols-1  place-content-center gap-3">
            <div class="grid sm:grid-cols-2 grid-cols-1 max-w-[200px]">
                <span class="place-self-center">Total Enrollee:</span>
                <h4 class=" font-bold text-xl place-self-center">@{{encountersAnalytics?.totalEnrolleesAll}}</h4>
            </div>
            <div class="cgradient" ></div>
            <div class="lg:flex grid-cols-1 overflow-y-auto" :class="`sm:grid-cols-${encountersAnalytics?.enrollee_by_scheme?.length}`">
                <div class="text-center lg:block md:px-0 min-w-[150px] px-3 flex justify-between" v-for="(scheme,i) in encountersAnalytics?.enrollee_by_scheme">
                    <span class="place-self-left text-sm">Total @{{scheme.mode_of_enrolment}}</span>
                    <h4>@{{formatNumber(scheme?.total)}}</h4>
                </div>
            </div>
        </div>
        <div class="chart-container md:col-span-2 px-3 py-2 grid grid-cols-1  place-content-center gap-3">
            <div class="grid sm:grid-cols-2 grid-cols-1">
                <span class="place-self-center">Total Capitation:</span>
                <h4 class=" font-bold text-xl place-self-center">@{{formatCurrency(encountersAnalytics?.capitation)}}</h4>
            </div>
            <div class="cgradient" ></div>
            <div class="grid sm:grid-cols-2 grid-cols-1">
                <div class="md:place-self-center md:block md:px-0 px-3 flex justify-between">                    
                    <span class="place-self-left text-sm">Medical Bills</span>
                    <h4>@{{formatCurrency(medicalAnalytics?.medical_bill_amount)}}
                        <span class="ml-2">(@{{computePerc(medicalAnalytics?.medical_bill_amount, encountersAnalytics?.capitation)}})</span>
                    </h4>
                </div>
                <div  class="md:place-self-center md:block md:px-0 px-3 flex justify-between">                
                    <span class="place-self-left text-sm text-right">Cap Proceeds</span>
                    <h4 class="text-right">@{{ formatCurrency(encountersAnalytics?.capitation - medicalAnalytics?.medical_bill_amount)}}
                    <span class="ml-2">(@{{computePerc(encountersAnalytics?.capitation - medicalAnalytics?.medical_bill_amount, encountersAnalytics?.capitation)}})</span>
                    </h4>
                </div>
            </div>
        </div>

        <div class="chart-container md:col-span-2 px-3 py-2 grid grid-cols-1  place-content-center gap-3">
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
        <!-- <div id="enrolleeByVulnerableGroup" class="chart-container col-span-1  h-[250px] bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand('enrolleeByVulnerableGroup')"></div> -->
        <div id="enrolleeByVulnerableGroupCont" class="speedialer w-full col-span-1 h-[250px] relative overflow-y-hidden" @dblclick="toggleExpand('enrolleeByVulnerableGroupCont')">
            <div class="text-center hidden titleData gradient w-50 p-[2px] mb-2">
                Data from @{{title}}
            </div>
            <div id="enrolleeByVulnerableGroup" v-show="!dataCharts?.enrolleeByVulnerableGroup" :class="!dataCharts?.enrolleeByVulnerableGroup ? 'activechart' : 'activeNot'" class="chart-container absolute h-[inherit] w-full bg-[transparent] transition-all duration-300 ease-in-out"></div>
            <div id="enrolleeByVulnerableGroup_sub"  v-show="dataCharts?.enrolleeByVulnerableGroup" :class="dataCharts?.enrolleeByVulnerableGroup ? 'activechart' : 'activeNot'" class="chart-container absolute h-[inherit] w-full bg-[transparent] transition-all duration-300 ease-in-out"></div>
            <p-speeddial :model="[
                { label: 'Refresh', icon: 'pi pi-refresh', command: () => { dataCharts.enrolleeByVulnerableGroup = false } },
                { label: 'Expand', icon: 'pi pi-window-maximize', command: () => { toggleExpand('enrolleeByVulnerableGroupCont') } }
            ]" :radius="80" direction="up" :style="{ left: 0, bottom: 0 }"></p-speeddial>
        </div>

        <div id="EnrolleeBySex" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand('EnrolleeBySex')"></div>
        <div id="enrolleeByZone" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand('enrolleeByZone')"></div>
        <div id="topAccessedServiceCont"  class="speedialer w-full col-span-1 h-[250px] relative overflow-y-hidden" @dblclick="toggleExpand('topAccessedServiceCont')">
                <div class="text-center hidden titleData gradient w-50 p-[2px] mb-2">
                    Data from @{{title}}
                </div>
                <div v-show="!dataCharts?.topAccessedService" :class="!dataCharts?.topAccessedService?'activechart': 'activeNot'" id="topAccessedService" class="chart-container absolute h-[inherit] w-full  bg-[transparent] transition-all duration-300 ease-in-out "  ></div>
                <div v-show="dataCharts?.topAccessedService" :class="dataCharts?.topAccessedService?'activechart': 'activeNot'" id="topAccessedService_sub" class="chart-container absolute h-[inherit] w-full  bg-[transparent] transition-all duration-300 ease-in-out "  ></div>
                <p-speeddial :model="[{label: 'Refresh',icon: 'pi pi-refresh',command: ()=>{dataCharts.topAccessedService = false} },{label: 'Expand',icon: 'pi pi-window-maximize',command: () => {toggleExpand('topAccessedServiceCont')}}]"
                :radius="80"  direction="up" :style="{ left: 0, bottom: 0 }" > </p-speeddial>
        </div>
        <div id="EnrolleeByOccupations" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand('EnrolleeByOccupations')"></div>
        <div id="medicalsChart" class="chart-container col-span-1 h-[250px]  bg-[transparent] transition-all duration-300 ease-in-out "  @dblclick="toggleExpand('medicalsChart')"></div>
    </div>

    <transition name="fade">
    <div v-if="visible">
        <v-chartfilter @on-selected="subChartReolver" :lgas="lgas" :wards="wards" @close="visible=false"></v-chartfilter>
    </div>
  </transition>
  <transition name="fade">
  <div v-if="loading" class="bg-sky-500/70 inset-0 fixed z-[100001] flex flex-col justify-center items-center   overflow-y-auto h-full w-full" >
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
        components:{
            'v-dropdown': DropdownWithFilter,
            'v-chartfilter': ChartFilter
        },
        data() {
            return {
                barsLength:100,
                requestResolver:0,
                requestResolved:true,
                isFullScreen:false,
                prefix: '<?=config('constant.prefix')?>',
                loading:true,
                visible:false,
                dateRange: [],
                dateType:'months',
                dataCharts:{

                },
                lgas: <?= json_encode($lgas) ?>,
                wards:<?= json_encode($wards) ?>,
                // Define data properties to store the fetched data
                encountersAnalytics: null,
                totalEnrollees: null,
                totalReferrals: null,
                totalEncountersLastMonth: null,
                totalEncountersLastYearByQuarter: {},
                totalEncountersThisYearByQuarter: {},
                servicesBySex: [],
                chartRefresh: 0,
                chartRefreshSub:0,
                medicalAnalytics:{},
                subchartvalue:'',
                selected_chartID:'',
                filterOptionvValue:'Date Type',
                filterOptions:['Date Type', 'Location', 'Zone'],
                filter:{
                    location:{lga_id:null, ward_id:null},
                    zone:null,
                    dateType:null,
                },
                filters: {
                    dateRange: null,
                    search: ''
                },
                swiper:null,

            }
                // Additional data properties...
        },
        created() {
            this.filters.dateRange = ['2019-01-01',this.getDateNow()],
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
            getDateNow(){
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0'); // Add leading zero for single-digit months
                const day = String(today.getDate()).padStart(2, '0'); // Add leading zero for single-digit days

                return `${year}-${month}-${day}`;

            },
            async customFetch(route, filter){
                this.chartRefreshSub += 1
                this.loading = true
                const response = await axios.post(this.prefix+'/'+route,filter);
                this.loading = false
                return response.data
            },
            toggleExpand(chartId) {
                if (this.expandedChartId === chartId) {
                    this.expandedChartId = null;
                } else {
                    this.expandedChartId = chartId;
                }

                const chartContainer = document.getElementById(chartId)
                if (this.expandedChartId) {
                    chartContainer.querySelector('.titleData')?.classList.remove('hidden')
                    chartContainer.style.position = 'fixed';
                    chartContainer.style.left = '0';
                    chartContainer.style.top = '0';
                    chartContainer.style.width = '100vw';
                    chartContainer.style.height = '100vh';
                    chartContainer.style.zIndex = '9999';
                    chartContainer.style.backgroundColor = '#000';
                } else {
                    chartContainer.querySelector('.titleData')?.classList.add('hidden')
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
                                click: (event) =>{
                                    this.visible = true;
                                    this.subchartvalue = event.target.point.category
                                    this.selected_chartID = chartId;
                                }
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
                        dateRange:this.filters.dateRange,
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

                    this.plotLineChart('medicalsChart', categories, 'CAP Total Amount', capTotalAmountData, 'Total Medical Bill', totalAmountData, "Medical Bills By Months","Cap Proceeds: ₦"+  (this.encountersAnalytics.capitation - this.medicalAnalytics.medical_bill_amount));
                } catch (e) {
                    console.log(e,4444)
                }
            },
            replotBars(){
                    const Okeys = Object.keys(this.encountersAnalytics.occupations).slice(0, this.barsLength);
                    const Ovalues = Object.values(this.encountersAnalytics.occupations).slice(0,this.barsLength);
                    this.plotChart('EnrolleeByOccupations', 'column', 'Enrollee By Occupations', Okeys, 'Occupations', Ovalues,false,-90);
                    this.plotChart('topAccessedService', 'bar', 'Top Accessed Services', Object.keys(this.encountersAnalytics.top_accessed), 'Services', Object.values(this.encountersAnalytics.top_accessed));
                    this.plotChart('enrolleeByVulnerableGroup', 'bar', 'Number of Enrollee By Category', Object.keys(this.encountersAnalytics.vulnerability_status), 'Vunerables', Object.values(this.encountersAnalytics.vulnerability_status));
            },
            formatCurrency(number, locale = 'en-US', options = {}) {
                return '₦'+ this.formatNumber(number)
            },
            formatNumber(number, locale = 'en-US', options = {}) {
                return new Intl.NumberFormat(locale, options).format(number);
            },
            computePerc(val, total){
                const output = (val/total)* 100
                if(isNaN(output)){
                    return '0%'
                }
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
            },
            reload(){
                this.fetchTotalEncounters();
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
    app.use(primevue.config.default);
    app.component('p-speeddial', primevue.speeddial);
    app.component('p-dialog', primevue.dialog);
    app.component('p-dropdown', primevue.dropdown);
    app.component('p-selectbutton', primevue.selectbutton);

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
        transition: left 0.5s ease-in-out, opacity 0.5s ease;
    }


    .activeNot{
        left: -100%; /* Start from the left */
        opacity: 0; /* Start faded out */
    }

    .activechart {
        left: 0; /* Slide in */
        opacity: 1; /* Fade in */
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.5s;
    }

.speedialer:hover .p-speeddial{
    transform: translateY(0px);
    opacity: 1;
}
.p-speeddial{
    transition: left 0.5s ease-in-out, opacity 0.5s ease;
    transform: translateY(70px);
    opacity: 0;
}
.p-speeddial button.p-speeddial-button {

     /* Adjust the color to your preference */
    cursor: pointer;
}
.p-speeddial button.p-speeddial-button:focus{
    outline: 0;
}
.p-speeddial .p-speeddial-item {
    display: flex;
    justify-content: center;
    align-items: center;
}

/*
  Enter and leave animations can use different
  durations and timing functions.
*/
.slide-fade-enter-active, .slide-fade-leave-active {
  transition: all 0.3s;
}

.slide-fade-enter-from {
  transform: translateX(-20%); /* Start off-screen to the left */
  transition-delay: 0.3s;
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(20%); /* Exit off-screen to the right */
  transition-delay: 0.3s;
  opacity: 0; /* Optionally fade out as it slides away */
}


</style>
@endsection
