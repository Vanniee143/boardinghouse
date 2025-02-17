<?php

namespace App\Http\Controllers;

use App\Models\PaymentQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentQRCodeController extends Controller
{
    public function getQRCode($boardingHouseId, $paymentType)
    {
        try {
            Log::info('Fetching QR code:', [
                'boarding_house_id' => $boardingHouseId,
                'payment_type' => $paymentType
            ]);

            // Validate input parameters
            if (!$boardingHouseId || !$paymentType) {
                throw new \Exception('Invalid parameters provided');
            }

            // Debug the query
            $query = PaymentQrCode::where('boarding_house_id', $boardingHouseId)
                ->where('payment_type', strtolower($paymentType));
                
            Log::info('Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $qrCode = $query->first();

            Log::info('QR code query result:', [
                'qr_code' => $qrCode ? $qrCode->toArray() : null
            ]);

            if (!$qrCode) {
                return response()->json([
                    'status' => 'error',
                    'message' => "No QR code available for {$paymentType} payment"
                ], 404);
            }

            // Ensure the image path is properly formatted
            $qrImageUrl = $qrCode->qr_image;
            if (!$qrImageUrl) {
                throw new \Exception('QR image path is missing');
            }

            if (!str_starts_with($qrImageUrl, 'http') && !str_starts_with($qrImageUrl, '/storage/')) {
                $qrImageUrl = '/storage/' . $qrImageUrl;
            }

            Log::info('Returning QR code:', [
                'image_url' => $qrImageUrl,
                'account_name' => $qrCode->account_name
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'qr_image' => $qrImageUrl,
                    'account_name' => $qrCode->account_name
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching QR code:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch QR code: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
} 