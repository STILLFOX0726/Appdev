<?php
session_start();

// Mock products data - this should be in your products.php file
$products = [
    1 => [
        'name' => 'sunscree',
        'price' => 44.00,
        'platform' => 'Light Lotion'
    ],
    2 => [
        'name' => 'Aveeno',
        'price' => 249.99,
        'platform' => 'Body Wash'
    ],
    3 => [
        'name' => 'Anti-acne',
        'price' => 249.99,
        'platform' => 'Facial wash'
    ]
];

// Initialize the cart if not already initialized
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle removing from cart via GET request (from index.php)
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $productId = $_GET['id'];
    unset($_SESSION['cart'][$productId]);
}

// Handle removing from cart via POST request (from cart.php)
if (isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    unset($_SESSION['cart'][$productId]);
}

// Handle updating quantity
if (isset($_POST['update_quantity'])) {
    $productId = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// Calculate totals
$itemCount = count($_SESSION['cart']);
$subtotal = 0;
$shipping = 5.00; // Standard delivery

foreach ($_SESSION['cart'] as $productId => $quantity) {
    if (isset($products[$productId])) {
        $subtotal += $products[$productId]['price'] * $quantity;
    }
}

$totalCost = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Shopping Cart</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        padding: 40px;
        color: #333;
    }

    .shopping-cart {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #000;
    }

    .section-title {
        font-weight: bold;
        margin: 20px 0 10px 0;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }

    .cart-item {
        display: flex;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .cart-item-info {
        flex: 2;
    }

    .cart-item-name {
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 16px;
    }

    .cart-item-details {
        color: #666;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .remove-button {
        background: none;
        border: none;
        color: #0066cc;
        cursor: pointer;
        font-size: 14px;
        padding: 0;
        text-decoration: underline;
    }

    .remove-button:hover {
        color: #004499;
    }

    .quantity-input {
        width: 50px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }

    .update-button {
        background: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 14px;
    }

    .update-button:hover {
        background: #e0e0e0;
    }

    .cart-summary {
        display: flex;
        gap: 30px;
    }

    .cart-items-list {
        flex: 2;
    }

    .price-summary-container {
        flex: 1;
        margin-left: 20px;
    }

    .price-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .price-header {
        display: flex;
        justify-content: space-between;
        font-weight: bold;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ddd;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .order-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
    }

    .order-summary-title {
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 15px;
    }

    .order-summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .promo-code {
        display: flex;
        margin-top: 5px;
    }

    .promo-code input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px 0 0 3px;
    }

    .promo-code button {
        background: #0066cc;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 0 3px 3px 0;
        cursor: pointer;
    }

    .promo-code button:hover {
        background: #004499;
    }

    .checkout-button {
        display: block;
        background: #0066cc;
        color: white;
        text-align: center;
        padding: 12px;
        border-radius: 3px;
        text-decoration: none;
        font-weight: bold;
        margin-top: 20px;
    }

    .checkout-button:hover {
        background: #004499;
    }

    .continue-shopping {
        display: inline-block;
        margin-top: 20px;
        color: #0066cc;
        text-decoration: none;
    }

    .continue-shopping:hover {
        text-decoration: underline;
    }

    .sidebar .menu li a {
        color: white;
        text-decoration: none;
        display: block;
        width: 100%;
        height: 100%;
    }

    .sidebar .menu li a:hover {
        font-weight: bold;
        opacity: 1;
    }
    </style>
</head>
<body>

<div class="shopping-cart">
    <h1>Shopping Cart</h1>

    <div class="cart-summary">
        <div class="cart-items-list">
            <div class="section-title">PRODUCT DETAILS</div>

            <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $productId => $quantity): ?>
                    <?php if (isset($products[$productId])): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?= htmlspecialchars($products[$productId]['name']) ?></div>
                            <div class="cart-item-details">
                                <?= htmlspecialchars($products[$productId]['platform']) ?>
                                <form action="" method="post" style="display: inline; margin-left: 10px;">
                                    <input type="number" name="quantity" value="<?= $quantity ?>" min="1" class="quantity-input">
                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                    <button type="submit" name="update_quantity" class="update-button">Update</button>
                                </form>
                                <form action="" method="post" style="display: inline; margin-left: 10px;">
                                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                                    <button type="submit" name="remove_from_cart" class="remove-button">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <div class="price-summary-container">
            <div class="price-summary">
                <div class="price-header">
                    <span>QUANTITY</span>
                    <span>PRICE</span>
                    <span>TOTAL</span>
                </div>
                
                <?php foreach ($_SESSION['cart'] as $productId => $quantity): ?>
                    <?php if (isset($products[$productId])): ?>
                    <div class="price-row">
                        <span><?= $quantity ?></span>
                        <span>₱<?= number_format($products[$productId]['price'], 2) ?></span>
                        <span>₱<?= number_format($products[$productId]['price'] * $quantity, 2) ?></span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="order-summary">
                <div class="order-summary-title">Order Summary</div>
                
                <div class="order-summary-item">
                    <span>PROMO CODE</span>
                </div>
                <form class="promo-code" action="" method="post">
                    <input type="text" name="promo_code" placeholder="Enter your code">
                    <button type="submit" name="apply_promo">APPLY</button>
                </form>
                
                <a href="HECKOUT" target="_blank" class="checkout-button">CHECKOUT</a>
            </div> 
        </div>
    </div>

    <a href="index.php" class="continue-shopping">Continue Shopping</a>
</div>

</body>
</html>