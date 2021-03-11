<?php

namespace App\Http\Controllers;

use App\Http\Helpers\HelperController;
use App\Settings;
use App\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\EthereumValidator;
use Illuminate\Support\Facades\Mail;
use Mailgun\Mailgun;

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
        $enabledGraphs = HelperController::getEnabledGraphs();
        if (count($allWallets) > 0) {

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
            'activeWorkers' => json_encode($activeWorkers), 'enabledGraphs' => $enabledGraphs
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

    public function contactUs(Request $request)
    {
        try {

            $html = view('mail.contactus', ['name' => $request->name, 'email' => $request->email, 'subject' => $request->subject,
                'msg' => $request->message])->render();

            $sendedResult = HelperController::sendMail('sramsiks@gmail.com', 'support@miner-stats.com', 'Contact us request', $html);

            if ($sendedResult === true) {
                echo 'OK';
            } else {
                echo 'Message not sended.' . $sendedResult;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }

    public function showSettings()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }
        $allSettingsRecords = Settings::where('user_id', $user->id)->get(['parametr_key', 'parametr_val']);
        $settings = [
            'currentHashrate' => 1,
            'avgHashrate' => 1,
            'activeWorkers' => 1,
            'validShares' => 1,
            'staleShares' => 1,
        ];

        foreach ($allSettingsRecords as $setting) {
            $settings[$setting->parametr_key] = $setting->parametr_val;
        }
        return view('settings', ['settings' => $settings]);
    }

    public function updateSettings(Request $request)
    {

        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }


        parse_str($request->parametrs, $param);

        $parametrs = [
            'currentHashrate' => isset($param['currentHashrate']) ? 1 : 0,
            'avgHashrate' => isset($param['avgHashrate']) ? 1 : 0,
            'activeWorkers' => isset($param['activeWorkers']) ? 1 : 0,
            'validShares' => isset($param['validShares']) ? 1 : 0,
            'staleShares' => isset($param['staleShares']) ? 1 : 0,
        ];

        foreach ($parametrs as $key => $val) {

            Settings::updateOrCreate(['user_id' => $user->id, 'parametr_key' => $key], ['parametr_val' => $val]);

        }
        //  return response()->json(['error'=>false]);

    }

}
