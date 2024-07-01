<?php
use Autumn\System\Templates\Component;

$this->defineLayoutImport('/theme/cartzilla/vendor/swiper/swiper-bundle.min.css');
$this->defineLayoutImport('/theme/cartzilla/vendor/swiper/swiper-bundle.min.js');


$carousel = function ($paginationType = 'bullets') {
    $swiperOptions = [
        'spaceBetween' => 20,
        'loop' => true,
        'navigation' => [
            'prevEl' => '.btn-prev',
            'nextEl' => '.btn-next'
        ]
    ];

    switch ($paginationType) {
        case 'progressbar':
            $swiperOptions['pagination'] = [
                'el' => '.swiper-pagination',
                'type' => 'progressbar'
            ];
            break;
        case 'fraction':
            $swiperOptions['pagination'] = [
                'el' => '.swiper-pagination',
                'type' => 'fraction'
            ];
            break;
        case 'scrollbar':
            $swiperOptions['direction'] = 'vertical';
            $swiperOptions['mousewheel'] = true;
            $swiperOptions['scrollbar'] = [
                'el' => '.swiper-scrollbar'
            ];
            unset($swiperOptions['loop']); // Remove loop for vertical scrollbars
            break;
        case 'bullets':
        default:
            $swiperOptions['pagination'] = [
                'el' => '.swiper-pagination',
                'clickable' => true
            ];
            break;
    }

    return function () use ($paginationType, $swiperOptions) { ?>
<div class="swiper hover-effect-opacity" data-swiper='{$swiperOptionsJson}'>
  <div class="swiper-wrapper">
    <div class="swiper-slide">
      <div class="ratio ratio-16x9 bg-body-tertiary">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center display-4">1</div>
      </div>
    </div>
    <div class="swiper-slide">
      <div class="ratio ratio-16x9 bg-body-tertiary">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center display-4">2</div>
      </div>
    </div>
    <div class="swiper-slide">
      <div class="ratio ratio-16x9 bg-body-tertiary">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center display-4">3</div>
      </div>
    </div>
  </div>

  <div class="position-absolute top-50 start-0 z-2 translate-middle-y ms-3 hover-effect-target opacity-0">
    <button type="button" class="btn btn-prev btn-icon btn-outline-secondary rounded-circle animate-slide-start" aria-label="Prev">
      <i class="ci-chevron-left fs-lg animate-target"></i>
    </button>
  </div>

  <div class="position-absolute top-50 end-0 z-2 translate-middle-y me-3 hover-effect-target opacity-0">
    <button type="button" class="btn btn-next btn-icon btn-outline-secondary rounded-circle animate-slide-end" aria-label="Next">
      <i class="ci-chevron-right fs-lg animate-target"></i>
    </button>
  </div>

<?php

        // Add pagination or scrollbar
        echo match ($paginationType) {
            'fraction' => '<div class="swiper-pagination text-body-secondary fs-6 opacity-50 fw-semibold mb-2"></div>',
            'scrollbar' => '<div class="swiper-scrollbar"></div>',
            default => '<div class="swiper-pagination"></div>',
        };

        echo '</div>'; // Close swiper container
    };
};

// 使用示例：
$carouselBullets = $carousel('bullets');
$carouselProgress = $carousel('progressbar');
$carouselFraction = $carousel('fraction');
$carouselScrollbar = $carousel('scrollbar');

// 输出 Swiper 轮播组件
// $swiperComponent = $carouselBullets();
// $swiperComponent = $carouselProgress();
// $swiperComponent = $carouselFraction();
// $swiperComponent = $carouselScrollbar();

// 可根据需要选择生成不同类型的轮播组件
