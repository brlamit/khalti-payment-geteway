<?php

namespace App\Services;

class KhaltiPaymentService
{
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->secretKey = env('KHALTI_SECRET_KEY');
        $this->baseUrl = env('KHALTI_Testing', true)
            ? 'https://a.khalti.com/api/v2/epayment/initiate/'
            : 'https://khalti.com/api/v2/epayment/initiate/';
    }

    public function initiatePayment($amount, $purchaseOrderId, $purchaseOrderName, $customerInfo)
    {
        $postFields = json_encode([
            'return_url' => route('khalti.success'),
            'website_url' => config('app.url'),
            'amount' => (int) ($amount * 100), // Convert to paisa
            'purchase_order_id' => $purchaseOrderId,
            'purchase_order_name' => $purchaseOrderName,
            'customer_info' => $customerInfo,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Authorization: Key ' . $this->secretKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            throw new \Exception('cURL Error: ' . $err);
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400 || isset($data['error_key'])) {
            throw new \Exception('Khalti API Error: ' . $response);
        }

        return $data;
    }

    public function verifyPayment($pidx)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Key ' . $this->secretKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            throw new \Exception('cURL Error: ' . $err);
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400 || isset($data['error_key'])) {
            throw new \Exception('Khalti Verification Error: ' . $response);
        }

        return $data;
    }
}
