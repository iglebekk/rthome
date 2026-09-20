<?php

return [
    'title' => 'Products',
    'description' => 'Manage the products and prices you use on invoices.',
    'create_title' => 'Create product',
    'create_description' => 'Add a reusable product for future invoices.',
    'edit_title' => 'Edit product',
    'edit_description' => 'Update the product details. Existing invoice lines are unchanged.',
    'empty' => 'No products yet',
    'empty_description' => 'Create your first product to start creating invoices.',
    'fields' => [
        'name' => 'Product name',
        'description' => 'Description',
        'price' => 'Price including VAT',
        'vat_rate' => 'VAT treatment',
        'active' => 'Available for new invoices',
    ],
    'vat_rates' => [
        '25' => '25% VAT',
        '15' => '15% VAT',
        '12' => '12% VAT',
        'exempt' => 'VAT exempt',
        'outside_scope' => 'Outside VAT scope',
    ],
    'actions' => [
        'create' => 'Create product',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],
    'delete_title' => 'Delete :name?',
    'delete_description' => 'This product will be unavailable for new invoices. Existing invoices are unchanged.',
    'used_by_draft' => 'Used by a draft',
    'messages' => [
        'created' => 'Product created.',
        'updated' => 'Product updated.',
        'deleted' => 'Product deleted.',
    ],
];
