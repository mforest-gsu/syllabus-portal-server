<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\KernelEvents;

class Kernel extends BaseKernel implements EventSubscriberInterface
{
    use MicroKernelTrait;


    /**
     * @return array<string,string|array{0:string,1:int}|list<array{0:string,1?:int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 2]
        ];
    }


    /**
     * @param ResponseEvent $event
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->getResponse()->getStatusCode() > 499) {
            $event->getResponse()->setStatusCode(200);
        }
    }
}
