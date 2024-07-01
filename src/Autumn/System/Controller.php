<?php

namespace Autumn\System;

use Autumn\I18n\Translation;
use Autumn\Interfaces\ArrayInterface;
use Autumn\Interfaces\ContextInterface;
use Autumn\System\Responses\JsonResponse;
use Autumn\System\Responses\RedirectResponse;
use Autumn\Traits\ArrayPropertiesTrait;
use Autumn\Traits\ContextInterfaceTrait;

class Controller implements ContextInterface, ArrayInterface
{
    use ContextInterfaceTrait;
    use ArrayPropertiesTrait;

    public const METHOD_GET = 'index';
    public const METHOD_POST = 'post';
    public const METHOD_PUT = 'put';
    public const METHOD_PATCH = 'path';
    public const METHOD_HEAD = 'head';
    public const METHOD_DELETE = 'delete';
    public const METHOD_OPTIONS = 'options';
    public const METHOD_TRACE = 'trace';
    public const METHOD_CONNECT = 'connect';

    protected string $viewPath = '';
    protected array $languageDomains = [];

    private ?Translation $translation = null;

    protected function loadLang(string $domain, string $lang = null): Translation
    {
        if ($this->translation === null) {
            $lang ??= Translation::lang();
            $this->translation = new Translation($domain, $lang);
            foreach ($this->languageDomains as $language) {
                if ($translations = Translation::load($language, null, $lang)) {
                    $this->translation->merge($translations);
                }
            }
            Translation::load($domain, null, $lang);
        }

        return $this->translation;
    }

    protected function view(string $view, array $args = null, array $context = null): View
    {
        if (!str_starts_with($view, '/')) {
            $view = $this->viewPath . $view;
        }

        $args = array_merge($this->toArray(), $args ?? []);
        $view = new View($view, $args, $context);
        $view->setTranslation($this->translation);
        return $view;
    }

    protected function redirect(string $location, int $statusCode = null, string $reasonPhrase = null): RedirectResponse
    {
        return new RedirectResponse($location, $statusCode, $reasonPhrase);
    }

    protected function actionResult(string $action, mixed $result, array $context = null): array
    {
        return compact('action', 'result');
    }
}