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
        'invitations' => [
            'title' => 'Membership invitations',
            'description' => 'Create shareable links that let people join this club.',
            'name' => 'Link name (optional)',
            'duration' => 'Link lifetime',
            'durations' => ['1' => '1 day', '7' => '7 days', '30' => '30 days', '90' => '90 days'],
            'create' => 'Create invitation link',
            'default_name' => 'Club invitation',
            'copy' => 'Copy link',
            'copied' => 'Link copied',
            'status_active' => 'Active until :date',
            'status_expired' => 'Expired :date',
            'status_revoked' => 'Deactivated :date',
            'deactivate' => 'Deactivate',
            'empty' => 'No invitation links yet.',
            'empty_description' => 'Create a link to invite new members.',
            'join_title' => 'Join :club',
            'join_description' => 'You have been invited to join this club.',
            'join_as' => 'You are signed in as :name (:email).',
            'join_confirm' => 'Join club',
            'sign_in' => 'Sign in to continue',
            'register' => 'Create an account to continue',
            'verify_required' => 'Verify your email address before joining this club.',
            'messages' => [
                'joined' => 'You joined the club.',
                'already_member' => 'You are already a member of this club.',
                'revoked' => 'Invitation link deactivated.',
            ],
        ],
        'events' => [
            'title' => 'Events',
            'description' => 'Import events in bulk using a JSON object.',
            'import' => [
                'title' => 'Import events',
                'description' => 'Paste a JSON object containing an events array. Imported events are always added as new events; existing events are not replaced.',
                'fields' => 'Each event can include name, location, starts_at, ends_at, registration_url and short_description. Images are not supported.',
                'timezone' => 'Use ISO 8601 dates with an explicit timezone offset, for example 2030-01-15T18:00:00+01:00. Dates are stored in UTC.',
                'json_label' => 'Events JSON',
                'agent_prompt_title' => 'Prompt for an AI agent',
                'agent_prompt_trigger' => 'Get AI prompt',
                'agent_prompt_label' => 'Copy this complete instruction',
                'agent_prompt' => <<<'PROMPT'
Create JSON for importing events into a club management system.

Return only valid JSON, without Markdown code fences or any explanation.

Use this exact structure:
{
  "events": [
    {
      "name": "Annual meetup",
      "location": "Main Hall",
      "starts_at": "2030-01-15T18:00:00+01:00",
      "ends_at": "2030-01-15T20:00:00+01:00",
      "registration_url": "https://example.com/register",
      "short_description": "A short event introduction."
    }
  ]
}

Rules:
- The root value must be an object with an events array.
- Create one object per event.
- name, starts_at and ends_at are required.
- location, registration_url and short_description are optional. Omit optional fields when there is no value.
- Do not include images or any fields other than name, location, starts_at, ends_at, registration_url and short_description.
- Use ISO 8601 date-time strings with seconds and an explicit timezone, for example 2030-01-15T18:00:00+01:00 or 2030-01-15T17:00:00Z.
- ends_at must be later than starts_at.
- Use at most 100 events.
- Preserve the supplied names, descriptions, locations and URLs accurately.

Now convert the event information I provide into the required JSON structure.
PROMPT,
                'close' => 'Close',
                'submit' => 'Import events',
                'validation' => [
                    'invalid_json' => 'Enter a valid JSON object containing an events array.',
                    'events_array' => 'The events value must be an array.',
                ],
                'messages' => [
                    'imported' => '{1} One event imported.|[2,*] :count events imported.',
                ],
            ],
        ],
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
