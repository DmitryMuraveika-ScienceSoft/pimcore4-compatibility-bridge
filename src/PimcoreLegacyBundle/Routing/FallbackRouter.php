<?php

namespace PimcoreLegacyBundle\Routing;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class FallbackRouter implements RouterInterface
{
    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var UrlMatcherInterface
     */
    protected $matcher;

    /**
     * @var RouteCollection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $routeDefaults = [
        '_controller' => 'PimcoreLegacyBundle:Fallback:fallback'
    ];

    /**
     * @param RequestContext $context
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getRouteDefaults()
    {
        return $this->routeDefaults;
    }

    /**
     * @param array $routeDefaults
     */
    public function setRouteDefaults(array $routeDefaults)
    {
        $this->routeDefaults = $routeDefaults;
    }

    /**
     * @return UrlMatcherInterface
     */
    public function getMatcher()
    {
        if (null === $this->matcher) {
            $this->matcher = new UrlMatcher($this->getRouteCollection(), $this->context);
        }

        return $this->matcher;
    }

    /**
     * @return Route
     */
    protected function buildFallbackRoute()
    {
        $route = new Route('/{path}');
        $route->setDefaults($this->getRouteDefaults());
        $route->setRequirement('path', '.*');

        return $route;
    }

    /**
     * @inheritDoc
     */
    public function getRouteCollection()
    {
        if (null === $this->collection) {
            $this->collection = new RouteCollection();
            $this->collection->add(
                'pimcore_legacy_fallback',
                $this->buildFallbackRoute()
            );
        }

        return $this->collection;
    }

    /**
     * @inheritDoc
     */
    public function match($pathinfo)
    {
        return $this->getMatcher()->match($pathinfo);
    }

    /**
     * @inheritDoc
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        throw new \RuntimeException('Legacy route generation is not supported');
    }
}
