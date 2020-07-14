<?php

namespace Drupal\Tests\gizra_custom\ExampleWebDriverTest;

use weitzman\DrupalTestTraits\Entity\NodeCreationTrait;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;
use weitzman\DrupalTestTraits\ScreenShotTrait;

/**
 * A WebDriver test suitable for testing Ajax and client-side interactions.
 */
class GizraCustomWebDriverTest extends ExistingSiteWebDriverTestBase {

  use UserCreationTrait;
  use NodeCreationTrait;
  use ScreenShotTrait;

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
    $node = $this->createNode([
      'title' => 'Bar',
      'type' => 'group',
      'status' => 1,
      'body' => ['value' => 'Gizra test group'],
      'uid' => ['target_id' => 1],
    ]);
    $this->visit('/user/login');
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    // These lines are left here as examples of how to debug requests.
    // \weitzman\DrupalTestTraits\ScreenShotTrait::captureScreenshot();
    $this->captureScreenshot();

    $page = $this->getCurrentPage();
    $page->fillField('name', 'foo');
    $page->fillField('pass', 'test!23$');
    $submit_button = $page->findButton('Log in');
    $submit_button->press();
    $web_assert->statusCodeEquals(200);

    $this->visit('/node/' . $node->id());
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $this->captureScreenshot();

    // Test autocomplete on article creation.
    // Verify the text on the page.
    $web_assert->pageTextContains('Hi ' . $user->getAccountName() . ', click here if you would like to subscribe to this group called ' . $node->label());
  }

}
