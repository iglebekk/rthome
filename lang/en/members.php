<?php

return [
    'title' => 'Members',
    'description' => 'Search contact details and see who holds each position.',
    'create_title' => 'Add a member',
    'create_description' => 'Add contact details now. They can activate an account later.',
    'edit_title' => 'Edit member',
    'edit_description' => 'Keep contact information current.',
    'fields' => ['name' => 'Name', 'email' => 'Email address', 'phone' => 'Phone number'],
    'invoice' => [
        'title' => 'Invoice settings',
        'description' => 'The company name is the recipient for a potential invoice.',
        'company_name' => 'Company name',
        'organization_number' => 'Organization number',
        'address' => 'Invoice address',
        'postal_code' => 'Postal code',
        'city' => 'City',
        'lookup' => 'Fetch from Brreg',
        'lookup_loading' => 'Fetching from Brreg…',
        'lookup_required' => 'Enter an organization number before fetching from Brreg.',
        'lookup_not_found' => 'No organization was found for this organization number.',
        'lookup_unavailable' => 'Brreg is currently unavailable. Try again or enter the details manually.',
        'lookup_confirmation' => 'Replace the current invoice details with the information from Brreg?',
    ],
    'search_placeholder' => 'Search by name, email or phone',
    'positions' => 'Positions',
    'no_positions' => 'No positions',
    'linked_email' => 'This email belongs to an activated account and is managed from that user profile.',
    'empty' => 'No members found',
    'empty_description' => 'Add the first member or try a different search.',
    'actions' => ['create' => 'Add member', 'edit' => 'Edit member', 'delete' => 'Delete member'],
    'delete_title' => 'Delete :name?',
    'delete_description' => 'Their positions will become unfilled. If this is the final member, the entire club and all its data will also be deleted.',
    'import' => [
        'title' => 'Import members',
        'description' => 'Paste a JSON object containing a members array. Existing members are matched by email and updated; new members are added.',
        'fields' => 'Each member needs a name and can include email and phone. Empty email and phone values keep existing contact details. Use at most 100 members.',
        'json_label' => 'Members JSON',
        'agent_prompt_title' => 'Prompt for an AI agent',
        'agent_prompt_trigger' => 'Get AI prompt',
        'agent_prompt_label' => 'Copy this complete instruction',
        'agent_prompt' => <<<'PROMPT'
Create JSON for importing members into a club management system.

Return only valid JSON, without Markdown code fences or any explanation.

Use this exact structure:
{
  "members": [
    {
      "name": "Taylor Smith",
      "email": "taylor@example.com",
      "phone": "+47 999 99 999"
    }
  ]
}

Rules:
- The root value must be an object with a members array.
- Create one object per member.
- name is required. email and phone are optional.
- An existing member with the same email will have their name and provided phone updated.
- Omit email or phone when there is no value; do not use empty strings to remove existing contact details.
- Do not repeat an email address in the same import.
- Do not include fields other than name, email and phone.
- Use at most 100 members.
- Preserve supplied names, email addresses and phone numbers accurately.

Now convert the member information I provide into the required JSON structure.
PROMPT,
        'close' => 'Close',
        'submit' => 'Import members',
        'validation' => [
            'invalid_json' => 'Enter a valid JSON object containing a members array.',
            'members_array' => 'The members value must be an array.',
            'duplicate_email' => 'Each email address may only appear once in an import.',
        ],
        'messages' => [
            'completed' => 'Import complete: :created created, :updated updated.',
        ],
    ],
    'messages' => ['created' => 'Member added.', 'updated' => 'Member updated.', 'deleted' => 'Member deleted.'],
];
