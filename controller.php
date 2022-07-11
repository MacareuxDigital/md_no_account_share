<?php

namespace Concrete\Package\MdNoAccountShare;

use Concrete\Core\Package\Package;
use Concrete\Core\Session\SessionValidator as CoreSessionValidator;
use Concrete\Core\Session\SessionValidatorInterface;
use Concrete\Core\User\Event\User;
use Macareux\NoAccountShare\SessionValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Controller extends Package
{
    /**
     * The minimum concrete5 version compatible with this package.
     *
     * @var string
     */
    protected $appVersionRequired = '8.5.0';

    /**
     * The handle of this package.
     *
     * @var string
     */
    protected $pkgHandle = 'md_no_account_share';

    /**
     * The version number of this package.
     *
     * @var string
     */
    protected $pkgVersion = '0.1';

    /**
     * @see https://documentation.concretecms.org/developers/packages/adding-custom-code-to-packages
     *
     * @var string[]
     */
    protected $pkgAutoloaderRegistries = [
        'src' => '\Macareux\NoAccountShare',
    ];

    /**
     * Get the translated name of the package.
     *
     * @return string
     */
    public function getPackageName()
    {
        return t('Macareux Prevent Account Sharing');
    }

    /**
     * Get the translated package description.
     *
     * @return string
     */
    public function getPackageDescription()
    {
        return t('Prevent each user has more than one active session simultaneously.');
    }

    public function on_start()
    {
        $app = $this->app;

        $app->bind(SessionValidatorInterface::class, SessionValidator::class);
        $app->singleton(CoreSessionValidator::class, SessionValidator::class);

        /** @var EventDispatcherInterface $director */
        $director = $app->make('director');
        $director->addListener('on_user_login', function ($event) use ($app) {
            /** @var User $event */
            /** @var SessionValidator $sessionValidator */
            $sessionValidator = $app->make(CoreSessionValidator::class);
            if ($sessionValidator->hasActiveSession()) {
                /** @var int $lastLogin Unix Timestamp */
                $lastLogin = $event->getUserObject()->getUserInfoObject()->getLastLogin();
                /** @var Session $session */
                $session = $app->make('session');
                $session->set('uLastLogin', $lastLogin);
            }
        });
    }
}
