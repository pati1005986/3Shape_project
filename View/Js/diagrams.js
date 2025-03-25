document.addEventListener("DOMContentLoaded", function() {
    // Configuración general de los gráficos
    const chartConfig = {
        statusChart: {
            dataKey: 'status',
            processData: (data) => {
                const counts = {};
                data.forEach(item => counts[item.user_status] = (counts[item.user_status] || 0) + 1);
                return {
                    labels: Object.keys(counts),
                    values: Object.values(counts),
                    label: 'Equipment Status',
                    colors: ['rgba(13, 202, 240, 0.2)', 'rgba(13, 202, 240, 1)']
                };
            }
        },
        countryChart: {
            dataKey: 'country',
            processData: (data) => ({
                labels: data.map(item => item.purchase_country),
                values: data.map(item => item.total_ventas),
                label: 'Top 5 Countries',
                colors: ['rgba(255, 159, 64, 0.2)', 'rgba(255, 159, 64, 1)']
            })
        },
        expiredChart: {
            dataKey: 'expired',
            processData: (data) => ({
                labels: data.map(item => item.expired || "Not Specified"),
                values: data.map(item => item.total),
                label: 'Expired Assets',
                colors: ['rgba(255, 99, 132, 0.2)', 'rgba(255, 99, 132, 1)']
            })
        }
    };

    // Instancias de los gráficos
    const charts = {};

    // Inicializar todos los gráficos
    Object.keys(chartConfig).forEach(chartId => {
        const config = chartConfig[chartId];
        const ctx = document.getElementById(chartId).getContext('2d');
        const {
            labels,
            values,
            label,
            colors
        } = config.processData(chartData[config.dataKey]);

        charts[chartId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: colors[0],
                    borderColor: colors[1],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });

    // Control de visibilidad
    document.querySelectorAll('.chart-switch').forEach(button => {
        button.addEventListener('click', function() {
            const targetChart = this.dataset.chart;

            // Ocultar todos los gráficos
            document.querySelectorAll('.chart-canvas').forEach(canvas => {
                canvas.classList.add('d-none');
            });

            // Mostrar el gráfico seleccionado
            document.getElementById(targetChart).classList.remove('d-none');

            // Actualizar botones activos
            document.querySelectorAll('.chart-switch').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Mostrar primer gráfico por defecto
    document.querySelector('.chart-switch').click();
});