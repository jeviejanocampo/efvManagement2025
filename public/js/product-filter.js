document.addEventListener("DOMContentLoaded", function() {
    let searchInput = document.getElementById("search-bar");
    let categoryFilter = document.getElementById("category-filter");
    let brandFilter = document.getElementById("brand-filter");
    let tableRows = document.querySelectorAll("#order-table tr");

    function filterTable() {
        let searchValue = searchInput.value.toLowerCase();
        let selectedCategory = categoryFilter.value.toLowerCase();
        let selectedBrand = brandFilter.value.toLowerCase();

        tableRows.forEach(row => {
            let productName = row.getAttribute("data-name").toLowerCase();
            let productCategory = row.getAttribute("data-category").toLowerCase();
            let productBrand = row.getAttribute("data-brand").toLowerCase();

            let matchesSearch = searchValue === "" || productName.includes(searchValue);
            let matchesCategory = selectedCategory === "" || productCategory === selectedCategory;
            let matchesBrand = selectedBrand === "" || productBrand === selectedBrand;

            if (matchesSearch && matchesCategory && matchesBrand) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener("input", filterTable);
    categoryFilter.addEventListener("change", filterTable);
    brandFilter.addEventListener("change", filterTable);

    let statusFilter = document.getElementById("status-filter");

    function filterTable() {
        let searchValue = searchInput.value.toLowerCase();
        let selectedCategory = categoryFilter.value.toLowerCase();
        let selectedBrand = brandFilter.value.toLowerCase();
        let selectedStatus = statusFilter.value.toLowerCase();

        tableRows.forEach(row => {
            let productName = row.getAttribute("data-name").toLowerCase();
            let productCategory = row.getAttribute("data-category").toLowerCase();
            let productBrand = row.getAttribute("data-brand").toLowerCase();
            let productStatus = row.querySelector("td:nth-child(8)").textContent.trim().toLowerCase();

            let matchesSearch = searchValue === "" || productName.includes(searchValue);
            let matchesCategory = selectedCategory === "" || productCategory === selectedCategory;
            let matchesBrand = selectedBrand === "" || productBrand === selectedBrand;
            let matchesStatus = selectedStatus === "" || productStatus === selectedStatus;

            if (matchesSearch && matchesCategory && matchesBrand && matchesStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    statusFilter.addEventListener("change", filterTable);

    

});
