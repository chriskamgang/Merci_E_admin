<script>
import { Autoplay, Navigation, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/autoplay";
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from "vue";
import axios from "axios";
import FormValidation from "@/Components/FormValidation.vue";

export default {
  data() {
    return {
      activeStep: 0,
      direction: 1,
      maxStep: 3,
      stepHeight: 195,
      userType: 'user',
      driverType: 'driver',
      screenshotTab: 'user',
      Autoplay, Navigation, Pagination,
      mobileMenuOpen: false,
      scrolled: false,
      header: window.headers,
      locales: this.$page.props.locales,
      selectedLocale: this.$page.props.singlelandingHeader ? this.$page.props.singlelandingHeader.locale : 'en',
      headers: this.$page.props.singlelandingHeader,
      langDropdownOpen: false,
    }
  },
  components: {
    Swiper,
    SwiperSlide,
    FormValidation,
    Head
  },
  props: {
    home: '',
    about: '',
    user_app: '',
    driver_app: '',
    contact: '',
    singlelandingpage: Object,
  },
  setup(props) {
    const form = useForm({
      name: props.singlelandingpage ? props.singlelandingpage.name || "" : "",
      mail: props.singlelandingpage ? props.singlelandingpage.mail || "" : "",
      subject: props.singlelandingpage ? props.singlelandingpage.subject || "" : "",
      comments: props.singlelandingpage ? props.singlelandingpage.comments || "" : "",
    });

    const validationRules = {
      name: { required: true },
      mail: { required: true },
      subject: { required: true },
      comments: { required: true },
    };

    const validationRef = ref(null);
    const errors = ref({});
    const successMessage = ref(props.successMessage || '');
    const alertMessage = ref(props.alertMessage || '');

    const dismissMessage = () => {
      successMessage.value = "";
      alertMessage.value = "";
    };

    const handleSubmit = async () => {
      errors.value = validationRef.value.validate();
      if (Object.keys(errors.value).length > 0) {
        return;
      }
      if (enablerecaptcha == 1) {
        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
          alertMessage.value = 'Failed to get reCAPTCHA.';
          return;
        }
        form.data().recaptchaResponse = recaptchaResponse;
      }

      try {
        let response;
        const requestData = { ...form.data() };
        response = await axios.post('/single-landingpage/contactmessage', requestData);

        if (response.status === 201) {
          successMessage.value = 'Message envoy\u00e9 avec succ\u00e8s.';
          form.reset();
        } else {
          alertMessage.value = '\u00c9chec de l\'envoi du message.';
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          errors.value = error.response.data.errors;
        } else {
          console.error('Error saving Message:', error);
          alertMessage.value = '\u00c9chec de l\'envoi du message.';
        }
      }
    };

    return {
      Pagination,
      form,
      successMessage,
      alertMessage,
      handleSubmit,
      dismissMessage,
      validationRules,
      validationRef,
      errors,
      recaptchaKey: window.recaptchaKey,
      enablerecaptcha: window.enablerecaptcha
    };
  },
  mounted() {
    window.myRecaptchaMethod = this.myRecaptchaMethod;
    this.loadRecaptcha();

    // Scroll handler for sticky nav
    window.addEventListener('scroll', () => {
      this.scrolled = window.scrollY > 60;
    });

    // Close lang dropdown on outside click
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.me-lang-dropdown')) {
        this.langDropdownOpen = false;
      }
    });

    this.startTaxiAnimation();
  },
  beforeUnmount() {
    clearInterval(this.taxiTimer);
  },
  methods: {
    stripHtmlTags(content) {
      const parser = new DOMParser();
      const parsedContent = parser.parseFromString(content, 'text/html');
      return parsedContent.body.textContent || "";
    },
    loadRecaptcha() {
      const script = document.createElement('script');
      script.src = 'https://www.google.com/recaptcha/api.js';
      script.async = true;
      script.defer = true;
      document.head.appendChild(script);
    },
    myRecaptchaMethod(response) {
      this.recaptchaToken = response;
    },
    startTaxiAnimation() {
      this.taxiTimer = setInterval(() => {
        this.activeStep += this.direction;
        if (this.activeStep === this.maxStep) {
          this.direction = -1;
        }
        if (this.activeStep === 0) {
          this.direction = 1;
        }
      }, 2000);
    },
    scrollTo(id) {
      this.mobileMenuOpen = false;
      const el = document.getElementById(id);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    },
    headerLogoUrl() {
      if (!this.header) return '';
      const selectedHeader = this.header.find(h => h.locale === this.selectedLocale);
      return selectedHeader ? selectedHeader.header_logo_url : (this.header[0] ? this.header[0].header_logo_url : '');
    },
    changeLocale(locale) {
      this.selectedLocale = locale;
      this.langDropdownOpen = false;
      window.location.href = `?locale=${this.selectedLocale}`;
    },
    toggleLangDropdown() {
      this.langDropdownOpen = !this.langDropdownOpen;
    },
  }
};
</script>

<template>
  <div class="me-landing">
    <!-- ============================================================ -->
    <!-- NAVIGATION -->
    <!-- ============================================================ -->
    <nav class="me-nav" :class="{ 'me-nav--scrolled': scrolled }">
      <div class="me-container me-nav__inner">
        <a href="/" class="me-nav__logo">
          <img :src="headerLogoUrl()" alt="Merci E" class="me-nav__logo-img" />
        </a>

        <!-- Desktop links -->
        <ul class="me-nav__links">
          <li><a @click.prevent="scrollTo('hero')" href="#hero">Accueil</a></li>
          <li><a @click.prevent="scrollTo('services')" href="#services">Services</a></li>
          <li><a @click.prevent="scrollTo('how-it-works')" href="#how-it-works">Comment \u00e7a marche</a></li>
          <li><a @click.prevent="scrollTo('screenshots')" href="#screenshots">Captures</a></li>
          <li><a @click.prevent="scrollTo('why-us')" href="#why-us">Pourquoi nous</a></li>
          <li><a @click.prevent="scrollTo('about')" href="#about">\u00c0 propos</a></li>
          <li><a @click.prevent="scrollTo('contact')" href="#contact">Contact</a></li>
        </ul>

        <div class="me-nav__actions">
          <!-- Language switcher -->
          <div class="me-lang-dropdown" v-if="locales">
            <button class="me-lang-dropdown__toggle" @click="toggleLangDropdown">
              <i class="ri-translate-2"></i>
            </button>
            <div class="me-lang-dropdown__menu" v-show="langDropdownOpen">
              <a
                v-for="(language, locale) in locales"
                :key="locale"
                href="javascript:void(0);"
                class="me-lang-dropdown__item"
                :class="{ 'me-lang-dropdown__item--active': selectedLocale === locale }"
                @click="changeLocale(locale)">
                {{ language }}
                <i v-if="selectedLocale === locale" class="ri-check-line"></i>
              </a>
            </div>
          </div>

          <a href="/mi-admin" class="me-btn me-btn--outline me-btn--sm">
            <i class="ri-dashboard-line"></i> Dashboard
          </a>
        </div>

        <!-- Mobile hamburger -->
        <button class="me-nav__hamburger" @click="mobileMenuOpen = !mobileMenuOpen">
          <i :class="mobileMenuOpen ? 'ri-close-line' : 'ri-menu-line'"></i>
        </button>
      </div>

      <!-- Mobile menu -->
      <div class="me-nav__mobile" :class="{ 'me-nav__mobile--open': mobileMenuOpen }">
        <a @click.prevent="scrollTo('hero')" href="#">Accueil</a>
        <a @click.prevent="scrollTo('services')" href="#">Services</a>
        <a @click.prevent="scrollTo('how-it-works')" href="#">Comment \u00e7a marche</a>
        <a @click.prevent="scrollTo('screenshots')" href="#">Captures</a>
        <a @click.prevent="scrollTo('why-us')" href="#">Pourquoi nous</a>
        <a @click.prevent="scrollTo('about')" href="#">\u00c0 propos</a>
        <a @click.prevent="scrollTo('contact')" href="#">Contact</a>
        <div class="me-nav__mobile-actions">
          <a href="/mi-admin" class="me-btn me-btn--primary me-btn--sm" style="width:100%;text-align:center;">
            <i class="ri-dashboard-line"></i> Dashboard
          </a>
          <div v-if="locales" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
            <a
              v-for="(language, locale) in locales"
              :key="locale"
              href="javascript:void(0);"
              class="me-btn me-btn--sm"
              :class="selectedLocale === locale ? 'me-btn--primary' : 'me-btn--outline'"
              @click="changeLocale(locale)"
              style="font-size:13px;">
              {{ language }}
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- ============================================================ -->
    <!-- HERO -->
    <!-- ============================================================ -->
    <section class="me-hero" id="hero">
      <div class="me-container">
        <div class="me-hero__content">
          <div class="me-hero__badge">
            <i class="ri-map-pin-2-fill"></i> Bafoussam, Cameroun
          </div>
          <h1 class="me-hero__title">Merci E</h1>
          <p class="me-hero__subtitle">
            {{ stripHtmlTags(singlelandingpage.hero_para) }}
          </p>
          <div class="me-hero__actions">
            <a :href="singlelandingpage.download_user_link_android" target="_blank" class="me-btn me-btn--primary me-btn--lg">
              <i class="ri-google-play-fill"></i> T\u00e9l\u00e9charger
            </a>
            <a @click.prevent="scrollTo('contact')" href="#contact" class="me-btn me-btn--outline-dark me-btn--lg">
              <i class="ri-mail-send-line"></i> Nous contacter
            </a>
          </div>
        </div>
        <div class="me-hero__phones">
          <div class="me-hero__phone me-hero__phone--back">
            <img src="/landing/screenshots/user-booking.png" alt="R\u00e9servation" />
          </div>
          <div class="me-hero__phone me-hero__phone--front">
            <img src="/landing/screenshots/user-home.png" alt="Accueil" />
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- SERVICES -->
    <!-- ============================================================ -->
    <section class="me-section" id="services">
      <div class="me-container">
        <div class="me-section__header">
          <h2 class="me-section__title">{{ singlelandingpage.adv_title }}</h2>
          <div class="me-section__bar"></div>
          <p class="me-section__subtitle">{{ stripHtmlTags(singlelandingpage.adv_para) }}</p>
        </div>

        <div class="me-services__grid">
          <div class="me-services__swiper">
            <swiper
              class="rounded"
              :loop="true"
              :modules="[Autoplay]"
              :centeredSlides="true"
              :slides-per-view="3"
              :space-between="24"
              :autoplay="{ delay: 3000, disableOnInteraction: false }"
              :breakpoints="{
                320: { slidesPerView: 1, spaceBetween: 16 },
                640: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 24 },
                1440: { slidesPerView: 3, spaceBetween: 28 }
              }">
              <swiper-slide>
                <div class="me-service-card">
                  <div class="me-service-card__icon"><i class="ri-taxi-line"></i></div>
                  <h4 class="me-service-card__title">{{ singlelandingpage.adv_box1_title }}</h4>
                  <p class="me-service-card__text">{{ stripHtmlTags(singlelandingpage.adv_box1_para) }}</p>
                  <div class="me-service-card__media" v-if="singlelandingpage.adv_box1_img_url">
                    <video autoplay muted loop playsinline>
                      <source :src="singlelandingpage.adv_box1_img_url" type="video/mp4">
                    </video>
                  </div>
                </div>
              </swiper-slide>
              <swiper-slide>
                <div class="me-service-card">
                  <div class="me-service-card__icon"><i class="ri-map-pin-line"></i></div>
                  <h4 class="me-service-card__title">{{ singlelandingpage.adv_box2_title }}</h4>
                  <p class="me-service-card__text">{{ stripHtmlTags(singlelandingpage.adv_box2_para) }}</p>
                  <div class="me-service-card__media" v-if="singlelandingpage.adv_box2_img_url">
                    <video autoplay muted loop playsinline>
                      <source :src="singlelandingpage.adv_box2_img_url" type="video/mp4">
                    </video>
                  </div>
                </div>
              </swiper-slide>
              <swiper-slide>
                <div class="me-service-card">
                  <div class="me-service-card__icon"><i class="ri-group-line"></i></div>
                  <h4 class="me-service-card__title">{{ singlelandingpage.adv_box3_title }}</h4>
                  <p class="me-service-card__text">{{ stripHtmlTags(singlelandingpage.adv_box3_para) }}</p>
                  <div class="me-service-card__media" v-if="singlelandingpage.adv_box3_img_url">
                    <video autoplay muted loop playsinline>
                      <source :src="singlelandingpage.adv_box3_img_url" type="video/mp4">
                    </video>
                  </div>
                </div>
              </swiper-slide>
              <swiper-slide>
                <div class="me-service-card">
                  <div class="me-service-card__icon"><i class="ri-truck-line"></i></div>
                  <h4 class="me-service-card__title">{{ singlelandingpage.adv_box4_title }}</h4>
                  <p class="me-service-card__text">{{ stripHtmlTags(singlelandingpage.adv_box4_para) }}</p>
                  <div class="me-service-card__media" v-if="singlelandingpage.adv_box4_img_url">
                    <video autoplay muted loop playsinline>
                      <source :src="singlelandingpage.adv_box4_img_url" type="video/mp4">
                    </video>
                  </div>
                </div>
              </swiper-slide>
              <swiper-slide>
                <div class="me-service-card">
                  <div class="me-service-card__icon"><i class="ri-shield-star-line"></i></div>
                  <h4 class="me-service-card__title">{{ singlelandingpage.adv_box5_title }}</h4>
                  <p class="me-service-card__text">{{ stripHtmlTags(singlelandingpage.adv_box5_para) }}</p>
                  <div class="me-service-card__media" v-if="singlelandingpage.adv_box5_img_url">
                    <video autoplay muted loop playsinline>
                      <source :src="singlelandingpage.adv_box5_img_url" type="video/mp4">
                    </video>
                  </div>
                </div>
              </swiper-slide>
            </swiper>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- HOW IT WORKS -->
    <!-- ============================================================ -->
    <section class="me-section me-section--dark" id="how-it-works">
      <div class="me-container">
        <div class="me-section__header">
          <h2 class="me-section__title" style="color:#fff;">{{ singlelandingpage.app_works_title }}</h2>
          <div class="me-section__bar"></div>
          <p class="me-section__subtitle" style="color:rgba(255,255,255,0.7);">{{ stripHtmlTags(singlelandingpage.app_works_para) }}</p>
        </div>

        <!-- Toggle -->
        <div class="me-toggle">
          <button
            @click="userType = 'user'"
            class="me-toggle__btn"
            :class="{ 'me-toggle__btn--active': userType === 'user' }">
            <i class="ri-user-3-line"></i> {{ singlelandingpage.app_works_user_title }}
          </button>
          <button
            @click="userType = 'driver'"
            class="me-toggle__btn"
            :class="{ 'me-toggle__btn--active': userType === 'driver' }">
            <i class="ri-steering-2-line"></i> {{ singlelandingpage.app_works_driver_title }}
          </button>
        </div>

        <div class="me-hiw__layout">
          <!-- Phone -->
          <div class="me-hiw__phone-col">
            <div class="me-phone-frame">
              <img
                v-if="userType === 'user'"
                src="/landing/screenshots/user-home.png"
                alt="User App" />
              <img
                v-else
                src="/landing/screenshots/driver-home.png"
                alt="Driver App" />
            </div>
          </div>

          <!-- Steps -->
          <div class="me-hiw__steps-col">
            <!-- User steps -->
            <template v-if="userType === 'user'">
              <div class="me-step" :class="{ 'me-step--active': activeStep === 0 }">
                <div class="me-step__number">01</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.user_box1_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.user_box1_para) }}</p>
                </div>
              </div>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 1 }">
                <div class="me-step__number">02</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.user_box2_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.user_box2_para) }}</p>
                </div>
              </div>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 2 }">
                <div class="me-step__number">03</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.user_box3_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.user_box3_para) }}</p>
                </div>
              </div>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 3 }">
                <div class="me-step__number">04</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.user_box4_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.user_box4_para) }}</p>
                </div>
              </div>
            </template>

            <!-- Driver steps -->
            <template v-else>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 0 }">
                <div class="me-step__number">01</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.driver_box1_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.driver_box1_para) }}</p>
                </div>
              </div>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 1 }">
                <div class="me-step__number">02</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.driver_box2_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.driver_box2_para) }}</p>
                </div>
              </div>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 2 }">
                <div class="me-step__number">03</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.driver_box3_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.driver_box3_para) }}</p>
                </div>
              </div>
              <div class="me-step" :class="{ 'me-step--active': activeStep === 3 }">
                <div class="me-step__number">04</div>
                <div class="me-step__content">
                  <h4>{{ singlelandingpage.driver_box4_title }}</h4>
                  <p>{{ stripHtmlTags(singlelandingpage.driver_box4_para) }}</p>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- SCREENSHOTS -->
    <!-- ============================================================ -->
    <section class="me-section" id="screenshots">
      <div class="me-container">
        <div class="me-section__header">
          <h2 class="me-section__title">D\u00e9couvrez l'application</h2>
          <div class="me-section__bar"></div>
          <p class="me-section__subtitle">Explorez les fonctionnalit\u00e9s de Merci E \u00e0 travers nos applications passager et chauffeur.</p>
        </div>

        <div class="me-toggle" style="margin-bottom:40px;">
          <button
            @click="screenshotTab = 'user'"
            class="me-toggle__btn me-toggle__btn--light"
            :class="{ 'me-toggle__btn--active': screenshotTab === 'user' }">
            <i class="ri-user-3-line"></i> Passager
          </button>
          <button
            @click="screenshotTab = 'driver'"
            class="me-toggle__btn me-toggle__btn--light"
            :class="{ 'me-toggle__btn--active': screenshotTab === 'driver' }">
            <i class="ri-steering-2-line"></i> Chauffeur
          </button>
        </div>

        <!-- User Screenshots -->
        <div v-if="screenshotTab === 'user'">
          <swiper
            :loop="true"
            :modules="[Autoplay, Pagination]"
            :centeredSlides="true"
            :slides-per-view="3"
            :space-between="24"
            :autoplay="{ delay: 3000, disableOnInteraction: false }"
            :pagination="{ clickable: true, el: '.me-swiper-pagination-user' }"
            :breakpoints="{
              320: { slidesPerView: 1.3, spaceBetween: 16 },
              640: { slidesPerView: 2.2, spaceBetween: 20 },
              768: { slidesPerView: 2.5, spaceBetween: 22 },
              1024: { slidesPerView: 3.5, spaceBetween: 24 },
              1440: { slidesPerView: 4, spaceBetween: 28 }
            }">
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/user-home.png" alt="Accueil" /></div>
                <span class="me-screenshot__label">Accueil</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/user-booking.png" alt="R\u00e9servation" /></div>
                <span class="me-screenshot__label">R\u00e9servation</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/user-history.png" alt="Historique" /></div>
                <span class="me-screenshot__label">Historique</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/user-wallet.png" alt="Portefeuille" /></div>
                <span class="me-screenshot__label">Portefeuille</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/user-account.png" alt="Mon compte" /></div>
                <span class="me-screenshot__label">Mon compte</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/user-login.png" alt="Connexion" /></div>
                <span class="me-screenshot__label">Connexion</span>
              </div>
            </swiper-slide>
          </swiper>
          <div class="me-swiper-pagination-user"></div>
        </div>

        <!-- Driver Screenshots -->
        <div v-else>
          <swiper
            :loop="true"
            :modules="[Autoplay, Pagination]"
            :centeredSlides="true"
            :slides-per-view="3"
            :space-between="24"
            :autoplay="{ delay: 3000, disableOnInteraction: false }"
            :pagination="{ clickable: true, el: '.me-swiper-pagination-driver' }"
            :breakpoints="{
              320: { slidesPerView: 1.3, spaceBetween: 16 },
              640: { slidesPerView: 2.2, spaceBetween: 20 },
              768: { slidesPerView: 2.5, spaceBetween: 22 },
              1024: { slidesPerView: 3.5, spaceBetween: 24 },
              1440: { slidesPerView: 4, spaceBetween: 28 }
            }">
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/driver-home.png" alt="Tableau de bord" /></div>
                <span class="me-screenshot__label">Tableau de bord</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/driver-ride.png" alt="Course en cours" /></div>
                <span class="me-screenshot__label">Course en cours</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/driver-earnings.png" alt="Gains" /></div>
                <span class="me-screenshot__label">Gains</span>
              </div>
            </swiper-slide>
            <swiper-slide>
              <div class="me-screenshot">
                <div class="me-screenshot__frame"><img src="/landing/screenshots/driver-account.png" alt="Compte chauffeur" /></div>
                <span class="me-screenshot__label">Mon compte</span>
              </div>
            </swiper-slide>
          </swiper>
          <div class="me-swiper-pagination-driver"></div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- WHY CHOOSE US -->
    <!-- ============================================================ -->
    <section class="me-section me-section--light" id="why-us">
      <div class="me-container">
        <div class="me-section__header">
          <h2 class="me-section__title">{{ singlelandingpage.why_choose_title }}</h2>
          <div class="me-section__bar"></div>
        </div>

        <div class="me-why__layout">
          <div class="me-why__col">
            <div class="me-why-card">
              <div class="me-why-card__num">01</div>
              <div>
                <h4 class="me-why-card__title">{{ singlelandingpage.why_choose_box1_title }}</h4>
                <p class="me-why-card__text">{{ stripHtmlTags(singlelandingpage.why_choose_box1_para) }}</p>
              </div>
            </div>
            <div class="me-why-card">
              <div class="me-why-card__num">03</div>
              <div>
                <h4 class="me-why-card__title">{{ singlelandingpage.why_choose_box3_title }}</h4>
                <p class="me-why-card__text">{{ stripHtmlTags(singlelandingpage.why_choose_box3_para) }}</p>
              </div>
            </div>
          </div>

          <div class="me-why__phone-col">
            <div class="me-phone-frame me-phone-frame--shadow">
              <img src="/landing/screenshots/user-booking.png" alt="App Screenshot" />
            </div>
          </div>

          <div class="me-why__col">
            <div class="me-why-card">
              <div class="me-why-card__num">02</div>
              <div>
                <h4 class="me-why-card__title">{{ singlelandingpage.why_choose_box2_title }}</h4>
                <p class="me-why-card__text">{{ stripHtmlTags(singlelandingpage.why_choose_box2_para) }}</p>
              </div>
            </div>
            <div class="me-why-card">
              <div class="me-why-card__num">04</div>
              <div>
                <h4 class="me-why-card__title">{{ singlelandingpage.why_choose_box4_title }}</h4>
                <p class="me-why-card__text">{{ stripHtmlTags(singlelandingpage.why_choose_box4_para) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- ABOUT -->
    <!-- ============================================================ -->
    <section class="me-section" id="about">
      <div class="me-container">
        <div class="me-about__layout">
          <div class="me-about__img-col">
            <img :src="singlelandingpage.about_img_url" alt="About" class="me-about__img" />
          </div>
          <div class="me-about__text-col">
            <span class="me-label">\u00c0 propos</span>
            <h2 class="me-about__title">
              <span>{{ singlelandingpage.about_title_1 }}</span>
              <span class="me-text-accent"> {{ singlelandingpage.about_title_2 }}</span>
            </h2>
            <p class="me-about__text">{{ stripHtmlTags(singlelandingpage.about_para) }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CEO Section -->
    <section class="me-section me-section--light">
      <div class="me-container">
        <div class="me-about__layout me-about__layout--reverse">
          <div class="me-about__text-col">
            <span class="me-label">Leadership</span>
            <h2 class="me-about__title">
              <span>{{ singlelandingpage.ceo_title_1 }}</span>
              <span class="me-text-accent"> {{ singlelandingpage.ceo_title_2 }}</span>
            </h2>
            <p class="me-about__text">{{ stripHtmlTags(singlelandingpage.ceo_para) }}</p>
          </div>
          <div class="me-about__img-col">
            <img :src="singlelandingpage.ceo_img_url" alt="CEO" class="me-about__img" />
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- DOWNLOAD -->
    <!-- ============================================================ -->
    <section class="me-download" id="download">
      <div class="me-container">
        <div class="me-download__card">
          <div class="me-download__content">
            <h2 class="me-download__title">{{ singlelandingpage.download_title }}</h2>
            <p class="me-download__text">{{ stripHtmlTags(singlelandingpage.download_para) }}</p>

            <div class="me-download__group">
              <span class="me-download__group-label">Application Passager</span>
              <div class="me-download__buttons">
                <a :href="singlelandingpage.download_user_link_apple" target="_blank" class="me-btn me-btn--white me-btn--sm">
                  <i class="ri-apple-fill"></i> App Store
                </a>
                <a :href="singlelandingpage.download_user_link_android" target="_blank" class="me-btn me-btn--white me-btn--sm">
                  <i class="ri-google-play-fill"></i> Play Store
                </a>
              </div>
            </div>

            <div class="me-download__group">
              <span class="me-download__group-label">Application Chauffeur</span>
              <div class="me-download__buttons">
                <a :href="singlelandingpage.download_driver_link_apple" target="_blank" class="me-btn me-btn--white me-btn--sm">
                  <i class="ri-apple-fill"></i> App Store
                </a>
                <a :href="singlelandingpage.download_driver_link_android" target="_blank" class="me-btn me-btn--white me-btn--sm">
                  <i class="ri-google-play-fill"></i> Play Store
                </a>
              </div>
            </div>
          </div>
          <div class="me-download__phones">
            <div class="me-phone-frame me-phone-frame--tilt-left">
              <img src="/landing/screenshots/user-wallet.png" alt="Wallet" />
            </div>
            <div class="me-phone-frame me-phone-frame--tilt-right">
              <img src="/landing/screenshots/driver-earnings.png" alt="Earnings" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- CONTACT -->
    <!-- ============================================================ -->
    <section class="me-section me-section--contact" id="contact">
      <div class="me-container">
        <div class="me-section__header">
          <h2 class="me-section__title">{{ singlelandingpage.contact_heading }}</h2>
          <div class="me-section__bar"></div>
          <p class="me-section__subtitle">{{ singlelandingpage.contact_para }}</p>
        </div>

        <!-- Success / error messages -->
        <div v-if="successMessage" class="me-alert me-alert--success" @click="dismissMessage">
          <i class="ri-check-double-line"></i> {{ successMessage }}
        </div>
        <div v-if="alertMessage" class="me-alert me-alert--error" @click="dismissMessage">
          <i class="ri-error-warning-line"></i> {{ alertMessage }}
        </div>

        <div class="me-contact__layout">
          <!-- Contact info -->
          <div class="me-contact__info">
            <div class="me-contact-item">
              <div class="me-contact-item__icon"><i class="ri-map-pin-2-fill"></i></div>
              <div>
                <strong>{{ singlelandingpage.contact_address_title }}</strong>
                <p>{{ stripHtmlTags(singlelandingpage.contact_address) }}</p>
              </div>
            </div>
            <div class="me-contact-item">
              <div class="me-contact-item__icon"><i class="ri-phone-fill"></i></div>
              <div>
                <strong>{{ singlelandingpage.contact_phone_title }}</strong>
                <p>{{ singlelandingpage.contact_phone }}</p>
              </div>
            </div>
            <div class="me-contact-item">
              <div class="me-contact-item__icon"><i class="ri-mail-fill"></i></div>
              <div>
                <strong>{{ singlelandingpage.contact_mail_title }}</strong>
                <p>{{ singlelandingpage.contact_mail }}</p>
              </div>
            </div>
            <div class="me-contact-item">
              <div class="me-contact-item__icon"><i class="ri-global-line"></i></div>
              <div>
                <strong>{{ singlelandingpage.contact_web_title }}</strong>
                <p><a :href="singlelandingpage.contact_web" target="_blank" class="me-text-accent">{{ singlelandingpage.contact_web }}</a></p>
              </div>
            </div>
          </div>

          <!-- Contact form -->
          <div class="me-contact__form-wrap">
            <form @submit.prevent="handleSubmit" class="me-contact-form">
              <FormValidation :form="form" :rules="validationRules" ref="validationRef">
                <div class="me-form-group">
                  <input
                    name="name" id="name" type="text"
                    class="me-input"
                    :placeholder="singlelandingpage.form_name"
                    v-model="form.name" />
                  <span v-for="(error, index) in errors.name" :key="index" class="me-error">{{ error }}</span>
                </div>
                <div class="me-form-group">
                  <input
                    name="email" id="email" type="email"
                    class="me-input"
                    :placeholder="singlelandingpage.form_mail"
                    v-model="form.mail" />
                  <span v-for="(error, index) in errors.mail" :key="index" class="me-error">{{ error }}</span>
                </div>
                <div class="me-form-group">
                  <input
                    name="subject" id="subject" type="text"
                    class="me-input"
                    :placeholder="singlelandingpage.form_subject"
                    v-model="form.subject" />
                  <span v-for="(error, index) in errors.subject" :key="index" class="me-error">{{ error }}</span>
                </div>
                <div class="me-form-group">
                  <textarea
                    name="comments" id="comments" rows="4"
                    class="me-input me-textarea"
                    :placeholder="singlelandingpage.form_message"
                    v-model="form.comments"></textarea>
                  <span v-for="(error, index) in errors.comments" :key="index" class="me-error">{{ error }}</span>
                </div>
                <div class="me-form-group" v-if="enablerecaptcha == 1">
                  <div class="g-recaptcha" :data-sitekey="recaptchaKey" data-callback="myRecaptchaMethod"></div>
                </div>
                <button type="submit" class="me-btn me-btn--primary me-btn--lg" style="width:100%;">
                  <i class="ri-send-plane-fill"></i> {{ singlelandingpage.form_btn }}
                </button>
              </FormValidation>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- FOOTER -->
    <!-- ============================================================ -->
    <footer class="me-footer">
      <div class="me-container">
        <div class="me-footer__top">
          <div class="me-footer__brand">
            <img :src="headerLogoUrl()" alt="Merci E" class="me-footer__logo" />
            <p class="me-footer__tagline">La mobilit\u00e9 simplifi\u00e9e au Cameroun.</p>
          </div>
          <div class="me-footer__links">
            <h5>Navigation</h5>
            <a @click.prevent="scrollTo('hero')" href="#">Accueil</a>
            <a @click.prevent="scrollTo('services')" href="#">Services</a>
            <a @click.prevent="scrollTo('how-it-works')" href="#">Comment \u00e7a marche</a>
            <a @click.prevent="scrollTo('about')" href="#">\u00c0 propos</a>
          </div>
          <div class="me-footer__links">
            <h5>Application</h5>
            <a @click.prevent="scrollTo('screenshots')" href="#">Captures d'\u00e9cran</a>
            <a @click.prevent="scrollTo('download')" href="#">T\u00e9l\u00e9charger</a>
            <a @click.prevent="scrollTo('contact')" href="#">Contact</a>
            <a href="/mi-admin">Dashboard</a>
          </div>
          <div class="me-footer__links">
            <h5>Contact</h5>
            <p>{{ singlelandingpage.contact_phone }}</p>
            <p>{{ singlelandingpage.contact_mail }}</p>
            <p>{{ stripHtmlTags(singlelandingpage.contact_address) }}</p>
          </div>
        </div>
        <div class="me-footer__bottom">
          <p>&copy; {{ new Date().getFullYear() }} Merci E. Tous droits r\u00e9serv\u00e9s.</p>
          <p class="me-footer__credit">Dev by Chris Skyler</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
/* ============================================================
   MERCI E - Modern Landing Page
   Primary: #0050a0 | Background: #ffffff
   ============================================================ */

/* --- Reset & Base --- */
.me-landing {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1a1a2e;
  overflow-x: hidden;
  background: #ffffff;
  line-height: 1.6;
}
.me-landing *, .me-landing *::before, .me-landing *::after {
  box-sizing: border-box;
}
.me-landing img { max-width: 100%; height: auto; }
.me-landing a { text-decoration: none; color: inherit; }
.me-landing ul { list-style: none; margin: 0; padding: 0; }

/* --- Container --- */
.me-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

/* --- Buttons --- */
.me-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  border-radius: 12px;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.25s ease;
  font-size: 15px;
  padding: 12px 24px;
  text-decoration: none;
  line-height: 1.4;
}
.me-btn--sm { padding: 8px 18px; font-size: 14px; border-radius: 10px; }
.me-btn--lg { padding: 14px 32px; font-size: 16px; }
.me-btn--primary {
  background: #0050a0;
  color: #fff;
  border-color: #0050a0;
}
.me-btn--primary:hover {
  background: #003d7a;
  border-color: #003d7a;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,80,160,0.3);
}
.me-btn--outline {
  background: transparent;
  color: #0050a0;
  border-color: #0050a0;
}
.me-btn--outline:hover {
  background: #0050a0;
  color: #fff;
}
.me-btn--outline-dark {
  background: transparent;
  color: #1a1a2e;
  border-color: #d0d5dd;
}
.me-btn--outline-dark:hover {
  background: #1a1a2e;
  color: #fff;
  border-color: #1a1a2e;
}
.me-btn--white {
  background: rgba(255,255,255,0.15);
  color: #fff;
  border-color: rgba(255,255,255,0.35);
}
.me-btn--white:hover {
  background: #fff;
  color: #0050a0;
  border-color: #fff;
}

/* --- Label tag --- */
.me-label {
  display: inline-block;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: #0050a0;
  margin-bottom: 12px;
}
.me-text-accent { color: #0050a0; }

/* --- Section --- */
.me-section {
  padding: 100px 0;
}
.me-section--light {
  background: #f7f9fc;
}
.me-section--dark {
  background: linear-gradient(160deg, #0d1a2e 0%, #132240 60%, #0a2e5c 100%);
}
.me-section--contact {
  background: #f7f9fc;
}
.me-section__header {
  text-align: center;
  margin-bottom: 56px;
  max-width: 640px;
  margin-left: auto;
  margin-right: auto;
}
.me-section__title {
  font-size: 36px;
  font-weight: 800;
  margin: 0 0 12px 0;
  color: #1a1a2e;
  letter-spacing: -0.5px;
}
.me-section__bar {
  width: 60px;
  height: 4px;
  background: linear-gradient(90deg, #0050a0, #1a72cc);
  border-radius: 2px;
  margin: 0 auto 16px;
}
.me-section__subtitle {
  font-size: 16px;
  color: #6b7280;
  margin: 0;
  line-height: 1.7;
}

/* ============================================================
   NAVIGATION
   ============================================================ */
.me-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255,255,255,0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(0,0,0,0.06);
  transition: all 0.3s ease;
}
.me-nav--scrolled {
  background: rgba(255,255,255,0.98);
  box-shadow: 0 2px 24px rgba(0,0,0,0.08);
}
.me-nav__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 72px;
}
.me-nav__logo-img {
  height: 40px;
  width: auto;
}
.me-nav__links {
  display: flex;
  align-items: center;
  gap: 32px;
}
.me-nav__links a {
  font-size: 14px;
  font-weight: 500;
  color: #4b5563;
  transition: color 0.2s;
  cursor: pointer;
}
.me-nav__links a:hover {
  color: #0050a0;
}
.me-nav__actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
.me-nav__hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 28px;
  color: #1a1a2e;
  cursor: pointer;
}
.me-nav__mobile {
  display: none;
  flex-direction: column;
  padding: 16px 24px 24px;
  background: #fff;
  border-top: 1px solid #e5e7eb;
}
.me-nav__mobile a {
  padding: 12px 0;
  font-size: 15px;
  font-weight: 500;
  color: #374151;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
}
.me-nav__mobile a:hover { color: #0050a0; }
.me-nav__mobile-actions {
  padding-top: 16px;
}

/* Language dropdown */
.me-lang-dropdown {
  position: relative;
}
.me-lang-dropdown__toggle {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s;
}
.me-lang-dropdown__toggle:hover {
  border-color: #0050a0;
  color: #0050a0;
}
.me-lang-dropdown__menu {
  position: absolute;
  top: 48px;
  right: 0;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  min-width: 160px;
  padding: 8px;
  z-index: 100;
}
.me-lang-dropdown__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  transition: background 0.15s;
}
.me-lang-dropdown__item:hover {
  background: #f3f4f6;
}
.me-lang-dropdown__item--active {
  background: #eef4ff;
  color: #0050a0;
  font-weight: 600;
}

/* ============================================================
   HERO
   ============================================================ */
.me-hero {
  padding: 140px 0 80px;
  background: linear-gradient(170deg, #ffffff 0%, #f0f5fc 100%);
  position: relative;
  overflow: hidden;
}
.me-hero::before {
  content: "";
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(0,80,160,0.06), transparent 70%);
  top: -200px;
  right: -100px;
  pointer-events: none;
}
.me-hero .me-container {
  display: flex;
  align-items: center;
  gap: 60px;
}
.me-hero__content {
  flex: 1;
  min-width: 0;
}
.me-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #eef4ff;
  color: #0050a0;
  padding: 6px 16px;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 24px;
  border: 1px solid rgba(0,80,160,0.12);
}
.me-hero__title {
  font-size: 72px;
  font-weight: 900;
  line-height: 1;
  color: #1a1a2e;
  margin: 0 0 20px 0;
  letter-spacing: -3px;
}
.me-hero__subtitle {
  font-size: 18px;
  color: #6b7280;
  line-height: 1.7;
  max-width: 520px;
  margin: 0 0 36px 0;
}
.me-hero__actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}
.me-hero__phones {
  flex-shrink: 0;
  position: relative;
  width: 380px;
  height: 480px;
}
.me-hero__phone {
  position: absolute;
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0,0,0,0.12);
  border: 3px solid #e5e7eb;
  background: #fff;
}
.me-hero__phone img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.me-hero__phone--front {
  width: 220px;
  z-index: 2;
  left: 0;
  top: 20px;
}
.me-hero__phone--back {
  width: 200px;
  z-index: 1;
  right: 0;
  top: 80px;
  opacity: 0.85;
}

/* ============================================================
   SERVICES
   ============================================================ */
.me-service-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  padding: 32px 24px;
  text-align: center;
  transition: all 0.3s ease;
  height: 100%;
}
.me-service-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 48px rgba(0,80,160,0.1);
  border-color: #0050a0;
}
.me-service-card__icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: #eef4ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 28px;
  color: #0050a0;
}
.me-service-card__title {
  font-size: 18px;
  font-weight: 700;
  margin: 0 0 8px 0;
  color: #1a1a2e;
}
.me-service-card__text {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 16px 0;
  line-height: 1.6;
}
.me-service-card__media {
  border-radius: 12px;
  overflow: hidden;
  background: #f3f4f6;
  aspect-ratio: 16/10;
}
.me-service-card__media video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Swiper service overrides */
.me-services__swiper .swiper-slide {
  opacity: 0.65;
  transform: scale(0.92);
  transition: all 0.4s ease;
  padding: 16px 0;
}
.me-services__swiper .swiper-slide-active {
  opacity: 1;
  transform: scale(1);
}

/* ============================================================
   HOW IT WORKS
   ============================================================ */
.me-toggle {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 56px;
}
.me-toggle__btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  border: 2px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.6);
  cursor: pointer;
  transition: all 0.25s;
}
.me-toggle__btn--active {
  background: #0050a0;
  color: #fff;
  border-color: #0050a0;
  box-shadow: 0 4px 16px rgba(0,80,160,0.4);
}
.me-toggle__btn--light {
  border-color: #d0d5dd;
  color: #6b7280;
  background: #fff;
}
.me-toggle__btn--light.me-toggle__btn--active {
  background: #0050a0;
  color: #fff;
  border-color: #0050a0;
}

.me-hiw__layout {
  display: flex;
  align-items: center;
  gap: 64px;
}
.me-hiw__phone-col {
  flex-shrink: 0;
}
.me-hiw__steps-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.me-step {
  display: flex;
  align-items: flex-start;
  gap: 20px;
  padding: 20px 24px;
  border-radius: 16px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.06);
  transition: all 0.4s ease;
}
.me-step--active {
  background: rgba(0,80,160,0.15);
  border-color: rgba(0,80,160,0.3);
  box-shadow: 0 8px 32px rgba(0,80,160,0.15);
}
.me-step__number {
  min-width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 16px;
  color: rgba(255,255,255,0.5);
  transition: all 0.4s;
}
.me-step--active .me-step__number {
  background: #0050a0;
  color: #fff;
  border-color: #0050a0;
  box-shadow: 0 4px 16px rgba(0,80,160,0.5);
}
.me-step__content h4 {
  font-size: 17px;
  font-weight: 600;
  color: rgba(255,255,255,0.85);
  margin: 0 0 6px 0;
}
.me-step--active .me-step__content h4 {
  color: #fff;
}
.me-step__content p {
  font-size: 14px;
  color: rgba(255,255,255,0.45);
  margin: 0;
  line-height: 1.6;
}
.me-step--active .me-step__content p {
  color: rgba(255,255,255,0.7);
}

/* ============================================================
   PHONE FRAME
   ============================================================ */
.me-phone-frame {
  width: 260px;
  border-radius: 32px;
  overflow: hidden;
  border: 4px solid #1a1a2e;
  background: #000;
  box-shadow: 0 24px 64px rgba(0,0,0,0.2);
}
.me-phone-frame img {
  display: block;
  width: 100%;
}
.me-phone-frame--shadow {
  box-shadow: 0 32px 72px rgba(0,80,160,0.15);
  border-color: #e5e7eb;
}

/* ============================================================
   SCREENSHOTS
   ============================================================ */
.me-screenshot {
  text-align: center;
  padding: 16px 0;
}
.me-screenshot__frame {
  width: 200px;
  margin: 0 auto;
  border-radius: 24px;
  overflow: hidden;
  border: 3px solid #e5e7eb;
  background: #fff;
  box-shadow: 0 12px 40px rgba(0,0,0,0.08);
  transition: all 0.3s;
}
.me-screenshot__frame:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 48px rgba(0,80,160,0.12);
  border-color: #0050a0;
}
.me-screenshot__frame img {
  display: block;
  width: 100%;
}
.me-screenshot__label {
  display: block;
  margin-top: 14px;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
}
.me-swiper-pagination-user,
.me-swiper-pagination-driver {
  text-align: center;
  margin-top: 24px;
}
.me-swiper-pagination-user .swiper-pagination-bullet,
.me-swiper-pagination-driver .swiper-pagination-bullet {
  background: #0050a0;
}

/* ============================================================
   WHY CHOOSE US
   ============================================================ */
.me-why__layout {
  display: flex;
  align-items: center;
  gap: 40px;
}
.me-why__col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 32px;
}
.me-why__phone-col {
  flex-shrink: 0;
  display: flex;
  justify-content: center;
}
.me-why-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}
.me-why-card__num {
  min-width: 52px;
  height: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, #0050a0, #003d7a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 18px;
  color: #fff;
  box-shadow: 0 6px 20px rgba(0,80,160,0.25);
  flex-shrink: 0;
}
.me-why-card__title {
  font-size: 17px;
  font-weight: 700;
  margin: 0 0 6px 0;
  color: #1a1a2e;
}
.me-why-card__text {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
  line-height: 1.6;
}

/* ============================================================
   ABOUT
   ============================================================ */
.me-about__layout {
  display: flex;
  align-items: center;
  gap: 64px;
}
.me-about__layout--reverse {
  flex-direction: row-reverse;
}
.me-about__img-col {
  flex: 1;
}
.me-about__img {
  width: 100%;
  max-width: 500px;
  border-radius: 24px;
  object-fit: cover;
  box-shadow: 0 24px 64px rgba(0,0,0,0.08);
}
.me-about__text-col {
  flex: 1;
}
.me-about__title {
  font-size: 36px;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 20px 0;
  color: #1a1a2e;
}
.me-about__text {
  font-size: 16px;
  line-height: 1.8;
  color: #4b5563;
  margin: 0;
}

/* ============================================================
   DOWNLOAD
   ============================================================ */
.me-download {
  padding: 80px 0;
}
.me-download__card {
  background: linear-gradient(135deg, #0d1a2e 0%, #0050a0 100%);
  border-radius: 28px;
  padding: 56px 64px;
  display: flex;
  align-items: center;
  gap: 56px;
  position: relative;
  overflow: hidden;
}
.me-download__card::before {
  content: "";
  position: absolute;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(255,255,255,0.06), transparent 70%);
  top: -150px;
  right: -100px;
  pointer-events: none;
}
.me-download__content {
  flex: 1;
}
.me-download__title {
  font-size: 36px;
  font-weight: 800;
  color: #fff;
  margin: 0 0 16px 0;
  line-height: 1.2;
}
.me-download__text {
  font-size: 16px;
  color: rgba(255,255,255,0.7);
  margin: 0 0 32px 0;
  max-width: 440px;
  line-height: 1.7;
}
.me-download__group {
  margin-bottom: 20px;
}
.me-download__group-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(255,255,255,0.45);
  margin-bottom: 10px;
}
.me-download__buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.me-download__phones {
  flex-shrink: 0;
  display: flex;
  gap: 16px;
  align-items: flex-end;
}
.me-phone-frame--tilt-left {
  width: 180px;
  transform: rotate(-5deg);
}
.me-phone-frame--tilt-right {
  width: 160px;
  transform: rotate(5deg) translateY(-20px);
}

/* ============================================================
   CONTACT
   ============================================================ */
.me-contact__layout {
  display: flex;
  gap: 48px;
  align-items: flex-start;
}
.me-contact__info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 28px;
}
.me-contact-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}
.me-contact-item__icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #eef4ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #0050a0;
  flex-shrink: 0;
}
.me-contact-item strong {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #1a1a2e;
  margin-bottom: 2px;
}
.me-contact-item p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
  line-height: 1.5;
}
.me-contact__form-wrap {
  flex: 1;
  max-width: 480px;
}
.me-contact-form {
  background: #fff;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 12px 48px rgba(0,0,0,0.06);
  border: 1px solid #e5e7eb;
}
.me-form-group {
  margin-bottom: 16px;
}
.me-input {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  font-size: 14px;
  color: #1a1a2e;
  background: #f9fafb;
  transition: all 0.2s;
  outline: none;
  font-family: inherit;
}
.me-input:focus {
  border-color: #0050a0;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(0,80,160,0.1);
}
.me-textarea {
  resize: vertical;
  min-height: 100px;
}
.me-error {
  display: block;
  font-size: 12px;
  color: #dc2626;
  margin-top: 4px;
}

/* Alerts */
.me-alert {
  max-width: 600px;
  margin: 0 auto 24px;
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}
.me-alert--success {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #a7f3d0;
}
.me-alert--error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

/* ============================================================
   FOOTER
   ============================================================ */
.me-footer {
  background: #0d1a2e;
  color: rgba(255,255,255,0.7);
  padding: 64px 0 0;
}
.me-footer__top {
  display: flex;
  gap: 48px;
  padding-bottom: 48px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.me-footer__brand {
  flex: 1.5;
}
.me-footer__logo {
  height: 36px;
  width: auto;
  margin-bottom: 12px;
}
.me-footer__tagline {
  font-size: 14px;
  color: rgba(255,255,255,0.5);
  margin: 0;
}
.me-footer__links {
  flex: 1;
}
.me-footer__links h5 {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 16px 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.me-footer__links a,
.me-footer__links p {
  display: block;
  font-size: 14px;
  color: rgba(255,255,255,0.5);
  margin-bottom: 10px;
  transition: color 0.2s;
  cursor: pointer;
}
.me-footer__links a:hover {
  color: #fff;
}
.me-footer__bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 0;
}
.me-footer__bottom p {
  font-size: 13px;
  color: rgba(255,255,255,0.4);
  margin: 0;
}
.me-footer__credit {
  font-weight: 600;
  color: rgba(255,255,255,0.55) !important;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
  .me-nav__links { display: none; }
  .me-nav__actions { display: none; }
  .me-nav__hamburger { display: block; }
  .me-nav__mobile--open { display: flex; }

  .me-hero .me-container {
    flex-direction: column;
    text-align: center;
  }
  .me-hero__subtitle { margin-left: auto; margin-right: auto; }
  .me-hero__actions { justify-content: center; }
  .me-hero__phones {
    width: 300px;
    height: 380px;
    margin: 0 auto;
  }
  .me-hero__phone--front { width: 180px; }
  .me-hero__phone--back { width: 160px; }
  .me-hero__title { font-size: 52px; }

  .me-hiw__layout {
    flex-direction: column;
    gap: 40px;
  }

  .me-why__layout {
    flex-direction: column;
    gap: 40px;
  }
  .me-why__phone-col { order: -1; }
  .me-why__col { width: 100%; }

  .me-about__layout,
  .me-about__layout--reverse {
    flex-direction: column;
    gap: 40px;
  }

  .me-download__card {
    flex-direction: column;
    padding: 40px 32px;
    gap: 40px;
  }
  .me-download__phones {
    justify-content: center;
  }

  .me-contact__layout {
    flex-direction: column;
    gap: 40px;
  }
  .me-contact__form-wrap {
    max-width: 100%;
    width: 100%;
  }

  .me-footer__top {
    flex-wrap: wrap;
    gap: 32px;
  }
  .me-footer__brand { flex: 1 1 100%; }
  .me-footer__links { flex: 1 1 calc(33% - 24px); }
}

@media (max-width: 640px) {
  .me-section { padding: 64px 0; }
  .me-section__title { font-size: 28px; }
  .me-hero { padding: 120px 0 60px; }
  .me-hero__title { font-size: 42px; letter-spacing: -2px; }
  .me-hero__phones {
    width: 260px;
    height: 340px;
  }
  .me-hero__phone--front { width: 160px; }
  .me-hero__phone--back { width: 140px; }

  .me-phone-frame { width: 200px; }
  .me-screenshot__frame { width: 170px; }

  .me-download__card { padding: 32px 24px; }
  .me-download__title { font-size: 28px; }
  .me-phone-frame--tilt-left { width: 150px; }
  .me-phone-frame--tilt-right { width: 130px; }

  .me-about__title { font-size: 28px; }
  .me-about__img { max-width: 100%; }

  .me-contact-form { padding: 24px; }

  .me-footer__links { flex: 1 1 100%; }
  .me-footer__bottom {
    flex-direction: column;
    gap: 8px;
    text-align: center;
  }
}

@media (max-width: 380px) {
  .me-hero__title { font-size: 36px; }
  .me-hero__phones {
    width: 220px;
    height: 300px;
  }
  .me-hero__phone--front { width: 140px; }
  .me-hero__phone--back { width: 120px; }
}
</style>
