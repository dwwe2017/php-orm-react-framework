<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Handlers;


use Blocktrail\CryptoJSAES\CryptoJSAES;
use Controllers\AbstractBase;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Entities\Group;
use Entities\User;
use Exception;
use Helpers\EntityHelper;
use Monolog\Logger;
use Services\DoctrineService;
use Services\LoggerService;
use Traits\UtilTraits\InstantiationStaticsUtilTrait;

/**
 * Class SessionHandler
 * @package Handlers
 */
class SessionHandler
{
    use InstantiationStaticsUtilTrait;

    /**
     * @var EntityManager
     */
    private EntityManager $em;

    /**
     * @var Logger
     */
    private Logger $loggerService;

    /**
     * @var bool
     */
    private bool $registered = false;

    /**
     * @var User|null
     */
    private ?User $user;

    /**
     * @var Group|null
     */
    private ?Group $group;

    /**
     * @var int
     */
    private int $role = Group::ROLE_ANY;

    /**
     * @var AnnotationReader
     */
    private AnnotationReader $annotation_reader;

    /**
     * SessionHandler constructor.
     * @param DoctrineService $doctrineService
     * @param Logger $loggerService
     * @throws Exception
     */
    private function __construct(DoctrineService $doctrineService, Logger $loggerService)
    {
        try {
            /**
             * @see AbstractBase::initHandlers()
             * @see DoctrineService::getSystemDoctrineService()
             * @see DoctrineService::getEntityManager()
             */
            $this->em = $doctrineService->getEntityManager();

            /**
             * @see LoggerService::getLogger()
             */
            $this->loggerService = $loggerService;

            /**
             * @internal If the uid of the user exists in the session
             * or if the parameters of the login process exist
             */
            if (isset($_SESSION["uid"])) {
                $this->sessionRenew($_SESSION["uid"]);
            } elseif (isset($_POST["username"]) && isset($_POST["password"])) {
                $this->initRegistration($_POST["username"], $_POST["password"]);
            } elseif (isset($_COOKIE["TSI2usr"]) && isset($_COOKIE["TSI2pwd"]) && isset($_COOKIE["TSI2hash"])) {
                $username = CryptoJSAES::decrypt($_COOKIE["TSI2usr"], $_COOKIE["TSI2hash"]);
                $password = CryptoJSAES::decrypt($_COOKIE["TSI2pwd"], $_COOKIE["TSI2hash"]);
                $this->initRegistration($username, $password);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param DoctrineService $doctrineService
     * @param Logger $loggerService
     * @return SessionHandler|null
     * @throws Exception
     */
    public static final function init(DoctrineService $doctrineService, Logger $loggerService): ?SessionHandler
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($doctrineService, $loggerService);
        }

        return self::$instance;
    }

    /**
     * @param null $redirect
     */
    public final function signOut($redirect = null): void
    {
        /**
         * @internal Only if the user is still logged in, otherwise there will be a redirection error in the browser
         */
        if (isset($_SESSION["uid"])) {
            unset($_SESSION["uid"]);
            $_SESSION = array();

            $this->clearCookies();
        }

        if (!is_null($redirect)) {
            header("Location: " . $redirect);
            exit();
        }
    }

    /**
     *
     */
    public final function clearCookies(): void
    {
        try {
            /**
             * @internal Set expiration date in the past for deleting the cookies if any
             */
            $expire = (new DateTime)->modify("-1 year")->getTimestamp();

            if (isset($_COOKIE["TSI2usr"])) {
                setcookie('TSI2usr', "", $expire, "/", "", false, true);
            }

            if (isset($_COOKIE["TSI2pwd"])) {
                setcookie('TSI2pwd', "", $expire, "/", "", false, true);
            }

            if (isset($_COOKIE["TSI2hash"])) {
                setcookie('TSI2hash', "", $expire, "/", "", false, true);
            }
        } catch (Exception $e) {
            $this->loggerService->error($e->getMessage(), $e->getTrace());
        }
    }

    /**
     * @return bool
     */
    public final function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * @return User|null
     */
    public final function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return Group|null
     */
    public final function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * Check whether the user has the necessary authorizations based on the roles
     * @param int $required_role_level
     * @return bool
     */
    public final function hasRequiredRole(int $required_role_level): bool
    {
        return $this->isRoot() || $this->getRole() >= $required_role_level;
    }

    /**
     * @return bool
     */
    public final function isRoot(): bool
    {
        return $this->getRole() === Group::ROLE_ROOT;
    }

    /**
     * Returns the name of the role based on the number or constant
     * @return string
     * @see NavigationHandler::getRolesConvertedIntoReadableTerms()
     */
    public final function getRoleName()
    {
        switch ($this->getRole()) {
            case Group::ROLE_ROOT:
                return "ROOT";
            case Group::ROLE_ADMIN:
                return "ADMIN";
            case Group::ROLE_RESELLER:
                return "RESELLER";
            case Group::ROLE_USER:
                return "USER";
            case Group::ROLE_ANY:
                return "ANY";
        }

        return $this->getRole();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @example $this->getUser()->setName("Another"); $this->flush();
     */
    public final function flush()
    {
        $this->getEm()->flush();
    }

    /**
     * @return User|null
     */
    public final function getParentUser(): ?User
    {
        return $this->getUser()->getBy();
    }

    /**
     * @return mixed
     */
    public final function getUsers()
    {
        if ($this->isRoot()) {
            return $this->getEm()->getRepository("Entities\User")->findAll();
        }

        return $this->getUser()->getUsers();
    }

    /**
     * @param array $users
     * @return array
     */
    public final function getUsersArray(array $users = []): array
    {
        $result = array();
        $users = empty($users) ? $this->getUsers() : $users;
        $getters = EntityHelper::init($this->getEm())->getGetterMethods(User::class, ["avatar"]);

        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $data = array();
            foreach ($getters as $fieldName => $getterMethod) {
                $methodResult = $user->{$getterMethod}();
                if (strcasecmp($fieldName, "users") == 0) {
                    $data[$user->getId()][$fieldName] = count($methodResult); //array_push($result, self::getUsersArray($methodResult));
                } elseif ($methodResult instanceof DateTime) {
                    $data[$user->getId()][$fieldName] = $methodResult->format("d.m.Y");
                } elseif (is_object($methodResult) && method_exists($methodResult, "getName")) {
                    $data[$user->getId()][$fieldName] = htmlentities($methodResult->getName());
                } else {
                    $data[$user->getId()][$fieldName] = htmlentities($methodResult);
                }
            }

            $result[] = $data[$user->getId()];
        }

        return $result;
    }

    /**
     * @return ClassMetadata
     */
    public function getDefaultMeta(): ClassMetadata
    {
        return $this->getEm()->getClassMetadata(User::class);
    }

    /**
     * @return EntityManager
     * @see DoctrineService::getSystemDoctrineService()
     */
    private final function getEm(): EntityManager
    {
        return $this->em;
    }

    /**
     * @param $username
     * @param $password
     */
    public final function initRegistration($username, $password)
    {
        $repo = $this->em->getRepository("Entities\User");

        /**
         * Get user from database
         * @see User::getName()
         */
        $user = $repo->findOneBy(["name" => $username]);

        /**
         * Check if user exists and password is valid
         * @see User::isValidPassword()
         */
        if ($user instanceof User && $user->isValidPassword($password)) {

            /**
             * @internal Declare variables and set the session uid
             */
            $this->sessionCreate($user);

            if (isset($_POST["remember"])) {
                try {
                    $passphrase = bin2hex(random_bytes(16));
                    $username = CryptoJSAES::encrypt($_POST["username"], $passphrase);
                    $password = CryptoJSAES::encrypt($_POST["password"], $passphrase);

                    /**
                     * @internal If the post parameter "remember" exists, further cookies will be created
                     */
                    $expire = (new DateTime)->modify("+1 year")->getTimestamp();
                    setcookie('TSI2hash', $passphrase, $expire, "/", "", false, true);
                    setcookie('TSI2usr', $username, $expire, "/", "", false, true);
                    setcookie('TSI2pwd', $password, $expire, "/", "", false, true);

                } catch (Exception $e) {
                    $this->loggerService->error($e->getMessage(), $e->getTrace());
                }
            }
        }
    }

    /**
     * @param $uid
     */
    private function sessionRenew($uid)
    {
        $repo = $this->em->getRepository("Entities\User");
        $user = $repo->find($uid);
        if ($user instanceof User) {
            $this->sessionCreate($user);
        }
    }

    /**
     * @param User $user
     */
    private function sessionCreate(User $user)
    {
        $this->user = $user;
        $this->group = $this->getUser()->getGroup();
        $this->role = $user->getGroup()->getRole();
        $_SESSION["uid"] = $user->getId();
        $this->registered = true;
    }
}
