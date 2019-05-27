<?php

namespace App\AppBundle\Helper;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\FileLocator;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class AbstractMenuBuilder
{

    protected $securityContext;
    protected $isLoggedIn = false;
    protected $factory;
    protected $container;

    public function __construct(AuthorizationCheckerInterface $securityContext, $container, FactoryInterface $factory)
    {
        $this->securityContext = $securityContext;

        try {
            $this->isLoggedIn = $this->securityContext->isGranted('IS_AUTHENTICATED_FULLY');
        } catch (\Exception $exc) {
//            $log = $container->get('app.log');
//            $log->error('AbstractMenuBuilder:securityContext', [
//                'code' => $exc->getCode(),
//                'message' => $exc->getMessage()
//            ]);
        }
        $this->isLoggedIn = true;

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
        $menuName = $menu->getName();
        $routeName = $childrens[0];
        if ($this->hasRouteExists($routeName)) {
            $el = $menu->addChild($menuName . '.' . $routeName . '.default', array('route' => $routeName));
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
        $menuName = $menu->getName();
        if ($this->hasRouteExists($parentRouting)) {
            $hasLast = false;
            $totalRouteCount = 0;
            foreach ($childrens as $key => $route) {
                $routeCount = 0;
                foreach (['index' => ['icon' => 'fa-list'], 'tree' => ['icon' => 'fa-arrows'], 'new' => ['icon' => 'fa-pencil']] as $routeSuffix => $typeAttributes) {
                    $routeName = $route . '_' . $routeSuffix;
                    $fullLabel = $menuName . '.' . $route . '.' . $routeSuffix;
                    if ($this->hasRouteExists($routeName)) {
                        $routeCount++;

                        $menu[$menuName . '.' . $parentRouting . '.default']->addChild($fullLabel, [
                            'route' => $routeName
                        ])->setAttributes($this->mergeAttributes(\array_merge($typeAttributes, ['icon-direction' => 'left'])))->setLinkAttribute('class', 'nav-link');
                    }
                }
                $totalRouteCount = $totalRouteCount + $routeCount;
                if (($key + 1) === \count($childrens)) {
                    $hasLast = true;
                }
                if ($routeCount > 1 && !$hasLast) {
                    $menu[$menuName . '.' . $parentRouting . '.default']->addChild($fullLabel . '.divider')->setAttributes(['class' => 'dropdown-divider']);
                }
            }
            if ($totalRouteCount > 1) {
                $el = $menu[$menuName . '.' . $parentRouting . '.default'];
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

    protected function createAdminMenuBody(ItemInterface &$menu)
    {
        $routes = [];
        $router = $this->container->get('router');
        foreach ($router->getRouteCollection()->all() as $key => $item) {
            if (strpos($key, '_index')) {
                $itemName = str_replace('_index', '', $key);
                $routes[$itemName] = $itemName;
            }
        }

        ksort($routes);

        if (\count($routes)) {
            foreach ($routes as $route) {
                $this->addChild($menu, [$route]);
            }
        }
    }
}
