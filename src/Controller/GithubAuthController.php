<?php

namespace Drupal\social_auth_github\Controller;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\Controller\SocialAuthOAuth2ControllerBase;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\SocialAuthUserManager;
use Drupal\social_auth_github\GithubAuthManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for Simple Github Connect module routes.
 */
class GithubAuthController extends SocialAuthOAuth2ControllerBase {

  /**
   * GithubAuthController constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_google network plugin.
   * @param \Drupal\social_auth\SocialAuthUserManager $user_manager
   *   Manages user login/registration.
   * @param \Drupal\social_auth_github\GithubAuthManager $github_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   SocialAuthDataHandler object.
   */
  public function __construct(MessengerInterface $messenger,
                              NetworkManager $network_manager,
                              SocialAuthUserManager $user_manager,
                              GithubAuthManager $github_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler) {

    parent::__construct('Social Auth Github', 'social_auth_github', $messenger, $network_manager, $user_manager, $github_manager, $request, $data_handler);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('plugin.network.manager'),
      $container->get('social_auth.user_manager'),
      $container->get('social_auth_github.manager'),
      $container->get('request_stack'),
      $container->get('social_auth.data_handler')
    );
  }

  /**
   * Response for path 'user/login/github/callback'.
   *
   * Github returns the user here after user has authenticated.
   */
  public function callback() {

    /* @var \League\OAuth2\Client\Provider\GithubResourceOwner $profile */
    $profile = $this->processCallback();

    // If authentication was successful.
    if ($profile !== NULL) {

      // Gets (or not) extra initial data.
      $data = $this->userManager->checkIfUserExists($profile->getId()) ? NULL : $this->providerManager->getExtraDetails();

      // If user information could be retrieved.
      return $this->userManager->authenticateUser($profile->getName(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), $profile->toArray()['avatar_url'], $data);
    }

    return $this->redirect('user.login');
  }

}
