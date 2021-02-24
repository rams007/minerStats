<?php

namespace App\Http\Controllers;

use App\Wallets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{
    public function dashboard(){
        $user = Auth::user();
        if (!$user){
            return redirect('/login');
        }
        $allWallets = Wallets::where('user_id',$user->id)->get(['id','wallet']);
        $today = date('m/d/Y');
        $date1weekAgo = date('m/d/Y', time() -604800 );
        return view('dashboard',['allWallets'=>$allWallets, 'today'=>$today, 'date1weekAgo'=>$date1weekAgo]);
    }
}
