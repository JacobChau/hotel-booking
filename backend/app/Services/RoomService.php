<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\Carbon;

class RoomService extends BaseService
{
    public function __construct(Room $room)
    {
        parent::__construct($room);
    }

    public function getAllRooms($params = [])
    {
        $perPage = $params['perPage'] ?? 10;
        $filters = $params['filters'] ?? [];
        $columnSearch = $params['columnSearch'] ?? null;
        $termSearch = $params['termSearch'] ?? null;
        $page = $params['page'] ?? 1;

        // Use the new availability system if dates are provided
        if (isset($filters['checkin']) && isset($filters['checkout'])) {
            $query = RoomAvailability::getAvailableRooms(
                $filters['checkin'],
                $filters['checkout'],
                $filters['guest'] ?? null
            );
        } else {
            $query = $this->model->newQuery();
            
            // Filter by guest capacity if no dates provided
            if (isset($filters['guest'])) {
                $query->guest($filters['guest']);
            }
        }

        // Handle price sorting
        if (
            isset($filters['sortColumn']) &&
            $filters['sortColumn'] === 'price'
        ) {
            $direction = $filters['sortOrder'] ?? 'asc';
            $query->sortByPrice($direction);
            unset($filters['sortColumn'], $filters['sortOrder']);
        }

        // Set the current page for pagination
        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        return $this->getAll($perPage, $query, $filters, $columnSearch, $termSearch);
    }

    public function getRoom(int $id): Room
    {
        return $this->getById($id);
    }

    /**
     * Initialize room availability for a date range
     * This should be called when setting up rooms or extending availability
     */
    public function initializeRoomAvailability($roomId, $startDate, $endDate)
    {
        RoomAvailability::generateAvailability($roomId, $startDate, $endDate, true);
    }

    /**
     * Set room unavailable for specific dates
     */
    public function setRoomUnavailable($roomId, $startDate, $endDate, $notes = null)
    {
        RoomAvailability::generateAvailability($roomId, $startDate, $endDate, false);

        if ($notes) {
            RoomAvailability::where('room_id', $roomId)
                ->whereBetween('date', [$startDate, $endDate])
                ->update(['notes' => $notes]);
        }
    }

    /**
     * Get rooms available for specific dates
     */
    public function getAvailableRooms($checkin, $checkout, $guestCount = null)
    {
        return RoomAvailability::getAvailableRooms($checkin, $checkout, $guestCount)->get();
    }
}
