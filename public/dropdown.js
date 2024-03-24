const DropdownWithFilter = {
    template: `
      <div class="w-[inherit] cursor-pointer">
      <div v-if="openDropdown" @click="openDropdown=false" class="top-0 left-0 w-[100vw] h-[100vh] fixed z-[1] "></div>
      <div class="relative z-[2] w-[inherit] ">
            <div class="form-input w-full bg-white px-4 py-2 text-left border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"                        
            @click="openDropdown=!openDropdown"
            >
            <span v-if="selectedValue">
              {{selectedValue}}
              </span>
              <span v-else>{{placeholder}}</span>
            </div>
            <div
            class="fixed w-[inherit] bg-white mt-1 shadow-lg z-[50]"
            v-show="openDropdown"
            >
            <div class="py-2 bg-white">
            <input
            type="text"
            class="form-input w-[80%] mx-auto px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            :placeholder="filter"
            :value="filter"
            @input="handleInput($event)"          
            />
            </div>
            <ul class="max-h-60  overflow-auto ">
            
                <li
                v-for="option in filteredOptions"
                :key="option[optionValue]"              
                @click="selectOption(option)"
                class="p-2 hover:bg-gray-100 cursor-pointer text-gray-600 text-left"
                >
                {{ option[optionLabel] }}
                </li>
            </ul>
            </div>
        </div>
      </div>
    `,
    props: {
      modelValue: null,
      options: Array,
      optionValue:{
        default:0
      },
      optionLabel:{
        default: 0
      },
      placeholder:'Type to filter...'
    },
    data() {
      return {
        filter: '',
        openDropdown: false,
        selectedValue:null,
        selectedLabel:null
      };
    },
    watch:{
      options(newv,oldv){
        this.filter=null
        this.selectedValue=null
        this.$emit('update:modelValue', null);
      }
    },
    computed: {
      filteredOptions() {
        if (!this.filter) return this.options;
        try{

            return this.options.filter(option =>
                option[this.optionLabel].toLowerCase().includes(this.filter.toLowerCase())
                );
        }catch(e){
            console.log(e)
        }
      },
    },
    created(){
        
    },
    methods: {
      handleInput(e){
        this.filter = e.target.value
        this.openDropdown =true
      },
      selectOption(option) {
        this.$emit('update:modelValue',this.optionValue === 0?option: option[this.optionValue]);
        this.$emit('change',this.optionValue === 0?option: option[this.optionValue]);
        this.filter = ''; // Update the input to show the selected label        
        this.selectedValue = option[this.optionLabel]
        setTimeout(()=>{
            this.openDropdown = false;
        },100)
      },
    },
  };
  