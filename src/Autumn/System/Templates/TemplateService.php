<?php

namespace Autumn\System\Templates;

use Autumn\App;
use Autumn\System\ClassFactory\DocComment;
use Autumn\System\Extension;
use Autumn\System\Service;
use Autumn\System\View;

class TemplateService extends Service implements RendererInterface
{
    /**
     * @var array<string|RendererInterface>
     */
    private array $renderers = [];

    /**
     * Creates and configures the default instance of this service.
     *
     * @return static
     */
    protected static function createDefaultInstance(): static
    {
        $instance = parent::createDefaultInstance();

        foreach (glob(__DIR__ . '/Renderers/*Renderer.php') as $file) {
            $instance->addRenderer(__NAMESPACE__ . '\\Renderers\\' . basename($file, '.php'));
        }

        return $instance;
    }

    /**
     * Adds a renderer to the service.
     *
     * @param string|RendererInterface $renderer The renderer to add.
     */
    public function addRenderer(string|RendererInterface $renderer): void
    {
        if (is_subclass_of($renderer, RendererInterface::class)
            && !in_array($renderer, $this->renderers, true)) {
            $this->renderers[] = $renderer;
        }
    }

    public function render(mixed $data, \ArrayAccess|array $args = null, array $context = null): void
    {
        echo $this->output($data, $args, $context);
    }

    /**
     * Outputs the data by processing it through the registered renderers.
     *
     * @param mixed $data The data to be output.
     * @param \ArrayAccess|array|null $args Optional arguments for the renderer.
     * @param array|null $context Optional context for the renderer.
     * @return mixed The processed data or null if output is handled.
     */
    public function output(mixed $data, \ArrayAccess|array $args = null, array $context = null): mixed
    {
        while ($data instanceof \Closure) {
            $data = call($data, $args, $context);
        }

        if (($data === null) || ($data === false)) {
            return null;
        }

        if (is_scalar($data)) {
            return $data;
        }

        if (is_array($data) && array_is_list($data)) {
            foreach ($data as $item) {
                $this->render($item, $args, $context);
            }

            return null;
        }

        foreach ($this->renderers as $index => $converter) {
            if (is_string($converter)) {
                $converter = app($converter, true);

                if (!$converter) {
                    unset($this->renderers[$index]);
                    continue;
                }

                $this->renderers[$index] = $converter;
            }

            $data = $converter->output($data, $args, $context);
        }

        return $data;
    }

    /**
     * Gets the file extension for template files.
     *
     * @return string The file extension for template files.
     */
    public function getTemplateFileExt(): string
    {
        return '.php';
    }

    /**
     * Resolves the full file path of a template by checking different directories.
     *
     * @param string $template The name of the template.
     * @param array|null $context Optional context that might include additional information.
     * @return string|null The resolved file path or null if not found.
     */
    public function getTemplateFile(string $template, array $context = null): ?string
    {
        $fileName = DIRECTORY_SEPARATOR . trim(strtr(strtr($template, '\\', '/'), '/', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR)
            . $this->getTemplateFileExt();

        return realpath($this->getThemeViewPath() . $fileName)
            ?: realpath($this->getApplicationViewPath() . $fileName)
                ?: realpath($this->getExtensionViewPath($context) . $fileName)
                    ?: null;
    }

    /**
     * Gets the current theme name from configuration
     *
     * @return string The name of the current theme
     */
    public function getThemeName(): string
    {
        return env('THEME_NAME') ?: 'default';
    }

    /**
     * Gets the path to the theme view directory.
     *
     * @return string The path to the theme view directory.
     */
    public function getThemeViewPath(): string
    {
        return App::map('src', 'themes', $this->getThemeName());
    }

    /**
     * Gets the path to the application view directory.
     *
     * @return string The path to the application view directory.
     */
    public function getApplicationViewPath(): string
    {
        return App::context()->map('views', 'default');
    }

    /**
     * Gets the path to the extension view directory if an extension is specified in the context.
     *
     * @param array|null $context Context array that might contain the extension class.
     * @return string|null The path to the extension view directory or null if not applicable.
     */
    public function getExtensionViewPath(array $context = null): ?string
    {
        if (is_string($extension = $context[Extension::class] ?? null)) {
            if (is_subclass_of($extension, Extension::class)) {
                return $extension::path('views', $this->getThemeName());
            }
        }

        return null;
    }

    /**
     * Renders the specified view.
     *
     * @param View $view The view to render.
     * @param array|null $context The rendering context.
     */
    public function renderView(View $view, array $context = null): void
    {
        $file = $this->getTemplateFile($view->getName());
        if (!$file) {
            return;
        }

        $callback = $this->compileView($view, $file);
        if (!$callback) {
            return;
        }

        // $context ??= $view->getContext();
        if ($context['use_layout'] ?? $view->can('use_layout')) {
            unset($context['use_layout']);
            $callback = $this->compileLayout($view, $callback, $context) ?: $callback;
        }

        $result = call($callback, $view, $context);
        $this->output($result, null, $context);
    }

    /**
     * Compiles the specified view into a callable.
     *
     * @param View $view The view to compile.
     * @param string $file The template file path.
     * @param array|null $context The compilation context.
     * @return callable|null
     */
    public function compileView(View $view, string $file, array $context = null): ?callable
    {
        ob_start();

        try {
            $result = (function () {
                extract($this->toArray());
                return include func_get_arg(0);
            })->bindTo($view)($file);

            if ($result === false) {    // template is not found
                return null;
            }

            if ($result instanceof \Closure) {
                return $result;
            }

            if ($result === 1) {
                $result = ob_get_contents();
            }

            return fn() => $this->output($result, null, $context ?? $view->getContext());
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Detects and compiles the layout view from the callback.
     *
     * @param View $view The view to detect the layout for.
     * @param callable $callback The callback containing the layout information.
     * @param array|null $context The detection context.
     * @return callable|null
     */
    public function compileLayout(View $view, callable $callback, array $context = null): ?callable
    {
        $defaultLayout = env('THEME_DEFAULT_LAYOUT', '/common/default');

        $checkedLayouts = [];

        foreach (DocComment::paramsOf($callback, 'layout') as $layout) {
            if ($layoutTemplate = $this->extractLayoutNameFromComment($layout) ?: $defaultLayout) {

                if (in_array($layoutTemplate, $checkedLayouts)) {
                    // avoid unnecessary double detection of the layout view
                    continue;
                }
                $checkedLayouts[] = $layoutTemplate;

                if (!str_starts_with($layoutTemplate, '/')) {
                    if (!isset($path)) {
                        if ($pos = strrpos($path = $view->getName(), '/')) {
                            $path = substr($path, 0, $pos + 1);
                        } else {
                            $path = '/';
                        }
                    }

                    $layoutTemplate = $path . $layoutTemplate;
                }

                if ($layoutFile = $this->getTemplateFile($layoutTemplate)) {
                    $layoutView = new View($layoutTemplate, $view->toArray(), $context);
                    $layoutView->setContents($callback);

                    $slots = $context['use_slots'] ?? $view->getContext()['use_slots'] ?? null;
                    unset($context['use_slots']);

                    if (is_array($slots)) {
                        foreach ($slots as $slot => $definitions) {
                            if (is_array($definitions)) {
                                foreach ($definitions as $definition) {
                                    if (is_callable($definition)) {
                                        $layoutView->defineSlot($slot, $definition);
                                    }
                                }
                            }
                        }
                    }

                    return $this->compileView($layoutView, $layoutFile, $context);
                }
            }
        }

        return null;
    }

    /**
     * Extracts the layout name from the doc comment.
     *
     * @param string $comment The doc comment containing the layout name.
     * @return string|null
     */
    public function extractLayoutNameFromComment(string $comment): ?string
    {
        return preg_match('#^\s*((?:\.{0,2}/)*(?:[\w-]+/)*[\w-]+)#', $comment, $matches)
            ? $matches[1]
            : null;
    }
}
