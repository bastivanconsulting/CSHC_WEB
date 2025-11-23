<?php $title = "CSHC - Infrastructure Cloud & Réseau"; ?>

<!-- Hero Section -->
<div class="position-relative overflow-hidden">
    <!-- Background Glow -->
    <div class="position-absolute top-0 start-50 translate-middle" style="width: 800px; height: 800px; background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, rgba(37,99,235,0) 70%); pointer-events: none; z-index: -1;"></div>

    <div class="container py-5 my-5">
        <div class="row align-items-center gx-5">
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill border border-primary border-opacity-25 bg-primary bg-opacity-10 mb-4">
                    <span class="badge bg-primary rounded-pill">NOUVEAU</span>
                    <span class="text-primary fw-medium small">Infrastructure VPLS 100G disponible</span>
                </div>

                <h1 class="display-3 fw-bolder mb-4 leading-tight">
                    L'infrastructure <br>
                    <span class="text-gradient">Cloud & Réseau</span> <br>
                    Nouvelle Génération.
                </h1>

                <p class="lead text-secondary mb-5" style="max-width: 500px;">
                    Déployez vos services critiques sur l'AS214939.
                    Une protection Anti-DDoS native, une latence minimale et une architecture conçue pour la résilience.
                </p>

                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="#services" class="btn btn-primary btn-lg px-5 py-3">
                        <i class="fas fa-rocket me-2"></i> Démarrer
                    </a>
                    <a href="/login" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-terminal me-2"></i> Console
                    </a>
                </div>

                <div class="mt-5 pt-4 border-top border-white border-opacity-10 d-flex gap-5 text-muted small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success"></i> <span>99.99% Uptime</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt text-primary"></i> <span>DDoS Protection</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-headset text-info"></i> <span>Support 24/7</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="position-relative">
                    <!-- Abstract Tech Visualization -->
                    <div class="glass-card border-0 p-0 overflow-hidden shadow-lg" style="transform: perspective(1000px) rotateY(-5deg);">
                        <div class="bg-dark p-2 border-bottom border-white border-opacity-10 d-flex gap-2">
                            <div class="rounded-circle bg-danger" style="width: 10px; height: 10px;"></div>
                            <div class="rounded-circle bg-warning" style="width: 10px; height: 10px;"></div>
                            <div class="rounded-circle bg-success" style="width: 10px; height: 10px;"></div>
                        </div>
                        <div class="p-4 font-monospace small text-success">
                            <div class="mb-2">$ ssh admin@core.cshc.network</div>
                            <div class="mb-2">Authenticating to gateway... <span class="text-white">OK</span></div>
                            <div class="mb-2">Checking AS214939 status... <span class="text-white">ONLINE</span></div>
                            <div class="mb-2">
                                > Uplinks: <br>
                                &nbsp;&nbsp;[+] LEVEL3 ..... 100Gbps [ACTIVE]<br>
                                &nbsp;&nbsp;[+] COGENT ..... 100Gbps [ACTIVE]<br>
                                &nbsp;&nbsp;[+] DE-CIX ..... 400Gbps [ACTIVE]
                            </div>
                            <div class="mb-2">
                                > DDoS Shield: <br>
                                &nbsp;&nbsp;[+] Scrubbing Centers ... READY<br>
                                &nbsp;&nbsp;[+] Traffic Analysis .... OK
                            </div>
                            <div class="typing-cursor">_</div>
                        </div>
                    </div>

                    <!-- Floating Badge -->
                    <div class="glass-card position-absolute bottom-0 end-0 p-3 mb-n4 me-n4 shadow-lg" style="max-width: 200px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-25 p-2 rounded-circle text-success">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <div class="small text-muted">Latence Réseau</div>
                                <div class="fw-bold text-white">1.2 ms</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- [HOOK] -->
<?= Hook::call('home_after_hero') ?>

<!-- Solutions Grid -->
<div class="container py-5 mb-5" id="services">
    <div class="row text-center mb-5">
        <div class="col-lg-6 mx-auto">
            <h6 class="text-primary fw-bold text-uppercase ls-2">Nos Solutions</h6>
            <h2 class="display-6 fw-bold mb-3">Conçu pour la performance</h2>
            <p class="text-secondary">Une gamme complète de services d'infrastructure pour propulser vos applications.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card 1 -->
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 d-flex flex-column">
                <div class="mb-4">
                    <span class="d-inline-block p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 text-primary">
                        <i class="fas fa-globe-europe fa-2x"></i>
                    </span>
                </div>
                <h4 class="mb-3">VPN Cloud HA</h4>
                <p class="text-muted flex-grow-1">
                    Accès distants sécurisés via OpenVPN & Wireguard.
                    Cluster haute disponibilité multi-sites pour une continuité de service sans faille.
                </p>
                <div class="pt-4 mt-auto border-top border-white border-opacity-10">
                    <ul class="list-unstyled mb-0 small text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Chiffrement AES-256</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i> IP Dédiée</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 d-flex flex-column position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 bg-primary text-white text-xs fw-bold px-2 py-1 rounded-bottom-start">POPULAIRE</div>
                <div class="mb-4">
                    <span class="d-inline-block p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 text-success">
                        <i class="fas fa-project-diagram fa-2x"></i>
                    </span>
                </div>
                <h4 class="mb-3">VPLS Cloud</h4>
                <p class="text-muted flex-grow-1">
                    Extension de LAN niveau 2 (Layer 2).
                    Interconnectez vos datacenters, bureaux et instances Cloud comme s'ils étaient sur le même switch.
                </p>
                <div class="pt-4 mt-auto border-top border-white border-opacity-10">
                    <ul class="list-unstyled mb-0 small text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Jumbo Frames</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i> Faible Latence</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 d-flex flex-column">
                <div class="mb-4">
                    <span class="d-inline-block p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </span>
                </div>
                <h4 class="mb-3">Anti-DDoS & FW</h4>
                <p class="text-muted flex-grow-1">
                    Protection périmétrique avancée et Firewall managé.
                    Filtrage des flux illégitimes en amont de votre infrastructure pour garantir la disponibilité.
                </p>
                <div class="pt-4 mt-auto border-top border-white border-opacity-10">
                    <ul class="list-unstyled mb-0 small text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Arista Clean Traffic</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i> 2Tbps Mitigation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Partners / Tech Stack -->
    <div class="row mt-5 pt-5 justify-content-center align-items-center opacity-50 grayscale">
        <div class="col-6 col-md-3 text-center mb-4 mb-md-0">
            <h5 class="fw-bold text-white mb-0"><i class="fas fa-microchip me-2"></i> MIKROTIK</h5>
        </div>
        <div class="col-6 col-md-3 text-center mb-4 mb-md-0">
            <h5 class="fw-bold text-white mb-0"><i class="fas fa-network-wired me-2"></i> ARISTA</h5>
        </div>
        <div class="col-6 col-md-3 text-center mb-4 mb-md-0">
            <h5 class="fw-bold text-white mb-0"><i class="fab fa-linux me-2"></i> DEBIAN</h5>
        </div>
        <div class="col-6 col-md-3 text-center">
            <h5 class="fw-bold text-white mb-0"><i class="fas fa-server me-2"></i> DELL EMC</h5>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="container mb-5">
    <div class="glass-card p-5 text-center position-relative overflow-hidden">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(37,99,235,0.2), transparent 50%); pointer-events: none;"></div>

        <h2 class="fw-bold mb-3">Prêt à déployer votre infrastructure ?</h2>
        <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
            Rejoignez les entreprises qui font confiance à CSHC pour leur connectivité critique.
            Mise en service en moins de 15 minutes.
        </p>
        <a href="/login" class="btn btn-primary btn-lg px-5">Créer un compte</a>
    </div>
</div>

<!-- [HOOK] -->
<?= Hook::call('home_bottom') ?>