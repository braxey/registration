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

    public function setRegistration(bool $open)
    {
        $this->registration_open = $open;
        $this->save();
    }

    public function toggleRegistration()
    {
        $this->setRegistration(!$this->registrationIsOpen());
    }

    public function getMaxSlotsPerUser(): int
    {
        return $this->max_slots_per_user;
    }
}
