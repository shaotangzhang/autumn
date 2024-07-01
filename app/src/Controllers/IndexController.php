<?php

namespace App\Controllers;

class IndexController extends AbstractController
{
    protected array $languageDomains = ['home'];

    protected string $viewPath = '/home/';

    public function index(): mixed
    {
        $page = $this->getPageService()->getHomePage();
        $args = $page?->toArray() ?? [];

        $args['banners'] = $this->getPageService()?->getBanners('home') ?? [];
        $args['products'] = $this->getProductService()?->getFeatured() ?? [];
        $args['categories'] = $this->getProductService()?->getCategories() ?? [];

        return $this->view('index', $args);
    }
}