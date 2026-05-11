<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\FamilyRelationshipController;
use App\Http\Controllers\InvitationRedeemController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login/google/redirect', [SocialiteController::class, 'redirect'])
        ->name('login.google.redirect');
    Route::get('login/google/callback', [SocialiteController::class, 'callback'])
        ->name('login.google.callback');

    Route::get('family-join/{token}', InvitationRedeemController::class)
        ->name('family-join.redeem');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resources([
        'family-relationships' => FamilyRelationshipController::class,
    ]);

    Route::post('family-relationships/share-link', [FamilyRelationshipController::class, 'shareLink'])
        ->name('family-relationships.share-link');
});

require __DIR__.'/settings.php';
