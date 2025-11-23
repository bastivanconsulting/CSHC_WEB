<?php $title = "Nouveau Ticket"; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="/support" class="text-decoration-none text-muted hover-white"><i class="fas fa-arrow-left me-2"></i> Retour au support</a>
            </div>

            <div class="glass-card p-5">
                <h3 class="fw-bold text-white mb-4">Ouvrir un nouveau ticket</h3>

                <form action="/support/new" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-white">Sujet</label>
                        <input type="text" name="subject" class="form-control" placeholder="Ex: Problème de latence VPN" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Priorité</label>
                        <select name="priority" class="form-select">
                            <option value="low">Basse</option>
                            <option value="medium" selected>Moyenne</option>
                            <option value="high">Haute</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white">Message</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Décrivez votre problème en détail..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="/support" class="btn btn-outline-light">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">Envoyer le ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
