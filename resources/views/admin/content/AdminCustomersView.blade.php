@extends('admin.dashboard.adminDashboard')

@section('content')
<div class="bg-white p-4 shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Customer List</h2>

    {{-- Filter Controls --}}
    <div class="flex flex-wrap gap-4 mb-4">
        <input type="text" id="searchInput" placeholder="Search..." class="border px-2 py-2 rounded w-full sm:w-1/3" onkeyup="filterTable()">

        <select id="statusFilter" class="border px-2 py-2 rounded w-full sm:w-1/5" onchange="filterTable()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

        <input type="date" id="dateFilter" class="border px-2 py-2 rounded w-full sm:w-1/5" onchange="filterTable()">
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <p class="italic text-sm text-gray-600 mb-2">
            * To set a customer's status, click the <span class="font-semibold">Active</span> or <span class="font-semibold">Inactive</span> button in the status column.
        </p>

        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-4 py-2 border-b">Name</th>
                    <th class="px-4 py-2 border-b">Email</th>
                    <th class="px-4 py-2 border-b">Phone</th>
                    <th class="px-4 py-2 border-b">Alt. Phone</th>
                    <th class="px-4 py-2 border-b">Address</th>
                    <th class="px-4 py-2 border-b">City</th>
                    <th class="px-4 py-2 border-b">Created</th>
                    <th class="px-4 py-2 border-b">Status</th>
                </tr>
            </thead>
            <tbody id="customerTable">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $user->full_name }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->email }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->phone_number }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->second_phone_number }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->address }}</td>
                        <td class="px-4 py-2 border-b">{{ $user->city }}</td>
                        <td class="px-4 py-2 border-b created-date">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 border-b">
                            <button onclick="openStatusModal({{ $user->id }}, '{{ $user->status }}')" 
                                class="text-white text-xs font-bold px-2 py-1 rounded focus:outline-none 
                                    {{ $user->status === 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                                {{ ucfirst($user->status) }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>

<div id="statusModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white rounded-lg shadow-md p-6 w-72">
        <h3 class="text-lg font-semibold mb-4">Change Customer Status</h3>
        <input type="hidden" id="modalCustomerId">
        <div class="flex justify-between space-x-4">
            <button onclick="submitStatus('active')" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded w-1/2">Active</button>
            <button onclick="submitStatus('inactive')" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded w-1/2">Deactivate</button>
        </div>
        <button onclick="closeStatusModal()" class="mt-4 text-sm text-gray-600 underline w-full text-center">Cancel</button>
    </div>
</div>

<script>
        function openStatusModal(customerId, currentStatus) {
        document.getElementById('modalCustomerId').value = customerId;
        document.getElementById('statusModal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    function submitStatus(newStatus) {
        const customerId = document.getElementById('modalCustomerId').value;

        if (!confirm(`Are you sure you want to set status to "${newStatus}"?`)) {
            return;
        }

        fetch('{{ route('admin.customer.updateStatus') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_id: customerId,
                status: newStatus
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload to reflect changes
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
        });

        closeStatusModal();
    }

</script>

<script>
    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const statusValue = document.getElementById('statusFilter').value.toLowerCase();
        const dateValue = document.getElementById('dateFilter').value;

        const rows = document.querySelectorAll('#customerTable tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const fullText = Array.from(cells).map(td => td.innerText.toLowerCase()).join(' ');
            const statusText = cells[8].innerText.toLowerCase();
            const createdDate = cells[7].innerText.trim();

            const matchesSearch = fullText.includes(searchValue);
            const matchesStatus = !statusValue || statusText === statusValue;
            const matchesDate = !dateValue || createdDate === dateValue;

            if (matchesSearch && matchesStatus && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
