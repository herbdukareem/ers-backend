
<div class="w-full mt-4 ">
    <div class="bg-white rounded  pb-3 border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border">
    <div class="p-3" >
        <div class="flex flex-wrap p-3 rounded-lg card justify-between">
            <div class="w-full md:w-1/6">
                <label class="block">Filter</label>
                <input id="input" wire:model="dateRange" class="w-full border" autocomplete="off">
            </div>
            <div class="w-full md:w-1/6">
                <label class="block">LGA</label>
                <select class="w-full border" autocomplete="off" wire:model="searchLga">
                    <option value=""></option>
                    @foreach($lgas as $lga)
                    <option value="{{$lga->id}}">{{$lga->lga}}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-1/6">
                <label class="block">Ward</label>
                <select class="w-full border" autocomplete="off" wire:model="searchWard">
                    <option value=""></option>
                    @foreach($wards as $ward)
                    <option value="{{$ward->id}}">{{$ward->ward}}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-1/6">
                <label class="block">Facility</label>
                <select class="w-full border" autocomplete="off" wire:model="searchFacility">
                    <option value=""></option>
                    @foreach($facilities as $facility)
                    <option value="{{$facility->id}}">{{$facility->hcpname}}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-1/6 flex items-center">
                <button wire:click="clear" class="btn btn-light mx-2 hover:text-[skyblue]" wire:loading.attr="disabled">Clear</button>
                <button wire:click="exportData" class="btn btn-light mx-2 hover:text-[skyblue]" wire:loading.attr="disabled">Export</button>
            </div>
        </div>
        <hr class="gradient-hr">
    </div>
    
    <div class="overflow-x-auto">
        <table  class="min-w-full text-left text-sm font-light">
        <thead class="border-b font-medium dark:border-neutral-500">
                <tr>
                    <th scope="col" class="px-6 py-1">S/N</th>
                    <th scope="col" class="px-6 py-1">Name</th>
                    <th scope="col" class="px-6 py-1">Nicare ID</th>
                    <th scope="col" class="px-6 py-1">LGA</th>
                    <th scope="col" class="px-6 py-1">Ward</th>
                    <th scope="col" class="px-6 py-1">Facility</th>
                    <th scope="col" class="px-6 py-1">Phone Number</th>
                    <th scope="col" class="px-6 py-1">Reporting Month</th>
                    <th scope="col" class="px-6 py-1">Date of Visit</th>
                    <th scope="col" class="px-6 py-1">Reason for Visit</th>
                    <th scope="col" class="px-6 py-1">Service Accessed</th>
                    <th scope="col" class="px-6 py-1">Referred</th>
                </tr>
            </thead>
            <tbody>
            @foreach($enroleeVisits as $i=> $visit)
                <tr  class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                    <td class="whitespace-nowrap px-6 py-4 font-medium">{{ ($enroleeVisits->perPage() * ($enroleeVisits->currentPage() - 1)) + $i+1}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->name_of_enrolee}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->nicare_id}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->lga_name}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->ward_name}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->facility}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->phone_number}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->reporting_month}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->date_of_visit}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->reason_for_visit}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->service}}</td>
                    <td class="whitespace-nowrap px-6 py-4">{{$visit->referred}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class=" mt-4">
        <div class="pagination px-4 py-4">
            {{$enroleeVisits->links()}}
        </div>
    </div>
    </div>
    <div class="mt-4 bg-white rounded p-2 my-5 border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border ">
        <p class="my-1 text-red-600 font-semibold">Total Referred: {{$referred}}</p>
        <p class="my-1 text-red-600 font-semibold">Total Non Referred: {{$not_referred}}</p>
    </div>
    <div class="bg-white rounded border-0 border-transparent border-solid shadow-xl dark:bg-slate-850 dark:shadow-dark-xl rounded-2xl bg-clip-border ">
        <canvas wire:key="{{ $chatkey }}" id="genderCountsChart" style="width: 100%;height:700px;"></canvas>
    </div>
</div>

<script>

   
var datasets = [];
var dateColorMap = []
    document.addEventListener("livewire:load", function() {
        Livewire.on('chatkeyUpdated', function(newChart) {
        initializeChart(JSON.parse(newChart));
    });

        /*  alert(3)
         var reasons = JSON.parse(@json($reasons));
         var maleCounts = JSON.parse(@json($maleCounts));
         var femaleCounts = JSON.parse(@json($femaleCounts));
         var totalCounts = JSON.parse(@json($totalCounts));
         var reporting_month = JSON.parse(@json($reporting_month)); */
    
         initializeChart(JSON.parse(@json($chartData)))

    })

    function getDateColor(date) {
        // Check if the date already has a color assigned
        if (!dateColorMap[date]) {
            // If not, generate a random color and store it in the map
            dateColorMap[date] = getRandomColor();
        }
        return dateColorMap[date];
    }
    // Function to generate random colors
    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
    function initializeChart(chartData){
        var datasetsD = chartData;
        // Create an array to hold datasets for each reason
        datasets = [];
        dateColorMap = []
        var data = {
            labels: [], // Reporting month on the x-axis
            datasets: datasets // Use the datasets array
        }
        const uniqueDates = [...new Set(datasetsD.flatMap((item) => item.reporting_month))];
        var dateRange = uniqueDates.sort();


        datasetsD.forEach((item) => {
            const dataset = {
                label: item.service,
                backgroundColor: getDateColor(item.service),
                borderColor: getDateColor(item.service),
                data: [],
                borderRadius: 15,
                barPercentage: 1.005,
            };

            // Loop through the data points for each city
            dateRange.forEach((date) => {
                const dataPoint = item.reporting_month === date;
                dataset.data.push(dataPoint ? item.total_count : 0);
            });

            data.labels = dateRange;
            data.datasets.push(dataset);

        });        

        var ctx = document.getElementById('genderCountsChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar', // Change the chart type to bar
            data,          
            options: {
                title: {
                    display: true,
                    text: 'Chart Title'
                },
                scales: {
                    x: {

                        type: 'category', // Set the x-axis type to category
                        ticks: {
                            autoSkip: false
                        },
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        ticks: {
                            major: {
                                enabled: true
                            }
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'value'
                        }
                    }
                }
            }
        });

    }
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
        multipleDatesSeparator: ' - ',
        view: 'months',
        minView: 'months',
        dateFormat: 'MMMM yyyy',
        onSelect: function(formattedDate, date, inst) {
            if (formattedDate.formattedDate.length > 1) {
                @this.set('dateRange', formattedDate.formattedDate)
            }
        }
    });

    // Listen for changes in the AirDatepicker input field
</script>
<style>
    .pagi div nav div span {
        display: flex;
    }
</style>