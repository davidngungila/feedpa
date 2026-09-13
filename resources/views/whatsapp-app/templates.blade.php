@extends('layouts.app')
@section('title', 'WhatsApp Templates')

@section('content')
<div class="space-y-4 max-w-4xl mx-auto">
    <h1 class="text-lg font-bold">WhatsApp Templates</h1>
    <div class="card p-5">
        <h3 class="text-xs font-bold mb-3">Create Template</h3>
        <form method="POST" action="{{ route('whatsapp-app.templates.store') }}" class="space-y-3">
            @csrf
            <input type="text" name="name" placeholder="Payment Confirmation" required class="w-full px-3 py-2 rounded-xl border text-sm">
            <input type="text" name="code" placeholder="payment_confirmation" required class="w-full px-3 py-2 rounded-xl border text-sm">
            <textarea name="content" rows="4" placeholder="Hello {customer_name}, We confirm receipt of TZS {amount}. Reference: {reference}" required class="w-full px-3 py-2 rounded-xl border text-sm"></textarea>
            <p class="text-[11px] text-primary-500">Variables: {customer_name} {amount} {reference} {date} {invoice_number} {account_number}</p>
            <button class="px-4 py-2 rounded-xl bg-primary-600 text-white text-xs font-bold">Create</button>
        </form>
    </div>
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Code</th><th>Variables</th><th>Status</th></tr></thead>
            <tbody class="divide-y">
                @foreach($templates as $t)
                <tr><td class="text-xs font-bold">{{ $t->name }}</td><td class="font-mono text-xs">{{ $t->code }}</td><td class="text-xs">{{ implode(', ', $t->variables ?? []) }}</td><td><span class="badge {{ $t->is_active?'badge-green':'badge-red' }}">{{ $t->is_active?'Active':'Inactive' }}</span></td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $templates->links() }}</div>
    </div>
</div>
@endsection
