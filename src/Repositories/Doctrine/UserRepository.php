<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  UserRepository.php - Part of the lumen-doctrine-oauth2 project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\OAuth\Lumen\Repositories\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Hashing\Hasher;
use Jitesoft\Exceptions\Security\OAuth2\InvalidGrantException;
use Jitesoft\OAuth\Lumen\Entities\Contracts\UserInterface;
use Jitesoft\OAuth\Lumen\Entities\User;
use Jitesoft\OAuth\Lumen\OAuth;
use Psr\Log\LoggerInterface;
use League\OAuth2\Server\{
    Entities\ClientEntityInterface as Client,
    Entities\UserEntityInterface as OAuthUser,
    Repositories\UserRepositoryInterface
};

class UserRepository extends AbstractRepository implements UserRepositoryInterface {

    protected $hash;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param Hasher $hash
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, Hasher $hash) {
        parent::__construct($em, $logger);

        $this->hash = $hash;
    }

    /**
     * Get a user entity.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType The grant type used
     * @param Client $clientEntity
     * @return OAuthUser|null|UserInterface
     * @throws InvalidGrantException
     */
    public function getUserEntityByUserCredentials($username,
                                                    $password,
                                                    $grantType,
                                                    Client $clientEntity): ?UserInterface {

        /** @var \Jitesoft\OAuth\Lumen\Entities\Client $clientEntity */
        if (!array_key_exists($grantType, OAuth::GRANT_TYPES)
            || !$clientEntity->hasGrant(OAuth::GRANT_TYPES[$grantType])) {
            throw new InvalidGrantException('Invalid grant.', $grantType);
        }

        $value = config('oauth2.user_identification', 'authKey');
        /** @var UserInterface $user */
        $user = $this->em->getRepository(User::class)->findOneBy([
            $value  => $username
        ]);

        return $this->hash->check($password, $user->getPassword()) ? $user : null;
    }

    /**
     * @param string $identifier
     * @return OAuthUser|null|object|UserInterface
     */
    public function getUserByIdentifier(string $identifier): ?UserInterface {
        return $this->em->getRepository(User::class)->findOneBy([
            'identifier' => $identifier
        ]);
    }

}
