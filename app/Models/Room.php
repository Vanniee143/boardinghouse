<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $primaryKey = 'room_id';
    
    protected $fillable = [
        'room_name',
        'bed_quantity',
        'status',
        'room_images',
        'map_link',
        'price',
        'boarding_house_id',
        'user_id',
        'created_by',
        'updated_by'
    ];

    /**
     * Get the boarding house that owns the room
     */
    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id', 'boarding_house_id');
    }

    /**
     * Get the user who created the room
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the user who last updated the room
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Get the bookings for this room
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id', 'room_id');
    }
} 