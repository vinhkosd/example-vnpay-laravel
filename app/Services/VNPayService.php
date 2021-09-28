<?php
namespace App\Services;
use Omnipay\Omnipay;
use Request;

class VNPayService{
    
    const VNPAY_ORDER_TYPE = 190000;
    const VNPAY_UNIT = 100;
    const VNPAY_RESPONSE_CODE_SUCCESS = '00';
    const VNPAY_TRANSACTION_STATUS_SUCCESS = '00';
    
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
            'vnp_SecureHashType' => "sha256",
            'vnp_ReturnUrl' => route('payment.processOrder'),
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
            $payAmount = number_format(intval($response->vnp_Amount) / self::VNPAY_UNIT);
            // var_dump($response->getData()); // toàn bộ data do VNPay gửi sang.
            return "Giao dịch thành công! Bạn đã thanh toán thành công số tiền $payAmount VND!";
        } else {
        
            // print $response->getMessage();
            return "Giao dịch không thành công!";
        }
    }
    
    public function VnPayIPN($input) { 
        $returnData = array();
        $response = $this->gateway->notification()->send();
        $responseData = $response->getData();
        if (!empty($responseData)) {
            // TODO: xử lý kết quả.
            if ($responseData['vnp_ResponseCode'] == '00' || $responseData['vnp_TransactionStatus'] == '00') {
                $Status = 1; // Trạng thái thanh toán thành công
            } else {
                $Status = 2; // Trạng thái thanh toán thất bại / lỗi
            }
            
            $returnData['RspCode'] = '00';
            $returnData['Message'] = 'Confirm Success';
            return $returnData;
        }
        else {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
            return $responseData;
        }
    }
}