<?php
////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2019. DW Web-Engineering
// https://www.teamspeak-interface.de
// Developer: Daniel W.
//
// License Informations: This program may only be used in conjunction with a valid license.
// To purchase a valid license please visit the website www.teamspeak-interface.de

namespace Controllers;


use Doctrine\Common\Annotations\AnnotationException;
use Exceptions\CacheException;
use Exceptions\DoctrineException;
use Interfaces\ControllerInterfaces\RestrictedControllerInterface;
use ReflectionException;
use Traits\ControllerTraits\RestrictedControllerTrait;

/**
 * Class RestrictedController
 * @package Controllers
 */
class RestrictedController extends AbstractBase implements RestrictedControllerInterface
{
    use RestrictedControllerTrait;

    /**
     * RestrictedController constructor.
     * @param string $baseDir
     * @throws AnnotationException
     * @throws CacheException
     * @throws DoctrineException
     * @throws ReflectionException
     */
    public function __construct(string $baseDir)
    {
        parent::__construct($baseDir);

        if (!$this->registered) {
            if ($this->getRequestHandler()->isXml()) {
                $this->redirect(null, "publicXml", "forbidden");
            } else {
                $this->redirect(null, "public", "login", array(
                    "redirect" => urlencode($this->getRequestHandler()->getRequestUrl())
                ));
            }
        }
    }

    /**
     *
     */
    public function indexAction(): void
    {
        // TODO: Implement indexAction() method.
    }
}