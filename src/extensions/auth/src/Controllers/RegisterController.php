<?php

namespace Autumn\Extensions\Auth\Controllers;

use Autumn\Extensions\Auth\Forms\RegistrationForm;
use Autumn\Extensions\Auth\Interfaces\CredentialInterface;
use Autumn\Extensions\Auth\Services\RegistrationService;
use Autumn\System\Responses\RedirectResponse;

class RegisterController extends AbstractController
{
    public const METHOD_POST = 'register';

    protected string $viewPath = 'login/';

    protected array $languageDomains = ['login', 'register', 'login/register'];

    private ?RegistrationService $registrationService = null;

    /**
     * @return RegistrationService|null
     */
    public function getRegistrationService(): ?RegistrationService
    {
        return $this->registrationService ??= make(RegistrationService::class);
    }

    public function index(?string $redirect, ?string $error): mixed
    {
        $this->set('title', 'Register');
        return $this->view('register', ['error' => $error, 'redirect' => $redirect]);
    }

    public function register(RegistrationForm $form, string $redirect = null): RedirectResponse|CredentialInterface
    {
        // Submit registration and obtain user entity
        $user = $this->getRegistrationService()->submit($form);

        // Create user session
        $userSession = $this->getAuthService()->createSession($user);

        // Login user session
        $this->getAuthService()->login($userSession);

        // Redirect if specified
        if ($redirect) {
            return $this->redirect($redirect);
        }

        // Return credential interface if no redirect
        return $this->getAuthService()->createCredential($userSession);
    }
}
