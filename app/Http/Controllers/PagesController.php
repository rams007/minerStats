<?php

namespace App\Http\Controllers;

use App\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\EthereumValidator;

class PagesController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }
        $allWallets = Wallets::where('user_id', $user->id)->get(['id', 'wallet']);
        foreach ($allWallets as $wallet) {
            $trimed = substr($wallet->wallet, 0, 5);
            $trimed .= '****';
            $trimed .= substr($wallet->wallet, -5, 5);
            $wallet->wallet = $trimed;
        }
        $today = date('m/d/Y');
        $weekAgoTs = time() - 604800;
        $date1weekAgo = date('m/d/Y', $weekAgoTs);

        //@todo allow user to choose what data they need/ if user have only 1 worker them dont need show this line on graph

        $currentHashrates = [];
        $validShares = [];
        $staleShares = [];
        $averageHashrates = [];
        $activeWorkers = [];
        if (!empty($allWallets)) {

            $sql = "SELECT time,current_hashrate,valid_shares,stale_shares,average_hashrate,active_workers
                    FROM mining_stats
                    WHERE wallet_id = ? AND time >= ? AND time <= ? ";


            $allStats = DB::select($sql, [$allWallets[0]->id, date('Y-m-d 00:00:00', $weekAgoTs), date('Y-m-d 23:59:59')]);
            foreach ($allStats as $stat) {
                $x = strtotime($stat->time) * 1000;
                $currentHashrates[] = ['x' => $x, 'y' => $stat->current_hashrate / 1000000]; //convert to MegaHash
                $validShares[] = ['x' => $x, 'y' => $stat->valid_shares];
                $staleShares[] = ['x' => $x, 'y' => $stat->stale_shares];
                $averageHashrates[] = ['x' => $x, 'y' => $stat->average_hashrate / 1000000];
                $activeWorkers[] = ['x' => $x, 'y' => $stat->active_workers];

            }
        }

        return view('dashboard', ['allWallets' => $allWallets, 'today' => $today, 'date1weekAgo' => $date1weekAgo,
            'currentHashrates' => json_encode($currentHashrates), 'validShares' => json_encode($validShares),
            'staleShares' => json_encode($staleShares), 'averageHashrates' => json_encode($averageHashrates),
            'activeWorkers' => json_encode($activeWorkers)
        ]);

    }

    public function getData(Request $request)
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => true, 'msg' => 'User not logined']);
        }

        $today = date('m/d/Y');
        $weekAgoTs = time() - 604800;
        $date1weekAgo = date('m/d/Y', $weekAgoTs);

        //@todo allow user to choose what data they need/ if user have only 1 worker them dont need show this line on graph

        $currentHashrates = [];
        $validShares = [];
        $staleShares = [];
        $averageHashrates = [];
        $activeWorkers = [];

        $startTime = $request->startDate . " 00:00:00";
        $endTime = $request->endDate . " 23:59:59";
        $sql = "SELECT time,current_hashrate,valid_shares,stale_shares,average_hashrate,active_workers
                    FROM mining_stats
                    WHERE wallet_id = ? AND time >= ? AND time <= ? ";


        $allStats = DB::select($sql, [$request->walletId, $startTime, $endTime]);
        foreach ($allStats as $stat) {
            $x = strtotime($stat->time) * 1000;
            $currentHashrates[] = ['x' => $x, 'y' => $stat->current_hashrate / 1000000]; //convert to MegaHash
            $validShares[] = ['x' => $x, 'y' => $stat->valid_shares];
            $staleShares[] = ['x' => $x, 'y' => $stat->stale_shares];
            $averageHashrates[] = ['x' => $x, 'y' => $stat->average_hashrate / 1000000];
            $activeWorkers[] = ['x' => $x, 'y' => $stat->active_workers];

        }


        return response()->json(['currentHashrates' => $currentHashrates, 'validShares' => $validShares,
            'staleShares' => $staleShares, 'averageHashrates' => $averageHashrates,
            'activeWorkers' => $activeWorkers
        ]);

    }

    public function showWallets()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }
        $allWallets = Wallets::where('user_id', $user->id)->get(['id', 'wallet']);
        foreach ($allWallets as $wallet) {
            $trimed = substr($wallet->wallet, 0, 5);
            $trimed .= '****';
            $trimed .= substr($wallet->wallet, -5, 5);
            $wallet->wallet = $trimed;
        }

        return view('wallets', ['allWallets' => $allWallets]);
    }

    public function doWalletActions(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => true, 'msg' => 'User not logined']);
        }

        $validator = new EthereumValidator();
        switch ($request->action) {
            case 'add':
                try {
                    if ($validator->isAddress($request->wallet)) {
                        Wallets::create([
                            'user_id' => $user->id,
                            'wallet' => $request->wallet
                        ]);
                        return response()->json(['error' => false, 'msg' => 'Added']);
                    } else {
                        return response()->json(['error' => true, 'msg' => 'Adress not valid']);
                    }


                } catch (\Exception $e) {
                    return response()->json(['error' => true, 'msg' => $e->getMessage()]);
                }

                break;

            case 'delete':

                $walletRecord = Wallets::where('id', $request->walletId)->where('user_id', $user->id)->first();
                if ($walletRecord) {
                    $walletRecord->delete();
                    return response()->json(['error' => false, 'msg' => 'Deleted']);
                } else {
                    return response()->json(['error' => true, 'msg' => 'Record not found']);
                }
                break;

            default:
                return response()->json(['error' => true, 'msg' => 'Unknown Action']);
                break;


        }

    }

}
