<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  TokenTrait.php - Part of the loauthd project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Loauthd\Entities\Traits;

use Carbon\Carbon;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Jitesoft\Loauthd\Entities\Contracts\ClientInterface;
use Jitesoft\Loauthd\Entities\Contracts\ScopeInterface;
use Jitesoft\Loauthd\Entities\TokenScope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

trait TokenTrait {

    /**
     * @var Carbon
     * @ORM\Column(type="datetime", name="expiry", nullable=false)
     */
    protected $expiry;

    /**
     * @var string
     * @ORM\Column(type="string", name="user_identifier", nullable=true)
     */
    protected $userIdentifier;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var array|ScopeInterface[]|Collection
     */
    protected $scopes;

    /**
     * Associate a scope with the token.
     *
     * @param ScopeEntityInterface $scope
     */
    public abstract function addScope(ScopeEntityInterface $scope);

    /**
     * Return an array of scopes associated with the token.
     *
     * @return ScopeInterface[]|array
     */
    public function getScopes() {
        return $this->scopes->map(function(TokenScope $scope) {
            return $scope->getScope();
        })->toArray();
    }

    /**
     * Get the token's expiry date time.
     *
     * @return \DateTime
     */
    public function getExpiryDateTime() {
        return $this->expiry;
    }

    /**
     * Set the date time when the token expires.
     *
     * @param \DateTime $dateTime
     */
    public function setExpiryDateTime(\DateTime $dateTime) {
        $this->expiry = Carbon::instance($dateTime);
    }

    /**
     * Set the identifier of the user associated with the token.
     *
     * @param string|int $identifier The identifier of the user
     */
    public function setUserIdentifier($identifier) {
        $this->userIdentifier = $identifier;
    }

    /**
     * Get the token user's identifier.
     *
     * @return string|int
     */
    public function getUserIdentifier() {
        return $this->userIdentifier;
    }

    /**
     * Get the client that the token was issued to.
     *
     * @return ClientEntityInterface
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Set the client that the token was issued to.
     *
     * @param ClientEntityInterface $client
     */
    public function setClient(ClientEntityInterface $client) {
        $this->client = $client;
    }

}
