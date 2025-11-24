<?php
// modules/ClientProfile/Views/admin_form_fields.php
$lastname = htmlspecialchars($u['lastname'] ?? '');
$firstname = htmlspecialchars($u['firstname'] ?? '');
$address = htmlspecialchars($u['address'] ?? '');
$siret = htmlspecialchars($u['siret'] ?? '');
$vat = htmlspecialchars($u['vat_number'] ?? '');

$isCompany = ($u['client_type'] ?? '') === 'entreprise' ? 'selected' : '';
$isIndividual = ($u['client_type'] ?? '') === 'particulier' ? 'selected' : '';
?>

<div class='mt-3 pt-3 border-top border-secondary'>
    <h6 class='text-info mb-3'><i class='fas fa-id-card me-2'></i>Informations Légales & Contact</h6>

    <div class='row g-2'>
        <div class='col-md-6'>
            <label class='small text-secondary'>Nom</label>
            <input type='text' name='lastname' class='form-control' value='<?= $lastname ?>' placeholder='Doe'>
        </div>
        <div class='col-md-6'>
            <label class='small text-secondary'>Prénom</label>
            <input type='text' name='firstname' class='form-control' value='<?= $firstname ?>' placeholder='John'>
        </div>

        <div class='col-12'>
            <label class='small text-secondary'>Adresse Complète</label>
            <textarea name='address' class='form-control' rows='2' placeholder='123 Rue de la Tech...'><?= $address ?></textarea>
        </div>

        <div class='col-md-12'>
            <label class='small text-secondary'>Type de Client</label>
            <select name='client_type' class='form-select' id='clientTypeSelector' onchange='toggleCompanyFields()'>
                <option value='particulier' <?= $isIndividual ?>>Particulier</option>
                <option value='entreprise' <?= $isCompany ?>>Entreprise (Pro)</option>
            </select>
        </div>

        <div class='col-md-6 company-field' style='display:none;'>
            <label class='small text-secondary'>SIRET</label>
            <input type='text' name='siret' class='form-control font-monospace' value='<?= $siret ?>' placeholder='14 chiffres'>
        </div>
        <div class='col-md-6 company-field' style='display:none;'>
            <label class='small text-secondary'>Numéro TVA</label>
            <input type='text' name='vat_number' class='form-control font-monospace' value='<?= $vat ?>' placeholder='FR...'>
        </div>
    </div>
</div>

<script>
function toggleCompanyFields() {
    let select = document.getElementById('clientTypeSelector');
    if(!select) return;
    let type = select.value;
    let fields = document.querySelectorAll('.company-field');
    fields.forEach(f => f.style.display = (type === 'entreprise' ? 'block' : 'none'));
}
setTimeout(toggleCompanyFields, 100);
</script>
