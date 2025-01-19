// Handle tag data
function handleTagData(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    
    // Add all data to FormData
    Object.keys(data).forEach(key => {
        formData.append(key, data[key]);
    });
    
    // Send request to server
    return fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        const message = {
            'addTag': 'Tag added successfully',
            'updateTag': 'Tag updated successfully',
            'deleteTag': 'Tag has been deleted'
        }[action];
        
        return Swal.fire('Success!', message, 'success')
            .then(() => location.reload());
    })
    .catch(error => {
        const message = {
            'addTag': 'Failed to add tag',
            'updateTag': 'Failed to update tag',
            'deleteTag': 'Failed to delete tag'
        }[action];
        
        Swal.fire('Error!', message, 'error');
    });
}

// Tag Functions
function openAddTagModal() {
    Swal.fire({
        title: 'Add New Tag',
        html: `
            <form id="addTagForm" class="mt-4">
                <input type="text" id="tagName" class="swal2-input" placeholder="Tag Name" required>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Tag',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const name = document.getElementById('tagName').value;
            if (!name) {
                Swal.showValidationMessage('Please enter a tag name');
                return false;
            }
            return { name: name };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            handleTagData('addTag', { name: result.value.name });
        }
    });
}

function editTag(id, name) {
    Swal.fire({
        title: 'Edit Tag',
        html: `
            <form id="editTagForm" class="mt-4">
                <input type="text" id="editTagName" class="swal2-input" value="${name}" required>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const name = document.getElementById('editTagName').value;
            if (!name) {
                Swal.showValidationMessage('Please enter a tag name');
                return false;
            }
            return { id: id, name: name };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            handleTagData('updateTag', { 
                id: result.value.id, 
                name: result.value.name 
            });
        }
    });
}

function deleteTag(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            handleTagData('deleteTag', { id: id });
        }
    });
}
