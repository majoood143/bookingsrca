<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return parent::form($schema)->components([
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
            CaptchaField::make('captcha'),
        ]);
    }
}
