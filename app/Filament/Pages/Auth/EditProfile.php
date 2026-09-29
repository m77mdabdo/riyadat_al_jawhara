<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

/**
 * Filament's profile page, plus: changing the email or password requires the
 * current password, so an unattended signed-in session can't be used to take
 * over the account.
 */
class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent()->live(onBlur: true),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                TextInput::make('currentPassword')
                    ->label('Current password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->currentPassword()
                    ->required(fn (Get $get): bool => filled($get('password')) || $get('email') !== $this->getUser()->email)
                    ->helperText('Required to change your email or password.')
                    ->dehydrated(false),
            ]);
    }
}
