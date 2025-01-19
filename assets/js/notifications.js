// Show PHP session messages using SweetAlert2
document.addEventListener('DOMContentLoaded', function() {
    // Check for success message in PHP session
    const successMessage = document.querySelector('meta[name="success_message"]')?.getAttribute('content');
    if (successMessage) {
        Swal.fire({
            title: 'Success!',
            text: successMessage,
            icon: 'success'
        });
    }

    // Check for error message in PHP session
    const errorMessage = document.querySelector('meta[name="error_message"]')?.getAttribute('content');
    if (errorMessage) {
        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error'
        });
    }
});
