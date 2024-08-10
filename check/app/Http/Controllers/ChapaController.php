<?php

namespace App\Http\Controllers;

use App\Services\ChapaService;
use Illuminate\Http\Request;

class ChapaController extends Controller
{
  
//php artisan make:controller ChapaController

    protected  $chapaService;
    
    public function __construct(ChapaService $chapaService)
    {
    $this->chapaService = $chapaService;
    }

    public function initializePayment(Request $request)
    {
    
 

    $data = [
        'amount' => $request->input('amount'),
        'currency' => 'ETB',
        'email' => $request->input('email'),
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'tx_ref' => uniqid('tx_', true),
        'callback_url' => url('/payment/callback'),
    ];

    $response = $this->chapaService->initializePayment($data);
    return response()->json($response);
    
}


public function verifyPayment($transactionId){

    $response = $this->chapaService->verifyPayment($transactionId);
    return response()->json($response);

}


}
