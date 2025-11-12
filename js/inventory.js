// Inventory Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Optional: Auto-submit on filter change
            // document.getElementById('filtersForm').submit();
        });
    });

    // Search input debounce (optional enhancement)
    const searchInput = document.querySelector('.search-input');
    let searchTimeout;
    
    if (searchInput) {
        // Optional: Add real-time search with debounce
        // searchInput.addEventListener('input', function() {
        //     clearTimeout(searchTimeout);
        //     searchTimeout = setTimeout(() => {
        //         document.getElementById('filtersForm').submit();
        //     }, 500);
        // });
    }

    // Action button handlers
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const equipmentId = row.querySelector('.equipment-id').textContent;
            const equipmentName = row.querySelector('.equipment-name strong').textContent;
            
            // TODO: Implement view details modal or navigation
            console.log('View details for:', equipmentId, equipmentName);
            alert(`View details for: ${equipmentName} (${equipmentId})`);
        });
    });

    const reserveButtons = document.querySelectorAll('.reserve-btn');
    reserveButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const equipmentId = row.querySelector('.equipment-id').textContent;
            const equipmentName = row.querySelector('.equipment-name strong').textContent;
            
            // TODO: Implement reservation functionality
            console.log('Reserve equipment:', equipmentId, equipmentName);
            if (confirm(`Reserve ${equipmentName} (${equipmentId})?`)) {
                // Handle reservation
                alert('Reservation functionality will be implemented soon.');
            }
        });
    });

    // Table row click handler (optional)
    const tableRows = document.querySelectorAll('.inventory-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on action buttons
            if (e.target.closest('.action-btn')) {
                return;
            }
            
            // Optional: Navigate to detail page or show modal
            // const equipmentId = row.querySelector('.equipment-id').textContent;
            // window.location.href = `equipment-details.php?id=${equipmentId}`;
        });
    });
});

