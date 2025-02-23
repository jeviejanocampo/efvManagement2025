document.addEventListener("DOMContentLoaded", function() {
    let searchInput = document.getElementById("search-bar");
    let categoryFilter = document.getElementById("category-filter");
    let brandFilter = document.getElementById("brand-filter");
    let statusFilter = document.getElementById("status-filter");
    let minPriceInput = document.getElementById("min-price");
    let maxPriceInput = document.getElementById("max-price");
    let applyPriceFilterBtn = document.getElementById("apply-price-filter");
    let clearPriceBtn = document.getElementById("clear-price");

    let tableRows = document.querySelectorAll("#order-table tr");

    function filterTable() {
        let searchValue = searchInput.value.toLowerCase();
        let selectedCategory = categoryFilter.value.toLowerCase();
        let selectedBrand = brandFilter.value.toLowerCase();
        let selectedStatus = statusFilter.value.toLowerCase();
        let minPrice = parseFloat(minPriceInput.value) || 0;
        let maxPrice = parseFloat(maxPriceInput.value) || Infinity;

        tableRows.forEach(row => {
            let productName = row.getAttribute("data-name").toLowerCase();
            let productCategory = row.getAttribute("data-category").toLowerCase();
            let productBrand = row.getAttribute("data-brand").toLowerCase();
            let productStatus = row.querySelector("td:nth-child(8)").textContent.trim().toLowerCase();
            let productPrice = parseFloat(row.querySelector("td:nth-child(5)").textContent.trim()) || 0;

            let matchesSearch = searchValue === "" || productName.includes(searchValue);
            let matchesCategory = selectedCategory === "" || productCategory === selectedCategory;
            let matchesBrand = selectedBrand === "" || productBrand === selectedBrand;
            let matchesStatus = selectedStatus === "" || productStatus === selectedStatus;
            let matchesPrice = productPrice >= minPrice && productPrice <= maxPrice;

            if (matchesSearch && matchesCategory && matchesBrand && matchesStatus && matchesPrice) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener("input", filterTable);
    categoryFilter.addEventListener("change", filterTable);
    brandFilter.addEventListener("change", filterTable);
    statusFilter.addEventListener("change", filterTable);
    applyPriceFilterBtn.addEventListener("click", filterTable);

    clearPriceBtn.addEventListener("click", function() {
        minPriceInput.value = "";
        maxPriceInput.value = "";
        filterTable();
    });
});
