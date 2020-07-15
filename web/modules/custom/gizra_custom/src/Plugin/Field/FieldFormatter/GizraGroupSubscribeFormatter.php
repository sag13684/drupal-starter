<?php

namespace Drupal\gizra_custom\Plugin\Field\FieldFormatter;

use Drupal\og\Plugin\Field\FieldFormatter\GroupSubscribeFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\og\Og;
use Drupal\og\OgMembershipInterface;

/**
 * Plugin implementation for the OG subscribe formatter.
 *
 * @FieldFormatter(
 *   id = "gizra_custom_group_subscribe",
 *   label = @Translation("Gizra Group subscribe"),
 *   description = @Translation("Display Personalized OG Group subscribe and un-subscribe links."),
 *   field_types = {
 *     "og_group"
 *   }
 * )
 */
class GizraGroupSubscribeFormatter extends GroupSubscribeFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $group = $items->getEntity();
    $user = $this->entityTypeManager->load(($this->currentUser->id()));

    if (!Og::isMember($group, $user, [
      OgMembershipInterface::STATE_ACTIVE,
      OgMembershipInterface::STATE_PENDING,
    ])) {
      /** @var \Drupal\Core\Access\AccessResult $access */
      if (($access = $this->ogAccess->userAccess($group, 'subscribe', $user)) && $access->isAllowed()) {
        if ($user->id()) {
          $link['title'] = $this->t('Hi @username, click here if you would like to subscribe to this group called @title',
            [
              '@username' => $user->getAccountName(),
              '@title' => $group->label(),
            ]);
        }
        else {
          $link['title'] = $this->t('Hi, click here if you would like to subscribe to this group called @title',
            [
              '@title' => $group->label(),
            ]);
        }
      }
    }

    if (!empty($link['title'])) {
      $elements[0]['#title'] = $link['title'];
    }

    return $elements;
  }

}
