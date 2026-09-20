<x-layouts.app :title="__('invoices.edit_title')" :$club>
    <x-app.page-header :title="__('invoices.edit_title')" :description="__('invoices.edit_description')" :eyebrow="$club->name" />
    <x-invoice-creations.form
        :$club
        :$members
        :$products
        :$creation
        :$submissionToken
        :$invoiceDate
        :$dueDate
        :$selectedMembers
        :$selectedLines
    />
</x-layouts.app>
