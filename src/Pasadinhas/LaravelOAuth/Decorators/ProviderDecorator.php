<?php namespace Pasadinhas\LaravelOAuth\Decorators;

use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Service\ServiceInterface;

class ProviderDecorator implements ProviderDecoratorContract {

    /**
     * The provider being decorated.
     *
     * @var ServiceInterface $provider
     */
    private $provider;

    /**
     * Constructs the decorator.
     *
     * @param ServiceInterface $provider
     */
    function __construct(ServiceInterface $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * Sets the decorator provider.
     *
     * @param ServiceInterface $provider
     *
     * @return mixed
     */
    public function setProvider(ServiceInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Sends an authenticated API request to the path provided.
     * If the path provided is not an absolute URI, the base API Uri (service-specific) will be used.
     *
     * @param string|UriInterface $path
     * @param string              $method       HTTP method
     * @param array               $body         Request body if applicable (an associative array will
     *                                          automatically be converted into a urlencoded body)
     * @param array               $extraHeaders Extra headers if applicable. These will override service-specific
     *                                          any defaults.
     *
     * @return string
     */
    public function request($path, $method = 'GET', $body = null, array $extraHeaders = array())
    {
        return $this->provider->request($path, $method, $body, $extraHeaders);
    }

    /**
     * Returns the url to redirect to for authorization purposes.
     *
     * @param array $additionalParameters
     *
     * @return UriInterface
     */
    public function getAuthorizationUri(array $additionalParameters = array())
    {
        return $this->provider->getAuthorizationUri($additionalParameters);
    }

    /**
     * Returns the authorization API endpoint.
     *
     * @return UriInterface
     */
    public function getAuthorizationEndpoint()
    {
        return $this->provider->getAccessTokenEndpoint();
    }

    /**
     * Returns the access token API endpoint.
     *
     * @return UriInterface
     */
    public function getAccessTokenEndpoint()
    {
        return $this->provider->getAccessTokenEndpoint();
    }

    /**
     * Handles dynamic method calls to providers.
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $instance = $this->provider;

        switch (count($args))
        {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
}