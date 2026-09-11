<?php

require_once __DIR__ . '/vendor/autoload.php';

use MachinesSMM\Api;

// Initialize API client
// API key can be passed as parameter or read from MACHINES_SMM_API_KEY environment variable
try {
    $api = new Api(debug: true); // Enable debug logging
    
    // ========================================
    // Get Services
    // ========================================
    echo "=== Getting Services ===\n";
    $services = $api->services();
    print_r($services);
    
    // ========================================
    // Get Balance
    // ========================================
    echo "\n=== Getting Balance ===\n";
    $balance = $api->balance();
    print_r($balance);
    
    // ========================================
    // Add Order - Basic
    // ========================================
    echo "\n=== Adding Order (Basic) ===\n";
    $order = $api->order([
        'service' => 1,
        'link' => 'http://example.com/test',
        'quantity' => 100,
        'runs' => 2,
        'interval' => 5
    ]);
    print_r($order);
    $orderId = $order->order ?? null;
    
    // ========================================
    // Add Order - Custom Comments
    // ========================================
    echo "\n=== Adding Order (Custom Comments) ===\n";
    $order = $api->order([
        'service' => 1,
        'link' => 'http://example.com/test',
        'comments' => "good pic\ngreat photo\n:)\n;)"
    ]);
    print_r($order);
    
    // ========================================
    // Add Order - Package
    // ========================================
    echo "\n=== Adding Order (Package) ===\n";
    $order = $api->order([
        'service' => 1,
        'link' => 'http://example.com/test'
    ]);
    print_r($order);
    
    // ========================================
    // Add Order - Drip-feed
    // ========================================
    echo "\n=== Adding Order (Drip-feed) ===\n";
    $order = $api->order([
        'service' => 1,
        'link' => 'http://example.com/test',
        'quantity' => 100,
        'runs' => 10,
        'interval' => 60
    ]);
    print_r($order);
    
    // ========================================
    // Add Order - Subscriptions (Old posts only)
    // ========================================
    echo "\n=== Adding Order (Subscriptions - Old Posts Only) ===\n";
    $order = $api->order([
        'service' => 1,
        'username' => 'username',
        'min' => 100,
        'max' => 110,
        'posts' => 0,
        'delay' => 30,
        'expiry' => '11/11/2022'
    ]);
    print_r($order);
    
    // ========================================
    // Add Order - Subscriptions (Unlimited new posts + 5 old posts)
    // ========================================
    echo "\n=== Adding Order (Subscriptions - Unlimited New + 5 Old Posts) ===\n";
    $order = $api->order([
        'service' => 1,
        'username' => 'username',
        'min' => 100,
        'max' => 110,
        'old_posts' => 5,
        'delay' => 30,
        'expiry' => '11/11/2022'
    ]);
    print_r($order);
    
    // ========================================
    // Add Order - Comment Likes
    // ========================================
    echo "\n=== Adding Order (Comment Likes) ===\n";
    $order = $api->order([
        'service' => 1,
        'link' => 'http://example.com/test',
        'quantity' => 100,
        'username' => 'test'
    ]);
    print_r($order);
    
    // ========================================
    // Add Order - Poll
    // ========================================
    echo "\n=== Adding Order (Poll) ===\n";
    $order = $api->order([
        'service' => 1,
        'link' => 'http://example.com/test',
        'quantity' => 100,
        'answer_number' => '7'
    ]);
    print_r($order);
    
    // ========================================
    // Get Order Status
    // ========================================
    if ($orderId) {
        echo "\n=== Getting Single Order Status ===\n";
        $status = $api->status($orderId);
        print_r($status);
    }
    
    // ========================================
    // Get Multiple Orders Status
    // ========================================
    echo "\n=== Getting Multiple Orders Status ===\n";
    $statuses = $api->multiStatus([1, 2, 3]);
    print_r($statuses);
    
    // ========================================
    // Refill Order
    // ========================================
    echo "\n=== Refilling Order ===\n";
    $refill = $api->refill(1);
    print_r($refill);
    
    // ========================================
    // Refill Multiple Orders
    // ========================================
    echo "\n=== Refilling Multiple Orders ===\n";
    $refills = $api->multiRefill([1, 2]);
    print_r($refills);
    
    // Extract refill IDs from response
    $refillIds = [];
    if (isset($refills->refill)) {
        $refillIds = [$refills->refill];
    }
    
    // ========================================
    // Get Refill Status
    // ========================================
    if (!empty($refillIds)) {
        echo "\n=== Getting Refill Status ===\n";
        $refillStatus = $api->refillStatus($refillIds[0]);
        print_r($refillStatus);
    }
    
    // ========================================
    // Get Multiple Refill Statuses
    // ========================================
    if (!empty($refillIds)) {
        echo "\n=== Getting Multiple Refill Statuses ===\n";
        $refillStatuses = $api->multiRefillStatus($refillIds);
        print_r($refillStatuses);
    }
    
    // ========================================
    // Cancel Orders
    // ========================================
    echo "\n=== Canceling Orders ===\n";
    $cancelled = $api->cancel([1, 2]);
    print_r($cancelled);
    
    // ========================================
    // Set Custom Timeout
    // ========================================
    echo "\n=== Using Custom Timeout ===\n";
    $api->setTimeout(60); // Set 60 second timeout
    $balance = $api->balance();
    print_r($balance);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
