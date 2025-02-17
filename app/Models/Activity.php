<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    protected $primaryKey = 'activity_id';
    
    protected $fillable = [
        'type',
        'description',
        'performed_by',
        'boarding_house_id',
        'created_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the user who performed the activity
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by', 'user_id')
            ->select(['user_id', 'name', 'profile_picture', 'userType']);
    }

    /**
     * Get the boarding house associated with the activity
     */
    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class, 'boarding_house_id', 'boarding_house_id');
    }

    /**
     * Get formatted profile picture URL
     */
    public function getFormattedProfilePictureAttribute()
    {
        if (!$this->performer || !$this->performer->profile_picture) {
            return null;
        }

        $profilePath = str_replace(['public/', 'storage/'], '', $this->performer->profile_picture);
        if (Storage::disk('public')->exists($profilePath)) {
            return asset('storage/' . $profilePath);
        }

        return null;
    }
} 