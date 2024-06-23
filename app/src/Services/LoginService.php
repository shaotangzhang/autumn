<?php
namespace App\Services;

use App\Forms\Login\ForgetPasswordForm;
use App\Forms\Login\LoginForm;
use App\Forms\Login\RegisterForm;
use App\Forms\Login\ResetPasswordForm;
use Autumn\System\Service;
use Throwable;

/**
 * Class LoginService
 *
 * This class extends the Service class to handle login-related form submissions such as login,
 * registration, password reset, and password recovery.
 */
class LoginService extends Service
{
    /**
     * @var array SUBMISSION_FORMS
     *
     * A constant array that maps form request classes to their respective submission handler methods
     * specifically for login-related functionalities.
     */
    public const SUBMISSION_FORMS = [
        LoginForm::class => 'login',
        RegisterForm::class => 'register',
        ForgetPasswordForm::class => 'forgetPassword',
        ResetPasswordForm::class => 'resetPassword'
    ];

    /**
     * Handle login form submission.
     *
     * @param LoginForm $form The login form request to be processed.
     * @param array|null $context Optional. Additional context information for the login.
     * @return mixed The result of the login process.
     */
    public function login(LoginForm $form, array $context = null): mixed
    {
        try {
            $username = $form->get('username');
            $password = $form->get('password');

            // Validate and authenticate user with $username and $password
            // ...

            // $this->logger->info('User logged in', ['username' => $username]);

            return ['status' => 'success', 'message' => 'Logged in successfully'];
        } catch (Throwable $e) {
            // $this->logger->error('Login failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Login failed'];
        }
    }

    /**
     * Handle registration form submission.
     *
     * @param RegisterForm $form The registration form request to be processed.
     * @param array|null $context Optional. Additional context information for the registration.
     * @return mixed The result of the registration process.
     */
    public function register(RegisterForm $form, array $context = null): mixed
    {
        try {
            $username = $form->get('username');
            $password = $form->get('password');
            $email = $form->get('email');

            // Register the user with $username, $password, and $email
            // ...

            // $this->logger->info('User registered', ['username' => $username]);
            return ['status' => 'success', 'message' => 'Registered successfully'];
        } catch (Throwable $e) {
            // $this->logger->error('Registration failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Registration failed'];
        }
    }

    /**
     * Handle password recovery request submission.
     *
     * @param ForgetPasswordForm $form The password recovery form request to be processed.
     * @param array $context Additional context information for the password recovery.
     * @return mixed The result of the password recovery process.
     */
    public function resendPassword(ForgetPasswordForm $form, array $context): mixed
    {
        try {
            $email = $form->get('email');

            // Send password recovery email to $email
            // ...

            // $this->logger->info('Password recovery email sent', ['email' => $email]);

            return ['status' => 'success', 'message' => 'Password recovery email sent'];
        } catch (Throwable $e) {
            // $this->logger->error('Password recovery failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Password recovery failed'];
        }
    }

    /**
     * Handle password reset request submission.
     *
     * @param ResetPasswordForm $form The password reset form request to be processed.
     * @param array $context Additional context information for the password reset.
     * @return mixed The result of the password reset process.
     */
    public function resetPassword(ResetPasswordForm $form, array $context): mixed
    {
        try {
            $token = $form->get('token');
            $newPassword = $form->get('new_password');

            // Verify token and reset password with $newPassword
            // ...

            // $this->logger->info('Password reset successful', ['token' => $token]);
            return ['status' => 'success', 'message' => 'Password reset successfully'];
        } catch (Throwable $e) {
            // $this->logger->error('Password reset failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Password reset failed'];
        }
    }

    /**
     * Logout the current user.
     *
     * This static method logs out the current user.
     *
     * @return void
     */
    public static function logout(): void
    {
        // Implementation for logout functionality
    }

    /**
     * Verify the password recovery token.
     *
     * This method verifies the provided token for password recovery.
     *
     * @param string $token The recovery token to be verified.
     * @return void
     * @throws Throwable
     */
    public function verifyRecoverToken(string $token): void
    {
        try {
            // Verify the token
            // ...

            // $this->logger->info('Recovery token verified', ['token' => $token]);
        } catch (Throwable $e) {
            // $this->logger->error('Token verification failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
