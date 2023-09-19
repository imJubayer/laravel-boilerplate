<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

function apiResponse($success, $msg, $data, $httpStatusCode=null, bool $isMeta=false){
    $response = [
        'success' => $success,
        'msg' => $msg,
        'data' => $isMeta ? $data->toArray()['data'] : $data,
        // 'meta' => $meta
    ];
    if($isMeta){
        $response['meta'] = ['current_page' => $data->toArray()['current_page'], 'limit' => $data->toArray()['to'], 'total' => $data->toArray()['total']];
    }
    return response()->json($response, $httpStatusCode ? $httpStatusCode : 200);
}

// function getProfileInfo($userId=null){
//     if($userId){
//         $id = $userId;
//     } else {
//         $id = Auth::user()->id;
//     }
//     $user = User::where('id', $id)->with(['accounts.share', 'accounts.deposits' => function ($query) {
//         $query->orderBy('deposit_for');
//     }])->first();

//     foreach ($user->accounts as $key => $account) {
//         $account->amountDetails = getAccountTotalAmount($account->id);
//     }

//     $usr = User::find($id);
//     if($user){
//         $totalDeposit = Deposit::where([
//             ['user_id', $id],
//             ['status', 1]
//         ])->sum('amount');

//         $totalFundRaising = Deposit::where([
//             ['user_id', $id],
//             ['status', 1]
//         ])->sum('fund_raising');

//         $totalFine = Deposit::where([
//             ['user_id', $id],
//             ['status', 1]
//         ])->sum('fine');
    
//         $totalDue = Deposit::where([
//             ['user_id', $id],
//             ['status', 0],
//             ['deposit_for' , '<', Carbon::now()]
//         ])->sum('amount');
    
//         $roles = $usr->getRoleNames();
//         $user->totalDeposit = (int)$totalDeposit;
//         $user->totalDue = (int)$totalDue;
//         $user->totalFine = (int)$totalFine;
//         $user->currentBalance = ($totalDeposit + $totalFundRaising) - $totalFine;
//         $user->role = getPriorityRole($roles);
//         $user->profile_image = $user->profile_image;
//     } else {
//         $user = new stdClass();
//     }
//     return $user;
// }

// function getAccountTotalAmount($ifsaId){
//     $totalDeposit = Deposit::where([
//         ['ifsa_id', $ifsaId],
//         ['status', 1]
//     ])->sum('amount');

//     $totalFundRaising = Deposit::where([
//         ['ifsa_id', $ifsaId],
//         ['status', 1]
//     ])->sum('fund_raising');

//     $totalFine = Deposit::where([
//         ['ifsa_id', $ifsaId],
//         ['status', 1]
//     ])->sum('fine');

//     return ['totalDeposit' => $totalDeposit, 'totalFundRaising' => $totalFundRaising, 'totalFine' => $totalFine, 'totalAmount' => ($totalDeposit + $totalFundRaising) - $totalFine];
// }

function getPriorityRole($roles){
    if($roles->contains('superadmin')){
        $role = 'superadmin';
    } else if($roles->contains('superadmin')){
        $role = 'admin';
    } else {
        $role = 'member';
    }
    return $role;
}

function getTimeStamp($dateString){
    $date = Carbon::parse(str_replace('/', '-', $dateString));
    $timestamp = $date->toDateTimeString();

    return $timestamp;
}
