<?php

use Autumn\System\Templates\Component;

return function (
    string   $toggler = null,   // #navbarNav
    bool     $togglerOffcanvas = null,
    string   $sticky = null,    // top
    array    $attributes = null,
    array    $context = null,
    iterable $children = [],
    mixed    $extClass = null,
    mixed    $stickyElement = null
) {
    // Combine class names and ensure they are unique
    $classes = array_filter([
        'navbar navbar-expand',
        $sticky ? "navbar-sticky sticky-$sticky" : null,
        $attributes['class'] ?? null,
        'd-block bg-body z-fixed py-1 py-lg-0 py-xl-1 px-0',
        $extClass
    ]);

    // Prepare attributes with JSON encoded sticky element if provided
    $attributes['class'] = implode(' ', $classes);
    $attributes['data-sticky-element'] = $stickyElement ? json_encode($stickyElement) : null;

    // Prepare the toggler component
    $togglerComponent = $toggler ? '<!-- Offcanvas menu toggler (Hamburger) -->
        <button type="button" class="navbar-toggler d-block flex-shrink-0 me-3 me-sm-4"
                data-bs-toggle="' . ($togglerOffcanvas ? 'offcanvas' : 'collapse') . '"
                data-bs-target="' . htmlspecialchars($toggler) . '" aria-controls="' . htmlspecialchars($toggler) . '" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>' : '';

    // Prepare the logo component
    $siteTitle = htmlspecialchars(t('site.title', site('title')));
    $logoComponent = '<!-- Navbar brand (Logo) -->
        <a class="navbar-brand fs-2 p-0 pe-lg-2 pe-xxl-0 me-0 me-sm-3 me-md-4 me-xxl-5"
           href="' . htmlspecialchars(site('home', '/')) . '">' . $siteTitle . '</a>';

    // Return the combined component stack
    return Component::stack($context['tagName'] ?? 'header', $attributes,
        $togglerComponent, $logoComponent, ...$children
    );
};

