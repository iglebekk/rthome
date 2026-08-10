<?php

return [
    'title' => 'Positions',
    'description' => 'Prioritize club responsibilities and make open roles visible.',
    'create_title' => 'Create a position',
    'create_description' => 'Define a position with an optional member, priority and term.',
    'edit_title' => 'Edit position',
    'edit_description' => 'Update responsibility, optional member, order or term.',
    'fields' => ['name' => 'Position name', 'member' => 'Member (optional)', 'sort_order' => 'Priority order (optional)', 'start_date' => 'Start date', 'end_date' => 'End date'],
    'select_member' => 'No member assigned',
    'unfilled' => 'Unfilled',
    'period' => ['open' => 'Open'],
    'empty' => 'No positions yet',
    'empty_description' => 'Create positions to clarify how your club is organized.',
    'actions' => ['create' => 'Create position', 'edit' => 'Edit position', 'delete' => 'Delete position'],
    'delete_title' => 'Delete :name?',
    'delete_description' => 'This removes the position permanently.',
    'messages' => ['created' => 'Position created.', 'updated' => 'Position updated.', 'deleted' => 'Position deleted.'],
];
