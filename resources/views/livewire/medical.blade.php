<div class="w-100 px-3 mt-4">
    <div class="mb-3">
        <label>Filter</label>
        <div class="w-75 d-flex">
            <input id="input" wire:model="dateRange" class="form-control" autocomplete="off">
            <button wire:click="clear" class="btn btn-light mx-2" wire:loading.attr="disabled">Clear</button>
            <button wire:click="exportData" class="btn btn-light mx-2" wire:loading.attr="disabled">Export</button>
        </div>
    </div>
    <table class="table table-condensed table-bordered shadow-sm">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Facility</th>                                
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medical_bills as $i=> $visit)
            <tr>
                <td>{{ ($medical_bills->perPage() * ($medical_bills->currentPage() - 1)) + $i+1}}</td> <td>{{$visit->facility}}</td>                
                <td>{{$visit->amount}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagi">
        {{$medical_bills->links()}}
    </div>

    <canvas wire:key="{{ $chatkey }}" id="genderCountsChart" style="width: 100%;height:400px;"></canvas>
</div>

<script>

   /* 
var datasets = [];
var dateColorMap = []
    document.addEventListener("livewire:load", function() {
        Livewire.on('chatkeyUpdated', function(newChart) {
        initializeChart(JSON.parse(newChart));
    });


    
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
                barPercentage: 3,
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
        range: true,
        multipleDatesSeparator: ' - ',
        onSelect: function(formattedDate, date, inst) {
            if (formattedDate.formattedDate.length > 1) {
                @this.set('dateRange', formattedDate.formattedDate)
            }
        }
    });
 */
    // Listen for changes in the AirDatepicker input field
</script>
<style>
    .pagi div nav div span {
        display: flex;
    }
</style>