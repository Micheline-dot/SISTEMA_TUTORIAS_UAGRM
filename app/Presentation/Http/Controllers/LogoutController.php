<?php

namespace App\Presentation\Http\Controllers;

use App\Business\Usuarios\UseCases\LogoutUser;
use Illuminate\Http\RedirectResponse;

final class LogoutController
{
    public function __invoke(LogoutUser $logout): RedirectResponse
    {
        $logout->execute();

        return redirect()->route('login');
    }
}
