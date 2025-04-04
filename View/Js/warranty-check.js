$(document).ready(function() {
    const swalCustomClass = {
        popup: 'card shadow-lg',
        title: 'modal-title fw-bold',
        htmlContainer: 'modal-body py-3',
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-outline-secondary'
    };

    const swalCustomStyle = {
        customClass: swalCustomClass,
        backdrop: `rgba(33, 37, 41, 0.6)`,
        showClass: {
            popup: 'animate__animated animate__fadeIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOut'
        },
        buttonsStyling: false,
        padding: '2.5rem',
        borderRadius: '16px',
        background: '#fff',
        color: '#212529',
        width: 'auto',
        maxWidth: '32rem'
    };

    function checkWarranty() {
        $.ajax({
            url: '/3Shape_project/Model/Warranty.php',
            method: 'POST',
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    ...swalCustomStyle,
                    title: 'Checking Warranties...',
                    html: '<div class="d-flex flex-column align-items-center">' +
                          '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                          '<p class="mb-0 text-muted">Please wait while we verify the warranty status</p>' +
                          '</div>',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            success: function(response) {
                if (response.success) {
                    let message = response.message;
                    if (response.updatedAssets && response.updatedAssets.length > 0) {
                        message += '\n\nUpdated Assets: ' + response.updatedAssets.join(', ');
                    }
                    Swal.fire({
                        ...swalCustomStyle,
                        icon: 'success',
                        title: 'Success',
                        html: '<div class="text-success mb-3"><i class="fas fa-check-circle fa-3x"></i></div>' +
                              '<p class="mb-0">' + message + '</p>',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        ...swalCustomStyle,
                        icon: 'error',
                        title: 'Error',
                        html: '<div class="text-danger mb-3"><i class="fas fa-times-circle fa-3x"></i></div>' +
                              '<p class="mb-0">' + (response.message || 'An error occurred while verifying warranties') + '</p>',
                        confirmButtonText: 'Close'
                    });
                }

                if (response.errors && response.errors.length > 0) {
                    setTimeout(() => {
                        Swal.fire({
                            ...swalCustomStyle,
                            icon: 'warning',
                            title: 'Warning',
                            html: '<div class="text-warning mb-3"><i class="fas fa-exclamation-triangle fa-3x"></i></div>' +
                                  '<div class="alert alert-warning p-3 mb-0">' +
                                  '<p class="mb-2 fw-bold">The following issues were found:</p>' +
                                  '<ul class="list-unstyled mb-0">' +
                                  response.errors.map(error => '<li class="mb-2"><i class="fas fa-exclamation-circle me-2"></i>' + error + '</li>').join('') +
                                  '</ul></div>',
                            confirmButtonText: 'Understood'
                        });
                    }, 1500);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Failed to check warranties';
                if (error) {
                    errorMessage += ': ' + error;
                }
                Swal.fire({
                    ...swalCustomStyle,
                    icon: 'error',
                    title: 'Connection Error',
                    html: '<div class="text-danger mb-3"><i class="fas fa-wifi fa-3x"></i></div>' +
                          '<p class="mb-0">' + errorMessage + '</p>',
                    confirmButtonText: 'Try Again',
                    showCancelButton: true,
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed) {
                        checkWarranty();
                    }
                });
            }
        });
    }

    // Add button to check warranties
    const checkButton = $('<button>', {
        text: 'Check Warranties',
        class: 'btn btn-primary btn-lg shadow-sm',
        style: 'margin: 20px 0; padding: 12px 24px; font-weight: 500;',
        click: checkWarranty
    }).prepend('<i class="fas fa-shield-alt me-2"></i>');

    // Add the button to the DOM (adjust the selector according to your HTML structure)
    $('.container').first().prepend(checkButton);
});