<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VNPayService;

class PaymentController extends Controller
{
    public function __construct (
        VNPayService $vnPayService
    ) {
        $this->vnPayService = $vnPayService;
    }
    
    public function index(Request $request) {
        return [];
    }
    
    public function createOrder(Request $request)
    {        
        $input = $request->all();
        $urlRedirect = $this->vnPayService->createOrder($input);
        // return redirect($urlRedirect);
        return $urlRedirect;
    }
    
    public function processOrder(Request $request)
    {
        $input = $request->all();
        return $this->vnPayService->processOrder($request, $input);
    }
}
