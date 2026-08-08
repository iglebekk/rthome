<?php

return [
    'create' => [
        'title' => 'Create a club',
        'description' => 'Give your club a name. You will become its first member.',
        'submit' => 'Create club',
    ],
    'settings' => [
        'title' => 'Club settings',
        'description' => 'Update the club name or permanently remove the workspace.',
        'name' => 'Club name',
        'danger_title' => 'Delete this club',
        'danger_description' => 'This permanently deletes every member, position, event and uploaded event image.',
        'confirm_label' => 'Type the club name to confirm',
        'delete' => 'Delete club permanently',
    ],
    'index' => [
        'title' => 'Your clubs',
        'description' => 'Choose a club to open its dashboard.',
        'empty_title' => 'You do not have any clubs yet',
        'empty_description' => 'Create your first club to get started.',
    ],
    'actions' => [
        'create' => 'Create a club',
        'create_another' => 'Create another club',
    ],
    'messages' => [
        'created' => 'Club created.',
        'updated' => 'Club settings updated.',
        'deleted' => 'Club deleted.',
        'deleted_with_last_member' => 'The final member and club were deleted.',
    ],
];
