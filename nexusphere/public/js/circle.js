document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('circle-item').forEach(button => {
        button.addEventListener('click', async () => {

            const response = await fetch(`/circle/${circleId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            if (!response.ok) {
                alert('通信エラー');
                return;
            }

            const data = await response.json();
            button.querySelector('.circle-join').textContent = data.circle_join;
        });
    });
});