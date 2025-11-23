<?php $title = "Solutions Cloud & Réseau Sécurisées"; ?>

<!-- Hero Section Modern -->
<section class="position-relative py-5 overflow-hidden">
    <!-- Background Elements -->
    <div class="position-absolute top-0 start-50 translate-middle" style="width: 800px; height: 800px; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, rgba(0,0,0,0) 70%); z-index: -1;"></div>

    <div class="container position-relative z-1 py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-10 border border-primary border-opacity-20 text-primary text-sm fw-bold mb-4">
                    <span class="d-block w-2 h-2 rounded-circle bg-primary animate-pulse"></span>
                    Réseau Nouvelle Génération
                </div>
                <h1 class="display-4 fw-bold mb-4 lh-tight">
                    L'infrastructure <br>
                    <span class="text-gradient">Hybride & Sécurisée</span> <br>
                    pour votre entreprise.
                </h1>
                <p class="lead text-muted mb-5 pe-lg-5">
                    Connectez vos sites, vos clouds et vos collaborateurs avec une latence minimale.
                    Une solution souveraine opérée sur l'AS214939.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="/login" class="btn btn-primary btn-lg px-5 py-3 rounded-3 shadow-lg hover-scale">
                        <i class="fas fa-rocket me-2"></i> Démarrer
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3 rounded-3 hover-scale">
                        En savoir plus
                    </a>
                </div>

                <div class="mt-5 pt-4 border-top border-secondary border-opacity-10 d-flex gap-5 text-muted small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success"></i> Setup Instantané
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success"></i> Support 24/7
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success"></i> 99.9% Uptime
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="position-relative">
                    <!-- Abstract Tech Visual -->
                    <div class="glass-card p-4 position-relative z-2 rotate-n3">
                        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-secondary border-opacity-10 pb-3">
                            <div class="d-flex gap-2">
                                <div class="w-3 h-3 rounded-circle bg-danger"></div>
                                <div class="w-3 h-3 rounded-circle bg-warning"></div>
                                <div class="w-3 h-3 rounded-circle bg-success"></div>
                            </div>
                            <div class="text-muted text-xs font-monospace">root@cshc-core:~#</div>
                        </div>
                        <div class="font-monospace text-sm text-secondary">
                            <div class="mb-2"><span class="text-success">➜</span> <span class="text-primary">init-connection</span> --target=hq-paris</div>
                            <div class="mb-2 text-white">Establishing secure tunnel... <span class="text-success">OK</span></div>
                            <div class="mb-2 text-white">Verifying encryption keys... <span class="text-success">OK</span></div>
                            <div class="mb-2 text-white">Route optimization... <span class="text-warning">2ms latency</span></div>
                            <div class="mb-0"><span class="text-success">➜</span> Connection active.</div>
                        </div>
                    </div>

                    <!-- Floating Badge -->
                    <div class="position-absolute top-100 start-0 translate-middle-y z-3 ms-n4 mt-n5 d-none d-lg-block">
                        <div class="glass-card p-3 d-flex align-items-center gap-3 shadow-lg">
                            <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle">
                                <i class="fas fa-shield-alt fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-xs text-muted text-uppercase fw-bold">Statut</div>
                                <div class="text-white fw-bold">Protégé par Arista</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section id="features" class="py-5 bg-dark bg-opacity-50">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold mb-3">Une suite complète</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                De la simple interconnexion VPN au transit IP complexe, nous avons les outils pour sécuriser votre infrastructure.
            </p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card h-100 hover-up">
                    <div class="glass-card-body">
                        <div class="d-inline-flex p-3 rounded-xl bg-primary bg-opacity-10 text-primary mb-4">
                            <i class="fas fa-cloud fa-2x"></i>
                        </div>
                        <h3 class="h4 fw-bold text-white mb-3">VPN Cloud Hybride</h3>
                        <p class="text-muted mb-4">
                            Reliez vos datacenters, bureaux et télétravailleurs sur un réseau privé unifié. Compatible OpenVPN, WireGuard et IPsec.
                        </p>
                        <a href="#" class="text-primary fw-bold text-sm text-decoration-none">Découvrir <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card h-100 hover-up">
                    <div class="glass-card-body">
                        <div class="d-inline-flex p-3 rounded-xl bg-purple bg-opacity-10 text-purple mb-4">
                            <i class="fas fa-ethernet fa-2x"></i>
                        </div>
                        <h3 class="h4 fw-bold text-white mb-3">VPLS Layer 2</h3>
                        <p class="text-muted mb-4">
                            Extension de LAN géographique. Vos serveurs à Paris et New York communiquent comme s'ils étaient sur le même switch.
                        </p>
                        <a href="#" class="text-purple fw-bold text-sm text-decoration-none">Découvrir <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="glass-card h-100 hover-up">
                    <div class="glass-card-body">
                        <div class="d-inline-flex p-3 rounded-xl bg-accent bg-opacity-10 text-accent mb-4">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                        <h3 class="h4 fw-bold text-white mb-3">Hébergement FTTx</h3>
                        <p class="text-muted mb-4">
                            Des machines virtuelles haute performance hébergées au plus près de vos points de présence réseau.
                        </p>
                        <a href="#" class="text-accent fw-bold text-sm text-decoration-none">Découvrir <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats / Trust -->
<section class="py-5 border-top border-secondary border-opacity-10">
    <div class="container">
        <div class="row text-center opacity-50 grayscale g-4 align-items-center justify-content-center">
            <div class="col-6 col-md-3">
                <h4 class="fw-bold text-white mb-0"><i class="fas fa-microchip me-2"></i> MIKROTIK</h4>
            </div>
            <div class="col-6 col-md-3">
                <h4 class="fw-bold text-white mb-0"><i class="fas fa-network-wired me-2"></i> ARISTA</h4>
            </div>
            <div class="col-6 col-md-3">
                <h4 class="fw-bold text-white mb-0"><i class="fab fa-linux me-2"></i> LINUX</h4>
            </div>
            <div class="col-6 col-md-3">
                <h4 class="fw-bold text-white mb-0"><i class="fas fa-shield-virus me-2"></i> FORTINET</h4>
            </div>
        </div>
    </div>
</section>

<?= Hook::call('home_bottom') ?>
