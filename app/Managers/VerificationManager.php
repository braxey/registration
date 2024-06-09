<?php

namespace App\Managers;

use Illuminate\Support\Collection;
use App\Constants\EmailTypes;
use App\Models\User;
use App\Models\PhoneVerification;
use App\Services\QueueService;
use App\Exceptions\ModelNotFoundExcepion;
use App\Exceptions\VerificationLimitHitException;
use Carbon\Carbon;

class VerificationManager
{
    protected QueueService $queueService;
    protected User $user;
    protected Collection $verifications;

    public function __construct(?string $email)
    {
        $this->queueService = app(QueueService::class);

        $user = User::fromEmail($email);
        if ($user === null) {
            throw new ModelNotFoundException();
        }

        $this->user = $user;
        $this->verifications = PhoneVerification::where('user_id', $user->getId())->orderBy('time_sent', 'desc')->get();
    }

    public function sendVerification(bool $createNewToken = false)
    {
        // enforce limit on number of verification sends
        if ($this->hasHitLimit()) {
            throw new VerificationLimitHitException();
        }

        // either create or grab the token to send
        if ($createNewToken) {
            $token = generateSecureNumericToken();
        } else {
            $token = $this->getRecentToken();
        }

        // record the new verification send
        PhoneVerification::create([
            'token'     => $token,
            'time_sent' => Carbon::now('EST'),
            'user_id'   => $this->user->getId(),
        ]);

        // kick off sending queued emails so verifications get sent as soon as they can
        $this->queueService->push($this->user->getEmail(), EmailTypes::VERIFICATION, ['token' => $token]);
        $this->queueService->handleQueueDispatch();
    }

    public function verify(?string $token)
    {
        // if the submitted token is correct, we need to delete all verification records associated with the user
        if ($this->isCorrectToken($token)) {
            $this->verifications->each(function (PhoneVerification $verification) {
                $verification->delete();
            });
            return true;
        }
        return false;
    }

    private function hasHitLimit()
    {
        $fiveMinutesAgo = Carbon::now('EST')->subMinutes(5);

        $numberSentWithinPastFiveMinutes = $this->verifications->filter(function (PhoneVerification $verification) use ($fiveMinutesAgo) {
            return $verification->getParsedTimeSent()->gte($fiveMinutesAgo);
        })->count();

        return $numberSentWithinPastFiveMinutes >= config('mail.verification-limit');
    }

    private function getRecentToken()
    {
        if ($this->verifications->count() === 0) {
            throw new ModelNotFoundException();
        }

        return $this->verifications->first()->getToken();
    }

    private function isCorrectToken(?string $token)
    {
        return $token !== null && strlen($token) === 7 && $this->getRecentToken() === $token;
    }

}