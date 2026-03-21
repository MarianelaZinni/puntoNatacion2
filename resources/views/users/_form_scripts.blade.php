@push('scripts')
<script>
(function () {
    const roleSelect = document.getElementById('role');
    const teacherSection = document.getElementById('teacher-section');
    const studentSection = document.getElementById('student-section');

    function updateSections() {
        const role = roleSelect ? roleSelect.value : '';
        if (teacherSection) teacherSection.classList.toggle('hidden', role !== 'profesor');
        if (studentSection) studentSection.classList.toggle('hidden', role !== 'alumno');
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updateSections);
        updateSections();
    }
})();
</script>
@endpush
