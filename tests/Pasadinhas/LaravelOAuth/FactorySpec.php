<?php

namespace tests\Pasadinhas\LaravelOAuth;

use Illuminate\Contracts\Config\Repository;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Service\ServiceInterface;
use OAuth\ServiceFactory;
use Pasadinhas\LaravelOAuth\Decorators\ProviderDecorator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FactorySpec extends ObjectBehavior
{
    const INTERFACE_CREDENTIALS = 'OAuth\Common\Consumer\CredentialsInterface';
    const INTERFACE_STORAGE = 'OAuth\Common\Storage\TokenStorageInterface';
    const INTERFACE_SERVICE = 'OAuth\Common\Service\ServiceInterface';
    const INTERFACE_DECORATOR = 'Pasadinhas\LaravelOAuth\Decorators\ProviderDecoratorContract';

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
        )->shouldBeCalledTimes(1)->willReturn(new Foo);

        $config->has('oauth::providers.Foo')->willReturn(true);
        $config->has('oauth::providers.Foo.class')->willReturn(false);
        $config->has('oauth::providers.Foo.decorators')->willReturn(false);
        $config->get('oauth::providers.Foo.consumer_key')->willReturn('foo');
        $config->get('oauth::providers.Foo.consumer_secret')->willReturn('bar');
        $config->get('oauth::providers.Foo.callback_url')->willReturn('http://baz.com/login');

        $this->make('Foo');
    }

    function it_registers_the_provider_if_a_class_is_set_in_the_configuration(Repository $config, ServiceFactory $serviceFactory)
    {
        $serviceFactory->registerService('Bar', 'Namespace\Of\Bar')->shouldBeCalledTimes(1);
        $serviceFactory->createService('Bar', Argument::cetera())->shouldBeCalled()->willReturn(new Foo);

        $config->has('oauth::providers.Bar')->willReturn(true);
        $config->has('oauth::providers.Bar.decorators')->willReturn(false);
        $config->has('oauth::providers.Bar.class')->willReturn(true);
        $config->get('oauth::providers.Bar.class')->willReturn('Namespace\Of\Bar');
        $config->get('oauth::providers.Bar.consumer_key')->willReturn('foo');
        $config->get('oauth::providers.Bar.consumer_secret')->willReturn('bar');
        $config->get('oauth::providers.Bar.callback_url')->willReturn('http://baz.com/login');

        $this->make('Bar');
    }

    function it_decorates_the_provider_if_decorators_are_provided_in_config(Repository $config, ServiceFactory $serviceFactory)
    {
        $serviceFactory->createService(
            'Foo',
            Argument::type(self::INTERFACE_CREDENTIALS),
            Argument::type(self::INTERFACE_STORAGE)
        )->shouldBeCalledTimes(1)->willReturn(new Foo);

        $config->has('oauth::providers.Foo')->willReturn(true);
        $config->has('oauth::providers.Foo.class')->willReturn(false);
        $config->has('oauth::providers.Foo.decorators')->willReturn(true);
        $config->get('oauth::providers.Foo.decorators')->willReturn([
            'tests\Pasadinhas\LaravelOAuth\Decorator1',
        ]);
        $config->get('oauth::providers.Foo.consumer_key')->willReturn('foo');
        $config->get('oauth::providers.Foo.consumer_secret')->willReturn('bar');
        $config->get('oauth::providers.Foo.callback_url')->willReturn('http://baz.com/login');

        $foo = $this->make('Foo');

        $foo->shouldHaveType(self::INTERFACE_SERVICE);
        $foo->shouldHaveType(self::INTERFACE_DECORATOR);
    }

    function it_allows_for_dynamic_calls_to_providers_methods_with_the_given_decorator(Repository $config, ServiceFactory $serviceFactory)
    {
        $serviceFactory->createService(
            'Foo',
            Argument::type(self::INTERFACE_CREDENTIALS),
            Argument::type(self::INTERFACE_STORAGE)
        )->shouldBeCalledTimes(1)->willReturn(new Foo);

        $config->has('oauth::providers.Foo')->willReturn(true);
        $config->has('oauth::providers.Foo.class')->willReturn(false);
        $config->has('oauth::providers.Foo.decorators')->willReturn(true);
        $config->get('oauth::providers.Foo.decorators')->willReturn([
            'tests\Pasadinhas\LaravelOAuth\Decorator1',
            'tests\Pasadinhas\LaravelOAuth\Decorator2',
        ]);
        $config->get('oauth::providers.Foo.consumer_key')->willReturn('foo');
        $config->get('oauth::providers.Foo.consumer_secret')->willReturn('bar');
        $config->get('oauth::providers.Foo.callback_url')->willReturn('http://baz.com/login');

        $foo = $this->make('Foo');

        $foo->testDynamicMethods1()->shouldReturn('1');
        $foo->testDynamicMethods2()->shouldReturn('2');
    }
}

class Foo implements ServiceInterface {
    public function request($path, $method = 'GET', $body = null, array $extraHeaders = array()) {}
    public function getAuthorizationUri(array $additionalParameters = array()) {}
    public function getAuthorizationEndpoint() {}
    public function getAccessTokenEndpoint() {}
}

class Decorator1 extends ProviderDecorator {
    public function testDynamicMethods1() {
        return '1';
    }
}

class Decorator2 extends ProviderDecorator {
    public function testDynamicMethods2() {
        return '2';
    }
}
