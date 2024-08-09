<?php 
session_start();

// Redirect to login page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'home.php'; 
?>
<!DOCTYPE html>
<lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- bootstrap links -->
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- fonts links -->

    <style>
        .cart-icon {
            position: fixed;
            bottom: 100px;
            right: 50px;
            background-color: #ffffff;
            border: 2px solid #000000;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            height: 50px;
            z-index: 1000; 
        }

        .cart-icon i {
            font-size: 24px;
        }

        .cart-icon #cart-count {
            position: absolute;
            top: -8px;
            right: -7px;
            background-color: #ff0000;
            color: #ffffff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        .checkout-form {
            max-width: 400px;
            margin: auto;
        }
    </style>
</head>
<body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  <!-- bootstrap links -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXlZgB+5Y+6LUDr9B0rxmB/i4bfae2iD3GVrQK6AAcY9M+gPAZuSB5hFHIn/"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWi5tXCdN9fGHYx6F0B3aJwK9pFzXzzZZo0D/G24GRAfUqDI16d8YQ/63E/"></script>
  <!-- bootstrap links -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
      const cartIcon = document.querySelector('.cart-icon');
      const cartCount = document.getElementById('cart-count');
      const cartItemsList = document.getElementById('cart-items');
      const totalPriceElem = document.getElementById('total-price');
      const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
      const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
      let cart = [];

      // Add to Cart buttons
      const addToCartButtons = document.querySelectorAll('.fa-cart-shopping');

      addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
          const itemCard = button.closest('.card');
          const itemName = itemCard.querySelector('h3').innerText;
          const itemPrice = parseFloat(itemCard.querySelector('p').innerText.replace('$', ''));

          addToCart(itemName, itemPrice);
        });
      });

      function addToCart(name, price) {
        const existingItem = cart.find(item => item.name === name && item.price === price);

        if (existingItem) {
          existingItem.quantity += 1;
        } else {
          cart.push({ name, price, quantity: 1 });
        }

        updateCart();
      }

      function updateCart() {
        cartItemsList.innerHTML = '';
        let totalPrice = 0;
        let totalItems = 0;

        cart.forEach((item, index) => {
          const listItem = document.createElement('li');
          listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

          const itemDetails = document.createElement('span');
          itemDetails.innerText = `${item.name} - $${item.price.toFixed(2)} x ${item.quantity}`;
          listItem.appendChild(itemDetails);

          const removeButton = document.createElement('button');
          removeButton.classList.add('btn', 'btn-danger', 'btn-sm');
          removeButton.innerText = 'Remove';
          removeButton.addEventListener('click', () => {
            removeFromCart(index);
          });
          listItem.appendChild(removeButton);

          cartItemsList.appendChild(listItem);

          totalPrice += item.price * item.quantity;
          totalItems += item.quantity;
        });

        totalPriceElem.innerText = totalPrice.toFixed(2);
        cartCount.innerText = totalItems;
      }

      function removeFromCart(index) {
        cart.splice(index, 1); // Remove item from cart array
        updateCart(); // Update cart display
      }

      // Show cart modal when cart icon is clicked
      cartIcon.addEventListener('click', () => {
        updateCart(); // Update cart display before showing modal
        cartModal.show();
      });

      // Checkout button functionality
      document.getElementById('checkout-btn').addEventListener('click', () => {
        if (cart.length === 0) {
          alert('Your cart is empty. Add items to proceed to checkout.');
          return;
        }
        checkoutModal.show();
      });

      // Handle form submission for checkout
      document.getElementById('checkout-form').addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Here you can add further logic to process the payment details
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const cardNumber = document.getElementById('card-number').value.trim();
        const expiryDate = document.getElementById('expiry-date').value.trim();
        const cvv = document.getElementById('cvv').value.trim();
        
        // Example validation (you should add more robust validation)
        if (!name || !email || !cardNumber || !expiryDate || !cvv) {
          alert('Please fill in all payment details.');
          return;
        }
        
        // Simulate payment processing (replace with actual payment processing)
        setTimeout(() => {
          alert('Payment successful!');
          cart = []; // Clear the cart after successful payment
          updateCart(); // Update cart display
          checkoutModal.hide(); // Close checkout modal
        }, 2000); // Simulate 2 seconds of processing time
      });
    });
  </script>
</body>
</html>
