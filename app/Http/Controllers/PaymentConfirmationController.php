<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentConfirmationController extends Controller
{
    public function confirmPayment(Request $request, $paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            
            // Update payment status
            $payment->status = Payment::STATUS_CONFIRMED;
            $payment->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment confirmed successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment confirmation error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectPayment(Request $request, $paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($paymentId);
            
            // Update payment status
            $payment->status = Payment::STATUS_REJECTED;
            $payment->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment rejected successfully',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment rejection error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 