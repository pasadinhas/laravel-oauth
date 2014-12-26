<?php namespace Pasadinhas\LaravelOAuth;

use Illuminate\Contracts\Config\Repository as Config;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\SymfonySession;
use OAuth\ServiceFactory;
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
     * @return \OAuth\Common\Service\ServiceInterface
     */
    public function make($name)
    {
        $this->assertProviderHasConfiguration($name);

        if ($this->config->has("oauth::providers.$name.class"))
        {
            $class = $this->config->get("oauth::providers.$name.class");
            $this->factory->registerService($name, $class);
        }

        $credentials = $this->makeCredentials($name);
        $storage = $this->makeStorage();

        return $this->factory->createService($name, $credentials, $storage);
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws Exceptions\ProviderConfigurationDoesNotExistException
     */
    private function assertProviderHasConfiguration($name)
    {
        if ( ! $this->config->has("oauth::providers.{$name}")) {
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
            $this->config->get("oauth::providers.{$name}.consumer_key"),
            $this->config->get("oauth::providers.{$name}.consumer_secret"),
            $this->config->get("oauth::providers.{$name}.callback_url")
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

} 