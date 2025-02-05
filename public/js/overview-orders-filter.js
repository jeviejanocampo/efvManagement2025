document.addEventListener('DOMContentLoaded', function() {
    // Get all filter elements
    const searchInput = document.getElementById('search-bar');
    const statusFilter = document.getElementById('status-filter');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const orderTable = document.getElementById('order-table');
    
    // Event listener for filters
    searchInput.addEventListener('input', filterOrders);
    statusFilter.addEventListener('change', filterOrders);
    startDateInput.addEventListener('change', filterOrders);
    endDateInput.addEventListener('change', filterOrders);

    function filterOrders() {
        const searchValue = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
        const endDate = endDateInput.value ? new Date(endDateInput.value) : null;

        const rows = orderTable.querySelectorAll('tr');

        rows.forEach(row => {
            const orderId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const userId = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const status = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
            const createdAt = new Date(row.querySelector('td:nth-child(5)').textContent);
            
            let matchesSearch = orderId.includes(searchValue) || userId.includes(searchValue);
            let matchesStatus = selectedStatus === '' || status.includes(selectedStatus);
            let matchesDateRange = true;

            // Apply date range filter
            if (startDate) {
                matchesDateRange = createdAt >= startDate;
            }
            if (endDate) {
                matchesDateRange = matchesDateRange && createdAt <= endDate;
            }

            // Show or hide row based on filters
            if (matchesSearch && matchesStatus && matchesDateRange) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});
