<?php $title = "Ticket #" . $ticket['id']; ?>

<div class="container py-5">
    <div class="mb-4">
        <a href="/support" class="text-decoration-none text-muted hover-white"><i class="fas fa-arrow-left me-2"></i> Retour au support</a>
    </div>

    <div class="row g-4">
        <!-- Messages Column -->
        <div class="col-lg-8">
            <div class="glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h3 class="fw-bold text-white mb-0"><?= htmlspecialchars($ticket['subject']) ?></h3>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Ticket #<?= $ticket['id'] ?></span>
                </div>
            </div>

            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach($messages as $msg): ?>
                    <?php $isMe = $msg['user_id'] == $_SESSION['user']['id']; ?>
                    <div class="d-flex <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="glass-card p-3" style="max-width: 85%; background: <?= $isMe ? 'rgba(37, 99, 235, 0.1)' : 'var(--bg-card)' ?>; border-color: <?= $isMe ? 'rgba(37, 99, 235, 0.3)' : 'var(--glass-border)' ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2 gap-3">
                                <strong class="<?= $isMe ? 'text-primary' : 'text-white' ?>">
                                    <?= $isMe ? 'Vous' : ($msg['role'] === 'admin' ? '<i class="fas fa-shield-alt text-warning me-1"></i> Support' : htmlspecialchars($msg['email'])) ?>
                                </strong>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= $msg['created_at'] ?></small>
                            </div>
                            <div class="text-secondary" style="white-space: pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if($ticket['status'] !== 'closed'): ?>
                <div class="glass-card p-4">
                    <h5 class="text-white mb-3">Répondre</h5>
                    <form action="/support/view/<?= $ticket['id'] ?>" method="POST">
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="4" placeholder="Votre réponse..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i> Envoyer</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary bg-opacity-10 text-center border-secondary text-muted">
                    <i class="fas fa-lock me-2"></i> Ce ticket est fermé.
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="glass-card p-4">
                <h5 class="text-white mb-3">Détails</h5>
                <ul class="list-unstyled text-secondary mb-0">
                    <li class="mb-3 d-flex justify-content-between">
                        <span>Statut:</span>
                        <span class="badge bg-secondary"><?= ucfirst($ticket['status']) ?></span>
                    </li>
                    <li class="mb-3 d-flex justify-content-between">
                        <span>Priorité:</span>
                        <span class="badge bg-dark border border-secondary"><?= ucfirst($ticket['priority']) ?></span>
                    </li>
                    <li class="mb-3 d-flex justify-content-between">
                        <span>Créé le:</span>
                        <span class="text-white"><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span>Dernière MAJ:</span>
                        <span class="text-white"><?= date('H:i', strtotime($ticket['updated_at'])) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
