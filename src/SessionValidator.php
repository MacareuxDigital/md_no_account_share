<?php

namespace Macareux\NoAccountShare;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\UserInfoRepository;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class SessionValidator extends \Concrete\Core\Session\SessionValidator
{
    /**
     * @inheritDoc
     */
    public function handleSessionValidation(SymfonySession $session)
    {
        $invalidate = parent::handleSessionValidation($session);
        if (!$invalidate && $session->has('uID')) {
            /** @var UserInfoRepository $userInfoRepository */
            $userInfoRepository = Application::getFacadeApplication()->make(UserInfoRepository::class);
            $userInfo = $userInfoRepository->getByID($session->get('uID'));
            if ($userInfo) {
                /** @var int $lastSessionStartedAt Unix Timestamp */
                $lastSessionStartedAt = $userInfo->getLastLogin();
                /** @var int $currentSessionStartedAt Unix Timestamp */
                $currentSessionStartedAt = (int) $session->get('uLastLogin');
                if ($currentSessionStartedAt < $lastSessionStartedAt) {
                    $this->logger->notice('Session Invalidated. The current session is older than recent one.');
                    $invalidate = true;
                }
            }
        }

        if ($invalidate) {
            $session->invalidate();
        }

        return $invalidate;
    }

}