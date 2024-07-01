<?php

use Autumn\System\Templates\Component;

/**
 * @layout
 */
return function (
    iterable $items = [],
    int      $selectedIndex = null,
    array    $attributes = null,
    array    $context = null,
    mixed    $extClass = null,
    mixed    $itemClass = null,
    mixed    $itemHeaderClass = null,
    mixed    $itemTitleClass = null,
    mixed    $itemBodyClass = null,
    mixed    $itemContentClass = null,
    mixed    $itemButtonClass = null,
    mixed    $itemIconClass = null,
    bool     $altIcon = null,
) {
    // Generate unique ID for the accordion if not provided
    $parentId = $attributes['id'] ??= uniqid('accordion-');

    // Initialize classes array for the accordion
    $attributes['class'] = ['accordion', 'accordion-alt-icon' => $altIcon ?? $context['alt-icon'] ?? null, $attributes['class'] ?? null, $extClass];

    // Initialize children array to store each accordion item
    $children = [];

    // Loop through each item to create accordion items
    foreach ($items as $index => $item) {

        if (!is_array($item) && !($item instanceof ArrayAccess)) {
            continue;
        }

        $selected = $item['selected'] ?? ($index === $selectedIndex);

        // Generate unique IDs for each accordion item and content
        $itemId = uniqid('accordion-item-');
        $contentId = $itemId . '-content';

        $icon = $item['icon'] ?? null;
        $title = $item['title'] ?? null;
        $content = $item['content'] ?? null;

        // Build icon span if icon is provided
        $iconSpan = $icon ? ['i', 'class' => [$icon, $itemIconClass ?? $context['icon-class'] ?? null, 'fs-lg pe-1 me-2']] : null;

        // Build header structure
        $header = [
            'h3',
            'class' => ['accordion-header', $itemHeaderClass ?? $context['item-header-class'] ?? null],
            'id' => $itemId,
            [
                'button',
                'type' => 'button',
                'class' => ['accordion-button animate-underline', $itemButtonClass ?? $context['item-button-class'] ?? null, 'collapsed' => !$selected],
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#' . $contentId,
                'aria-controls' => $contentId,
                $iconSpan,
                [
                    'span',
                    'class' => ['animate-target', $itemTitleClass ?? $context['item-title-class'] ?? null, 'me-2'],
                    $title
                ]
            ]
        ];

        // Build body structure
        $bodyStructure = [
            'div',
            'class' => ['accordion-collapse collapse', 'show' => $selected, $itemBodyClass ?? $context['item-body-class'] ?? null],
            'id' => $contentId,
            'data-bs-parent' => '#' . $parentId,
            [
                'div',
                'class' => ['accordion-body', $itemContentClass ?? $context['item-content-class'] ?? null],
                $content
            ]
        ];

        // Combine header and body into accordion item structure
        $accordionItem = [
            'div',
            'class' => ['accordion-item', $itemClass ?? $context['item-class'] ?? null],
            $header,
            $bodyStructure
        ];

        // Add accordion item to children array
        $children[] = $accordionItem;
    }

    // Use Component::stack() to generate final HTML structure
    return Component::stack('div', $attributes, ...$children);
};

//$items = [
//    [
//        'title' => 'Accordion Item #1',
//        'icon' => 'ci-bell fs-lg pe-1 me-2',
//        'content' => 'This is the first item\'s accordion body. It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element.',
//    ],
//    [
//        'title' => 'Accordion Item #2',
//        'icon' => 'ci-edit fs-lg pe-1 me-2',
//        'content' => 'This is the second item\'s accordion body. It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element.',
//    ],
//    [
//        'title' => 'Accordion Item #3',
//        'icon' => 'ci-clock fs-lg pe-1 me-2',
//        'content' => 'This is the third item\'s accordion body. It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element.'
//    ]
//];
//
//return $this->view('/component/accordion', [
//    'items' => $items,
//    'altIcon' => true,
//    'selectedIndex' => 5,
//    'itemButtonClass' => 'fs-5',
//]);