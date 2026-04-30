<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationRedeemController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::query()
            ->where('token', $token)
            ->firstOrFail();

        if (! $invitation->isValid()) {
            abort(410, 'This invitation has expired or has already been used.');
        }

        $request->session()->put('family_join_context', [
            'family_tree_id' => $invitation->family_tree_id,
            'inviter_id' => $invitation->inviter_id,
            'relationship_type' => $invitation->relationship_type,
            'tree_name' => $invitation->familyTree->name,
            'inviter_name' => $invitation->inviter->name,
            'invitation_token' => $token,
        ]);

        return redirect()->route('register');
    }
}
