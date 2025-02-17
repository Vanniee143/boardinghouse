<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardingHouse extends Model
{
    protected $primaryKey = 'boarding_house_id';

    protected $fillable = [
        'name',
        'description',
        'address',
        'landlord_name',
        'landlord_phone',
        'bh_images',
        'user_id',
        'created_by',
        'updated_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'boarding_house_id', 'boarding_house_id');
    }

    public function availableRooms()
    {
        return $this->rooms()->where('status', 'available');
    }

    public function occupiedRooms()
    {
        return $this->rooms()->where('status', 'occupied');
    }

    public function maintenanceRooms()
    {
        return $this->rooms()->where('status', 'maintenance');
    }

    public function paymentQrCodes()
    {
        return $this->hasMany(PaymentQrCode::class, 'boarding_house_id', 'boarding_house_id');
    }

    protected $casts = [
        'room_images' => 'string',
        'bh_images' => 'string'
    ];
} 
