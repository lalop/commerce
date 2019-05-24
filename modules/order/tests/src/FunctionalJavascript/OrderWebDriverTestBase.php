<?php

namespace Drupal\Tests\commerce_order\FunctionalJavascript;

use Drupal\Tests\commerce\FunctionalJavascript\CommerceWebDriverTestBase;

/**
 * Defines base class for commerce_order test cases.
 */
abstract class OrderWebDriverTestBase extends CommerceWebDriverTestBase {

  /**
   * The country list.
   *
   * @var array
   */
  protected $countryList;

  /**
   * The variation to test against.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariation
   */
  protected $variation;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_product',
    'commerce_order',
    'commerce_order_test',
    'inline_entity_form',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_order',
      'administer commerce_order_type',
      'access commerce_order overview',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $country_repository = $this->container->get('address.country_repository');
    $this->countryList = $country_repository->getList();

    // Create a product variation.
    $this->variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'sku' => $this->randomMachineName(),
      'price' => [
        'number' => 999,
        'currency_code' => 'USD',
      ],
    ]);

    // We need a product too otherwise tests complain about the missing
    // backreference.
    $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => $this->randomMachineName(),
      'stores' => [$this->store],
      'variations' => [$this->variation],
    ]);
  }

  /**
   * Asserts that the given address is rendered on the page.
   *
   * @param array $address
   *   The address.
   */
  protected function assertRenderedAddress(array $address) {
    $page = $this->getSession()->getPage();
    foreach ($address as $property => $value) {
      if ($property == 'country_code') {
        $value = $this->countryList[$value];
      }
      $this->assertContains($value, $page->find('css', 'p.address')->getText());
      $this->assertSession()->fieldNotExists("profile[address][0][address][$property]");
    }
    $this->assertSession()->fieldNotExists('profile[copy_to_address_book]');
  }

}
