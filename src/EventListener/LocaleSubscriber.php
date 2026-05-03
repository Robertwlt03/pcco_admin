<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private const string COOKIE_NAME = '_locale';
    private const string COOKIE_DURATION = '+1 year';
    private const int EVENT_PRIORITY = 20;

    private const array SUPPORTED_LOCALES = ['de', 'en'];
    private const array INTERCEPT_PATHS = ['/login', '/admin'];

    public function __construct(
        private readonly string $defaultLocale = 'de'
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', self::EVENT_PRIORITY]],
            KernelEvents::RESPONSE => [['onKernelResponse', self::EVENT_PRIORITY]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (in_array($path, self::INTERCEPT_PATHS)) {
            $locale = $request->cookies->get(self::COOKIE_NAME, $this->defaultLocale);
            $event->setResponse(new RedirectResponse('/' . $locale . $path));
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $pathParts = explode('/', trim($request->getPathInfo(), '/'));
        $locale = $pathParts[0] ?? null;

        if (in_array($locale, self::SUPPORTED_LOCALES)) {
            $event->getResponse()->headers->setCookie(
                Cookie::create(self::COOKIE_NAME)
                    ->withValue($locale)
                    ->withExpires(new \DateTime(self::COOKIE_DURATION))
                    ->withPath('/')
                    ->withHttpOnly(true)
                    ->withSameSite(Cookie::SAMESITE_LAX)
            );
        }
    }
}
