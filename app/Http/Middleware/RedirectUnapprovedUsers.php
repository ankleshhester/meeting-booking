<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;

class RedirectUnapprovedUsers
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // We check if the user is authenticated.
        if (Auth::check()) {
            $user = Auth::user();

            // 💡 SIMPLIFIED CHECK: Rely on the User model's boolean casting for 'is_approved'.
            // If the user is authenticated AND the approval flag is false:
            if ($user && !$user->is_approved) {

                // 1. Log the user out to prevent a lingering, unauthorized session.
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // 2. Send a user-friendly notification.
                Notification::make()
                    ->title('Account Pending Approval')
                    ->body('Your account is awaiting approval from an administrator. Please try again later.')
                    ->danger()
                    ->send();

                // 3. Redirect back to the login page.
                // This prevents the 403 and the "Wrong Credentials" loop.
                return redirect()->route('filament.admin.auth.login');
            }
        }

        return $next($request);
    }
}
