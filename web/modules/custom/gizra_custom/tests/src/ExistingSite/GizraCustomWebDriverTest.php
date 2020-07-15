<?php

namespace Drupal\Tests\gizra_custom\ExampleWebDriverTest;

use Drupal\FunctionalTests\AssertLegacyTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A WebDriver test suitable for testing Ajax and client-side interactions.
 */
class GizraCustomWebDriverTest extends ExistingSiteBase {

  use AssertLegacyTrait;

  /**
   * Test Group join link text.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testGroupJoinLinkText() {
    // Create an authenticated user.
    $user = $this->createUser([], 'foo', FALSE, ['pass' => 'test!23$']);
    // Create a group node.
    $node = $this->createNode([
      'title' => 'Bar',
      'type' => 'group',
      'status' => 1,
      'body' => ['value' => 'Gizra test group'],
      'uid' => ['target_id' => 1],
    ]);

    $this->drupalLogin($user);
    // Browse group page.
    $this->drupalGet($node->toUrl());
    // Match the group join link text.
    $this->assertLink('Hi foo, click here if you would like to subscribe to this group called Bar');
  }

}
