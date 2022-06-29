<?php

namespace AmeliaBooking\Infrastructure\WP\UserService;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\WP\UserRoles\UserRoles;
use Slim\Container;

/**
 * Class UserService
 *
 * @package AmeliaBooking\Infrastructure\WP\UserService
 */
class UserService
{
    /**
     * @var UserRepository $usersRepository
     */
    private $usersRepository;

    /**
     * UserService constructor.
     *
     * @param Container $container
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct($container)
    {
        $this->usersRepository = $container->get('domain.users.repository');
    }

    /**
     * Return the user entity for currently logged in user
     *
     * @return AbstractUser|bool|null
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function getCurrentUser()
    {
        // Return null user if WP user id is guest id (0)
        $uid = get_current_user_id();

        if ($uid === 0) {
            return null;
        }

        try {
            // First try to get from repository
            $currentUserEntity = $this->usersRepository->findByExternalId($uid);
            if (!$currentUserEntity instanceof AbstractUser) {
                throw new NotFoundException('User not found');
            }

            return $currentUserEntity;
        } catch (NotFoundException $e) {
            // If user not found creating an entity based on WordPress user data
            $userType = UserRoles::getUserAmeliaRole($wpUser = wp_get_current_user()) ?: 'customer';

            if (empty($wpUser->ID)) {
                return null;
            }

            $firstName = $wpUser->get('first_name') !== '' ?
                $wpUser->get('first_name') : $wpUser->get('user_nicename');
            $lastName = $wpUser->get('last_name') !== '' ?
                $wpUser->get('last_name') : $wpUser->get('user_nicename');
            $email = $wpUser->get('user_email');

            $currentUserEntity = UserFactory::create([
                'type'       => $userType,
                'firstName'  => $firstName,
                'lastName'   => $lastName,
                'email'      => $email ?: 'guest@example.com',
                'externalId' => $wpUser->ID
            ]);

            return $currentUserEntity;
        }
    }

    /**
     * Return the user entity for currently logged in user
     *
     * @param $userId
     *
     * @return AbstractUser|bool|null
     * @throws InvalidArgumentException
     */
    public function getWpUserById($userId)
    {
        $userType = UserRoles::getUserAmeliaRole($wpUser = get_user_by('id', $userId)) ?: 'customer';

        if (!$wpUser || empty($wpUser->ID)) {
            return null;
        }

        return UserFactory::create(
            [
                'type'       => $userType,
                'firstName'  =>
                    $wpUser->get('first_name') !== '' ? $wpUser->get('first_name') : $wpUser->get('user_nicename'),
                'lastName'   =>
                    $wpUser->get('last_name') !== '' ? $wpUser->get('last_name') : $wpUser->get('user_nicename'),
                'email'      => $wpUser->get('user_email') ?: 'guest@example.com',
                'externalId' => $wpUser->ID
            ]
        );
    }

    /**
     * Return all amelia role user ids
     *
     * @param $roles
     *
     * @return array
     */
    public function getWpUserIdsByRoles($roles)
    {
        $ids = [];

        $wpUsers = get_users(['role__in' => $roles]);

        foreach ($wpUsers as $user) {
            $ids[] = $user->ID;
        }

        return $ids;
    }

    /**
     * Return authenticated user
     *
     * @param $username
     * @param $password
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function getAuthenticatedUser($username, $password)
    {
        if (($wpUser = wp_authenticate($username, $password)) instanceof WP_Error) {
            return null;
        }

        if (empty($wpUser->ID)) {
            return null;
        }

        $currentUserEntity = $this->usersRepository->findByExternalId($wpUser->ID);

        if (!($currentUserEntity instanceof AbstractUser)) {
            return null;
        }

        return $currentUserEntity;
    }

    /**
     * login user
     *
     * @param $username
     * @param $password
     *
     * @return mixed
     */
    public function loginWordPressUser($username, $password)
    {
        $user = wp_signon(
            [
                'user_login'    => sanitize_user($username),
                'user_password' => trim($password),
                'remember'      => true,
            ],
            true
        );

        wp_set_current_user($user->ID);
    }

    /**
     * logout user
     *
     * @return mixed
     */
    public function logoutWordPressUser()
    {
        wp_logout();
    }
}
