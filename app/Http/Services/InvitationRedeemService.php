<?php

namespace App\Http\Services;

use App\Models\Invitation;
use Illuminate\Http\Request;

class InvitationRedeemService
{
    public function redeem(Request $request, string $token): void
    {
        $invitation = Invitation::query()
            ->where('token', $token)
            ->firstOrFail();

        if (! $invitation->isValid()) {
            abort(410, 'This invitation has expired or has already been used.');
        }

        $request->session()->put('family_join_context', [
            'inviter_id' => $invitation->inviter_id,
            'relationship_type' => $invitation->relationship_type,
            'inviter_name' => $invitation->inviter->name,
            'invitation_token' => $token,
        ]);
    }
}
