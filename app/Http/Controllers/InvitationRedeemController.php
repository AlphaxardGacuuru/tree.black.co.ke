<?php

namespace App\Http\Controllers;

use App\Http\Services\InvitationRedeemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationRedeemController extends Controller
{
    public function __construct(private readonly InvitationRedeemService $service) {}

    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $this->service->redeem($request, $token);

        return redirect()->route('register');
    }
}
