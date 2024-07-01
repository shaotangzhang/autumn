<?php

use Autumn\System\Templates\Component;

/**
 * @layout
 */
return function (
    iterable $items = [],
    int      $activeIndex = null,
    string   $homeText = 'Home',
    string   $homeAriaLabel = null,
    bool     $homeIconWithText = false,
    string   $homeIconClass = 'ci-home fs-base',
    array    $attributes = null,
    array    $context = null,
    mixed    $extClass = null
) {
    $attributes['class'] = ['breadcrumb', $attributes['class'] ?? null, $extClass];

    $children = [];

    foreach ($items as $index => $item) {
        $isActive = $index === $activeIndex;
        $itemLink = $item['href'] ?? $item['url'] ?? $item['link'] ?? null;
        $itemClass = ['breadcrumb-item', 'active' => !isset($itemLink) ?? $isActive];

        if ($index === 0 && ($homeIconClass || $homeIconWithText)) {
            $homeLink = [
                'a',
                'href' => $itemLink,
                'class' => 'd-flex align-items-center',
                'aria-label' => $homeAriaLabel ?? $homeText,
                [
                    'i',
                    'class' => $homeIconClass,
                    'aria-hidden' => 'true'
                ]
            ];

            if ($homeIconWithText) {
                $homeLink[] = ['span', 'class' => 'ms-2', $homeText];
            }

            $children[] = [
                'li',
                'class' => $itemClass,
                $homeLink
            ];
        } else {
            $children[] = [
                'li',
                'class' => $itemClass,
                $isActive ? $item['label'] : [
                    'a',
                    'href' => $itemLink,
                    $item['label'] ?? $item['text'] ?? $item['title'] ?? null,
                ],
                'aria-current' => $isActive ? 'page' : null
            ];
        }
    }

    //return Component::stack('nav', $attributes, Component::stack('ol', ['class' => 'breadcrumb'], ...$children));
    return Component::stack('nav', ['aria-label' => 'breadcrumb'], [
        'ol', ...$attributes, ...$children
    ]);

};

//         return component('breadcrumb', [
//            'items' => [
//                ['label' => 'Home', 'url' => '/'],
//                ['label' => 'Products list', 'url' => 'https://www.google.com'],
//                ['label' => 'Single product']
//            ],
//            'homeIconWithText' => true
//        ], ['use_layout' => true]);