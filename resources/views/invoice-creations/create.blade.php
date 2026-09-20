<x-layouts.app :title="__('invoices.create_title')" :$club>
    <x-app.page-header :title="__('invoices.create_title')" :description="__('invoices.create_description')" :eyebrow="$club->name" />
    <x-invoice-creations.form
        :$club
        :$members
        :$products
        :submission-token="$submissionToken"
        :invoice-date="$invoiceDate"
        :due-date="$dueDate"
        :$selectedMembers
        :$selectedLines
    />
</x-layouts.app>
