<?php
namespace App\Services;
use Omnipay\Omnipay;
use Request;

class VNPayService{
    
    const VNPAY_ORDER_TYPE = 190000;
    const VNPAY_UNIT = 100;
    
    public function __construct()
    {
        $this->gateway = Omnipay::create('VNPay');
        $this->gateway->initialize([
            'testMode'       => (bool)env('VNPAY_SANDBOX', false),
            'vnp_TmnCode'    => env('VNPAY_CODE', null),
            'vnp_HashSecret' => env('VNPAY_SECRET', null),
        ]);
    }
    
    public function createOrder($input) {
        $payAmount = !empty($input['amount']) ? intval($input['amount']) : 0;
        $response = $this->gateway->purchase([
            'vnp_TxnRef' => time(),
            'vnp_OrderType' => self::VNPAY_ORDER_TYPE,
            'vnp_OrderInfo' => time(),
            'vnp_IpAddr' => Request::ip(),
            'vnp_Amount' => ($payAmount * self::VNPAY_UNIT),
            'vnp_SecureHashType' => "SHA256",
            'vnp_ReturnUrl' => 'http://171.244.203.202:7777/api/payment/processOrder',
        ])->send();
        
        if ($response->isRedirect()) {
            $redirectUrl = $response->getRedirectUrl();
            
            // chuyển khách sang trang VNPay để thanh toán
            
            return $redirectUrl;
        }
    }
    
    public function processOrder($request, $input) { 
        // if(!isset($input['vnp_SecureHashType'])) {
        //     $request->request->add(['vnp_SecureHashType' => "SHA256"]);
        // }
        $response = $this->gateway->completePurchase($input)->send();

        if ($response->isSuccessful()) {
            // TODO: xử lý kết quả và hiển thị.
            var_dump($response->vnp_Amount);
            var_dump($response->vnp_TxnRef);
            
            // var_dump($response->getData()); // toàn bộ data do VNPay gửi sang.
            return $response->getData();
        } else {
        
            // print $response->getMessage();
            return "Giao dịch không thành công!";
        }
    } 
}