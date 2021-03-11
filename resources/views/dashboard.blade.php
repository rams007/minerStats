@extends('layout.main')

@section('content')

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Dashboard Page</h1>

    @if($allWallets->count() == 0)

        <div class="alert alert-primary" role="alert">
            Please add your wallet first
        </div>

    @else

        <div class="row">

            <div class="col-sm-6">

                <select class="form-control" name="selectedWallet" id="selectedWallet">
                    @foreach($allWallets as $wallet)
                        <option value="{{$wallet->id}}"> {{$wallet->wallet}} </option>
                    @endforeach

                </select>

            </div>
            <div class="col-sm-6"><input type="text" name="daterange" value="{{$date1weekAgo}} - {{$today}}"
                                         class="form-control"/>
            </div>


        </div>

        <div class="row">
            <div id="chart1"></div>
        </div>

    @endif


@endsection


@section('after_scripts')


    @if($allWallets->count() > 0)



        <!-- Daterange block -->
        <script type="text/javascript" src="/js/moment.min.js"></script>
        <script type="text/javascript" src="/js/daterangepicker.min.js"></script>
        <link rel="stylesheet" type="text/css" href="/css/daterangepicker.css"/>

        <!-- nvd3 block -->
        <link href="/css/nv.d3.css" rel="stylesheet" type="text/css">
        <script src="/js/d3.min.js" charset="utf-8"></script>
        <script src="/js/nv.d3.js"></script>


        <style>
            svg {
                display: block;
            }

            #chart1 {
                width: 100%;
                height: 300px;
            }

            .dashed {
                stroke-dasharray: 5, 5;
            }
        </style>

        <script>
            $(function () {
                $('input[name="daterange"]').daterangepicker({
                    opens: 'left'
                }, function (start, end, label) {
                    var postData = {
                        _token: '{{csrf_token()}}',
                        walletId: $('#selectedWallet').val(),
                        startDate: start.format('YYYY-MM-DD'),
                        endDate: end.format('YYYY-MM-DD')
                    };


                    $.post("/graph_data", postData, function () {

                    })
                        .done(function (result) {
                            // alert( "second success" );
                            data[0].values = result.currentHashrates;
                            data[1].values = result.averageHashrates;
                            data[2].values = result.activeWorkers;
                            data[3].values = result.validShares;
                            data[4].values = result.staleShares;

                            chart.update();

                        })
                        .fail(function () {
                            toastr.error('Cant update graph data');
                        });


                });
            });
        </script>


        <script>
            // Wrapping in nv.addGraph allows for '0 timeout render', stores rendered charts in nv.graphs, and may do more in the future... it's NOT required
            var chart;
            var data;
            var legendPosition = "top";

            var randomizeFillOpacity = function () {
                var rand = Math.random(0, 1);
                for (var i = 0; i < 100; i++) { // modify sine amplitude
                    data[4].values[i].y = Math.sin(i / (5 + rand)) * .4 * rand - .25;
                }
                data[4].fillOpacity = rand;
                chart.update();
            };

            var toggleLegend = function () {
                if (legendPosition == "top") {
                    legendPosition = "bottom";
                } else {
                    legendPosition = "top";
                }
                chart.legendPosition(legendPosition);
                chart.update();
            };

            nv.addGraph(function () {
                chart = nv.models.lineChart()
                    .options({
                        duration: 300,
                        useInteractiveGuideline: true
                    })
                ;

                // chart sub-models (ie. xAxis, yAxis, etc) when accessed directly, return themselves, not the parent chart, so need to chain separately
                chart.xAxis
                    .axisLabel("Date")
                    .tickFormat(function (d) {
                        return d3.time.format('%b %d %H:%M')(new Date(d));
                    })
                    .staggerLabels(true);

                chart.yAxis
                    .axisLabel('Values')
                    .tickFormat(function (d) {
                        if (d == null) {
                            return 'N/A';
                        }
                        return d3.format(',.2f')(d);
                    })
                ;

                data =    [
                        @if(isset($enabledGraphs['currentHashrate']))
                    {
                        values: {!! $currentHashrates !!},
                        key: "Current Hashrate",
                        color: "#1f77b4",
                    },  @endif
                        @if(isset($enabledGraphs['avgHashrate']))
                    {
                        values:{!! $averageHashrates !!},
                        key: "Average Hashrate",
                        color: "#fe8d2a"
                    }, @endif
                        @if(isset($enabledGraphs['activeWorkers']))
                    {
                        values: {!! $activeWorkers !!},
                        key: "Active workers",
                        color: "#2222ff",
                        area: true
                    }, @endif
                        @if(isset($enabledGraphs['validShares']))
                    {
                        values: {!! $validShares !!},
                        key: "Valid Shares",
                        color: "#c3dec1",
                        strokeWidth: 3.5
                    }, @endif
                        @if(isset($enabledGraphs['staleShares']))
                    {
                        area: true,
                        values: {!! $staleShares !!},
                        key: "Stale shares",
                        color: "#EF9CFB",
                        fillOpacity: .1
                    }
                    @endif
                ];

                d3.select('#chart1').append('svg')
                    .datum(data)
                    .call(chart);

                nv.utils.windowResize(chart.update);

                return chart;
            });





        </script>

    @endif
@endsection
