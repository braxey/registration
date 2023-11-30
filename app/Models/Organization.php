<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'org_name',
        'max_slots_per_user',
        'registration_open'
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->org_name;
    }

    public function registrationIsOpen(): bool
    {
        return (int) $this->registration_open === 1;
    }

    public function registrationIsClosed(): bool
    {
        return !$this->registrationIsOpen();
    }

    public function openRegistration()
    {
        $this->registration_open = true;
        $this->save();
    }

    public function closeRegistration()
    {
        $this->registration_open = false;
        $this->save();
    }

    public function toggleRegistration()
    {
        if ($this->registrationIsOpen()) {
            $this->closeRegistration();
        } else {
            $this->openRegistration();
        }
    }

    public function getMaxSlotsPerUser(): int
    {
        return $this->max_slots_per_user;
    }

    public function setMaxSlotsPerUser(int $slots)
    {
        $this->max_slots_per_user = $slots;
        $this->save();
    }
}
