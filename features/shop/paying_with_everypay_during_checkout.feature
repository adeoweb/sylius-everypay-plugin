@paying_with_everypay_during_checkout
Feature: Paying with EveryPay during checkout
  In order to buy products
  As a Customer
  I want to be able to pay with "EveryPay" payment gateway

  Background:
    Given the store operates on a single channel in "United States"
    And there is a user "john@example.com" identified by "password123"
    And the store has a payment method "EveryPay" with a code "everypay" and EveryPay payment gateway without using authorize
    And the store has a product "PHP T-Shirt" priced at "€19.99"
    And the store ships everywhere for free
    And I am logged in as "john@example.com"

  @ui
  Scenario: Successful payment in EveryPay
    Given I added product "PHP T-Shirt" to the cart
    And I have proceeded selecting "EveryPay" payment method
    When I confirm my order with EveryPay payment
    And I get redirected to EveryPay and complete my payment
    Then I should be notified that my payment has been completed
    And I should see the thank you page

  @ui
  Scenario: Cancelling the payment
    Given I added product "PHP T-Shirt" to the cart
    And I have proceeded selecting "EveryPay" payment method
    When I confirm my order with EveryPay payment
    And I click on "go back" during my EveryPay payment
    Then I should be able to pay again

  @ui
  Scenario: Retrying the payment with success
    Given I added product "PHP T-Shirt" to the cart
    And I have proceeded selecting "EveryPay" payment method
    And I have confirmed my order with EveryPay payment
    But I have clicked on "go back" during my EveryPay payment
    When I try to pay again with EveryPay payment
    And I get redirected to EveryPay and complete my payment
    Then I should be notified that my payment has been completed
    And I should see the thank you page

  @ui
  Scenario: Retrying the payment and failing
    Given I added product "PHP T-Shirt" to the cart
    And I have proceeded selecting "EveryPay" payment method
    And I have confirmed my order with EveryPay payment
    But I have clicked on "go back" during my EveryPay payment
    When I try to pay again with EveryPay payment
    And I click on "go back" during my EveryPay payment
    Then I should be able to pay again
