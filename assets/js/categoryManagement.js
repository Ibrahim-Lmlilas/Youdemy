// Handle category data
function handleCategoryData(action, data = {}) {
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
            'addCategory': 'Category added successfully',
            'updateCategory': 'Category updated successfully',
            'deleteCategory': 'Category has been deleted'
        }[action];
        
        return Swal.fire('Success!', message, 'success')
            .then(() => location.reload());
    })
    .catch(error => {
        const message = {
            'addCategory': 'Failed to add category',
            'updateCategory': 'Failed to update category',
            'deleteCategory': 'Failed to delete category'
        }[action];
        
        Swal.fire('Error!', message, 'error');
    });
}

// Category Functions
function openAddCategoryModal() {
    Swal.fire({
        title: 'Add New Category',
        html: `
            <form id="addCategoryForm" class="mt-4">
                <input type="text" id="categoryName" class="swal2-input" placeholder="Category Name" required>
                <textarea id="categoryDescription" class="swal2-textarea mt-3" placeholder="Category Description" rows="4"></textarea>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Category',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const name = document.getElementById('categoryName').value;
            const description = document.getElementById('categoryDescription').value;
            if (!name) {
                Swal.showValidationMessage('Please enter a category name');
                return false;
            }
            return { name: name, description: description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            handleCategoryData('addCategory', { 
                name: result.value.name,
                description: result.value.description
            });
        }
    });
}

function editCategory(id, name, description = '') {
    Swal.fire({
        title: 'Edit Category',
        html: `
            <form id="editCategoryForm" class="mt-4">
                <input type="text" id="editCategoryName" class="swal2-input" value="${name}" required>
                <textarea id="editCategoryDescription" class="swal2-textarea mt-3" rows="4">${description}</textarea>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const name = document.getElementById('editCategoryName').value;
            const description = document.getElementById('editCategoryDescription').value;
            if (!name) {
                Swal.showValidationMessage('Please enter a category name');
                return false;
            }
            return { id: id, name: name, description: description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            handleCategoryData('updateCategory', { 
                id: result.value.id,
                name: result.value.name,
                description: result.value.description
            });
        }
    });
}

function deleteCategory(id) {
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
            handleCategoryData('deleteCategory', { id: id });
        }
    });
}
