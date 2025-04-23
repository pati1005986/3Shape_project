// Mejora de los gráficos con Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si chartData está definido
    if (typeof chartData === 'undefined') {
        console.error('No chart data available');
        return;
    }

    // Configuración de colores modernos y armónicos con gradientes
    const colors = {
        status: [
            'rgba(13, 110, 253, 0.8)',  // Primary blue from manual.css
            'rgba(19, 132, 150, 0.8)',   // Info color variant
            'rgba(220, 223, 55, 0.8)',     // Header color from manual.css
            'rgba(73, 80, 87, 0.8)'      // Text color from manual.css
        ],
        country: [
            'rgba(13, 110, 253, 0.8)',
            'rgba(237, 253, 13, 0.8)',
            'rgba(230, 17, 17, 0.8)',
            'rgba(33, 61, 94, 0.8)',
            'rgba(4, 22, 51, 0.8)'
        ],
        expired: [
            'rgba(13, 110, 253, 0.8)',   // Active/Valid
            'rgba(73, 80, 87, 0.8)'       // Expired
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
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1200,
                    easing: 'easeInOutQuart',
                    onComplete: function(animation) {
                        const ctx = animation.chart.ctx;
                        ctx.save();
                        ctx.fillStyle = '#666';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = '16px "Segoe UI", Arial, sans-serif';
                        const text = 'Total';
                        const total = animation.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        ctx.fillText(text, animation.chart.width / 2, animation.chart.height / 2 - 15);
                        ctx.font = 'bold 24px "Segoe UI", Arial, sans-serif';
                        ctx.fillText(total, animation.chart.width / 2, animation.chart.height / 2 + 15);
                        ctx.restore();
                    }
                },
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
                animation: {
                    delay: function(context) {
                        return context.dataIndex * 100;
                    },
                    duration: 1000,
                    easing: 'easeOutElastic',
                    from: function(context) {
                        if (context.type === 'data') {
                            if (context.mode === 'default' && !context.dropped) {
                                context.dropped = true;
                                return {
                                    x: context.chart.width,
                                    y: context.dataPoint.y
                                };
                            }
                        }
                        return false;
                    }
                },
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
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: 'easeOutBounce'
                },
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
                        text: 'Status of Asset Warranties',
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

    // Función mejorada para adaptar los gráficos cuando cambia el tamaño de la ventana
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const chartContainer = document.querySelector('.chart-container');
            if (chartContainer) {
                const containerWidth = chartContainer.offsetWidth;
                const containerHeight = window.innerHeight * 0.6; // 60% de la altura de la ventana

                statusChart.resize(containerWidth, containerHeight);
                countryChart.resize(containerWidth, containerHeight);
                expiredChart.resize(containerWidth, containerHeight);

                // Actualizar opciones responsivas
                const fontSize = containerWidth < 768 ? 12 : 14;
                const legendPosition = containerWidth < 768 ? 'bottom' : 'right';

                [statusChart, countryChart, expiredChart].forEach(chart => {
                    if (chart.options.plugins.legend) {
                        chart.options.plugins.legend.position = legendPosition;
                        chart.options.plugins.legend.labels.font.size = fontSize;
                    }
                    if (chart.options.plugins.title) {
                        chart.options.plugins.title.font.size = fontSize + 6;
                    }
                    chart.update('none');
                });
            }
        }, 250); // Debounce de 250ms
    });
});