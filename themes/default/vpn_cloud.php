<div class="hero-section text-center py-5 position-relative overflow-hidden">
    <div class="container py-5" data-aos="fade-up">
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mb-3 px-3 py-2 rounded-pill">
            <i class="fas fa-lock me-2"></i>Sécurité Maximale
        </span>
        <h1 class="display-3 fw-bold text-white mb-4">VPN Cloud <span class="text-gradient">Haute Disponibilité</span></h1>
        <p class="lead text-muted mb-5 mx-auto" style="max-width: 700px;">
            Interconnectez vos sites distants avec une infrastructure VPN managée, chiffrée et redondée sur plusieurs datacenters.
        </p>
    </div>

    <div class="position-absolute top-50 start-50 translate-middle" style="z-index: -1; opacity: 0.1;">
        <i class="fas fa-shield-virus text-primary" style="font-size: 50vw;"></i>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4 align-items-center mb-5">
        <div class="col-lg-6" data-aos="fade-right">
            <h2 class="text-white fw-bold mb-4">Architecture Résiliente</h2>
            <p class="text-muted mb-4">
                Ne laissez plus une panne internet isoler vos équipes. Notre solution VPN Cloud HA bascule automatiquement entre plusieurs passerelles pour garantir la continuité de vos opérations.
            </p>
            <ul class="list-unstyled text-muted">
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Failover automatique < 3 secondes</li>
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Chiffrement AES-256 de bout en bout</li>
                <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Compatible SD-WAN</li>
            </ul>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
             <div class="card bg-dark border-secondary border-opacity-25 p-4 glass-card">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-server fa-2x text-primary me-3"></i>
                    <div>
                        <h5 class="text-white mb-0">Gateway Primaire</h5>
                        <small class="text-success">En ligne - Paris DC1</small>
                    </div>
                </div>
                <div class="progress mb-4" style="height: 2px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-server fa-2x text-secondary me-3"></i>
                    <div>
                        <h5 class="text-muted mb-0">Gateway Secondaire</h5>
                        <small class="text-muted">Standby - Frankfurt DC2</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.03) !important;
        backdrop-filter: blur(10px);
    }
    .text-gradient {
        background: linear-gradient(45deg, #2563eb, #60a5fa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>