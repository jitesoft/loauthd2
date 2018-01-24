<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  UserRepositoryTest.php - Part of the lumen-doctrine-oauth2 project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Loauthd\Tests\Repositories\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Illuminate\Hashing\BcryptHasher;
use Jitesoft\Exceptions\Security\OAuth2\InvalidGrantException;
use Jitesoft\Log\StdLogger;
use Jitesoft\Loauthd\Entities\Client;
use Jitesoft\Loauthd\Entities\User;
use Jitesoft\Loauthd\OAuth;
use Jitesoft\Loauthd\Repositories\Doctrine\Contracts\UserRepositoryInterface;
use Jitesoft\Loauthd\Repositories\Doctrine\UserRepository;
use Jitesoft\Loauthd\Tests\TestCase;
use Mockery;
use phpmock\Mock;
use phpmock\MockBuilder;

class UserRepositoryTest extends TestCase {

    /** @var UserRepositoryInterface */
    protected $repository;

    protected function setUp() {
        parent::setUp();

        $this->repository = new UserRepository($this->entityManagerMock, new StdLogger(), new BcryptHasher());
    }

    public function testGetUserEntityByUserCredentials() {
        $client = new Client('test', '', null, OAuth::GRANT_TYPE_PASSWORD);
        $user   = new User('abc', 'test', (new BcryptHasher())->make('abc'));

        $expectation = $this->entityManagerMock->shouldReceive('getRepository')
            ->once()
            ->with(User::class)
            ->andReturn(
                Mockery::mock(ObjectRepository::class)
                    ->shouldReceive('findOneBy')
                    ->once()
                    ->with(['username' => 'test'])
                    ->andReturn($user)
                    ->getMock()
            );

        $builder = new MockBuilder();
        $mock    = $builder
            ->setNamespace((new \ReflectionClass(UserRepository::class))->getNamespaceName())
            ->setName('config')
            ->setFunction(function(string $config, string $default) {
                $this->assertEquals(OAuth::CONFIG_NAMESPACE. '.user_identification',  $config);
                $this->assertEquals('authKey', $default);
                return 'username';
            }
            )->build();
        $mock->enable();

        $out = $this->repository
            ->getUserEntityByUserCredentials(
                $user->getAuthKey(),
                'abc',
                'password',
                $client
            );

        $this->assertSame($user, $out);
        $expectation->verify();
    }

    public function testGetUserEntityByUserCredentialsInvalidGrant() {
        $client = new Client('test', '', null, OAuth::GRANT_TYPE_PASSWORD);

        try {
            $this->repository
                ->getUserEntityByUserCredentials(
                    'abc',
                    '123',
                    'another_grant',
                    $client
                );
        } catch (InvalidGrantException $ex) {

            $this->assertEquals('Invalid grant.', $ex->getMessage());
            $this->assertEquals('another_grant', $ex->getGrant());
            return;
        }

        $this->assertTrue(false);
    }

    public function testGetUserByIdentifier() {
        $user = new User('abc', 'test', (new BcryptHasher())->make('abc'));

        $expectation = $this->entityManagerMock->shouldReceive('getRepository')
            ->once()
            ->with(User::class)
            ->andReturn(
                Mockery::mock(ObjectRepository::class)
                    ->shouldReceive('findOneBy')
                    ->once()
                    ->with(['identifier' => 'abc'])
                    ->andReturn($user)
                    ->getMock()
            );

        $out = $this->repository->getUserByIdentifier('abc');
        $expectation->verify();
        $this->assertSame($out, $user);
    }

    public function testGetUserByIdentifierNone() {
        $expectation = $this->entityManagerMock->shouldReceive('getRepository')
            ->once()
            ->with(User::class)
            ->andReturn(
                Mockery::mock(ObjectRepository::class)
                    ->shouldReceive('findOneBy')
                    ->once()
                    ->with(['identifier' => 'abc'])
                    ->andReturn(null)
                    ->getMock()
            );

        $out = $this->repository->getUserByIdentifier('abc');
        $expectation->verify();
        $this->assertNull($out);
    }

}
