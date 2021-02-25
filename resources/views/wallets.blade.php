@extends('layout.main')

@section('content')

    <!-- Page Heading -->


    <div class="card mb-4">
        <div class="card-header">
            Wallets list
            <span class="float-right"> <button class="btn " data-toggle="modal" data-target="#addWalletModal"><i
                        class="fas fa-fw fa-plus"></i></button> </span>
        </div>
        <div class="card-body">

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>
                        Wallet
                    </th>
                    <th>
                        Action
                    </th>
                </tr>

                </thead>
                <tbody>

                @foreach($allWallets as $wallet)

                    <tr>
                        <td>{{$wallet->wallet}}</td>
                        <td>
                            <button class="btn btn-danger" onclick="deleteWallet({{$wallet->id}})"> Delete</button>
                        </td>
                    </tr>
                @endforeach

                </tbody>

            </table>

        </div>
    </div>


    <div class="modal" tabindex="-1" role="dialog" id="addWalletModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" id="wallet" name="wallet"
                           placeholder="Please enter your wallet">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="addWallet()">Add</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



@endsection


@section('after_scripts')


    <script>

        function deleteWallet(walletId) {

            var postData = {
                _token: '{{csrf_token()}}',
                action: 'delete',
                walletId: walletId
            };


            $.post("/wallet_actions", postData, function () {

            })
                .done(function (result) {
                    // alert( "second success" );
                    if (result.error == false) {
                        window.location.reload();
                    } else {
                        alert(result.msg);
                    }

                })
                .fail(function () {
                    alert("error");
                });

        }

        function addWallet() {

            var postData = {
                _token: '{{csrf_token()}}',
                action: 'add',
                wallet: $('#wallet').val()
            };


            $.post("/wallet_actions", postData, function () {

            })
                .done(function (result) {
                    // alert( "second success" );
                    //
                    if (result.error == false) {
                        window.location.reload();
                    } else {
                        alert(result.msg);
                    }
                })
                .fail(function () {
                    alert("error");
                });

        }


    </script>


@endsection
