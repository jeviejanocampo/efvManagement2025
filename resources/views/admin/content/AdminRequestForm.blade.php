@extends('admin.dashboard.adminDashboard')

@section('content')

<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

        <a href="{{ route('admin.refundRequests') }}" class="text-gray-600 hover:text-gray-900 flex items-center gap-2 mb-4 text-3">
            <i class="fa-solid fa-arrow-left"></i> 
        </a>
        
    <h1 class="text-3xl font-semibold border-b border-gray-300 pb-2">Add Replacement/Refund Form</h1>

    <!-- Refund Order Form -->
    <form action="{{ route('admin.refund.store') }}" method="POST" id="refundForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        
            <!-- Include Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

            <!-- Order ID with Searchable & Scrollable Dropdown -->
            <div>
                <label for="order_id" class="block text-gray-700">Order ID</label>
                <select name="order_id" id="order_id" class="w-full px-4 py-2 border rounded-md select2" required>
                    <option value="" disabled selected>Select Order</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->order_id }}" 
                            data-user-id="{{ $order->user_id }}" 
                            data-user-name="{{ $order->customer ? $order->customer->full_name : 'No customer found' }}">
                            {{ $order->order_id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Include jQuery and Select2 JS -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

            <!-- Initialize Select2 -->
            <script>
                $(document).ready(function() {
                    $('#order_id').select2({
                        placeholder: "Select Order",
                        allowClear: true
                    });

                    // Automatically update user_id field when an order is selected
                    $('#order_id').on('change', function() {
                        let selectedOrder = $(this).find(':selected');
                        let userId = selectedOrder.data('user-id');
                        let userName = selectedOrder.data('user-name');

                        if (userId) {
                            $('#user_display').val(userName + " (ID: " + userId + ")");
                            $('#user_id').val(userId);
                        } else {
                            $('#user_display').val("No customer found");
                            $('#user_id').val("");
                        }
                    });
                });
            </script>

            <!-- User ID (Auto-filled with Full Name & ID) -->
            <div>
                <label for="user_display" class="block text-gray-700">User</label>
                <input type="text" id="user_display" class="w-full px-4 py-2 border rounded-md bg-gray-200 cursor-not-allowed" readonly required>
                <input type="hidden" name="user_id" id="user_id">
            </div>

            <!-- Refund Reason -->
            <div>
                <label for="refund_reason" class="block text-gray-700">Refund Reason</label>
                <textarea name="refund_reason" id="refund_reason" class="w-full px-4 py-2 border rounded-md" required></textarea>
            </div>

            <!-- Processed By (Full Name & Role Display, User ID Stored) -->
            <div>
                <label for="processed_by_display" class="block text-gray-700">Processed By</label>
                <input type="text" id="processed_by_display" class="w-full px-4 py-2 border rounded-md bg-gray-200 cursor-not-allowed" 
                    value="{{ auth()->user()->name }} ({{ auth()->user()->role }})" readonly>
                <input type="hidden" name="processed_by" value="{{ auth()->id() }}">
            </div>

            <!-- Refund Method -->
            <div>
                <label for="refund_method" class="block text-gray-700">Refund Method</label>
                <input type="text" name="refund_method" id="refund_method" class="w-full px-4 py-2 border rounded-md" required>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-gray-700">Status</label>
                <select name="status" id="status" class="w-full px-4 py-2 border rounded-md" required>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="mt-4 col-span-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700">
                    Submit
                </button>
            </div>

        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("refundForm");

            form.addEventListener("submit", function (event) {
                event.preventDefault(); // Prevent default form submission

                // Show confirmation dialog
                const isConfirmed = confirm("Are you sure you want to submit this refund request?");
                
                if (isConfirmed) {
                    form.submit(); // Proceed with submission if confirmed
                }
            });

            // Success message (after redirection)
            @if(session('success'))
                alert("{{ session('success') }}");
            @endif

            // Error message (if any validation failed and redirected back)
            @if($errors->any())
                let errorMessage = "Something went wrong:\n";
                @foreach ($errors->all() as $error)
                    errorMessage += "- {{ $error }}\n";
                @endforeach
                alert(errorMessage);
            @endif
        });
        </script>


    </form>

</div>

@endsection
