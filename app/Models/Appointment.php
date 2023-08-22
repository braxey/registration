<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WalkIn;

class Appointment extends Model{
    use HasFactory;
    protected $fillable = [
        'description',
        'start_time',
        'end_time',
        'total_slots',
    ];

    public function addWalkIn(WalkIn $walkIn)
    {
        $updatedSlotsTaken = $this->slots_taken + $walkIn->slots;
        $this->slots_taken = $updatedSlotsTaken;
        if ($updatedSlotsTaken > $this->total_slots) {
            $this->total_slots = $updatedSlotsTaken;
        }
        $this->save();
    }

    public function removeWalkIn(WalkIn $walkIn)
    {
        $this->slots_taken -= $walkIn->slots;
        $this->save();
    }
}
