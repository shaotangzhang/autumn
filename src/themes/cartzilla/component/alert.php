<?php

use Autumn\System\Templates\Component;

/**
 * @layout
 */
return function (
    bool     $dismissible = false,
    int      $duration = null,
    string   $type = 'primary',
    string   $icon = null,
    iterable $children = [],
    array    $attributes = null,
    array    $context = null,
    mixed    $extClass = null,
    mixed    $iconClass = null,
    mixed    $closeButtonClass = null,
) {
    // Initialize classes array for the alert
    $attributes['class'] = ['alert', 'alert-' . $type, 'alert-dismissible fade show' => $dismissible, $attributes['class'] ?? null, $extClass];
    $attributes['role'] = 'alert';

    // Build icon span if icon is provided
    $iconSpan = $icon ? ['i', 'class' => [$icon, $iconClass ?? $context['icon-class'] ?? null]] : null;

    // Build close button if it is dismissible
    $dismissible = $dismissible ? [
        'button',
        'type' => 'button',
        'class' => ['btn-close', $closeButtonClass ?? $context['close-button-class'] ?? null],
        'data-bs-dismiss' => 'alert',
        'data-duration' => $duration,
        'aria-label' => 'Close'
    ] : null;

    // Use Component::stack() to generate final HTML structure
    return Component::stack('div', $attributes, ...[$iconSpan, ...$children, $dismissible]);
};


//return component('alert', [
//    'dismissible' => true,
//    'type' => 'success',
//    'icon' => 'ci-bell',
//    'iconClass' => 'fs-4 mt-1 mb-2 mb-sm-0',
//    'extClass' => ' d-sm-flex pb-4 pt-sm-4',
//], ['use_layout' => true],
//    [
//        'div',
//        'class' => 'ps-sm-3 pe-sm-4',
//        [
//            'h4',
//            'Well done!',
//            'class' => 'alert-heading mb-2'
//        ],
//        [
//            'p',
//            'Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.',
//            'class' => 'mb-3'
//        ],
//        [
//            'hr',
//            'class' => "text-success opacity-25 my-3"
//        ],
//        [
//            'p',
//            'Whenever you need to, be sure to use margin and padding utilities to keep things nice and tidy.',
//            'class' => 'mb-0'
//        ]
//    ]
//);