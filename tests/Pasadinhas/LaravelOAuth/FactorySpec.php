<?php

namespace tests\Pasadinhas\LaravelOAuth;

use Illuminate\Contracts\Config\Repository;
use OAuth\ServiceFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FactorySpec extends ObjectBehavior
{
    const INTERFACE_CREDENTIALS = 'OAuth\Common\Consumer\CredentialsInterface';
    const INTERFACE_STORAGE = 'OAuth\Common\Storage\TokenStorageInterface';

    function let(Repository $config, ServiceFactory $serviceFactory)
    {
        $this->beConstructedWith($config, $serviceFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pasadinhas\LaravelOAuth\Factory');
    }

    function it_throws_a_custom_exception_if_there_is_no_configuration_for_the_given_provider()
    {
        $exception = 'Pasadinhas\LaravelOAuth\Exceptions\ProviderConfigurationDoesNotExistException';
        $this->shouldThrow($exception)->duringMake('This provider does not exist.');
    }

    function it_creates_a_provider_if_everything_is_set(Repository $config, ServiceFactory $serviceFactory)
    {
        $serviceFactory->createService(
            'Foo',
            Argument::type(self::INTERFACE_CREDENTIALS),
            Argument::type(self::INTERFACE_STORAGE)
        )->shouldBeCalledTimes(1);

        $config->has('oauth::providers.Foo')->willReturn(true);
        $config->has('oauth::providers.Foo.class')->willReturn(false);
        $config->get('oauth::providers.Foo.consumer_key')->willReturn('foo');
        $config->get('oauth::providers.Foo.consumer_secret')->willReturn('bar');
        $config->get('oauth::providers.Foo.callback_url')->willReturn('http://baz.com/login');

        $this->make('Foo');
    }

    function it_registers_the_provider_if_a_class_is_set_in_the_configuration(Repository $config, ServiceFactory $serviceFactory)
    {
        $serviceFactory->registerService('Bar', 'Namespace\Of\Bar')->shouldBeCalledTimes(1);
        $serviceFactory->createService('Bar', Argument::cetera())->shouldBeCalled();

        $config->has('oauth::providers.Bar')->willReturn(true);
        $config->has('oauth::providers.Bar.class')->willReturn(true);
        $config->get('oauth::providers.Bar.class')->willReturn('Namespace\Of\Bar');
        $config->get('oauth::providers.Bar.consumer_key')->willReturn('foo');
        $config->get('oauth::providers.Bar.consumer_secret')->willReturn('bar');
        $config->get('oauth::providers.Bar.callback_url')->willReturn('http://baz.com/login');

        $this->make('Bar');
    }
}
