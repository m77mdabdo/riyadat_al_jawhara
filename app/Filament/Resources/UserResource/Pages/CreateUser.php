<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Support\StaffAccess;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * With "Send an invitation email" on (the default) the account gets an
 * unusable random password and the person chooses their own from the link in
 * the email; otherwise the admin sets the password here.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if (blank($data['password'] ?? null)) {
            $data['password'] = Hash::make(Str::password(40));
        }

        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        if (! ($this->data['send_invitation'] ?? false)) {
            return;
        }

        /** @var User $user */
        $user = $this->getRecord();
        UserResource::notifyAccessResult(StaffAccess::send($user, invitation: true), $user, invitation: true);
    }

    protected function getCreatedNotification(): ?Notification
    {
        // afterCreate reports whether the invitation went out.
        return ($this->data['send_invitation'] ?? false) ? null : parent::getCreatedNotification();
    }
}
