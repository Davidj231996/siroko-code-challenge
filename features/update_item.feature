Feature:
    I want to prove that everything works in removing a product from a cart

    Scenario: I want to remove products from a cart
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
        When I update a product of cart with data:
        """
        {
            "productId": "producto1",
            "quantity": 9,
            "cartId": "cart1"
        }
        """
        When I want to get total items of "cart1"
        Then the total items must be 11

    Scenario: I cannot update product of cart because it exceeds stock
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
        When I cannot update a product of cart with data:
        """
        {
            "productId": "producto1",
            "quantity": 11,
            "cartId": "cart1"
        }
        """
