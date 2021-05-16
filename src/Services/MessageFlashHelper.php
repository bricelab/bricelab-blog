<?php


namespace App\Services;


use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MessageFlashHelper
{
    private FlashBagInterface $flashBag;

    public function __construct(SessionInterface $session)
    {
        $this->flashBag = $session->getFlashBag();
    }

    public function addFlash(string $type, string $message): void
    {
        $this->flashBag->add($type, $message);
    }
}
