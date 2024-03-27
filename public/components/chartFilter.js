const ChartFilter = {
    template: `
    <div class="fixed inset-0 z-[10001] overflow-y-auto h-full w-full">
    <div class="fixed inset-0 z-[108] bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
      @click="$emit('close', false)"></div>
    <div class="relative top-20 z-[109] mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3 text-center text-gray-700">

      <h3 class=" mb-3 leading-6 text-left font-medium text-gray-700 cursor-pointer user-select-none" >
            <span class="text-left block text-lg">View By:</span>        
            <div class="mb-2 ">              
              <input type="radio"  v-model="filter.query_by" name="query_by" value="dateType" ref="dateType" class="cursor-pointer" /><span @click="$refs.dateType.click()"> DateType</span>
            </div>
            <div class="mb-2 ">
              <input type="radio"  v-model="filter.query_by" name="query_by" value="facility" ref="facility" class="cursor-pointer" /><span @click="$refs.facility.click()"> Facility</span>
            </div>
            <div class="mb-2 ">
              <input type="radio"  v-model="filter.query_by" name="query_by" value="ward" ref="ward" class="cursor-pointer" /><span @click="$refs.ward.click()"> Ward</span>
            </div>
        </h3>
            <div class="w-full">            
              <div class="mt-2 w-full mb-3" v-if="filter.query_by == 'dateType'">
                  <span class="text-left block">Date Type:</span>                
                  <select v-model="filter.dateType" class=" w-full text-gray-700  py-2 px-4 border border-gray-400 rounded"> 
                    <option value="days"> Day </option>
                    <option value="months"> Month </option>
                    <option value="years"> Year </option>
                  </select>              
                </div> 
                <div class="w-full mb-3" >
                  <span class="text-left block">Filter Option:</span>                
                  <select @change="selectOption($event)" v-model="filterOptionvValue" class=" w-full text-gray-700  py-2 px-4 border border-gray-400 rounded"> 
                    <option value=""> Select Option</option>
                    <option value="lga_ward"> LGA/Ward</option>
                    <option value="zone"> Zones</option>                    
                    </select>              
                </div>             
                <div class="mb-3 relative z-[1] " v-if="filterOptionvValue == 'lga_ward'">
                  <span class="text-left block">Lga:</span>                
                  <v-dropdown @change="updateWards" placeholder='Select Lga' v-model="filter.location.lga" :options="lgas"
                    option-label="lga" />
                </div>                              
                <div class="mb-3 relative z-[0] " v-show="filterOptionvValue == 'lga_ward' && filter.location.lga && filter.query_by =='ward'">
                  <span class="text-left block">Ward:</span>
                  <v-dropdown v-model="filter.location.ward" placeholder='Select Ward' :options="filterWards" 
                    optionLabel="ward" />
                </div>            
                <button class="mb-3 w-full bg-sky-500 text-white font-semibold py-2 px-4 border border-sky-700 rounded hover:bg-sky-400"
                  @click="handleOverlayHide(filter.location, 'location')">
                  Proceed
                </button>          
            </div>          
        </div>
      </div>
    </div>
  </div>
    `,    
    props:{
        lgas:{
          default:[]
        },
        wards:{
          default:[]
        },
        dateRange:null
    },
    components:{
        'v-dropdown': DropdownWithFilter,
        /* 'p-selectbutton':primevue.selectbutton */
    },
    data() {
      return {        
        use_date_type:[],
        filterOptionvValue:'lga_ward',
        filterOptions:['Date Type', 'Location', 'Zone'],        
        filterWards:[],
        filter:{
            location:{lga:null, ward:null},
            zone:null,
            query_by:'ward',
            dateType:null,
        },
      };
    },
    computed: {      
    },
    created(){
        
    },
    methods: {    
      updateWards(){
        this.filter.location.ward = null;
        const wards = this.wards.filter((item,i)=>{                    
          return item.lga_id == this.filter.location.lga.id
        });
        this.filterWards = wards;
      },        
      selectOption(e){
        const val = e.target.value
        this.filterOptionvValue = val
        if(val == 'zone'){          
          this.filter.zone = 'zone'
        }
      },     
      handleOverlayHide() {                          
            this.visible=false            
            this.$emit('on-selected', this.filter);            
        },       
    },
  };
  