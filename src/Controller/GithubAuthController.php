<?php

namespace Drupal\social_auth_github\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth\SocialAuthUserManager;
use Drupal\social_auth_github\GithubAuthManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for Simple Github Connect module routes.
 */
class GithubAuthController extends ControllerBase {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The user manager.
   *
   * @var \Drupal\social_auth\SocialAuthUserManager
   */
  private $userManager;

  /**
   * The github authentication manager.
   *
   * @var \Drupal\social_auth_github\GithubAuthManager
   */
  private $githubManager;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

  /**
   * The Social Auth Data Handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  private $dataHandler;

  /**
   * GithubAuthController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_github network plugin.
   * @param \Drupal\social_auth\SocialAuthUserManager $user_manager
   *   Manages user login/registration.
   * @param \Drupal\social_auth_github\GithubAuthManager $github_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   SocialAuthDataHandler object.
   */
  public function __construct(NetworkManager $network_manager,
                              SocialAuthUserManager $user_manager,
                              GithubAuthManager $github_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler) {

    $this->networkManager = $network_manager;
    $this->userManager = $user_manager;
    $this->githubManager = $github_manager;
    $this->request = $request;
    $this->dataHandler = $data_handler;

    // Sets the plugin id.
    $this->userManager->setPluginId('social_auth_github');

    // Sets the session keys to nullify if user could not logged in.
    $this->userManager->setSessionKeysToNullify(['access_token', 'oauth2state']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.network.manager'),
      $container->get('social_auth.user_manager'),
      $container->get('social_auth_github.manager'),
      $container->get('request_stack'),
      $container->get('social_auth.data_handler')
    );
  }

  /**
   * Response for path 'user/login/github'.
   *
   * Redirects the user to Github for authentication.
   */
  public function redirectToGithub() {
    /* @var \League\OAuth2\Client\Provider\Github|false $github */
    $github = $this->networkManager->createInstance('social_auth_github')->getSdk();

    // If github client could not be obtained.
    if (!$github) {
      drupal_set_message($this->t('Social Auth Github not configured properly. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    // Github service was returned, inject it to $githubManager.
    $this->githubManager->setClient($github);

    // Generates the URL where the user will be redirected for Github login.
    // If the user did not have email permission granted on previous attempt,
    // we use the re-request URL requesting only the email address.
    $github_login_url = $this->githubManager->getAuthorizationUrl();

    $state = $this->githubManager->getState();

    $this->dataHandler->set('oauth2state', $state);

    return new TrustedRedirectResponse($github_login_url);
  }

  /**
   * Response for path 'user/login/github/callback'.
   *
   * Github returns the user here after user has authenticated in Github.
   */
  public function callback() {
    /* @var \League\OAuth2\Client\Provider\Github|false $github */
    $github = $this->networkManager->createInstance('social_auth_github')->getSdk();

    // If Github client could not be obtained.
    if (!$github) {
      drupal_set_message($this->t('Social Auth Github not configured properly. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    $state = $this->dataHandler->get('oauth2state');

    // Retreives $_GET['state'].
    $retrievedState = $this->request->getCurrentRequest()->query->get('state');
    if (empty($retrievedState) || ($retrievedState !== $state)) {
      $this->userManager->nullifySessionKeys();
      drupal_set_message($this->t('Github login failed. Unvalid OAuth2 State.'), 'error');
      return $this->redirect('user.login');
    }

    // Saves access token to session.
    $this->dataHandler->set('access_token', $this->githubManager->getAccessToken());

    $this->githubManager->setClient($github)->authenticate();

    // Gets user's info from Github API.
    /* @var \League\OAuth2\Client\Provider\GithubResourceOwner $profile */
    if (!$profile = $this->githubManager->getUserInfo()) {
      drupal_set_message($this->t('Github login failed, could not load Github profile. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    // Gets (or not) extra initial data.
    $data = $this->userManager->checkIfUserExists($profile->getId()) ? NULL : $this->githubManager->getExtraDetails();

    // If user information could be retrieved.
    return $this->userManager->authenticateUser($profile->getName(), $profile->getEmail(), $profile->getId(), $this->githubManager->getAccessToken(), $profile->toArray()['avatar_url'], $data);
  }

}
