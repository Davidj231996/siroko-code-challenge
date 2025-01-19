Feature:
    I want to prove that everything works in confirming a cart

    Scenario: I want to confirm a cart
        Given I have a Product "producto1" with data:
        """
        {
            "name": "Producto 1",
            "price": "9.99",
            "stock": 10
        }
        """
        When I add a product to "cart1" with data:
        """
        {
            "productId": "producto1",
            "quantity": 10
        }
        """
        When I want to get total items of "cart1"
        Then the total items must be 10
        Given I have a Product "producto2" with data:
        """
        {
            "name": "Producto 2",
            "price": "9.99",
            "stock": 5
        }
        """
        When I add a product to "cart1" with data:
        """
        {
            "productId": "producto2",
            "quantity": 2,
            "cartId": "cart1"
        }
        """
        When I want to get total items of "cart1"
        Then the total items must be 12
        Then I confirm "cart1"
