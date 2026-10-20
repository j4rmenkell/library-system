<?php

return [
    // How long a reader keeps a book
    'loan_days' => 14,

    // Pending + borrowed books a reader may have at once
    'max_active_loans' => 3,

    // The admin account seeded in production. Read here rather than in the
    // seeder, because env() returns null once the config is cached.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Johann Batiancila'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
