function openModal(userId) {
    // Fetch user details using the userId
    fetch(`/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            const userDetailsContent = document.getElementById('userDetailsContent');
            userDetailsContent.innerHTML = `
                <p><strong>Full Name:</strong> ${data.full_name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Phone Number:</strong> ${data.phone_number}</p>
                <p><strong>Address:</strong> ${data.address}</p>
                <p><strong>City:</strong> ${data.city}</p>
                <p><strong>Status:</strong> ${data.status}</p>
            `;
            document.getElementById('userModal').classList.remove('hidden');
        });
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}

function updateOrderStatus(orderId) {
    const newStatus = document.getElementById('order_status').value;

    // Confirm the action
    const confirmUpdate = confirm(`Are you sure you want to update the status to "${newStatus}"?`);

    if (confirmUpdate) {
        // Send the update request via AJAX
        fetch(`/orders/update-status/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF token
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Display confirmation message from server
            if (data.success) {
                // Optionally, you can update the page with the new status
                document.getElementById('order_status').value = newStatus; 

                // Refresh the page to reflect changes
                window.location.reload();
            }
        })
        .catch(error => {
            alert("An error occurred while updating the status.");
        });
    }
}

function updateProductStatus(orderDetailId) {
    const newStatus = document.getElementById('edit_status_' + orderDetailId).value;

    // Confirm the action
    const confirmUpdate = confirm(`Are you sure you want to update the product status to "${newStatus}"?`);

    if (confirmUpdate) {
        // Send the update request via AJAX
        fetch(`/orders/update-product-status/${orderDetailId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF token
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => {
            // Check if the response is OK
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message); // Display success message

                // Optional: Update the DOM element with the new status
                const statusSpan = document.getElementById('status_span_' + orderDetailId);
                if (statusSpan) {
                    statusSpan.innerText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1); // Capitalize first letter
                }

                // Refresh the page
                location.reload();
            } else {
                alert(data.message); // Display error message
            }
        })
        .catch(error => {
            console.error("Error updating product status:", error); // Log detailed error in the console
            alert("An error occurred while updating the status. Please try again.");
        });
    }
}