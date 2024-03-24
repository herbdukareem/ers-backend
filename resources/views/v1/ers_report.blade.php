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

<div class="w-full mt-4  p-5 text-white" style="border-radius: 25px;" id="appRoot2">

        <p-dataTable 
                :globalFilterFields="['nicare_id','full_name','service']"
                stateStorage="session" stateKey="dt-state-demo-session" paginator :rows="7" filterDisplay="menu"
                row-group-mode="rowspan" group-rows-by="name_of_enrolee" sort-mode="single"                
                :value="visits.data"  :sort-order="1"  :scrollable="true" scroll-height="400px"  table-style="min-width: 50rem">                            
                <template #groupheader="slotProps">
                <div class="flex align-items-center gap-2">                
                    <i class="fas fa-user-circle w-[35px] h-[35px]"></i>
                    <span>@{{ slotProps.data.full_name }}</span>
                </div>
            </template>
            <template #header>
                <IconField iconPosition="left">
                <InputIcon>
                    <i class="pi pi-search" />
                </InputIcon>
                <InputText  placeholder="Global Search" />
                </IconField>
            </template>
            <p-column header="#" header-style="width:3rem">
                    <template #body="slotProps">
                        @{{ slotProps.index + 1 }}
                    </template>
            </p-column>
            <p-column field="name_of_enrolee" header="Full Name" ></p-column>
            <p-column field="nicare_id" header="Nicare ID" ></p-column>
            <p-column field="lga_name" header="Lga" ></p-column>
           
            <p-column field="ward_name" header="Ward" ></p-column>
            <p-column field="facility" header="Facility" ></p-column>         
            <p-column field="service" header="Service" style="width: 20%"></p-column>
            <p-column field="reason_for_visit" header="Reason" style="width: 20%"></p-column>
            <p-column field="date_of_visit" header="Date of Visit" ></p-column>
            <template #groupfooter="slotProps">
                <div class="flex justify-content-end font-bold w-full">Total Visits: @{{slotProps.data.visit_count}}</div>
            </template>
        </p-dataTable>
</div>

<script>
    const FilterMatchMode = primevue.FilterMatchMode
    const FilterOperator =  primevue.FilterOperator
    const {
        createApp
    } = Vue

    const app = createApp({
        data() {
            return {
                expandedRowGroups:null,
                date:null,
                prefix: '<?= config('constant.prefix') ?>',
                loading:true,
                visible:false,
                visits:{data:[]},
                dateRange: [],
             /*    filter: {
                    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
                    full_name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
                    service: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
                }, */
                // Define data properties to store the fetched data
                filters: {
                    dateRange: '',
                    search: ''
                }
                // Additional data properties...
            }
        },
        created() {
            // Call the API endpoints when the component is created            
            /*    this.fetchTotalEnrollees();
               this.fetchTotalReferrals();
               this.fetchEncountersLastMonth();
               this.encountersByQuarter();         */
               this.fetchEncounters(this.filters);
        },
        mounted() {
            window.addEventListener('custom-input-event', this.searchedInput);
            window.addEventListener('custom-date-event', this.searchedDate);
        },
        methods: {
            onRowGroupExpand(){

            },
            onRowGroupCollapse(){

            },
            async fetchEncounters(filters){
                const response = await axios.post(this.prefix+'/ecounters',filters);
                this.visits = response.data
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
       
        }
    })
    app.use(primevue.config.default);
    app.component('p-column', primevue.column); 
    app.component('p-datatable', primevue.datatable); 
    app.component('p-paginator', primevue.paginator); 
    app.component('p-virtualscroller', primevue.virtualscroller); 
    app.component('p-row', primevue.row); 
    app.component('InputText', primevue.inputtext); 
    app.component('IconField', primevue.iconfield);     
    app.component('InputIcon', primevue.inputicon);     
    app.component('p-columngroup', primevue.columngroup); 
    app.component('p-columngroup', primevue.columngroup);     
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
.p-datatable-wrapper{
    height: 400px; 
}
.p-datatable-scrollable{
    
}
</style>
@endsection