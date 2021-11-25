<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MomoController extends Controller
{
    public function callback($type, Request $request)
    {
        dd($type, $request->all());
    }

    public function generateUrl($type, Request $request)
    {
        $request->validate([
            'orderId'   => 'required',
            'orderInfo' => 'required',
            'amount'    => 'required',
            'extraData' => 'required',
        ]);
        $endpoint = "https://test-payment.momo.vn/gw_payment/transactionProcessor";
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $serectkey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderId = $request->orderId; // Mã đơn hàng
        $orderInfo = $request->orderInfo;
        $amount = $request->amount;
        $notifyurl = url('/api/momo/callback/notify');
        $returnUrl = url('/api/momo/callback/return');
        $extraData = $request->extraData;
        $requestId = time() . "";
        $requestType = "captureMoMoWallet";
//        $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
        $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyurl . "&extraData=" . $extraData;
        $signature = hash_hmac("sha256", $rawHash, $serectkey);
        $data = [
            'partnerCode' => $partnerCode,
            'accessKey'   => $accessKey,
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'returnUrl'   => $returnUrl,
            'notifyUrl'   => $notifyurl,
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        ];
        $client = new \GuzzleHttp\Client();
        $request = new \GuzzleHttp\Psr7\Request('POST', $endpoint);
        $response = $client->send($request, ['body' => json_encode($data)]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        if (!empty($result['errorCode'])) {
            $errors = array_intersect_key($result, array_flip(['errorCode', 'message', 'localMessage']));
            return response()->json(['status' => false, 'errors' => $errors], 400);
        }
        $data = [
            'mobile_url'  => $result['qrCodeUrl'],
            'desktop_url' => $result['payUrl']
        ];
        return response()->json(['status' => true, 'data' => $data]);
    }
}
