// Simple JavaScript for the AI Reviewer application
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh functionality for admin pages
    const autoRefreshElements = document.querySelectorAll('[data-auto-refresh]');

    autoRefreshElements.forEach(element => {
        const interval = element.dataset.autoRefresh || 30000; // Default 30 seconds

        setInterval(() => {
            window.location.reload();
        }, interval);
    });

    // Simple confirmation dialogs
    const deleteButtons = document.querySelectorAll('[data-confirm]');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Initialize tooltips or other UI components as needed
    console.log('AI Reviewer application initialized');
});
