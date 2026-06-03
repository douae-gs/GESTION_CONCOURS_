document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-supprimer').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Confirmer la suppression de ce concours ?')) {
                e.preventDefault();
            }
        });
    });
});
