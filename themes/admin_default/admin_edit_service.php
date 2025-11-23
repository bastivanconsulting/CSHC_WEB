<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-white">Éditer le Service #<?= $service['id'] ?></h3>
                    <a href="/admin" class="btn btn-outline-light btn-sm">Retour</a>
                </div>

                <form action="/admin/save-service" method="POST">
                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-secondary">Nom</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($service['name']) ?>">
                        </div>
                        
                        <!-- CORRECTION ICI : Retour au menu déroulant pour pouvoir changer le type -->
                        <div class="col-md-6">
                            <label class="small text-secondary">Type</label>
                            <select name="type" class="form-select" id="editServiceType">
                                <option value="OpenVPN" <?= $service['type'] == 'OpenVPN' ? 'selected' : '' ?>>OpenVPN</option>
                                <option value="Wireguard" <?= $service['type'] == 'Wireguard' ? 'selected' : '' ?>>Wireguard</option>
                                <option value="VPLS" <?= $service['type'] == 'VPLS' ? 'selected' : '' ?>>VPLS</option>
                                <option value="FTTx" <?= $service['type'] == 'FTTx' ? 'selected' : '' ?>>FTTx</option>
                                
                                <!-- Les modules (ex: Mobile) s'injectent ici -->
                                <?= Hook::call('register_service_type') ?>
                            </select>
                            <!-- Petit script pour forcer la sélection si c'est un type venant d'un module -->
                            <script>
                                document.getElementById('editServiceType').value = "<?= $service['type'] ?>";
                            </script>
                        </div>

                        <div class="col-md-6">
                            <label class="small text-secondary">Prix (€)</label>
                            <input type="text" name="price" class="form-control" value="<?= $service['price'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="small text-secondary">Engagement</label>
                            <select name="engagement" class="form-select">
                                <option value="Sans engagement" <?= $service['engagement'] == 'Sans engagement' ? 'selected' : '' ?>>Sans engagement</option>
                                <option value="12 mois" <?= $service['engagement'] == '12 mois' ? 'selected' : '' ?>>12 mois</option>
                                <option value="24 mois" <?= $service['engagement'] == '24 mois' ? 'selected' : '' ?>>24 mois</option>
                            </select>
                        </div>
                        
                        <!-- [HOOK] Champs spécifiques en édition : Le module injecte ici ses inputs pré-remplis -->
                        <div class="col-12">
                             <?= Hook::call('admin_service_edit_fields', ['service' => $service]) ?>
                        </div>

                        <div class="col-md-6">
                            <label class="small text-secondary">IP</label>
                            <input type="text" name="ip_address" class="form-control" value="<?= $service['ip_address'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="small text-secondary">Localisation</label>
                            <input type="text" name="location" class="form-control" value="<?= $service['location'] ?>">
                        </div>
                        
                        <div class="col-12">
                            <label class="small text-secondary">Détails</label>
                            <textarea name="details" class="form-control" rows="4"><?= htmlspecialchars($service['details']) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>