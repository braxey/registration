<?php

namespace App\Models;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class Phone
{
    protected string $locale;
    protected string $originalNumber;
    protected ?PhoneNumber $parsedNumber;
    protected PhoneNumberUtil $libphone;

    public function __construct(string $number, string $locale = 'US')
    {
        $this->originalNumber = $number;
        $this->locale = $locale;
        $this->libphone = PhoneNumberUtil::getInstance();

        try {
            $this->parsedNumber = $this->libphone->parse($number, strtoupper($locale));
        } catch (Exception $e) {
            $this->parsedNumber = null;
        }
    }

    public static function fromNumber(string $number, string $locale = 'US'): Phone
    {
        return new static($number, strtoupper($locale));
    }

    public function isValid(): bool
    {
        return ($this->parsedNumber !== null) && $this->libphone->isValidNumber($this->parsedNumber); 
    }

    public function isNotValid(): bool
    {
        return !$this->isValid();
    }

    public function getCountryCode(): ?int
    {
        if ($this->isValid()) {
            return (int) $this->parsedNumber->getCountryCode();
        }
        return null;
    }

    public function getCountryIsoCode(): ?string
    {
        if ($this->isValid()) {
            return $this->libphone->getRegionCodeForNumber($this->parsedNumber);
        }
        return null;
    }

    public function toE164(bool $withPlus = true): string
    {
        $e164 = $this->libphone->format($this->parsedNumber, PhoneNumberFormat::E164);
        if (!$withPlus) {
            return preg_replace("/[^0-9]/", "", $e164);
        }
        return $e164;
    }

    public function formatPhoneNational(): string
    {
        return $this->libphone->format($this->parsedNumber, PhoneNumberFormat::NATIONAL);
    }

    public function formatPhoneInternational(): string
    {
        return $this->libphone->format($this->parsedNumber, PhoneNumberFormat::INTERNATIONAL);
    }

    public function formatPhoneForCountry(string $iso = 'US'): string
    {
        if ($this->getCountryIsoCode() === $iso) {
            return $this->formatPhoneNational();
        }
        return $this->formatPhoneInternational();
    }
}
