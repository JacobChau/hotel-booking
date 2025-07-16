<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RoomAvailability extends Model
{
    use HasFactory;

    protected $table = 'room_availability';

    protected $fillable = [
        'room_id',
        'date',
        'is_available',
        'price_override',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'price_override' => 'decimal:2',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Check if a room is available for a date range
     */
    public static function isRoomAvailable($roomId, $checkin, $checkout)
    {
        $checkinDate = Carbon::parse($checkin);
        $checkoutDate = Carbon::parse($checkout);

        // Check if there are any unavailable dates in the range
        $unavailableDates = self::where('room_id', $roomId)
            ->where('is_available', false)
            ->whereBetween('date', [$checkinDate, $checkoutDate->subDay()])
            ->count();

        return $unavailableDates === 0;
    }

    /**
     * Get available rooms for a date range
     */
    public static function getAvailableRooms($checkin, $checkout, $guestCount = null)
    {
        $checkinDate = Carbon::parse($checkin);
        $checkoutDate = Carbon::parse($checkout);

        $query = Room::query();

        // Filter by guest capacity if specified
        if ($guestCount) {
            $query->where('guest', '>=', $guestCount);
        }

        // Exclude rooms that have unavailable dates in the range
        $query->whereDoesntHave('availability', function ($availabilityQuery) use ($checkinDate, $checkoutDate) {
            $availabilityQuery->where('is_available', false)
                ->whereBetween('date', [$checkinDate, $checkoutDate->subDay()]);
        });

        // Also exclude rooms that are already booked (not cancelled)
        $query->whereDoesntHave('bookings', function ($bookingQuery) use ($checkinDate, $checkoutDate) {
            $bookingQuery->where('status', '!=', 'cancelled')
                ->where(function ($dateQuery) use ($checkinDate, $checkoutDate) {
                    $dateQuery->where('check_in', '<', $checkoutDate)
                             ->where('check_out', '>', $checkinDate);
                });
        });

        return $query;
    }

    /**
     * Generate availability records for a room for a date range
     */
    public static function generateAvailability($roomId, $startDate, $endDate, $isAvailable = true)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $records = [];
        while ($start->lte($end)) {
            $records[] = [
                'room_id' => $roomId,
                'date' => $start->toDateString(),
                'is_available' => $isAvailable,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $start->addDay();
        }

        self::upsert($records, ['room_id', 'date'], ['is_available', 'updated_at']);
    }
}
