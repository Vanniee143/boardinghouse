<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $primaryKey = 'review_id';
    
    protected $fillable = [
        'user_id',
        'boarding_house_id',
        'room_id',
        'rating',
        'comment',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
        'rating' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function boardingHouse()
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id', 'boarding_house_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
} 