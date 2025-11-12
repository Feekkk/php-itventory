// View Item Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Status dropdown change handler
    const statusDropdown = document.querySelector('.status-dropdown');
    
    if (statusDropdown) {
        statusDropdown.addEventListener('change', function() {
            const equipmentId = this.getAttribute('data-equipment-id');
            const newStatus = this.value;

            if (!equipmentId) {
                console.error('Equipment ID not found');
                return;
            }

            // Update status via AJAX
            updateItemStatus(equipmentId, newStatus, this);
        });
    }

    function updateItemStatus(equipmentId, newStatus, dropdownElement) {
        // Show loading state
        const originalValue = dropdownElement.value;
        dropdownElement.disabled = true;
        dropdownElement.style.opacity = '0.6';
        dropdownElement.style.cursor = 'wait';

        // Create form data
        const formData = new FormData();
        formData.append('equipment_id', equipmentId);
        formData.append('status', newStatus);
        formData.append('action', 'update_status');

        // Send AJAX request
        fetch('ListInventory.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update dropdown class based on new status
                const statusClass = newStatus.toLowerCase().replace(' ', '-');
                dropdownElement.className = `status-dropdown status-${statusClass}`;
                
                // Update status badge if it exists
                const statusBadge = document.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.textContent = newStatus;
                    statusBadge.className = `status-badge status-${statusClass}`;
                }
                
                // Show success message
                showNotification('Status updated successfully', 'success');
            } else {
                // Revert to original value on error
                dropdownElement.value = originalValue;
                const originalStatusClass = originalValue.toLowerCase().replace(' ', '-');
                dropdownElement.className = `status-dropdown status-${originalStatusClass}`;
                showNotification(data.message || 'Failed to update status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            dropdownElement.value = originalValue;
            const originalStatusClass = originalValue.toLowerCase().replace(' ', '-');
            dropdownElement.className = `status-dropdown status-${originalStatusClass}`;
            showNotification('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            dropdownElement.disabled = false;
            dropdownElement.style.opacity = '1';
            dropdownElement.style.cursor = 'pointer';
        });
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: #ffffff;
            font-weight: 500;
            z-index: 10001;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;
        
        if (type === 'success') {
            notification.style.background = '#065f46';
        } else {
            notification.style.background = '#dc2626';
        }

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
});

// Add notification animations to CSS dynamically if needed
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

