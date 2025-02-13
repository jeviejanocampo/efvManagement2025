// public/js/sales-chart.js

// Wait for DOM content to load
document.addEventListener('DOMContentLoaded', () => {
    // Line Chart: Sales Overview
    const lineCtx = document.getElementById('salesLineChart');

    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: salesMonths, // Labels for X-axis (Months)
                datasets: [{
                    label: 'Monthly Sales (₱)',
                    data: salesData, // Sales data for Y-axis
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.4, // Smooth curve
                    fill: true, // Fill below the line
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

    // Donut Chart: Total Orders
    const ordersCtx = document.getElementById('totalOrdersChart');
    if (ordersCtx) {
        new Chart(ordersCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [120, 30, 10], // Example order stats
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)', // Green
                        'rgba(255, 206, 86, 0.6)', // Yellow
                        'rgba(255, 99, 132, 0.6)'  // Red
                    ],
                    hoverBackgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
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

