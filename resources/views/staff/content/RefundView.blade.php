@extends('staff.dashboard.StaffMain')

@section('content')

<style>
    td {
        text-align: center;
    }
    th {
        text-align: center;
    }
    .filter-container {
        margin-bottom: 1rem;
    }
</style>

<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <h1 class="text-4xl font-semibold mb-6 border-b border-gray pb-2">Replacement Report Overview</h1>

    <!-- Filter Section -->
    <div class="filter-container flex justify-start gap-2 items-center mb-2">      

        <!-- Date Filter -->
        <div class="flex items-center">
            <input type="date" id="dateInput" class="p-2 border rounded" onchange="filterTable()">
        </div>

        <!-- Status Filter -->
        <div class="flex items-center">
            <select id="statusInput" class="p-2 border rounded" onchange="filterTable()">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Completed - with changes">Completed - with changes</option>
                <option value="Completed - no changes">Completed - no changes</option>
            </select>
        </div>

        <div class="flex items-center">
            <input type="text" id="searchInput" class="p-2 w-500 border rounded" placeholder="Search by reference or user" onkeyup="filterTable()">
        </div>

    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm" id="refundTable">
            <thead class="bg-gray-100 text-gray-700 text-sm leading-normal">
                <tr>
                    <th class="py-3 px-4">Reference</th>
                    <th class="py-3 px-4">User</th>
                    <th class="py-3 px-4">Created</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach ($refunds as $refund)
                    <tr class="border-b hover:bg-gray-100 refundRow">
                        <td class="py-3 px-4">
                            {{ $refund->orderReference?->reference_id ?? 'ORD000' . $refund->refund_id }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="text-gray-700">{{ $refund->user->name ?? 'Unknown' }}</span>
                        </td>
                        <td class="py-3 px-4">{{ $refund->created_at->format('F d, Y \a\t h:i A') }}</td>
                        <td class="py-3 px-4">
                        @php
                        $status = $refund->overall_status;
                            $badgeClasses = match ($status) {
                                'Pending' => 'bg-yellow-600 text-white',
                                'Completed - with changes' => 'bg-green-600 text-green-800',
                                'Completed - no changes' => 'bg-blue-600 text-blue-800',
                                default => 'bg-gray-600 text-white-0',
                            };
                        @endphp

                        <span class="px-3 py-1 rounded-full text-sm text-white {{ $badgeClasses }}">
                            {{ $status }}
                        </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('refund.view.details', ['order_id' => $refund->order_id, 'reference_id' => $refund->orderReference?->reference_id ?? 'N/A']) }}" class="text-white hover:text-blue-800 bg-blue-500 p-2 rounded-lg">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $refunds->links() }}
        </div>
    </div>
</div>

<script>
    function filterTable() {
        let searchInput = document.getElementById('searchInput').value.toLowerCase();
        let dateInput = document.getElementById('dateInput').value;
        let statusInput = document.getElementById('statusInput').value;
        let tableRows = document.querySelectorAll('.refundRow');

        tableRows.forEach(function(row) {
            let referenceCell = row.cells[0].innerText.toLowerCase();
            let userCell = row.cells[1].innerText.toLowerCase();
            let dateCell = row.cells[2].innerText;
            let statusCell = row.cells[3].innerText.toLowerCase();

            let showRow = true;

            // Search filter (reference and user)
            if (searchInput && !referenceCell.includes(searchInput) && !userCell.includes(searchInput)) {
                showRow = false;
            }

            // Date filter
            if (dateInput && !dateCell.includes(dateInput)) {
                showRow = false;
            }

            // Status filter
            if (statusInput && !statusCell.includes(statusInput.toLowerCase())) {
                showRow = false;
            }

            // Show or hide row based on filters
            if (showRow) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

@endsection

@section('scripts')

@endsection
