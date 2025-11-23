<?php

Hook::add('navbar_items', function() {
    if (isset($_SESSION['user'])) {
        echo '<li class="nav-item"><a class="nav-link" href="/support"><i class="fas fa-life-ring me-1"></i> Support</a></li>';
    }
});

Hook::add('register_routes', function() {
    return [
        'support' => [
            'controller' => 'Modules\Support\Controllers\TicketController',
            'method' => 'index'
        ],
        'support/new' => [
            'controller' => 'Modules\Support\Controllers\TicketController',
            'method' => 'create'
        ],
        'support/view/(\d+)' => [
            'controller' => 'Modules\Support\Controllers\TicketController',
            'method' => 'view'
        ]
    ];
});

// Auto-install tables if not exist (Simplified for this context)
Hook::add('system_init', function() {
    $db = Database::getInstance();
    $db->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        status ENUM('open', 'answered', 'closed') DEFAULT 'open',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS ticket_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
});
