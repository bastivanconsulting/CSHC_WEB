<?php $title = "Support Technique"; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white">Support Technique</h2>
        <a href="/support/new" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Nouveau Ticket</a>
    </div>

    <div class="glass-card p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Sujet</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Dernière MAJ</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-ticket-alt fa-3x mb-3 opacity-50"></i><br>
                                Aucun ticket en cours.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td class="ps-4 fw-medium text-white"><?= htmlspecialchars($ticket['subject']) ?></td>
                                <td>
                                    <?php
                                    $prioClass = match($ticket['priority']) {
                                        'high' => 'danger',
                                        'medium' => 'warning',
                                        'low' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $prioClass ?> bg-opacity-10 text-<?= $prioClass ?> border border-<?= $prioClass ?>"><?= ucfirst($ticket['priority']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($ticket['status']) {
                                        'open' => 'success',
                                        'answered' => 'warning',
                                        'closed' => 'secondary',
                                        default => 'primary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?> rounded-pill"><?= ucfirst($ticket['status']) ?></span>
                                </td>
                                <td class="text-muted small"><?= $ticket['updated_at'] ?></td>
                                <td class="text-end pe-4">
                                    <a href="/support/view/<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-light">Gérer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
