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
        $partnerCode = 'MOMOZGAI20211012';
        $accessKey = '5vwvbxyCxDLl16Uq';
        $serectkey = 'hpo6bOpOqeqCE12mN4U2l7xT5fhlfpUv';
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
        $response = $client->send($request, [
            'body'    => json_encode($data),
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.104 Safari/537.36',
                'accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'
            ]
        ]);
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
