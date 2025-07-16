<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\Carbon;

class RoomAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addYear(); // Generate availability for next 365 days

        foreach ($rooms as $room) {
            // Generate availability for each room for the next year
            RoomAvailability::generateAvailability($room->id, $startDate, $endDate, true);
            
            // Randomly make some dates unavailable (for testing)
            $this->createRandomUnavailableDates($room->id, $startDate, $endDate);
        }
    }

    /**
     * Create some random unavailable dates for testing
     */
    private function createRandomUnavailableDates($roomId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Create 3-5 random unavailable periods per room
        $unavailablePeriods = rand(3, 5);
        
        for ($i = 0; $i < $unavailablePeriods; $i++) {
            // Random start date within the range
            $randomStart = $start->copy()->addDays(rand(0, $end->diffInDays($start) - 7));
            
            // Random period length (1-5 days)
            $periodLength = rand(1, 5);
            $randomEnd = $randomStart->copy()->addDays($periodLength);
            
            // Make sure we don't go beyond the end date
            if ($randomEnd->gt($end)) {
                $randomEnd = $end->copy();
            }
            
            // Set as unavailable
            RoomAvailability::where('room_id', $roomId)
                ->whereBetween('date', [$randomStart, $randomEnd])
                ->update([
                    'is_available' => false,
                    'notes' => 'Maintenance period'
                ]);
        }
    }
} 