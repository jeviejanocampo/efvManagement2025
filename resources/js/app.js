// Import Chart.js
import Chart from 'chart.js/auto';

// Select the canvas element
const ctx = document.getElementById('myChart');

// Create a simple bar chart
if (ctx) {
    const myChart = new Chart(ctx, {
        type: 'bar', // Chart type (bar, line, pie, etc.)
        data: {
            labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'], // X-axis labels
            datasets: [{
                label: '# of Votes',
                data: [12, 19, 3, 5, 2, 3], // Data for the chart
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
