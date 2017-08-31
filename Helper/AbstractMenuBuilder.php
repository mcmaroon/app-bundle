<?php
namespace App\AppBundle\Helper;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Finder\Finder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class AbstractMenuBuilder
{

    protected $securityContext;
    protected $isLoggedIn;
    protected $factory;
    protected $container;

    public function __construct(AuthorizationCheckerInterface $securityContext, $container, FactoryInterface $factory)
    {
        $this->securityContext = $securityContext;
        $this->isLoggedIn = $this->securityContext->isGranted('IS_AUTHENTICATED_FULLY');
        $this->factory = $factory;
        $this->container = $container;
    }

    // ~

    protected function hasRouteExists($routeName)
    {
        $router = $this->container->get('router');
        return $router->getRouteCollection()->get($routeName) ? true : false;
    }

    // ~

    protected function explodeAttributes(array &$attributes)
    {
        foreach ($attributes as $key => &$value) {
            $value = \explode(' ', $value);
        }
    }

    // ~

    protected function implodeAttributes(array &$attributes)
    {
        foreach ($attributes as $key => &$value) {
            $value = \implode(' ', $value);
        }
    }

    // ~

    protected function mergeAttributes(array $attributes = [])
    {
        $defaultAttr = [
            'class' => 'nav-item',
            'icon' => 'fa'
        ];
        $this->explodeAttributes($defaultAttr);
        $this->explodeAttributes($attributes);
        $attributes = \array_map('array_unique', \array_merge_recursive($defaultAttr, $attributes));
        $this->implodeAttributes($attributes);
        return $attributes;
    }

    // ~

    protected function mergeLinkAttributes(array $attributes = [])
    {
        $defaultAttr = [
            'class' => 'nav-link'
        ];
        $this->explodeAttributes($defaultAttr);
        $this->explodeAttributes($attributes);
        $attributes = \array_map('array_unique', \array_merge_recursive($defaultAttr, $attributes));
        $this->implodeAttributes($attributes);
        return $attributes;
    }

    // ~

    protected function addChild(ItemInterface &$menu, array $childrens, array $attributes = [], array $linkAttributes = [])
    {
        $routeName = $childrens[0];
        if ($this->hasRouteExists($routeName)) {
            $el = $menu->addChild('menu.' . $routeName, array('route' => $routeName));
            $el->setAttributes($this->mergeAttributes($attributes));
            $el->setLinkAttributes($this->mergeLinkAttributes($linkAttributes));
            if (\count($childrens)) {
                $el->setChildrenAttributes(['class' => 'dropdown-menu']);
                $this->addChildrens($menu, $routeName, $childrens, $attributes, $linkAttributes);
            }
        }
    }

    // ~

    protected function addChildrens(\Knp\Menu\MenuItem $menu, $parentRouting = '', array $childrens = array(), array $attributes = [], array $linkAttributes = [])
    {
        if ($this->hasRouteExists($parentRouting)) {
            $hasLast = false;
            $totalRouteCount = 0;
            foreach ($childrens as $key => $route) {
                $routeCount = 0;
                foreach (['index', 'tree', 'new'] as $routeSuffix) {
                    $routeName = $route . '_' . $routeSuffix;
                    $fullLabel = 'menu.' . $route . '.' . $routeSuffix;
                    if ($this->hasRouteExists($routeName)) {
                        $routeCount++;
                        $menu['menu.' . $parentRouting]->addChild($fullLabel, [
                            'route' => $routeName
                        ])->setAttribute('class', 'nav-item')->setLinkAttribute('class', 'nav-link');
                    }
                }
                $totalRouteCount = $totalRouteCount + $routeCount;
                if (($key + 1) === \count($childrens)) {
                    $hasLast = true;
                }
                if ($routeCount > 1 && !$hasLast) {
                    $menu['menu.' . $parentRouting]->addChild($fullLabel . '.divider')->setAttributes(['class' => 'dropdown-divider']);
                }
            }
            if ($totalRouteCount > 1) {
                $el = $menu['menu.' . $parentRouting];
                $el->setAttributes($this->mergeAttributes(\array_merge(['class' => 'dropdown'], $attributes)));
                $el->setLinkAttributes($this->mergeLinkAttributes(\array_merge(['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'], $linkAttributes)));
                $el->setChildrenAttributes(['class' => 'dropdown-menu']);
            }
        }
    }

    // ~

    public function createAdminMenu(RequestStack $requestStack)
    {
        $menu = $this->factory->createItem('adminmenu');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        if ($this->isLoggedIn === TRUE) {
            $this->createAdminMenuBody($menu);
        }

        return $menu;
    }

    // ~

    protected function getBundleName()
    {
        return '';
    }

    // ~

    protected function createAdminMenuBody(ItemInterface &$menu)
    {
        if (!\strlen($this->getBundleName())) {
            throw new \Exception('Invalid Bundle Name in AbstractMenuBuilder');
        }

        $routes = [];
        $path = $this->container->get('kernel')->locateResource($this->getBundleName()) . DIRECTORY_SEPARATOR . 'Entity';
        $finder = new Finder();
        $files = $finder->files()->in($path)->name('*.php');
        foreach ($files as $file) {
            $filename = \strtolower(\str_replace(['.php', 'Translation'], '', $file->getFileName()));
            $routes[$filename] = $filename;
        }
        if (\count($routes)) {
            foreach ($routes as $route) {
                $this->addChild($menu, [$route]);
            }
        }
    }
}
