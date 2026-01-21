<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Message\LogActivityMessage;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Subscriber dla eventów JWT - loguje aktywność przez kolejkę
 */
class JwtEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private RequestStack $requestStack,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
            Events::JWT_CREATED => 'onJwtCreated',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        $request = $this->requestStack->getCurrentRequest();
        
        $this->messageBus->dispatch(new LogActivityMessage(
            userId: method_exists($user, 'getId') ? $user->getId() : null,
            action: 'login',
            details: ['email' => $user->getUserIdentifier()],
            ipAddress: $request?->getClientIp(),
            userAgent: $request?->headers->get('User-Agent'),
        ));
    }

    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        $payload = $event->getData();
        
        if (method_exists($user, 'getFirstName')) {
            $payload['firstName'] = $user->getFirstName();
        }
        
        $event->setData($payload);
    }
}
