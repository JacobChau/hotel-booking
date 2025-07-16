<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'image',
        'guest',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'guest' => 'integer',
    ];

    protected $appends = [
        'name',
        'capacity',
        'size',
        'beds'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function availability()
    {
        return $this->hasMany(RoomAvailability::class);
    }

    public function scopeGuest($query, $guest)
    {
        if ($guest !== null) {
            return $query->where('guest', '>=', $guest);
        }
        return $query;
    }

    public function scopeSortByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    public function scopeAvailableForDates($query, $checkin, $checkout)
    {
        return RoomAvailability::getAvailableRooms($checkin, $checkout);
    }

    // Computed attributes for frontend compatibility
    public function getNameAttribute()
    {
        return $this->title;
    }

    public function getCapacityAttribute()
    {
        return $this->guest ?? 2;
    }

    public function getSizeAttribute()
    {
        // Default size based on guest capacity
        return ($this->guest ?? 2) * 150; // 150 sqft per guest
    }

    public function getBedsAttribute()
    {
        // Default bed configuration based on guest capacity
        $guests = $this->guest ?? 2;
        if ($guests <= 2) {
            return 'Queen bed';
        } elseif ($guests <= 4) {
            return '2 Queen beds';
        } else {
            return '2 Queen beds + Sofa bed';
        }
    }

    /**
     * Check if this room is available for a date range
     */
    public function isAvailableForDates($checkin, $checkout)
    {
        return RoomAvailability::isRoomAvailable($this->id, $checkin, $checkout);
    }

    /**
     * Get the price for a specific date (with override support)
     */
    public function getPriceForDate($date)
    {
        $availability = $this->availability()->where('date', $date)->first();
        
        if ($availability && $availability->price_override) {
            return $availability->price_override;
        }
        
        return $this->price;
    }
}
