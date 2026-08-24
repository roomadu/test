document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('visitorForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerText;
            submitBtn.innerText = 'Submitting...';
            submitBtn.disabled = true;

            let whomToMeetValue = document.querySelector('input[name="whomToMeet"]:checked')?.value;
            if (whomToMeetValue === 'Other') {
                whomToMeetValue = document.getElementById('whomToMeetOther').value;
            }

            const formData = {
                visitorName: document.getElementById('visitorName').value,
                visitorEmail: document.getElementById('visitorEmail').value,
                visitorPhone: document.getElementById('visitorPhone').value,
                whomToMeet: whomToMeetValue,
                date: document.getElementById('date').value,
                purpose: document.getElementById('purpose').value,
                numPeople: document.getElementById('numPeople').value,
                organization: '' 
            };

            try {
                const response = await fetch('/api/visit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    form.classList.add('hidden');
                    document.getElementById('successMessage').classList.remove('hidden');
                } else {
                    alert('Error submitting registration. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please check your connection and try again.');
            } finally {
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
            }
        });
    }
});
