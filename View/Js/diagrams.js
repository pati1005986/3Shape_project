// Mejora de los gráficos con Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si chartData está definido
    if (typeof chartData === 'undefined') {
        console.error('No chart data available');
        return;
    }

    // Configuración de colores
    const colors = {
        status: ['#4CAF50', '#FFC107', '#F44336', '#2196F3', '#9C27B0'],
        country: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'],
        expired: ['#27ae60', '#e74c3c']
    };

    // Procesar datos de equipos
    function processStatusData(data) {
        const statusCounts = {};
        
        data.forEach(item => {
            const status = item.user_status || 'Unknown';
            statusCounts[status] = (statusCounts[status] || 0) + 1;
        });
        
        return {
            labels: Object.keys(statusCounts),
            data: Object.values(statusCounts)
        };
    }

    // Crear gráfico de estado de equipos
    function createStatusChart() {
        const ctx = document.getElementById('statusChart').getContext('2d');
        const processedData = processStatusData(chartData.status);
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: processedData.labels,
                datasets: [{
                    data: processedData.data,
                    backgroundColor: colors.status,
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Equipment Status Distribution',
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        padding: {
                            top: 20,
                            bottom: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000
                }
            }
        });
    }

    // Crear gráfico de países
    function createCountryChart() {
        const ctx = document.getElementById('countryChart').getContext('2d');
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.country.map(item => item.purchase_country),
                datasets: [{
                    label: 'Number of Assets',
                    data: chartData.country.map(item => item.total_ventas),
                    backgroundColor: colors.country,
                    borderWidth: 1,
                    borderRadius: 5,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Top Countries by Asset Count',
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        padding: {
                            top: 20,
                            bottom: 20
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000
                }
            }
        });
    }

    // Crear gráfico de activos vencidos
    function createExpiredChart() {
        const ctx = document.getElementById('expiredChart').getContext('2d');
        const labels = chartData.expired.map(item => item.expired === '1' ? 'Expired' : 'Valid');
        const data = chartData.expired.map(item => parseInt(item.total));
        
        return new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.expired,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Expired (Green) vs Valid Assets (Red)',
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        padding: {
                            top: 20,
                            bottom: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000
                }
            }
        });
    }

    // Inicializar gráficos
    const statusChart = createStatusChart();
    const countryChart = createCountryChart();
    const expiredChart = createExpiredChart();

    // Active el primer gráfico por defecto
    document.querySelectorAll('.chart-switch')[0].classList.add('active');

    // Manejar cambios entre gráficos
    document.querySelectorAll('.chart-switch').forEach(button => {
        button.addEventListener('click', function() {
            const chartId = this.getAttribute('data-chart');
            
            // Ocultar todos los gráficos
            document.querySelectorAll('.chart-canvas').forEach(chart => {
                chart.classList.add('d-none');
            });
            
            // Mostrar el gráfico seleccionado
            document.getElementById(chartId).classList.remove('d-none');
            
            // Actualizar clase activa en botones
            document.querySelectorAll('.chart-switch').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Función para adaptar los gráficos cuando cambia el tamaño de la ventana
    window.addEventListener('resize', function() {
        statusChart.resize();
        countryChart.resize();
        expiredChart.resize();
    });
});