@extends('admin.dashboard.adminDashboard')

@section('content')

  <a href="{{ url('/admin/customers-view') }}" class="text-black hover:text-blue-800 text-lg inline-flex font-bold items-center mb-4">
        <i class="fas fa-arrow-left mr-2"></i>  
    </a>

<div class="bg-white p-4 shadow-md w-full">

    <label class="font-bold text-2xl">Edit Customer</label>

    <form id="editCustomerForm">
        @csrf
        <input type="hidden" name="id" value="{{ $customer->id }}">

        <div class="mb-4">
            <label class="block font-medium">Full Name</label>
            <input type="text" name="full_name" value="{{ $customer->full_name }}" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Email</label>
            <input type="email" name="email" value="{{ $customer->email }}" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Phone Number</label>
            <input type="text" name="phone_number" value="{{ $customer->phone_number }}" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Second Phone Number</label>
            <input type="text" name="second_phone_number" value="{{ $customer->second_phone_number }}" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Address</label>
            <input type="text" name="address" value="{{ $customer->address }}" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">City</label>
            <input type="text" name="city" value="{{ $customer->city }}" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Password <span class="text-gray-500 text-sm">(leave blank to keep current)</span></label>
            <input type="password" name="password" class="border px-3 py-2 rounded w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Status</label>
            <select name="status" class="border px-3 py-2 rounded w-full">
                <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="text-right">
            <button type="button" onclick="AdminManageCustomer()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Save</button>
        </div>
    </form>
</div>

<script>
    function AdminManageCustomer() {
        const form = document.getElementById('editCustomerForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.full_name || !data.email || !data.phone_number || !data.address || !data.city) {
            alert("Please fill in all required fields.");
            return;
        }

        fetch("{{ route('admin.customer.manage.update') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                alert('Customer updated successfully!');
                location.reload(); // ✅ Just reload the same page
            } else {
                alert('Error: ' + response.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Something went wrong while updating the customer.');
        });
    }
</script>
@endsection
