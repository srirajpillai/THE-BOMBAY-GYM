document.addEventListener('DOMContentLoaded', function() {
    const bookingTypeInputs = document.querySelectorAll('input[name="booking_type"]');
    const weekdaySelection = document.querySelector('.weekday-selection');
    const startDateInput = document.getElementById('start_date');

    // Set maximum date based on booking type
    function updateDateConstraints(bookingType) {
        const today = new Date();
        const maxDate = new Date();
        
        if (bookingType === 'single') {
            maxDate.setDate(today.getDate() + 7); // 1 week ahead
            weekdaySelection.style.display = 'none';
        } else if (bookingType === 'week') {
            maxDate.setDate(today.getDate() + 14); // 2 weeks ahead
            weekdaySelection.style.display = 'block';
        } else if (bookingType === 'month') {
            maxDate.setDate(today.getDate() + 30); // 1 month ahead
            weekdaySelection.style.display = 'block';
        }

        startDateInput.max = maxDate.toISOString().split('T')[0];
    }

    // Listen for booking type changes
    bookingTypeInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            updateDateConstraints(e.target.value);
        });
    });

    // Initial setup
    updateDateConstraints('single');
});