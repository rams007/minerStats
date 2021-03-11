@extends('layout.main')

@section('content')

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Settings</h1>

    <div class="card">
        <div class="card-header">
            Graph show options
        </div>
        <div class="card-body">
            <form id="graphSettings">
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="currentHashrate"
                               name="currentHashrate"
                               @if($settings['currentHashrate']==1) checked @endif >
                        <label class="form-check-label" for="currentHashrate">
                            Current Hashrate
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="avgHashrate" name="avgHashrate"
                               @if($settings['avgHashrate']==1) checked @endif >
                        <label class="form-check-label" for="avgHashrate">
                            Average Hashrate
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="activeWorkers"
                               name="activeWorkers" @if($settings['activeWorkers']==1) checked @endif >
                        <label class="form-check-label" for="activeWorkers">
                            Active workers
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="validShares" name="validShares"
                               @if($settings['validShares']==1) checked @endif >
                        <label class="form-check-label" for="validShares">
                            Valid Shares
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="staleShares" name="staleShares"
                               @if($settings['staleShares']==1) checked @endif >
                        <label class="form-check-label" for="staleShares">
                            Stale shares
                        </label>
                    </div>


                </div>
            </form>
            <div class="row">
                <button type="button" id="saveGraphSettings" class="btn btn-primary">Save</button>

            </div>

        </div>
    </div>




@endsection


@section('after_scripts')



    <script>

        $('#saveGraphSettings').click(function () {

            console.log('clicked');

            console.log($('#graphSettings').serialize());

            var postData = {
                _token: '{{csrf_token()}}',
                parametrs: $('#graphSettings').serialize()
            };

            $.post("/settings", postData, function () {

            })
                .done(function (result) {
                    toastr.success('Updated');

                })
                .fail(function () {
                    toastr.error('Cant update settings');
                });


        });

    </script>



@endsection
