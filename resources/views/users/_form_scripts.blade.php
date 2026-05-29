@push('scripts')
<script>
(function () {
    const roleSelect = document.getElementById('role');
    const teacherSection = document.getElementById('teacher-section');
    const studentSection = document.getElementById('student-section');
    const dniField = document.getElementById('dni-field');
    const emailRequiredIndicator = document.getElementById('email-required-indicator');
    const userDataSection = document.getElementById('user-data-section');
    const userInputs = document.querySelectorAll('[data-user-input]');
    const studentCheckboxes = document.querySelectorAll('[data-student-checkbox]');
    const isCreate = document.getElementById('user-form')?.action.includes('/users') && ! document.getElementById('user-form')?.querySelector('input[name="_method"][value="PUT"]');

    function updateSections() {
        const role = roleSelect ? roleSelect.value : '';
        const isStudentLinkedRole = ['alumno', 'super_alumno'].includes(role);
        const hasStudentSelection = Array.from(studentCheckboxes).some((checkbox) => checkbox.checked);
        const shouldLockDataFields = isCreate && (! role || (isStudentLinkedRole && ! hasStudentSelection));

        if (teacherSection) teacherSection.classList.toggle('hidden', role !== 'profesor');
        if (studentSection) studentSection.classList.toggle('hidden', ! isStudentLinkedRole);
        if (dniField) dniField.classList.toggle('hidden', ! isStudentLinkedRole);
        if (emailRequiredIndicator) emailRequiredIndicator.classList.toggle('hidden', isStudentLinkedRole);
        if (userDataSection) userDataSection.classList.toggle('opacity-60', shouldLockDataFields);

        userInputs.forEach((input) => {
            input.disabled = shouldLockDataFields;

            if (input.id === 'email') {
                input.required = ! isStudentLinkedRole;
            }
        });
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updateSections);
    }

    studentCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSections));

    updateSections();
})();
</script>
@endpush