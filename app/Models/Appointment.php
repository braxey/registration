<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WalkIn;
use Carbon\Carbon;

class Appointment extends Model{
    use HasFactory;
    protected $fillable = [
        'description',
        'start_time',
        'end_time',
        'total_slots',
        'walk_in_only',
    ];

    public function isWalkInOnly(): bool
    {
        return (int) $this->walk_in_only === 1;
    }

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

    public function isOpen(): bool
    {
        return (
            $this->status == "upcoming"
            && $this->total_slots > $this->slots_taken
            && now() < Carbon::parse($this->start_time)->setTime(12, 0, 0)
        );
    }

    public function canEdit(): bool
    {
        return (
            $this->status == "upcoming"
            && now() < Carbon::parse($this->start_time)->setTime(12, 0, 0)
        );
    }

    public function getStartDate(): string
    {
        return explode(' ', $this->start_time)[0];
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
