<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use App\Http\Requests\CreateBookingRequest;

class BookingController extends Controller
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        $bookings = $this->bookingService->getUserBookings();
        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    public function store(CreateBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request)->load('room');
            return response()->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $booking = $this->bookingService->getUserBooking($id);
            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }
    }

    public function cancel($id)
    {
        try {
            $booking = $this->bookingService->cancelBooking($id);
            return response()->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to cancel booking'
            ], 400);
        }
    }
}
