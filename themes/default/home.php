<?php $title = "CSHC - Infrastructure Cloud & Réseau"; ?>

<!-- Hero Section -->
<div class="container py-5 my-5">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <div class="badge bg-primary bg-opacity-25 text-primary border border-primary mb-3 rounded-pill px-3 py-2">
                <i class="fas fa-bolt me-2"></i> Haute Disponibilité Garantie
            </div>
            <h1 class="display-3 fw-bold mb-4">L'infrastructure <span class="text-primary">CoreSecure</span> de demain.</h1>
            <p class="lead text-muted mb-5">Solution VPN Cloud, VPLS et FTTx opérée sur l'AS214939. Connectez vos collaborateurs et sites distants avec une sécurité militaire et une latence minimale.</p>
            <div class="d-flex gap-3">
                <a href="#services" class="btn btn-primary btn-lg px-5">Explorer les offres</a>
                <a href="/login" class="btn btn-outline-light btn-lg px-4">Accès Client</a>
            </div>
        </div>
        <div class="col-lg-5 mt-5 mt-lg-0 text-center">
            <div class="position-relative">
                <div class="position-absolute top-50 start-50 translate-middle" style="width: 300px; height: 300px; background: var(--primary); filter: blur(120px); opacity: 0.4; z-index: -1;"></div>
                <i class="fas fa-server fa-10x text-white opacity-25"></i>
                <div class="position-absolute top-50 start-50 translate-middle">
                    <i class="fas fa-shield-alt fa-4x text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- [HOOK] : Zone d'injection pour les modules APRÈS le Hero (ex: Bannière Flash Info) -->
<?= Hook::call('home_after_hero') ?>

<!-- Services Grid -->
<div class="container mb-5" id="services">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <div class="d-inline-block p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
                    <i class="fas fa-globe-europe fa-2x"></i>
                </div>
                <h3>VPN Cloud HA</h3>
                <p class="text-muted">Accès distants sécurisés via OpenVPN & Wireguard. Cluster haute disponibilité pour une continuité de service sans faille.</p>
                <span class="badge badge-tech rounded-pill">Chiffrement AES-256</span>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <div class="d-inline-block p-3 rounded-circle bg-success bg-opacity-10 text-success mb-3">
                    <i class="fas fa-project-diagram fa-2x"></i>
                </div>
                <h3>VPLS Cloud</h3>
                <p class="text-muted">Extension de LAN niveau 2. Interconnectez vos datacenters et bureaux comme s'ils étaient dans la même pièce.</p>
                <span class="badge badge-tech rounded-pill">Layer 2</span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <div class="d-inline-block p-3 rounded-circle bg-danger bg-opacity-10 text-danger mb-3">
                    <i class="fas fa-user-shield fa-2x"></i>
                </div>
                <h3>Anti-DDoS & FW</h3>
                <p class="text-muted">Protection périmétrique avancée. Filtrage des flux illégitimes en amont de votre infrastructure.</p>
                <span class="badge badge-tech rounded-pill">Arista Clean Traffic</span>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 justify-content-center text-center opacity-50 grayscale">
        <div class="col-auto px-4"><h4 class="fw-bold text-white"><i class="fas fa-microchip"></i> MIKROTIK</h4></div>
        <div class="col-auto px-4"><h4 class="fw-bold text-white"><i class="fas fa-network-wired"></i> ARISTA</h4></div>
        <div class="col-auto px-4"><h4 class="fw-bold text-white"><i class="fab fa-linux"></i> LINUX</h4></div>
    </div>
</div>

<!-- [HOOK] : Zone d'injection pour les modules en BAS de page (ex: News, Partenaires) -->
<?= Hook::call('home_bottom') ?>