// public/js/sales-chart.js



document.addEventListener('DOMContentLoaded', () => {

    
    let salesLineChart; // Store chart instance globally

    const lineCtx = document.getElementById('salesLineChart');

    const dailySalesCtx = document.getElementById('dailySalesLineChart');


    // Function to initialize or update the chart
    function renderLineChart(labels, data) {
        if (salesLineChart) {
            salesLineChart.destroy(); // Destroy the previous chart instance
        }
        salesLineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: labels, // Dynamic labels for X-axis (Months)
                datasets: [{
                    label: 'Monthly Sales (₱)',
                    data: data, 
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.4, // Smooth curve
                    fill: true // Fill below the line
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Months',
                            font: { weight: 'bold' }
                        },
                        grid: { display: false }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Sales (₱)',
                            font: { weight: 'bold' }
                        },
                        grid: { color: 'rgba(200, 200, 200, 0.2)' },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: { display: true, position: 'top' }
                }
            }
        });
    }

    // Initial chart rendering
    if (lineCtx) {
        renderLineChart(salesMonths, salesData); // Render with existing dynamic data
    }

    // Date Range Filter Logic
    const dateRangeForm = document.getElementById('dateRangeForm');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    // Event listener for input changes to dynamically update the chart
    [startDateInput, endDateInput].forEach(input => {
        input.addEventListener('change', () => {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            // Skip if either date is invalid
            if (!startDate || !endDate || startDate > endDate) {
                return;
            }

            // Filter sales data based on the selected date range
            const filteredLabels = [];
            const filteredData = [];

            salesMonths.forEach((month, index) => {
                const currentDate = new Date(month); // Convert label to Date
                if (currentDate >= startDate && currentDate <= endDate) {
                    filteredLabels.push(salesMonths[index]); // Keep label
                    filteredData.push(salesData[index]); // Keep corresponding sales data
                }
            });

            // Update the chart with filtered data
            renderLineChart(filteredLabels, filteredData);
        });
    });

    // Clear Filter Logic
    const clearFilterBtn = document.createElement('button');
    clearFilterBtn.textContent = 'Clear Filter';
    clearFilterBtn.className = 'bg-gray-500 text-white px-2 py-1 text-sm rounded-md hover:bg-gray-600 ml-1';

    dateRangeForm.appendChild(clearFilterBtn);

    clearFilterBtn.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent default behavior
        startDateInput.value = ''; // Clear the start date
        endDateInput.value = ''; // Clear the end date

        // Reset the chart with original data
        renderLineChart(salesMonths, salesData);
    });

    
});

// Wait for DOM content to load
document.addEventListener('DOMContentLoaded', () => {   
    // Donut Chart: Total Orders
    const paymentCtx = document.getElementById('paymentMethodChart');
    if (paymentCtx) {
        new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: paymentLabels,
                datasets: [{
                    data: paymentValues,
                    backgroundColor: [
                        '#10B981', // Cash - green
                        '#6366F1', // GCASH - indigo
                        '#F59E0B', // PNB - amber
                        '#EF4444'  // Other - red
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        });
    }

    // Donut Chart: Total Sales
    const salesCtx = document.getElementById('totalSalesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'doughnut',
            data: {
                labels: ['In-Store'],
                datasets: [{
                    data: [60, 30, 10], // Example sales distribution
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)', // Blue
                        'rgba(153, 102, 255, 0.6)', // Purple
                        'rgba(255, 159, 64, 0.6)'  // Orange
                    ],
                    hoverBackgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Ensure the chart does not shrink
                plugins: {
                    legend: { display: false } // Disable the default legend
                }
            }
        });
    }

});

