function confirmSuspend(userId) {
    Swal.fire({
        title: 'Suspend User?',
        text: "This user will not be able to access their account!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EAB308',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, suspend them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/Yooudemy/controllers/admin/suspend_user.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_id';
            input.value = userId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmActivate(userId) {
    Swal.fire({
        title: 'Activate User?',
        text: "This user will regain access to their account!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22C55E',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, activate them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/Yooudemy/controllers/admin/activate_user.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_id';
            input.value = userId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

