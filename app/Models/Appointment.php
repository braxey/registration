<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\WalkIn;
use Carbon\Carbon;
use App\Models\AppointmentUser;


class Appointment extends Model{
    use HasFactory;
    protected $fillable = [
        'description',
        'start_time',
        'end_time',
        'total_slots',
        'walk_in_only',
    ];

    public function getId(): int
    {
        return $this->id;
    }

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

    public function getStartTime(): string
    {
        return $this->start_time;
    }

    public function getStartDate(): string
    {
        return explode(' ', $this->start_time)[0];
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function userSlots(int $userId): int
    {
        return AppointmentUser::where('user_id', $userId)->where('appointment_id', $this->getId())->first()->getSlotsTaken();
    }

    public function getTotalSlots(): int
    {
        return $this->total_slots;
    }

    public function getSlotsTaken(): int
    {
        return $this->slots_taken;
    }

    public function getAvailableSlots(): int
    {
        return $this->getTotalSlots() - $this->getSlotsTaken();
    }

    public function pastEnd(): bool
    {
        return (int) $this->past_end === 1;
    }

    public function incrementSlotsTaken(int $addedSlots)
    {
        $this->slots_taken += $addedSlots;
        $this->save();
    }

    public function decrementSlotsTaken(int $removedSlots)
    {
        $this->slots_taken -= $removedSlots;
        $this->save();
    }
}
