<?php

namespace Drupal\social_auth_github\Settings;

/**
 * Defines an interface for Social Auth Github settings.
 */
interface GithubAuthSettingsInterface {

  /**
   * Gets the client ID.
   *
   * @return string
   *   The client ID.
   */
  public function getClientId();

  /**
   * Gets the client secret.
   *
   * @return string
   *   The client secret.
   */
  public function getClientSecret();

}
