<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\WalkIn;
use Carbon\Carbon;
use App\Models\AppointmentUser;


class Appointment extends Model
{
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setWalkInOnly(bool $walkInOnly)
    {
        $this->walk_in_only = $walkInOnly;
        $this->save();
    }

    public function isWalkInOnly(): bool
    {
        return (int) $this->walk_in_only === 1;
    }

    public function addWalkIn(WalkIn $walkIn)
    {
        $updatedSlotsTaken = $this->slots_taken + $walkIn->getNumberOfSlots();
        $this->slots_taken = $updatedSlotsTaken;
        if ($updatedSlotsTaken > $this->getTotalSlots()) {
            $this->total_slots = $updatedSlotsTaken;
        }
        $this->save();
    }

    public function removeWalkIn(WalkIn $walkIn)
    {
        $this->decrementSlotsTaken($walkIn->getNumberOfSlots());
    }

    public function isOpen(): bool
    {
        return (
            $this->isUpcoming()
            && $this->getTotalSlots() > $this->getSlotsTaken()
            && now('EST') < $this->getParsedStartTime()->setTime(12, 0, 0)
        );
    }

    public function isClosed(): bool
    {
        return !$this->isOpen();
    }

    public function canEdit(): bool
    {
        return (
            $this->isUpcoming()
            && now('EST') < $this->getParsedStartTime()->setTime(12, 0, 0)
        );
    }

    public function cannotEdit(): bool
    {
        return !$this->canEdit();
    }

    public function getStartTime()
    {
        return $this->start_time;
    }

    public function getEndTime()
    {
        return $this->end_time;
    }

    public function getParsedStartTime(): Carbon
    {
        return Carbon::parse($this->getStartTime(), 'EST');
    }

    public function getParsedEndTime(): Carbon
    {
        return Carbon::parse($this->getEndTime(), 'EST');
    }

    public function getStartDate(): string
    {
        return explode(' ', $this->start_time)[0];
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        if ($status === 'completed') {
            $this->past_end = true;
        }
        $this->save();
    }

    public function isUpcoming(): bool
    {
        return $this->status == 'upcoming';
    }

    public function isInProgress(): bool
    {
        return $this->status == 'in progress';
    }

    public function isCompleted(): bool
    {
        return $this->status == 'completed';
    }

    public function getShowedUp(): int
    {
        $count = 0;

        AppointmentUser::where('appointment_id', $this->getId())
            ->get()
            ->map(function (AppointmentUser $booking) use (&$count) {
                $count += $booking->getShowedUp();
            });

        WalkIn::where('appointment_id', $this->getId())
            ->get()
            ->map(function (WalkIn $walkIn) use (&$count) {
                $count += $walkIn->getNumberOfSlots();
            });

        return $count;
    }

    public function userSlots(int $userId): int
    {
        $booking = AppointmentUser::where('user_id', $userId)->where('appointment_id', $this->getId())->first();
        if ($booking === null) {
            return 0;
        }

        return $booking->getSlotsTaken();
    }

    public function getTotalSlots(): int
    {
        return $this->total_slots;
    }

    public function setTotalSlots(int $slots)
    {
        $this->total_slots = $slots;
        $this->save();
    }

    public function getSlotsTaken(): int
    {
        return $this->slots_taken;
    }

    public function setSlotsTaken(int $slots)
    {
        $this->slots_taken = $slots;
        $this->save();
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

    public function getBookings(): Collection
    {
        return AppointmentUser::where('appointment_id', $this->getId())->get();
    }

    public function getWalkIns(): Collection
    {
        return WalkIn::where('appointment_id', $this->getId())->get();
    }

    public function decrementSlotsTaken(int $removedSlots)
    {
        $this->slots_taken -= $removedSlots;
        $this->save();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'start_time' => $this->getParsedStartTime(),
            'end_time' => $this->getParsedEndTime(),
            'total_slots' => $this->getTotalSlots(),
            'slots_taken' => $this->getSlotsTaken(),
            'past_end' => (int) $this->pastEnd(),
            'status' => $this->getStatus(),
            'walk_in_only' => (int) $this->isWalkInOnly(),
        ];
    }

    public static function fromId($id): ?Appointment
    {
        return static::where('id', $id)->first();
    }

    public static function getUpcoming(): Collection
    {
        return static::where('status', 'upcoming')->get();
    }
}
