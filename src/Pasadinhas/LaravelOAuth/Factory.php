<?php namespace Pasadinhas\LaravelOAuth;

use Illuminate\Contracts\Config\Repository as Config;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Service\ServiceInterface;
use OAuth\Common\Storage\SymfonySession;
use OAuth\ServiceFactory;
use Pasadinhas\LaravelOAuth\Decorators\ProviderDecoratorContract;
use Pasadinhas\LaravelOAuth\Exceptions\InvalidDecoratorException;
use Pasadinhas\LaravelOAuth\Exceptions\ProviderConfigurationDoesNotExistException;
use Symfony\Component\HttpFoundation\Session\Session;

class Factory {


    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @param Config         $config
     * @param ServiceFactory $factory
     */
    public function __construct(Config $config, ServiceFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * @param $name
     *
     * @return ServiceInterface
     */
    public function make($name)
    {
        $this->assertProviderHasConfiguration($name);

        $this->registerProvider($name);

        $credentials = $this->makeCredentials($name);
        $storage = $this->makeStorage();
        $provider = $this->makeProvider($name, $credentials, $storage);

        return $this->decorateProvider($provider, $name);
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws ProviderConfigurationDoesNotExistException
     */
    private function assertProviderHasConfiguration($name)
    {
        if ( ! $this->config->has("oauth.providers.{$name}")) {
            $message = "Provider {$name} has no configuration set.";
            throw new ProviderConfigurationDoesNotExistException($message);
        }
    }

    /**
     * @param $name
     *
     * @return Credentials
     */
    private function makeCredentials($name)
    {
        return new Credentials(
            $this->config->get("oauth.providers.{$name}.consumer_key"),
            $this->config->get("oauth.providers.{$name}.consumer_secret"),
            $this->config->get("oauth.providers.{$name}.callback_url")
        );
    }

    /**
     * @return SymfonySession
     */
    private function makeStorage()
    {
        return new SymfonySession(
            new Session()
        );
    }

    /**
     * @param $name
     */
    private function registerProvider($name)
    {
        if ($this->config->has("oauth.providers.$name.class")) {
            $class = $this->config->get("oauth.providers.$name.class");
            $this->factory->registerService($name, $class);
        }
    }

    /**
     * @param $name
     * @param $credentials
     * @param $storage
     *
     * @return ServiceInterface
     */
    public function makeProvider($name, $credentials, $storage)
    {
        return $this->factory->createService($name, $credentials, $storage);
    }

    /**
     * @param ServiceInterface $provider
     * @param $name
     *
     * @return ServiceInterface
     */
    public function decorateProvider(ServiceInterface $provider, $name)
    {
        if ($this->config->has("oauth.providers.$name.decorators"))
        {
            $decorators = $this->config->get("oauth.providers.$name.decorators");
            foreach ($decorators as $decorator)
            {
                $provider = $this->decorateProviderWith($provider, $decorator);
            }
        }

        return $provider;
    }

    private function decorateProviderWith(ServiceInterface $provider, $decorator)
    {
        $instance = new $decorator;

        if ( ! $instance instanceof ProviderDecoratorContract)
        {
            $message = "The decorator [$decorator] does not implement the ProviderDecoratorContract interface";
            throw new InvalidDecoratorException($message);
        }

        $instance->setProvider($provider);

        return $instance;
    }
} 