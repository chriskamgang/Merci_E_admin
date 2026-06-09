<script>
import { Head } from '@inertiajs/vue3';
import { Autoplay, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/autoplay";
import "swiper/css/pagination";

export default {
  components: { Head, Swiper, SwiperSlide },
  data() {
    return {
      Autoplay, Pagination,
      screenshotTab: 'user',
    }
  },
  props: {
    appName: { type: String, default: 'Merci E' },
    loginUrl: { type: String, default: '/mi-admin' },
    userPlayStore: { type: String, default: '#' },
    userAppStore: { type: String, default: '#' },
    driverPlayStore: { type: String, default: '#' },
    driverAppStore: { type: String, default: '#' },
  },
};
</script>

<template>
  <div class="vitrine">
    <Head title="Merci E — Votre taxi intelligent" />

    <!-- NAVBAR -->
    <nav class="vitrine-nav">
      <div class="vitrine-container vitrine-nav-inner">
        <div class="vitrine-logo">
          <img src="/landing/logo.png" alt="Merci E" onerror="this.style.display='none'" />
          <span>Merci E</span>
        </div>
        <div class="vitrine-nav-links">
          <a href="#features">Fonctionnalites</a>
          <a href="#screenshots">Application</a>
          <a href="#download">Telecharger</a>
          <a :href="loginUrl" class="vitrine-btn-nav">Dashboard</a>
        </div>
      </div>
    </nav>

    <!-- HERO -->
    <section class="vitrine-hero">
      <div class="vitrine-hero-bg"></div>
      <div class="vitrine-container vitrine-hero-content">
        <div class="vitrine-hero-text">
          <div class="vitrine-badge">Bafoussam, Cameroun</div>
          <h1>Votre trajet,<br/><span class="vitrine-highlight">simple et rapide.</span></h1>
          <p>Merci E — le taxi intelligent qui vous accompagne partout dans la ville. Commandez en quelques secondes, payez par Mobile Money.</p>
          <div class="vitrine-hero-actions">
            <a href="#download" class="vitrine-btn-primary">Telecharger l'app</a>
            <a :href="loginUrl" class="vitrine-btn-outline">Espace Admin</a>
          </div>
          <div class="vitrine-stats">
            <div class="vitrine-stat">
              <strong>500+</strong>
              <span>Courses</span>
            </div>
            <div class="vitrine-stat">
              <strong>50+</strong>
              <span>Chauffeurs</span>
            </div>
            <div class="vitrine-stat">
              <strong>4.8</strong>
              <span>Note App</span>
            </div>
          </div>
        </div>
        <div class="vitrine-hero-phones">
          <div class="vitrine-phone vitrine-phone-back">
            <img src="/landing/screenshots/driver-home.png" alt="App Chauffeur" />
          </div>
          <div class="vitrine-phone vitrine-phone-front">
            <img src="/landing/screenshots/user-home.png" alt="App Passager" />
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section class="vitrine-features" id="features">
      <div class="vitrine-container">
        <h2 class="vitrine-section-title">Pourquoi choisir Merci E ?</h2>
        <div class="vitrine-section-line"></div>
        <div class="vitrine-features-grid">
          <div class="vitrine-feature-card">
            <div class="vitrine-feature-icon">
              <i class="ri-taxi-line"></i>
            </div>
            <h3>Reservation rapide</h3>
            <p>Commandez un taxi en quelques secondes. Un chauffeur arrive a votre position en moins de 5 minutes.</p>
          </div>
          <div class="vitrine-feature-card">
            <div class="vitrine-feature-icon icon-green">
              <i class="ri-money-cny-circle-line"></i>
            </div>
            <h3>Prix transparents</h3>
            <p>Les tarifs sont affiches avant la course. Aucun frais cache, paiement par Mobile Money ou especes.</p>
          </div>
          <div class="vitrine-feature-card">
            <div class="vitrine-feature-icon icon-orange">
              <i class="ri-shield-check-line"></i>
            </div>
            <h3>Securite garantie</h3>
            <p>Chauffeurs verifies, suivi GPS en temps reel et partage de trajet avec vos proches.</p>
          </div>
          <div class="vitrine-feature-card">
            <div class="vitrine-feature-icon icon-purple">
              <i class="ri-customer-service-2-line"></i>
            </div>
            <h3>Support 7j/7</h3>
            <p>Notre equipe est disponible en francais et en langues locales pour vous accompagner a tout moment.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SCREENSHOTS -->
    <section class="vitrine-screenshots" id="screenshots">
      <div class="vitrine-container">
        <h2 class="vitrine-section-title vitrine-title-light">Decouvrez l'application</h2>
        <div class="vitrine-section-line"></div>
        <div class="vitrine-toggle">
          <button @click="screenshotTab = 'user'" :class="{ active: screenshotTab === 'user' }">Passager</button>
          <button @click="screenshotTab = 'driver'" :class="{ active: screenshotTab === 'driver' }">Chauffeur</button>
        </div>

        <div v-if="screenshotTab === 'user'" class="vitrine-screenshots-swiper">
          <swiper :loop="true" :modules="[Autoplay, Pagination]" :centeredSlides="true" :autoplay="{ delay: 3000, disableOnInteraction: false }" :pagination="{ clickable: true }" :breakpoints="{ 320: { slidesPerView: 1.3, spaceBetween: 15 }, 640: { slidesPerView: 2.2, spaceBetween: 20 }, 1024: { slidesPerView: 3.5, spaceBetween: 25 } }">
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/user-home.png" alt="Accueil" /></div><p>Accueil</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/user-booking.png" alt="Reservation" /></div><p>Reservation</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/user-history.png" alt="Historique" /></div><p>Historique</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/user-wallet.png" alt="Portefeuille" /></div><p>Portefeuille</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/user-account.png" alt="Compte" /></div><p>Mon compte</p></div></swiper-slide>
          </swiper>
        </div>

        <div v-else class="vitrine-screenshots-swiper">
          <swiper :loop="true" :modules="[Autoplay, Pagination]" :centeredSlides="true" :autoplay="{ delay: 3000, disableOnInteraction: false }" :pagination="{ clickable: true }" :breakpoints="{ 320: { slidesPerView: 1.3, spaceBetween: 15 }, 640: { slidesPerView: 2.2, spaceBetween: 20 }, 1024: { slidesPerView: 3.5, spaceBetween: 25 } }">
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/driver-home.png" alt="Dashboard" /></div><p>Tableau de bord</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/driver-ride.png" alt="Course" /></div><p>Course en cours</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/driver-earnings.png" alt="Gains" /></div><p>Gains</p></div></swiper-slide>
            <swiper-slide><div class="vitrine-ss-card"><div class="vitrine-ss-frame"><img src="/landing/screenshots/driver-account.png" alt="Compte" /></div><p>Mon compte</p></div></swiper-slide>
          </swiper>
        </div>
      </div>
    </section>

    <!-- DOWNLOAD -->
    <section class="vitrine-download" id="download">
      <div class="vitrine-container vitrine-download-inner">
        <div class="vitrine-download-text">
          <h2>Telecharger Merci E</h2>
          <p>Disponible gratuitement sur Android et iOS. Commencez a voyager des maintenant.</p>
          <h4>Application Passager</h4>
          <div class="vitrine-store-btns">
            <a :href="userPlayStore" target="_blank" class="vitrine-store-btn">
              <i class="ri-google-play-fill"></i> Google Play
            </a>
            <a :href="userAppStore" target="_blank" class="vitrine-store-btn apple">
              <i class="ri-apple-fill"></i> App Store
            </a>
          </div>
          <h4>Application Chauffeur</h4>
          <div class="vitrine-store-btns">
            <a :href="driverPlayStore" target="_blank" class="vitrine-store-btn">
              <i class="ri-google-play-fill"></i> Google Play
            </a>
            <a :href="driverAppStore" target="_blank" class="vitrine-store-btn apple">
              <i class="ri-apple-fill"></i> App Store
            </a>
          </div>
        </div>
        <div class="vitrine-download-phones">
          <div class="vitrine-phone">
            <img src="/landing/screenshots/user-wallet.png" alt="App" />
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="vitrine-footer">
      <div class="vitrine-container vitrine-footer-inner">
        <div class="vitrine-footer-left">
          <span class="vitrine-footer-brand">Merci E</span>
          <p>Votre service de taxi intelligent a Bafoussam, Cameroun.</p>
        </div>
        <div class="vitrine-footer-right">
          <a :href="loginUrl">Dashboard Admin</a>
          <a href="/privacy">Confidentialite</a>
          <a href="/terms">Conditions</a>
        </div>
      </div>
      <div class="vitrine-footer-bottom">
        <p>2025 &copy; Merci E — Bafoussam, Cameroun. Dev by <strong>Chris Skyler</strong></p>
      </div>
    </footer>
  </div>
</template>

<style>
/* ===== VITRINE RESET ===== */
.vitrine { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #1a1a2e; overflow-x: hidden; }
.vitrine * { box-sizing: border-box; margin: 0; padding: 0; }
.vitrine a { text-decoration: none; color: inherit; }
.vitrine-container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

/* ===== NAV ===== */
.vitrine-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: rgba(10, 15, 40, 0.95); backdrop-filter: blur(12px); padding: 12px 0; }
.vitrine-nav-inner { display: flex; justify-content: space-between; align-items: center; }
.vitrine-logo { display: flex; align-items: center; gap: 10px; }
.vitrine-logo img { height: 36px; width: auto; }
.vitrine-logo span { font-size: 22px; font-weight: 800; color: #fff; }
.vitrine-nav-links { display: flex; align-items: center; gap: 28px; }
.vitrine-nav-links a { color: rgba(255,255,255,0.75); font-size: 14px; font-weight: 500; transition: color 0.2s; }
.vitrine-nav-links a:hover { color: #fff; }
.vitrine-btn-nav { background: #0050a0; color: #fff !important; padding: 8px 20px; border-radius: 8px; font-weight: 600; transition: background 0.2s; }
.vitrine-btn-nav:hover { background: #003d7a; }

/* ===== HERO ===== */
.vitrine-hero { position: relative; min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #0a0f28 0%, #0d1a3a 50%, #0a1525 100%); padding-top: 80px; overflow: hidden; }
.vitrine-hero-bg { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 60px 60px; }
.vitrine-hero-content { display: flex; align-items: center; gap: 60px; position: relative; z-index: 1; width: 100%; }
.vitrine-hero-text { flex: 1; }
.vitrine-badge { display: inline-block; background: rgba(0,80,160,0.2); border: 1px solid rgba(0,80,160,0.4); color: #6ab0ff; padding: 6px 16px; border-radius: 50px; font-size: 13px; font-weight: 500; margin-bottom: 20px; }
.vitrine-hero-text h1 { font-size: 52px; font-weight: 800; color: #fff; line-height: 1.15; margin-bottom: 20px; }
.vitrine-highlight { background: linear-gradient(90deg, #0050a0, #1a72cc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.vitrine-hero-text p { color: rgba(255,255,255,0.65); font-size: 17px; line-height: 1.7; margin-bottom: 32px; max-width: 480px; }
.vitrine-hero-actions { display: flex; gap: 14px; margin-bottom: 40px; }
.vitrine-btn-primary { background: #0050a0; color: #fff; padding: 14px 32px; border-radius: 10px; font-weight: 600; font-size: 15px; transition: all 0.2s; }
.vitrine-btn-primary:hover { background: #003d7a; transform: translateY(-2px); }
.vitrine-btn-outline { border: 1.5px solid rgba(255,255,255,0.25); color: #fff; padding: 14px 32px; border-radius: 10px; font-weight: 600; font-size: 15px; transition: all 0.2s; }
.vitrine-btn-outline:hover { border-color: #fff; background: rgba(255,255,255,0.05); }
.vitrine-stats { display: flex; gap: 40px; }
.vitrine-stat { display: flex; flex-direction: column; }
.vitrine-stat strong { font-size: 28px; font-weight: 800; color: #fff; }
.vitrine-stat span { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 2px; }

.vitrine-hero-phones { flex: 0 0 380px; position: relative; height: 520px; }
.vitrine-phone { border-radius: 28px; overflow: hidden; border: 5px solid #1a1a2e; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
.vitrine-phone img { width: 100%; display: block; }
.vitrine-phone-front { position: absolute; width: 240px; right: 0; bottom: 0; z-index: 2; }
.vitrine-phone-back { position: absolute; width: 220px; left: 0; top: 0; z-index: 1; opacity: 0.85; transform: rotate(-5deg); }

/* ===== FEATURES ===== */
.vitrine-features { padding: 100px 0; background: #f8fbff; }
.vitrine-section-title { font-size: 34px; font-weight: 800; text-align: center; margin-bottom: 8px; }
.vitrine-section-line { width: 60px; height: 4px; background: linear-gradient(90deg, #0050a0, #1a72cc); border-radius: 2px; margin: 0 auto 50px; }
.vitrine-features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.vitrine-feature-card { background: #fff; border-radius: 16px; padding: 32px 24px; text-align: center; border: 1px solid #eef2f7; transition: all 0.3s; }
.vitrine-feature-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,50,100,0.08); }
.vitrine-feature-icon { width: 60px; height: 60px; border-radius: 14px; background: rgba(0,80,160,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; color: #0050a0; }
.vitrine-feature-icon.icon-green { background: rgba(16,185,129,0.1); color: #10b981; }
.vitrine-feature-icon.icon-orange { background: rgba(245,158,11,0.1); color: #f59e0b; }
.vitrine-feature-icon.icon-purple { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.vitrine-feature-card h3 { font-size: 17px; font-weight: 700; margin-bottom: 10px; }
.vitrine-feature-card p { font-size: 14px; color: #6b7280; line-height: 1.6; }

/* ===== SCREENSHOTS ===== */
.vitrine-screenshots { padding: 100px 0; background: linear-gradient(135deg, #0a0f28, #0d1a3a); }
.vitrine-title-light { color: #fff; }
.vitrine-toggle { display: flex; justify-content: center; gap: 0; margin-bottom: 40px; }
.vitrine-toggle button { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); padding: 10px 28px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.vitrine-toggle button:first-child { border-radius: 8px 0 0 8px; }
.vitrine-toggle button:last-child { border-radius: 0 8px 8px 0; }
.vitrine-toggle button.active { background: #0050a0; color: #fff; border-color: #0050a0; }
.vitrine-screenshots-swiper { padding-bottom: 50px; }
.vitrine-screenshots-swiper .swiper { padding-bottom: 40px; }
.vitrine-ss-card { text-align: center; transition: transform 0.3s; }
.vitrine-ss-card:hover { transform: translateY(-6px); }
.vitrine-ss-frame { width: 200px; margin: 0 auto; border-radius: 24px; overflow: hidden; border: 5px solid #1a1a2e; background: #1a1a2e; box-shadow: 0 12px 35px rgba(0,0,0,0.3); }
.vitrine-ss-frame img { width: 100%; display: block; }
.vitrine-ss-card p { color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 600; margin-top: 12px; }
.vitrine-screenshots-swiper .swiper-slide { opacity: 0.5; transform: scale(0.9); transition: all 0.3s; }
.vitrine-screenshots-swiper .swiper-slide-active { opacity: 1; transform: scale(1); }
.vitrine-screenshots-swiper .swiper-pagination-bullet { background: #fff; opacity: 0.3; }
.vitrine-screenshots-swiper .swiper-pagination-bullet-active { opacity: 1; background: #0050a0; }

/* ===== DOWNLOAD ===== */
.vitrine-download { padding: 100px 0; background: #f8fbff; }
.vitrine-download-inner { display: flex; align-items: center; gap: 60px; }
.vitrine-download-text { flex: 1; }
.vitrine-download-text h2 { font-size: 36px; font-weight: 800; margin-bottom: 14px; }
.vitrine-download-text p { color: #6b7280; font-size: 16px; margin-bottom: 28px; line-height: 1.6; }
.vitrine-download-text h4 { font-size: 14px; font-weight: 600; color: #6b7280; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
.vitrine-store-btns { display: flex; gap: 12px; margin-bottom: 24px; }
.vitrine-store-btn { display: inline-flex; align-items: center; gap: 8px; background: #1a1a2e; color: #fff; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; transition: all 0.2s; }
.vitrine-store-btn:hover { background: #0050a0; transform: translateY(-2px); }
.vitrine-store-btn i { font-size: 20px; }
.vitrine-download-phones { flex: 0 0 260px; }
.vitrine-download-phones .vitrine-phone { width: 240px; margin: 0 auto; }

/* ===== FOOTER ===== */
.vitrine-footer { background: #0a0f28; color: rgba(255,255,255,0.6); padding: 40px 0 0; }
.vitrine-footer-inner { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.vitrine-footer-brand { font-size: 22px; font-weight: 800; color: #fff; display: block; margin-bottom: 8px; }
.vitrine-footer-left p { font-size: 14px; max-width: 300px; }
.vitrine-footer-right { display: flex; gap: 24px; }
.vitrine-footer-right a { font-size: 14px; transition: color 0.2s; }
.vitrine-footer-right a:hover { color: #fff; }
.vitrine-footer-bottom { text-align: center; padding: 20px 0; }
.vitrine-footer-bottom p { font-size: 13px; color: rgba(255,255,255,0.4); }
.vitrine-footer-bottom strong { color: rgba(255,255,255,0.7); }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .vitrine-hero-content { flex-direction: column; text-align: center; padding-top: 40px; }
  .vitrine-hero-text h1 { font-size: 34px; }
  .vitrine-hero-text p { margin-left: auto; margin-right: auto; }
  .vitrine-hero-actions { justify-content: center; }
  .vitrine-stats { justify-content: center; }
  .vitrine-hero-phones { flex: none; height: 360px; width: 100%; display: flex; justify-content: center; }
  .vitrine-phone-front { width: 200px; position: relative; right: auto; bottom: auto; }
  .vitrine-phone-back { width: 180px; position: absolute; left: 10%; top: 10px; }
  .vitrine-features-grid { grid-template-columns: repeat(2, 1fr); }
  .vitrine-download-inner { flex-direction: column; text-align: center; }
  .vitrine-store-btns { justify-content: center; }
  .vitrine-download-phones { flex: none; }
  .vitrine-footer-inner { flex-direction: column; gap: 20px; text-align: center; align-items: center; }
  .vitrine-footer-right { flex-wrap: wrap; justify-content: center; }
  .vitrine-nav-links a:not(.vitrine-btn-nav) { display: none; }
}
@media (max-width: 480px) {
  .vitrine-features-grid { grid-template-columns: 1fr; }
  .vitrine-hero-actions { flex-direction: column; align-items: center; }
}
</style>
