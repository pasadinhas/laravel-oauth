<?php namespace Pasadinhas\LaravelOAuth\Decorators;

use OAuth\Common\Service\ServiceInterface;

interface ProviderDecoratorContract extends ServiceInterface {

    /**
     * Sets the decorator provider.
     *
     * @param ServiceInterface $provider
     *
     * @return mixed
     */
    public function setProvider(ServiceInterface $provider);

}