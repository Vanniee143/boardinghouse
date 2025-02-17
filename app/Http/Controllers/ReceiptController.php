<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Notification;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    public function generateReceipt($paymentId, Request $request)
    {
        try {
            Log::info('Starting receipt generation for payment: ' . $paymentId);
            
            $payment = Payment::with(['booking.user', 'booking.room.boardingHouse'])
                ->findOrFail($paymentId);
            
            Log::info('Payment data:', ['payment' => $payment->toArray()]);

            $data = [
                'payment' => $payment,
                'receipt_no' => 'RCP-' . str_pad($payment->payment_id, 6, '0', STR_PAD_LEFT),
                'date' => $payment->created_at->format('M d, Y'),
                'tenant_name' => $payment->booking->user->name,
                'room_name' => $payment->booking->room->room_name,
                'boarding_house' => $payment->booking->room->boardingHouse->name,
                'amount' => number_format($payment->amount, 2),
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'reference_number' => $payment->reference_number ?? 'N/A'
            ];

            $pdf = PDF::loadView('receipts.payment', $data);
            $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isPhpEnabled', false);
            $pdf->getDomPDF()->set_option('defaultCharset', 'utf-8');
            
            // Generate unique filename
            $filename = 'receipt_' . $payment->payment_id . '_' . time() . '.pdf';
            $path = 'receipts/' . $filename;
            
            // Save PDF to storage
            Storage::disk('public')->put($path, $pdf->output());

            // Only send notification if sendNotification is true
            $sendNotification = $request->input('sendNotification', true);
            if ($sendNotification) {
                // Create notification for user
                Notification::create([
                    'user_id' => $payment->booking->user_id,
                    'type' => 'payment',
                    'message' => "Your payment receipt is ready for download",
                    'link' => '/storage/' . $path,
                    'read' => false,
                    'has_attachment' => true,
                    'attachment_type' => 'receipt'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Receipt generated successfully',
                'data' => [
                    'receipt_url' => '/storage/' . $path
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt generation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadReceipt($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            $filename = 'receipt_' . $payment->payment_id . '.pdf';
            $path = 'receipts/' . $filename;

            if (!Storage::disk('public')->exists($path)) {
                // Generate new receipt if it doesn't exist
                return $this->generateReceipt($paymentId);
            }

            return response()->download(storage_path('app/public/' . $path));
        } catch (\Exception $e) {
            \Log::error('Receipt download error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download receipt'
            ], 500);
        }
    }
} 