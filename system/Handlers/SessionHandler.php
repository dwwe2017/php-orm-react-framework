<?php


namespace Handlers;


use Blocktrail\CryptoJSAES\CryptoJSAES;
use Controllers\AbstractBase;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Entities\Group;
use Entities\User;
use Exception;
use Exceptions\SessionException;
use Helpers\EntityHelper;
use Services\DoctrineService;
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
    private $em;

    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var Group|null
     */
    private $group;

    /**
     * @var int
     */
    private $role = Group::ROLE_ANY;

    /**
     * @var AnnotationReader
     */
    private $annotation_reader;

    /**
     * SessionHandler constructor.
     * @param DoctrineService $doctrineService
     * @throws SessionException
     */
    public function __construct(DoctrineService $doctrineService)
    {
        try {
            /**
             * @see AbstractBase::initHandlers()
             * @see DoctrineService::getSystemDoctrineService()
             * @see DoctrineService::getEntityManager()
             */
            $this->em = $doctrineService->getEntityManager();

            /**
             * @internal Get the parameters either from the login process or from the cookies, if any
             */
            $username = $_POST["username"] ?? $_COOKIE["TSI2usr"] ?? null;
            $password = $_POST["password"] ?? $_COOKIE["TSI2pwd"] ?? null;
            $passphrase = $_POST["passphrase"] ?? $_COOKIE["TSI2key"] ?? null;
            $remember = isset($_POST["remember"]);

            /**
             * @internal Check if already logged in
             */
            $uid = $_SESSION["uid"] ?? null;

            /**
             * @internal If the uid of the user exists in the session
             * or if the parameters of the login process exist
             */
            if ($uid || ($username && $password && $passphrase)) {

                /**
                 * @see User
                 */
                $repo = $this->em->getRepository("Entities\User");

                if ($uid) {
                    /**
                     * @internal If already logged in..
                     */
                    $user = $repo->find($uid);
                    if ($user instanceof User) {
                        $this->initRegistration($user);
                    }

                } elseif ($password && $username && $passphrase) {
                    /**
                     * The parameters are encrypted before dispatch via post request and for security reasons not in clear text sent.
                     * Consequently, the data must first be decrypted again.
                     * @see CryptoJSAES::decrypt()
                     */
                    $usernameEnc = CryptoJSAES::decrypt($username, $passphrase);
                    $passwordEnc = CryptoJSAES::decrypt($password, $passphrase);

                    /**
                     * Get user from database
                     * @see User::getName()
                     */
                    $user = $repo->findOneBy(["name" => $usernameEnc]);

                    /**
                     * Check if user exists and password is valid
                     * @see User::isValidPassword()
                     */
                    if ($user && $user instanceof User && $user->isValidPassword($passwordEnc)) {

                        /**
                         * @internal Declare variables and set the session uid
                         */
                        $this->initRegistration($user);

                        if ($remember) {
                            /**
                             * @internal If the post parameter "remember" exists, further cookies will be created
                             */
                            $expire = (new DateTime)->modify("+1 year")->getTimestamp();
                            @setcookie('TSI2usr', $username, $expire, "/", "", false, true);
                            @setcookie('TSI2pwd', $password, $expire, "/", "", false, true);
                            @setcookie('TSI2key', $passphrase, $expire, "/", "", false, true);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new SessionException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param DoctrineService $doctrineService
     * @return SessionHandler|null
     * @throws SessionException
     */
    public static final function init(DoctrineService $doctrineService)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($doctrineService);
        }

        return self::$instance;
    }

    /**
     * @param null $redirect
     * @throws Exception
     */
    public final function signOut($redirect = null): void
    {
        /**
         * @internal Only if the user is still logged in, otherwise there will be a redirection error in the browser
         */
        if (isset($_SESSION["uid"])) {
            unset($_SESSION["uid"]);
            $_SESSION = array();

            /**
             * @internal Set expiration date in the past for deleting the cookies if any
             */
            $expire = (new DateTime)->modify("-1 year")->getTimestamp();

            if (isset($_COOKIE["TSI2usr"])) {
                @setcookie('TSI2usr', "", $expire);
            }

            if (isset($_COOKIE["TSI2pwd"])) {
                @setcookie('TSI2pwd', "", $expire);
            }

            if (isset($_COOKIE["TSI2key"])) {
                @setcookie('TSI2key', "", $expire);
            }

            is_null($redirect) || header("Location: " . $redirect);
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
    public final function isRoot()
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
     * With this call, changes regarding the user can be saved.
     * @throws OptimisticLockException
     * @example $this->getUser()->setName("Another"); $this->flush();
     */
    public final function flush()
    {
        $this->getEm()->flush();
    }

    /**
     * @return User|null
     */
    public final function getBy()
    {
        return $this->getUser()->getBy();
    }

    /**
     * @return mixed
     */
    public final function getUsers()
    {
        if ($this->isRoot()) {
            return $this->getEm()->getRepository("Entities\\User")->findAll();
        }

        return $this->getUser()->getUsers();
    }

    /**
     * @param array $users
     * @return array
     */
    public final function getUsersArray(array $users = [])
    {
        $result = array();
        $users = empty($users) ? $this->getUsers() : $users;
        $getters = EntityHelper::init($this->getEm())->getGetterMethods(User::class);

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
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getDefaultMeta()
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
     * @param User $user
     */
    private function initRegistration(User $user)
    {
        $this->user = $user;
        $this->group = $this->getUser()->getGroup();
        $this->role = $user->getGroup()->getRole();
        $_SESSION["uid"] = $user->getId();
        $this->registered = true;
    }
}
