<?php

return [
    'title' => 'Members',
    'description' => 'Search contact details and see who holds each position.',
    'create_title' => 'Add a member',
    'create_description' => 'Add contact details now. They can activate an account later.',
    'edit_title' => 'Edit member',
    'edit_description' => 'Keep contact information current.',
    'fields' => ['name' => 'Name', 'email' => 'Email address', 'phone' => 'Phone number'],
    'search_placeholder' => 'Search by name, email or phone',
    'positions' => 'Positions',
    'no_positions' => 'No positions',
    'linked_email' => 'This email belongs to an activated account and is managed from that user profile.',
    'empty' => 'No members found',
    'empty_description' => 'Add the first member or try a different search.',
    'actions' => ['create' => 'Add member', 'edit' => 'Edit member', 'delete' => 'Delete member'],
    'delete_title' => 'Delete :name?',
    'delete_description' => 'Their positions will become unfilled. If this is the final member, the entire club and all its data will also be deleted.',
    'messages' => ['created' => 'Member added.', 'updated' => 'Member updated.', 'deleted' => 'Member deleted.'],
];
