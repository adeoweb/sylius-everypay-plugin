@managing_payment_methods
Feature: Adding a new EveryPay payment method
  In order to allow payment for orders, using the EveryPay gateway
  As an Administrator
  I want to add new payment methods to the system

  Background:
    Given the store operates on a single channel in "United States"
    And I am logged in as an administrator

  @ui
  Scenario: Adding a new EveryPay payment method
    Given I want to create a new EveryPay payment method
    When I name it "EveryPay" in "English (United States)"
    And I specify its code as "everypay"
    And I configure it with test EveryPay gateway data "TEST", "TEST", "TEST", "TEST"
    And I add it
    Then I should be notified that it has been successfully created
    And the payment method "EveryPay" should appear in the registry
