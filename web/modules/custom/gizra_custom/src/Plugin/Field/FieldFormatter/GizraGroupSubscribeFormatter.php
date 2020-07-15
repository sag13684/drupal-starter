<?php

namespace Drupal\gizra_custom\Plugin\Field\FieldFormatter;

use Drupal\og\Plugin\Field\FieldFormatter\GroupSubscribeFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\og\Og;
use Drupal\og\OgMembershipInterface;
use Drupal\user\EntityOwnerInterface;

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
    $elements = [];

    // Cache by the OG membership state.
    $elements['#cache']['contexts'] = ['og_membership_state'];

    $group = $items->getEntity();
    $entity_type_id = $group->getEntityTypeId();

    // $user = User::load($this->currentUser->id());
    $user = $this->entityTypeManager->load(($this->currentUser->id()));
    if (($group instanceof EntityOwnerInterface) && ($group->getOwnerId() == $user->id())) {
      // User is the group manager.
      $elements[0] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'title' => $this->t('You are the group manager'),
          'class' => ['group', 'manager'],
        ],
        '#value' => $this->t('You are the group manager'),
      ];

      return $elements;
    }

    if (Og::isMemberBlocked($group, $user)) {
      // If user is blocked, they should not be able to apply for
      // membership.
      return $elements;
    }

    if (Og::isMember($group, $user, [
      OgMembershipInterface::STATE_ACTIVE,
      OgMembershipInterface::STATE_PENDING,
    ])) {
      $link['title'] = $this->t('Unsubscribe from group');
      $link['url'] = Url::fromRoute('og.unsubscribe', [
        'entity_type_id' => $entity_type_id,
        'group' => $group->id(),
      ]);
      $link['class'] = ['unsubscribe'];
    }
    else {
      // If the user is authenticated, set up the subscribe link.
      if ($user->isAuthenticated()) {
        $parameters = [
          'entity_type_id' => $group->getEntityTypeId(),
          'group' => $group->id(),
        ];

        $url = Url::fromRoute('og.subscribe', $parameters);
      }
      else {
        // User is anonymous, link to user login and redirect back to here.
        $url = Url::fromRoute('user.login', [], ['query' => $this->getDestinationArray()]);
      }

      /** @var \Drupal\Core\Access\AccessResult $access */
      if (($access = $this->ogAccess->userAccess($group, 'subscribe without approval', $user)) && $access->isAllowed()) {
        $link['title'] = $this->t('Subscribe to group');
        $link['class'] = ['subscribe'];
        $link['url'] = $url;
      }
      elseif (($access = $this->ogAccess->userAccess($group, 'subscribe', $user)) && $access->isAllowed()) {
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
        $link['class'] = ['subscribe', 'request'];
        $link['url'] = $url;
      }
      else {
        $elements[0] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'title' => $this->t('This is a closed group. Only a group administrator can add you.'),
            'class' => ['group', 'closed'],
          ],
          '#value' => $this->t('This is a closed group. Only a group administrator can add you.'),
        ];

        return $elements;
      }
    }

    if (!empty($link['title'])) {
      $link += [
        'options' => [
          'attributes' => [
            'title' => $link['title'],
            'class' => ['group'] + $link['class'],
          ],
        ],
      ];

      $elements[0] = [
        '#type' => 'link',
        '#title' => $link['title'],
        '#url' => $link['url'],
      ];
    }

    return $elements;
  }

}
