document.addEventListener("DOMContentLoaded", () => {
    const searchBar = document.getElementById("search-bar");
    const statusFilter = document.getElementById("status-filter");
    const orderTable = document.getElementById("order-table");

    // Automatically select "Pending" in the status filter
    if (statusFilter) {
        statusFilter.value = "In Process";
    }

    // Run filterOrders initially to apply the default filter
    filterOrders();

    searchBar.addEventListener("input", filterOrders);
    statusFilter.addEventListener("change", filterOrders);

    function filterOrders() {
        const searchValue = searchBar.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const rows = orderTable.querySelectorAll("tr");

        rows.forEach((row) => {
            const orderID = row.cells[0].textContent.toLowerCase();
            const userID = row.cells[1].textContent.toLowerCase();
            const status = row.cells[5].textContent.toLowerCase();

            const matchesSearch = 
                orderID.includes(searchValue) || userID.includes(searchValue);
            const matchesStatus = 
                !statusValue || status.includes(statusValue);

            if (matchesSearch && matchesStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
});
