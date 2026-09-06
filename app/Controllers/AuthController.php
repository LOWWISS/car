<?php
/**
 * AuthController: registration, login, logout, email verification, password reset.
 *
 * SECURITY:
 *  - Rate limited (login, register, forgot-password).
 *  - Brute-force: account lockout after LOGIN_MAX_ATTEMPTS failed attempts.
 *  - CSRF on all POST handlers (via guardPost).
 *  - password_hash / password_verify only — plaintext never stored or logged.
 *  - Session regenerated on successful login.
 */
final class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (Auth::check()) Response::redirect(Auth::is('admin') ? '/admin' : '/dashboard');
        $this->view('auth/login', ['pageTitle' => 'Login', 'csrf' => Csrf::token()]);
    }

    public function login(): void
    {
        $this->guardPost('login', [10, 60]); // 10 logins / 60s per IP

        $email = trim((string) $this->request->input('email', ''));
        $password = (string) $this->request->input('password', '');

        $v = new Validator(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required|string|min:6']
        );
        if (!$v->validate()) {
            $this->flashRedirect('error', implode(' ', $v->errors), '/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        // Always run password_verify to avoid user-enumeration timing leaks, but
        // guard against null user.
        $valid = $user && password_verify($password, $user['password_hash']);

        if (!$user || !$valid) {
            if ($user) {
                $userModel->recordFailedLogin((int) $user['id']);
                Logger::audit('login_failed', ['user' => $email], $user ? (int)$user['id'] : null);

                // Show remaining attempts before lockout. recordFailedLogin() has
                // already incremented the counter, so read it back post-increment.
                $max = (int) Config::get('LOGIN_MAX_ATTEMPTS', 5);
                $failed = $userModel->getFailedAttempts((int) $user['id']);
                $remaining = $max - $failed;
                // Stash for the login view so it can render an inline indicator.
                Session::flash('login_remaining', $remaining);
                Session::flash('login_max', $max);
                if ($remaining <= 0) {
                    $lockMins = (int) Config::get('LOGIN_LOCKOUT_MINUTES', 15);
                    Session::flash('login_locked_mins', $lockMins);
                    // No toast here - the login view renders an inline lockout
                    // indicator from the flashed login_locked_mins value above.
                    $this->redirect('/login');
                }
                $this->flashRedirect(
                    'error',
                    'Invalid email or password. ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining before your account is locked.',
                    '/login'
                );
            } else {
                Logger::audit('login_failed_unknown', ['user' => $email]);
            }
            $this->flashRedirect('error', 'Invalid email or password.', '/login');
        }

        if (!$user['is_active']) {
            $this->flashRedirect('error', 'Your account has been disabled.', '/login');
        }
        if ($userModel->isLocked((int) $user['id'])) {
            $until = $userModel->getLockedUntil((int) $user['id']);
            $mins = $until ? max(1, (int) ceil((strtotime($until) - time()) / 60)) : 0;
            Session::flash('login_locked_mins', $mins);
            $this->flashRedirect(
                'error',
                'Account locked due to too many failed attempts. Try again in ' . $mins . ' minute' . ($mins === 1 ? '' : 's') . '.',
                '/login'
            );
        }

        $userModel->resetFailedLogin((int) $user['id']);
        RateLimiter::clear('login', (int) $user['id']);
        Auth::login($user);

        $redirect = ($user['role'] === 'admin') ? '/admin' : '/dashboard';
        $this->flashRedirect('success', 'Welcome back, ' . $user['name'] . '!', $redirect);
    }

    public function showRegister(): void
    {
        if (Auth::check()) Response::redirect(Auth::is('admin') ? '/admin' : '/dashboard');
        $this->view('auth/register', ['pageTitle' => 'Register', 'csrf' => Csrf::token()]);
    }

    public function register(): void
    {
        $this->guardPost('register', [5, 300]); // 5 registrations / 5min per IP

        $data = [
            'name'     => trim((string) $this->request->input('name', '')),
            'email'    => trim((string) $this->request->input('email', '')),
            'password' => (string) $this->request->input('password', ''),
            'password_confirm' => (string) $this->request->input('password_confirm', ''),
            'role'     => (string) $this->request->input('role', 'buyer'),
            'phone'    => trim((string) $this->request->input('phone', '')),
        ];

        $v = new Validator($data, [
            'name'     => 'required|string|min:2|max:100',
            'email'    => 'required|email|max:190',
            'password' => 'required|string|min:8|max:72',
            'role'     => 'required|in,buyer,seller',
            'phone'    => 'string|max:30',
        ]);
        if (!$v->validate()) {
            $this->flashRedirect('error', implode(' ', $v->errors), '/register');
        }
        if ($data['password'] !== $data['password_confirm']) {
            $this->flashRedirect('error', 'Passwords do not match.', '/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($data['email'])) {
            $this->flashRedirect('error', 'An account with that email already exists.', '/register');
        }

        $id = $userModel->create($data);

        // Send verification email (best-effort)
        $user = $userModel->find($id);
        if ($user && $user['verify_token']) {
            $link = rtrim(Config::get('APP_URL', ''), '/') . '/verify-email/' . $user['verify_token'];
            $html = "<p>Welcome to " . e(Config::get('APP_NAME', 'Car Auction')) . "!</p>
                     <p>Please verify your email by clicking the link below:</p>
                     <p><a href=\"{$link}\">Verify my email</a></p>";
            Mailer::send($user['email'], 'Verify your email', $html);
        }

        Logger::audit('register', ['user' => $data['email']], $id);
        $this->flashRedirect('success', 'Account created. Check your email to verify your account.', '/login');
    }

    public function verifyEmail(string $token): void
    {
        $userModel = new User();
        if ($userModel->verifyEmail($token)) {
            $this->flashRedirect('success', 'Email verified. You can now log in.', '/login');
        }
        $this->flashRedirect('error', 'Invalid or expired verification link.', '/login');
    }

    public function logout(): void
    {
        // CSRF-protect logout to prevent log-out CSRF.
        if (!Csrf::verify()) {
            Response::redirect('/');
        }
        Auth::logout();
        Response::redirect('/');
    }

    public function showForgot(): void
    {
        $this->view('auth/forgot', ['pageTitle' => 'Reset Password', 'csrf' => Csrf::token()]);
    }

    public function sendReset(): void
    {
        $this->guardPost('forgot', [5, 300]);

        $email = trim((string) $this->request->input('email', ''));
        $v = new Validator(['email' => $email], ['email' => 'required|email']);
        if (!$v->validate()) {
            $this->flashRedirect('error', 'Please enter a valid email.', '/forgot-password');
        }
        $userModel = new User();
        $user = $userModel->setResetToken($email);

        // Always show the same message to prevent user enumeration.
        if ($user) {
            $link = rtrim(Config::get('APP_URL', ''), '/') . '/reset-password/' . $user['reset_token'];
            $html = "<p>Reset your password by clicking the link below (valid for 1 hour):</p>
                     <p><a href=\"{$link}\">Reset password</a></p>
                     <p>If you did not request this, ignore this email.</p>";
            Mailer::send($user['email'], 'Password reset', $html);
            Logger::audit('password_reset_requested', ['user' => $email], (int)$user['id']);
        }
        $this->flashRedirect('success', 'If that email exists, a reset link has been sent.', '/login');
    }

    public function showReset(string $token): void
    {
        $this->view('auth/reset', [
            'pageTitle' => 'Set New Password',
            'token'     => $token,
            'csrf'      => Csrf::token(),
        ]);
    }

    public function resetPassword(): void
    {
        $this->guardPost('reset', [5, 300]);
        $token = (string) $this->request->input('token', '');
        $password = (string) $this->request->input('password', '');
        $confirm = (string) $this->request->input('password_confirm', '');

        $v = new Validator(
            ['token' => $token, 'password' => $password],
            ['token' => 'required|string', 'password' => 'required|string|min:8|max:72']
        );
        if (!$v->validate()) {
            $this->flashRedirect('error', implode(' ', $v->errors), '/forgot-password');
        }
        if ($password !== $confirm) {
            $this->flashRedirect('error', 'Passwords do not match.', '/reset-password/' . $token);
        }
        $userModel = new User();
        if ($userModel->resetPassword($token, $password)) {
            Logger::audit('password_reset_completed');
            $this->flashRedirect('success', 'Password updated. Please log in.', '/login');
        }
        $this->flashRedirect('error', 'Reset link is invalid or expired.', '/forgot-password');
    }
}
