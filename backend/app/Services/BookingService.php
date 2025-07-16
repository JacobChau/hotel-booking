<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomAvailability;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Requests\CreateBookingRequest;

class BookingService extends BaseService
{
    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
    }

    public function getUserBookings($params = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $user = Auth::user();
        $perPage = $params['per_page'] ?? 5;
        $page = $params['page'] ?? 1;
        $type = $params['type'] ?? 'all'; // 'upcoming', 'past', 'all'
        
        $query = Booking::with('room')->where('user_id', $user->id);
        
        // Filter by booking type
        if ($type === 'upcoming') {
            $query->where('check_out', '>', now()->toDateString())
                  ->where('status', '!=', 'cancelled');
        } elseif ($type === 'past') {
            $query->where(function($q) {
                $q->where('check_out', '<=', now()->toDateString())
                  ->orWhere('status', 'cancelled');
            });
        }
        
        $query->orderByDesc('created_at');
        
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function createBooking(CreateBookingRequest $request): Booking
    {
        $data = $request->validated();
        $room = Room::findOrFail($data['room_id']);
        $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));
        $bookingNumber = $data['booking_number'] ?? 'RES' . strtoupper(Str::random(8));
        $status = $data['status'] ?? (Carbon::parse($data['check_in'])->isFuture() ? 'upcoming' : 'past');
        $total = $data['total'] ?? ($room->price * $nights);

        // Check if room is available for the requested dates
        $isAvailable = RoomAvailability::isRoomAvailable(
            $room->id,
            $data['check_in'],
            $data['check_out']
        );

        if (!$isAvailable) {
            throw new \Exception('Room is not available for the selected dates');
        }

        // Create the booking
        $booking = $this->create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'guests' => $data['guests'],
            'total' => $total,
            'nights' => $nights,
            'booking_number' => $bookingNumber,
            'status' => $status,
            'title' => $data['title'],
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Mark room as unavailable for the booked dates
        $this->markRoomUnavailable($room->id, $data['check_in'], $data['check_out'], 'Booked');

        return $booking;
    }

    public function getUserBooking(int $id): Booking
    {
        return Booking::with('room')->where('user_id', Auth::id())->findOrFail($id);
    }

    public function cancelBooking(int $id): Booking
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);
        
        if ($booking->status === 'upcoming' || $booking->status === 'confirmed') {
            $booking->status = 'cancelled';
            $booking->cancelled_at = now();
            $booking->save();

            // Mark room as available again for the cancelled dates
            $this->markRoomAvailable($booking->room_id, $booking->check_in, $booking->check_out);
        }
        
        return $booking;
    }

    /**
     * Mark room as unavailable for specific dates
     */
    private function markRoomUnavailable($roomId, $checkIn, $checkOut, $notes = null)
    {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut)->subDay(); // Don't include checkout day
        
        while ($start->lte($end)) {
            RoomAvailability::updateOrCreate(
                [
                    'room_id' => $roomId,
                    'date' => $start->toDateString(),
                ],
                [
                    'is_available' => false,
                    'notes' => $notes,
                ]
            );
            $start->addDay();
        }
    }

    /**
     * Mark room as available for specific dates
     */
    private function markRoomAvailable($roomId, $checkIn, $checkOut)
    {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut)->subDay(); // Don't include checkout day
        
        while ($start->lte($end)) {
            RoomAvailability::updateOrCreate(
                [
                    'room_id' => $roomId,
                    'date' => $start->toDateString(),
                ],
                [
                    'is_available' => true,
                    'notes' => null,
                ]
            );
            $start->addDay();
        }
    }
}
