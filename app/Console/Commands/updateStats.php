<?php

namespace App\Console\Commands;

use App\MiningStats;
use App\Wallets;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class updateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateStats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update latest stats';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        define('ENDPOINT_URL', 'https://api.ethermine.org/');
        $guzzleClient = new Client();
        //server return stats every 10 minutes. so we dont need ask it ьщку щаеут
        $lastAllowedUpdateDate = date('Y-m-d H:i:s', time() - 600);
        $walletsForWork = Wallets::where('checked_at', '<', $lastAllowedUpdateDate)->orWhereNull('checked_at')->orderBy('updated_at')->get(['id', 'wallet']);
//print_r($walletsForWork);
        foreach ($walletsForWork as $wallet) {
            $url = ENDPOINT_URL . 'miner/' . $wallet->wallet . '/history';
            $historyResponse = $guzzleClient->get($url);
            if ($historyResponse->getStatusCode() !== 200) {
                Log::error('We cant get stats. Status code = ' . $historyResponse->getStatusCode() . ' url=' . $url);

            } else {
                $historyData = $historyResponse->getBody()->getContents();;
                $parsedData = json_decode($historyData);
                var_dump($parsedData);
                if (isset($parsedData->status)) {
                    if ($parsedData->status !== 'OK') {
                        Log::error('Response status = ' . $parsedData->status . ' Response = ' . $historyData);
                    } else {

                        foreach ($parsedData->data as $data) {
                            try {
                                MiningStats::create([
                                    'wallet_id' => $wallet->id,
                                    'time' => date('Y-m-d H:i:s', $data->time),
                                    'reported_hashrate' => $data->reportedHashrate,
                                    'current_hashrate' => $data->currentHashrate,
                                    'valid_shares' => $data->validShares,
                                    'invalid_shares' => $data->invalidShares,
                                    'stale_shares' => $data->staleShares,
                                    'average_hashrate' => $data->averageHashrate,
                                    'active_workers' => $data->activeWorkers
                                ]);

                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }

                        }

                    }
                } else {
                    Log::error('Cant convert response. Response = ' . $historyData);
                }
            }
            $wallet->checked_at= date('Y-m-d H:i:s');
            $wallet->save();
        }
    }
}
