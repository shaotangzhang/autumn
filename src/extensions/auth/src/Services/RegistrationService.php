<?php

namespace Autumn\Extensions\Auth\Services;

use Autumn\Exceptions\AuthenticationException;
use Autumn\Extensions\Auth\Forms\RegistrationForm;
use Autumn\Extensions\Auth\Models\Confirmation\ConfirmationCode;
use Autumn\Extensions\Auth\Models\Role\Role;
use Autumn\Extensions\Auth\Models\User\User;
use Autumn\Extensions\Auth\Models\User\UserEntity;
use Autumn\Mailing\Mail;
use Autumn\Mailing\MailService;
use Autumn\System\Service;

class RegistrationService extends Service
{
    public const SUBMISSION_FORMS = [
        RegistrationForm::class => 'signUp',
    ];

    /**
     * @throws \Throwable
     */
    public function signUp(RegistrationForm $form): UserEntity
    {
        return $this->register($form->someOf([
            'username', 'email', 'password', 'name' => 'nickname'
        ]));
    }

    /**
     * Register a new user.
     *
     * @param array $userData User registration data
     * @return UserEntity Newly registered user entity
     * @throws AuthenticationException If registration fails
     * @throws \Throwable
     */
    public function register(array $userData): UserEntity
    {
        // Validate user input
        $this->validateUserData($userData);

        // Generate username if not provided
        if (!isset($userData['username'])) {
            $name = explode('@', $userData['email'])[0];
            $username = substr($name, 0, 4) . '_' . substr(md5($userData['email']), 8, 6);
            $userData['username'] = $username;
            $userData['nickname'] ??= $name; // Set nickname if not provided
        }

        // Set default values if not provided
        $userData['type'] ??= env('USER_DEFAULT_ROLE', Role::USER);
        $userData['status'] ??= env('USER_DEFAULT_STATUS', User::defaultStatus());

        // Encrypt the password
        $userData['password'] = $this->encryptPassword($userData['password']);

        // Save user to database
        try {
            $user = User::create($userData);
        } catch (\Exception $e) {
            throw AuthenticationException::of('Failed to register a user.', $e);
        }

        // Optionally send confirmation email
        if (filter_var(env('USER_SEND_CONFIRMATION_MAIL_ON_REGISTER'), FILTER_VALIDATE_BOOL)) {
            $this->sendConfirmationEmail($user);
        }

        return $user;
    }

    /**
     * Validate user registration data.
     *
     * @param array $userData User registration data
     * @throws AuthenticationException If validation fails
     */
    private function validateUserData(array $userData): void
    {
        // Check if email is already registered
        if (User::find(['email' => $userData['email']])?->exists()) {
            throw AuthenticationException::of('Email address is already registered.');
        }

        // Check: Check if username is already registered
        if (($userData['username'] ?? null) && User::find(['username' => $userData['username']])?->exists()) {
            throw AuthenticationException::of('Email address is already registered.');
        }

        // Password complexity check
        if (strlen($userData['password']) < 4) {
            throw AuthenticationException::of('Password must be at least 4 characters long.');
        }
    }

    /**
     * Encrypt user password.
     *
     * @param string $password User password
     * @return string Encrypted password
     */
    public function encryptPassword(string $password): string
    {
        // Example: Use PHP's password_hash() function for encryption
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Send confirmation email to the user.
     *
     * @param UserEntity $user Newly registered user entity
     * @throws \Throwable
     */
    public function sendConfirmationEmail(UserEntity $user): bool
    {
        $code = ConfirmationCode::generate($user->getId(), 'registration', 8);
        $code->setDeletedAt('+7 days');
        $confirmation = ConfirmationCode::replace($code);

        // Example: Send confirmation email using a mailing service or library
        $confirmationLink = $this->getConfirmationLink($confirmation);
        $emailBody = "Please click the following link to confirm your email address: $confirmationLink";

        $mail = new Mail;
        $mail->to($user->getEmail())
            ->subject('Confirm Your Email')
            ->body($emailBody);

        return MailService::context()->send($mail);
    }

    public function getConfirmationLink(ConfirmationCode $confirmation): string
    {
        return 'https://example.com/confirm-email?token=' . $confirmation->getCode();
    }
}
