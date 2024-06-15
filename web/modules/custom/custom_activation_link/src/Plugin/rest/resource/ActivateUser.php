<?php

namespace Drupal\custom_activation_link\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to activate user accounts.
 *
 * @RestResource(
 *   id = "activate_user",
 *   label = @Translation("Activate User"),
 *   uri_paths = {
 *     "create" = "/api/activate-user"
 *   }
 * )
 */
class ActivateUser extends ResourceBase {

  /**
   * Responds to POST requests.
   *
   * @param array $data
   *   The data to use for activation.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the activation status.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function post(array $data) {
    // Check if token is provided and valid.
    if (!isset($data['token'])) {
      throw new AccessDeniedHttpException('Missing token.');
    }

    $token = $data['token'];
    // Extract user ID and token parts from the token.
    list($uid, $timestamp, $hash) = explode('/', $token);

    $user = User::load($uid);
    if ($user && $user->isBlocked() && $user->getLastLoginTime() == 0) {
      // Generate the expected hash.
      $expected_hash = user_pass_rehash($user, $timestamp);

      // Validate the hash.
      if (hash_equals($expected_hash, $hash)) {
        // Activate the user account.
        $user->activate();
        $user->save();

        // Return success response.
        return new ResourceResponse(['status' => 'success', 'message' => 'Account activated successfully.'], 200);
      }
    }

    // Return failure response.
    return new ResourceResponse(['status' => 'failure', 'message' => 'Invalid or expired token.'], 403);
  }
}
