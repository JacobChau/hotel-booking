<?php

namespace App\Http\Controllers;

use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private RoomService $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        $rooms = $this->roomService->getAllRooms($request->all());
        return response()->json($rooms);
    }

    public function search(Request $request)
    {
        $params = [
            'filters' => [
                'guest' => $request->get('guests'),
                'checkin' => $request->get('checkin'),
                'checkout' => $request->get('checkout'),
            ],
            'termSearch' => $request->get('destination'),
            'columnSearch' => ['title', 'description'],
            'perPage' => $request->get('per_page', 10), // Default to 10 items per page
            'page' => $request->get('page', 1), // Default to page 1
        ];

        $rooms = $this->roomService->getAllRooms($params);
        return response()->json($rooms);
    }

    public function show($id)
    {
        $roomId = (int) $id;

        if ($roomId <= 0) {
            return response()->json(['error' => 'Invalid room ID'], 400);
        }

        try {
            $room = $this->roomService->getRoom($roomId);
            return response()->json($room);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Room not found'], 404);
        }
    }

    public function suggestions(Request $request)
    {
        $query = $request->get('query', '');
        $limit = $request->get('limit', 10);
        
        $suggestions = $this->roomService->getRoomSuggestions($query, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}
