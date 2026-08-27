<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\Store;
use App\Models\Staff;
use App\Models\Commissionnaire;
use App\Models\AppSetting;

class PasswordResetController extends Controller
{
    private function findAccount(string $email): ?array
    {
        if ($admin = Admin::where('email', $email)->first()) {
            return [$admin, 'admin'];
        }
        if ($store = Store::where('email', $email)->first()) {
            return [$store, 'store'];
        }
        if ($staff = Staff::where('email', $email)->first()) {
            return [$staff, 'staff'];
        }
        if ($commissionnaire = Commissionnaire::where('email', $email)->first()) {
            return [$commissionnaire, 'commissionnaire'];
        }
        return null;
    }

    private function applyMailConfig(): void
    {
        $config = AppSetting::get('email_config', []);
        if (empty($config['host'])) {
            return; // fall back to .env defaults (log driver in dev)
        }

        config([
            'mail.default'                  => 'smtp',
            'mail.mailers.smtp.host'        => $config['host'],
            'mail.mailers.smtp.port'        => $config['port'] ?? 587,
            'mail.mailers.smtp.username'    => $config['username'] ?? null,
            'mail.mailers.smtp.password'    => $config['password'] ?? null,
            'mail.mailers.smtp.encryption'  => ($config['encryption'] ?? 'tls') === 'none' ? null : $config['encryption'],
            'mail.from.address'             => $config['from_email'] ?? config('mail.from.address'),
            'mail.from.name'                => $config['from_name'] ?? config('mail.from.name'),
        ]);
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => "L'email est requis.",
            'email.email'    => 'Email invalide.',
        ]);

        $found = $this->findAccount($request->email);

        // Always show the same message, whether the email exists or not,
        // to avoid leaking which emails are registered.
        $genericMessage = 'Si un compte existe avec cet email, un lien de réinitialisation vient de vous être envoyé.';

        if (!$found) {
            return back()->with('success', $genericMessage);
        }

        [$account, $actorType] = $found;

        DB::table('password_resets')->where('email', $request->email)->where('actor_type', $actorType)->delete();

        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email'      => $request->email,
            'actor_type' => $actorType,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $request->email]);

        $this->applyMailConfig();

        try {
            Mail::raw(
                "Bonjour,\n\nVous avez demandé la réinitialisation de votre mot de passe GazManager.\n" .
                "Cliquez sur ce lien pour choisir un nouveau mot de passe (valable 60 minutes) :\n{$resetUrl}\n\n" .
                "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.",
                function ($message) use ($request) {
                    $message->to($request->email)->subject('Réinitialisation de votre mot de passe GazManager');
                }
            );
        } catch (\Throwable $e) {
            // Swallow mail delivery failures — don't leak account existence via error state,
            // and the admin's SMTP config may simply not be set up yet.
        }

        return back()->with('success', $genericMessage);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required'  => 'Le mot de passe est requis.',
            'password.min'       => 'Le mot de passe doit avoir au moins 6 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $records = DB::table('password_resets')->where('email', $request->email)->get();

        $record = $records->first(function ($r) use ($request) {
            return Hash::check($request->token, $r->token);
        });

        if (!$record || now()->diffInMinutes($record->created_at) > 60) {
            return back()->withErrors(['token' => 'Ce lien de réinitialisation est invalide ou expiré.']);
        }

        $found = $this->findAccount($request->email);
        if (!$found || $found[1] !== $record->actor_type) {
            return back()->withErrors(['token' => 'Ce lien de réinitialisation est invalide ou expiré.']);
        }

        [$account] = $found;
        $account->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $request->email)->where('actor_type', $record->actor_type)->delete();

        return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
    }
}
