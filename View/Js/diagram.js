// Mejora de los gráficos con Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si chartData está definido
    if (typeof chartData === 'undefined') {
        console.error('No chart data available');
        return;
    }

    // Configuración de colores
    const colors = {
        status: [
            'rgba(63, 81, 181, 0.8)',   // Azul índigo
            'rgba(0, 150, 136, 0.8)',    // Verde azulado
            'rgba(255, 152, 0, 0.8)',    // Naranja
            'rgba(156, 39, 176, 0.8)',   // Púrpura
            'rgba(121, 85, 72, 0.8)'     // Marrón
        ],
        country: [
            'rgba(3, 169, 244, 0.8)',    // Azul claro
            'rgba(139, 195, 74, 0.8)',   // Verde lima
            'rgba(233, 30, 99, 0.8)',    // Rosa
            'rgba(255, 193, 7, 0.8)',    // Ámbar
            'rgba(96, 125, 139, 0.8)'    // Azul grisáceo
        ],
        expired: [
            'rgba(76, 175, 80, 0.8)',    // Verde
            'rgba(244, 67, 54, 0.8)'     // Rojo
        ]
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
                    borderWidth: 3,
                    borderColor: 'rgba(255, 255, 255, 0.9)',
                    hoverBorderColor: 'rgba(255, 255, 255, 1)',
                    hoverBackgroundColor: colors.status.map(color => color.replace('0.8', '1')),
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 14,
                                family: '"Segoe UI", Arial, sans-serif'
                            },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Equipment Status Distribution',
                        font: {
                            size: 20,
                            weight: 'bold',
                            family: '"Segoe UI", Arial, sans-serif'
                        },
                        padding: {
                            top: 30,
                            bottom: 30
                        },
                        color: '#333'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#333',
                        bodyColor: '#333',
                        bodyFont: {
                            size: 14,
                            family: '"Segoe UI", Arial, sans-serif'
                        },
                        padding: 15,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
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
                    duration: 1500,
                    easing: 'easeOutCirc'
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
                    borderWidth: 3,
                    borderRadius: 12,
                    barThickness: 30,
                    borderColor: 'rgba(255, 255, 255, 0.9)',
                    hoverBorderColor: 'rgba(255, 255, 255, 1)',
                    hoverBackgroundColor: colors.country.map(color => color.replace('0.8', '1')),
                    hoverOffset: 5
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
                            size: 20,
                            weight: 'bold',
                            family: '"Segoe UI", Arial, sans-serif'
                        },
                        padding: {
                            top: 30,
                            bottom: 30
                        },
                        color: '#333'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#333',
                        bodyColor: '#333',
                        bodyFont: {
                            size: 14,
                            family: '"Segoe UI", Arial, sans-serif'
                        },
                        padding: 15,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                family: '"Segoe UI", Arial, sans-serif'
                            },
                            color: '#666'
                        }
                    },
                    y: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 14,
                                weight: 'bold',
                                family: '"Segoe UI", Arial, sans-serif'
                            },
                            color: '#333'
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutCirc'
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
                    borderWidth: 3,
                    borderColor: 'rgba(255, 255, 255, 0.9)',
                    hoverBorderColor: 'rgba(255, 255, 255, 1)',
                    hoverBackgroundColor: colors.expired.map(color => color.replace('0.8', '1')),
                    hoverOffset: 8
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
                                size: 14,
                                family: '"Segoe UI", Arial, sans-serif'
                            },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Estado de Activos',
                        font: {
                            size: 20,
                            weight: 'bold',
                            family: '"Segoe UI", Arial, sans-serif'
                        },
                        padding: {
                            top: 30,
                            bottom: 30
                        },
                        color: '#333'
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#333',
                        bodyColor: '#333',
                        bodyFont: {
                            size: 14,
                            family: '"Segoe UI", Arial, sans-serif'
                        },
                        padding: 15,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
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
                    duration: 1500,
                    easing: 'easeOutCirc'
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