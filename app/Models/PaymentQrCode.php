<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentQrCode extends Model
{
    protected $table = 'payment_qr_codes';
    protected $primaryKey = 'qr_id';
    
    protected $fillable = [
        'payment_type',
        'qr_image',
        'account_name',
        'boarding_house_id',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public static function findQRCode($boardingHouseId, $paymentType)
    {
        $query = self::where('boarding_house_id', $boardingHouseId)
            ->where('payment_type', strtolower($paymentType));
            
        \Log::info('Debug QR Code Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'result' => $query->first()
        ]);
        
        return $query->first();
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id', 'boarding_house_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'qr_image_path', 'qr_image');
    }
} 