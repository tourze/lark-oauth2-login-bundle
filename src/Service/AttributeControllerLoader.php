<?php

namespace Tourze\LarkOAuth2LoginBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\LarkOAuth2LoginBundle\Controller\LarkOAuth2CallbackController;
use Tourze\LarkOAuth2LoginBundle\Controller\LarkOAuth2LoginController;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->addCollection($this->controllerLoader->load(LarkOAuth2LoginController::class));
        $collection->addCollection($this->controllerLoader->load(LarkOAuth2CallbackController::class));

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }
}
