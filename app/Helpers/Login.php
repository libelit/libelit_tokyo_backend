<?php

namespace App\Helpers;

use App\Enums\UserTypeEnum;
use Filament\Auth\Pages\Login as FilamentLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;

class Login extends FilamentLogin
{
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        $authProvider = Filament::auth()->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);

        $user = $authProvider->retrieveByCredentials($credentials);


        if ($user && $user->type !== UserTypeEnum::ADMIN) {
            throw ValidationException::withMessages([
                'data.email' => 'You are not authorized to access the admin panel.',
            ]);
        }

        return parent::authenticate();
    }
}
