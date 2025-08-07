<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ClinicContextComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $view->with([
                'currentClinicId' => Session::get('current_clinic_id'),
                'currentClinicName' => Session::get('current_clinic_name'),
                'currentUserRole' => Session::get('current_user_role'),
            ]);
        }
    }
}
