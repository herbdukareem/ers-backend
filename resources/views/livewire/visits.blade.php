
<div class="w-100 px-3 mt-4">
    <div class="">
        <div class="row round p-3 card flex-row bg-light d-flex justify-content-between border">
            <div class="col-md-2">
                <label>Filter</label>
                <input id="input" wire:model="dateRange" class="form-control" autocomplete="off">
            </div>
            <div class="col-md-2">
                <label>LGA</label>
                <select class="form-control" autocomplete="off" wire:model="searchLga" >
                    <option value=""></option>
                    @foreach($lgas as $lga)
                        <option value="{{$lga->id}}">{{$lga->lga}}</option>
                    @endforeach
                </select>                
            </div>
            <div class="col-md-2">
                <label>Ward</label>
                <select class="form-control" autocomplete="off" wire:model="searchWard" >
                    @foreach($wards as $ward)
                        <option value="{{$ward->id}}">{{$ward->ward}}</option>
                    @endforeach
                </select> 
            </div>
            <div class="col-md-2">
                <label>Facility</label>
                <select class="form-control" autocomplete="off" wire:model="searchFacility" >
                    <option value=""></option>
                    @foreach($facilities as $facility)
                        <option value="{{$facility->id}}">{{$facility->hcpname}}</option>
                    @endforeach
                </select>                 
            </div>
            <div class="col-md-2">
                <button wire:click="clear" class="btn btn-light mx-2" wire:loading.attr="disabled">Clear</button>
                <button wire:click="exportData" class="btn btn-light mx-2" wire:loading.attr="disabled">Export</button>
            </div>
        </div>
    </div>
    <table class="table table-condensed table-bordered shadow-sm">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Name</th>
                <th>Nicare ID</th>
                <th>LGA</th>
                <th>Ward</th>
                <th>Facility</th>
                <th>Phone Number</th>
                <th>Reporting Month</th>
                <th>Date of Visit</th>
                <th>Reason for Visit</th>
                <th>Service Accessed</th>
                <th>Referred</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enroleeVisits as $i=> $visit)
            <tr>
                <td>{{ ($enroleeVisits->perPage() * ($enroleeVisits->currentPage() - 1)) + $i+1}}</td>
                <td>{{$visit->name_of_enrolee}}</td>
                <td>{{$visit->nicare_id}}</td>
                <td>{{$visit->lga_name}}</td>
                <td>{{$visit->ward_name}}</td>
                <td>{{$visit->facility}}</td>
                <td>{{$visit->phone_number}}</td>
                <td>{{$visit->reporting_month}}</td>
                <td>{{$visit->date_of_visit}}</td>
                <td>{{$visit->reason_for_visit}}</td>
                <td>{{$visit->service}}</td>
                <td>{{$visit->referred}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="flex">
        <div class="pagi">
            {{$enroleeVisits->links()}}
        </div>
    </div>
    <div class="mt-3">
        <p class=" my-1 text-danger fw-bold"> Total Referred: {{$referred}}</p>
        <p class="my-1 text-danger fw-bold"> Total Non Referred: {{$not_referred}}</p>
    </div>

    <canvas wire:key="{{ $chatkey }}" id="genderCountsChart" style="width: 100%;height:700px;"></canvas>
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