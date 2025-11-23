<div class="hero-section text-center py-5 position-relative overflow-hidden">
    <div class="container py-5" data-aos="fade-up">
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mb-3 px-3 py-2 rounded-pill">
            <i class="fas fa-shield-alt me-2"></i>Protection Volumétrique
        </span>
        <h1 class="display-3 fw-bold text-white mb-4">Protection <span class="text-gradient">Anti-DDoS</span></h1>
        <p class="lead text-muted mb-5 mx-auto" style="max-width: 700px;">
            Une mitigation permanente et instantanée. Nous nettoyons votre trafic en bordure de réseau avant qu'il n'atteigne vos serveurs.
        </p>
    </div>

    <div class="position-absolute top-50 start-50 translate-middle" style="z-index: -1; opacity: 0.1;">
        <i class="fas fa-biohazard text-danger" style="font-size: 50vw;"></i>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4 align-items-center mb-5" x-data="{ mode: 'auto' }">
        <div class="col-lg-5" data-aos="fade-right">
            <h2 class="text-white fw-bold mb-4">Comment ça marche ?</h2>
            <div class="d-flex flex-column gap-3">
                <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark glass-card">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">1</span>
                        <strong class="text-white">Détection</strong>
                    </div>
                    <p class="mb-0 text-muted small">Analyse en temps réel de chaque paquet entrant sur le réseau (échantillonnage 1/1000).</p>
                </div>
                <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark glass-card">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2">2</span>
                        <strong class="text-white">Vacuuum (VAC)</strong>
                    </div>
                    <p class="mb-0 text-muted small">Redirection automatique du trafic suspect vers nos centres de nettoyage.</p>
                </div>
                <div class="p-3 border border-secondary border-opacity-25 rounded bg-dark glass-card">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success me-2">3</span>
                        <strong class="text-white">Livraison Propre</strong>
                    </div>
                    <p class="mb-0 text-muted small">Seul le trafic légitime atteint votre infrastructure, sans latence ajoutée.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-7" data-aos="fade-left">
            <div class="card bg-dark border-secondary border-opacity-25 h-100 p-5 glass-card text-center">
                <h3 class="text-white mb-4">Capacité Globale</h3>
                <div class="display-1 fw-bold text-gradient mb-2">15 Tbps</div>
                <p class="text-muted mb-4">Plus grande capacité de mitigation en Europe</p>
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-start">
                        <div class="h4 text-white mb-0">L3/L4</div>
                        <small class="text-muted">Volumétrique</small>
                    </div>
                    <div class="vr bg-secondary"></div>
                    <div class="text-start">
                        <div class="h4 text-white mb-0">L7</div>
                        <small class="text-muted">Applicatif (Web)</small>
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
        background: linear-gradient(45deg, #ef4444, #f87171);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>