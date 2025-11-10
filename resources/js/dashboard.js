// Dashboard Charts & Interactions
import Chart from 'chart.js/auto';

// Sentiment Pie Chart
export function initSentimentChart(elementId, data) {
    const ctx = document.getElementById(elementId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(data),
            datasets: [{
                data: Object.values(data),
                backgroundColor: [
                    '#10b981', // positive - green
                    '#ef4444', // negative - red
                    '#f59e0b', // neutral - amber
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Sentiment Distribution'
                }
            }
        }
    });
}

// Platform Bar Chart
export function initPlatformChart(elementId, data) {
    const ctx = document.getElementById(elementId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(data),
            datasets: [{
                label: 'Posts by Platform',
                data: Object.values(data),
                backgroundColor: [
                    '#ef4444', // youtube - red
                    '#3b82f6', // twitter - blue
                    '#ec4899', // instagram - pink
                    '#000000', // tiktok - black
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Auto-refresh data (optional)
export function setupAutoRefresh(intervalMinutes = 5) {
    setInterval(async () => {
        try {
            const response = await fetch('/api/posts/stats');
            const data = await response.json();
            // Update stats cards
            updateStatsCards(data);
        } catch (error) {
            console.error('Auto-refresh failed:', error);
        }
    }, intervalMinutes * 60 * 1000);
}

function updateStatsCards(data) {
    if (data.total) document.getElementById('stat-total').textContent = data.total;
    if (data.positive) document.getElementById('stat-positive').textContent = data.positive;
    if (data.negative) document.getElementById('stat-negative').textContent = data.negative;
    if (data.neutral) document.getElementById('stat-neutral').textContent = data.neutral;
}
