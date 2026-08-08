<?php

return [
    'title' => 'Positions',
    'description' => 'Prioritize club responsibilities and make open roles visible.',
    'create_title' => 'Create a position',
    'create_description' => 'Assign a member, priority and an optional term.',
    'edit_title' => 'Edit position',
    'edit_description' => 'Update responsibility, order or term.',
    'fields' => ['name' => 'Position name', 'member' => 'Member', 'sort_order' => 'Priority order', 'start_date' => 'Start date', 'end_date' => 'End date'],
    'select_member' => 'Choose a member',
    'unfilled' => 'Unfilled',
    'period' => ['open' => 'Open'],
    'empty' => 'No positions yet',
    'empty_description' => 'Create positions to clarify how your club is organized.',
    'actions' => ['create' => 'Create position', 'edit' => 'Edit position', 'delete' => 'Delete position'],
    'delete_title' => 'Delete :name?',
    'delete_description' => 'This removes the position permanently.',
    'messages' => ['created' => 'Position created.', 'updated' => 'Position updated.', 'deleted' => 'Position deleted.'],
];
