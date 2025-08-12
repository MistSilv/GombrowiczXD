document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-template');
                const row = document.getElementById('details-' + id);
                if (row.style.display === 'none') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });