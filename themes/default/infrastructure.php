<div class="hero-section text-center py-5 position-relative overflow-hidden">
    <div class="container py-5" data-aos="fade-up">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3 px-3 py-2 rounded-pill">
            <i class="fas fa-network-wired me-2"></i>Réseau Tier-1
        </span>
        <h1 class="display-3 fw-bold text-white mb-4">Une Infrastructure <span class="text-gradient">Globale</span></h1>
        <p class="lead text-muted mb-5 mx-auto" style="max-width: 700px;">
            Déployez vos services sur notre backbone de 12 Tbps. Une redondance totale pour une disponibilité garantie à 99.99%.
        </p>
    </div>

    <!-- Abstract Background Element -->
    <div class="position-absolute top-50 start-50 translate-middle" style="z-index: -1; opacity: 0.1;">
        <i class="fas fa-globe text-primary" style="font-size: 50vw;"></i>
    </div>
</div>

<div class="container mb-5">
    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100 bg-dark border-secondary border-opacity-25 glass-card">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 text-primary display-4">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3 class="text-white fw-bold">12 Tbps+</h3>
                    <p class="text-muted">Capacité Backbone</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100 bg-dark border-secondary border-opacity-25 glass-card">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 text-primary display-4">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="text-white fw-bold">32</h3>
                    <p class="text-muted">Points de Présence (PoPs)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card h-100 bg-dark border-secondary border-opacity-25 glass-card">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 text-primary display-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-white fw-bold">VAC</h3>
                    <p class="text-muted">Protection Anti-DDoS Standard</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Details with Alpine.js interaction -->
    <div class="row align-items-center mb-5" x-data="{ activeTab: 'eu' }">
        <div class="col-lg-5" data-aos="fade-right">
            <h2 class="text-white fw-bold mb-4">Présence Internationale</h2>
            <p class="text-muted mb-4">Nous opérons nos propres datacenters et points de présence sur 3 continents.</p>

            <div class="d-flex flex-column gap-3">
                <button @click="activeTab = 'eu'" :class="activeTab === 'eu' ? 'bg-primary text-white border-primary' : 'bg-transparent text-muted border-secondary'" class="btn text-start p-3 border border-opacity-25 d-flex align-items-center transition-all">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <div>
                        <strong class="d-block">Europe</strong>
                        <small>Paris, Frankfurt, London, Amsterdam</small>
                    </div>
                </button>
                <button @click="activeTab = 'na'" :class="activeTab === 'na' ? 'bg-primary text-white border-primary' : 'bg-transparent text-muted border-secondary'" class="btn text-start p-3 border border-opacity-25 d-flex align-items-center transition-all">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <div>
                        <strong class="d-block">Amérique du Nord</strong>
                        <small>Beauharnois, Vint Hill, Hillsboro</small>
                    </div>
                </button>
                <button @click="activeTab = 'apac'" :class="activeTab === 'apac' ? 'bg-primary text-white border-primary' : 'bg-transparent text-muted border-secondary'" class="btn text-start p-3 border border-opacity-25 d-flex align-items-center transition-all">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <div>
                        <strong class="d-block">Asie-Pacifique</strong>
                        <small>Singapore, Sydney</small>
                    </div>
                </button>
            </div>
        </div>
        <div class="col-lg-7" data-aos="fade-left">
            <div class="card bg-dark border-secondary border-opacity-25 h-100 p-4 position-relative overflow-hidden">
                <!-- Dynamic Content based on Alpine state -->
                <div x-show="activeTab === 'eu'" x-transition:enter="animate__animated animate__fadeIn">
                    <h4 class="text-white mb-3">Réseau Europe</h4>
                    <p class="text-muted">Notre coeur de réseau européen est interconnecté via une boucle optique redondante de 400G.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Latence < 5ms entre capitales</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Interconnexion directe avec +1500 opérateurs</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Certification ISO 27001 (Datacenters uniquement)</li>
                    </ul>
                    <img src="https://via.placeholder.com/600x300/0f172a/2563eb?text=Map+Europe" alt="Europe Map" class="img-fluid rounded mt-3 opacity-75 border border-secondary border-opacity-25">
                </div>

                <div x-show="activeTab === 'na'" x-transition:enter="animate__animated animate__fadeIn" style="display: none;">
                    <h4 class="text-white mb-3">Réseau Amérique du Nord</h4>
                    <p class="text-muted">Une infrastructure massive pour absorber les plus grandes attaques DDoS du monde.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Capacité de mitigation de 15 Tbps</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Conformité SOC 2 Type II</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Double adduction électrique</li>
                    </ul>
                    <img src="https://via.placeholder.com/600x300/0f172a/2563eb?text=Map+North+America" alt="NA Map" class="img-fluid rounded mt-3 opacity-75 border border-secondary border-opacity-25">
                </div>

                <div x-show="activeTab === 'apac'" x-transition:enter="animate__animated animate__fadeIn" style="display: none;">
                    <h4 class="text-white mb-3">Réseau Asie-Pacifique</h4>
                    <p class="text-muted">Extension stratégique pour servir les marchés émergents avec une latence minimale.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Routes optimisées vers la Chine</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Support local 24/7</li>
                    </ul>
                    <img src="https://via.placeholder.com/600x300/0f172a/2563eb?text=Map+Asia" alt="Asia Map" class="img-fluid rounded mt-3 opacity-75 border border-secondary border-opacity-25">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Quick inline styles to match the request until moved to main CSS */
    .glass-card {
        background: rgba(255, 255, 255, 0.03) !important;
        backdrop-filter: blur(10px);
    }
    .text-gradient {
        background: linear-gradient(45deg, #2563eb, #60a5fa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>